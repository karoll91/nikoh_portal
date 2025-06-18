<?php
/**
 * Admin - Hisobotlar va statistika
 */

if (!$admin) {
    redirect('admin_login');
}

// Sana filtr
$start_date = sanitize($_GET['start_date'] ?? date('Y-m-01')); // Oy boshi
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d')); // Bugun
$report_type = sanitize($_GET['report_type'] ?? 'overview');

// Asosiy statistika
try {
    // Umumiy statistika
    $overview_stats = [
        'total_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE created_at BETWEEN ? AND ? ", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0,
        'nikoh_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE application_type = 'nikoh' AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0,
        'ajralish_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE application_type = 'ajralish' AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0,
        'completed_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'tugallandi' AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0,
        'pending_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status IN ('yangi', 'korib_chiqilmoqda') AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0,
        'rejected_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'rad_etildi' AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0,
        'total_payments' => fetchOne("SELECT COALESCE(SUM(payment_required), 0) as total FROM applications WHERE payment_status = 'tolandi' AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['total'] ?? 0,
        'new_users' => fetchOne("SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0
    ];

    // Kunlik statistika
    $daily_stats = fetchAll("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total,
            SUM(CASE WHEN application_type = 'nikoh' THEN 1 ELSE 0 END) as nikoh,
            SUM(CASE WHEN application_type = 'ajralish' THEN 1 ELSE 0 END) as ajralish,
            SUM(CASE WHEN status = 'tugallandi' THEN 1 ELSE 0 END) as completed
        FROM applications 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 30
    ", [$start_date, $end_date . ' 23:59:59']);

    // Status bo'yicha statistika
    $status_stats = fetchAll("
        SELECT 
            status,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as percentage
        FROM applications 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY status
        ORDER BY count DESC
    ", [$start_date, $end_date . ' 23:59:59']);

    // Oylik trend
    $monthly_trend = fetchAll("
        SELECT 
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            COUNT(*) as total,
            SUM(CASE WHEN application_type = 'nikoh' THEN 1 ELSE 0 END) as nikoh,
            SUM(CASE WHEN application_type = 'ajralish' THEN 1 ELSE 0 END) as ajralish,
            COALESCE(SUM(payment_required), 0) as total_payment
        FROM applications 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY year DESC, month DESC
        LIMIT 12
    ");

    // Eng faol foydalanuvchilar
    $active_users = fetchAll("
        SELECT 
            u.first_name, u.last_name, u.passport_series,
            COUNT(a.id) as application_count,
            MAX(a.created_at) as last_application
        FROM users u
        JOIN applications a ON u.id = a.applicant_id
        WHERE a.created_at BETWEEN ? AND ?
        GROUP BY u.id
        ORDER BY application_count DESC
        LIMIT 10
    ", [$start_date, $end_date . ' 23:59:59']);

    // Eng faol adminlar
    $active_admins = fetchAll("
        SELECT 
            ad.full_name, ad.position,
            COUNT(a.id) as reviewed_count,
            MAX(a.review_date) as last_review
        FROM admin_users ad
        JOIN applications a ON ad.id = a.reviewed_by
        WHERE a.review_date BETWEEN ? AND ?
        GROUP BY ad.id
        ORDER BY reviewed_count DESC
        LIMIT 10
    ", [$start_date, $end_date . ' 23:59:59']);

} catch (Exception $e) {
    $overview_stats = array_fill_keys(['total_applications', 'nikoh_applications', 'ajralish_applications', 'completed_applications', 'pending_applications', 'rejected_applications', 'total_payments', 'new_users'], 0);
    $daily_stats = [];
    $status_stats = [];
    $monthly_trend = [];
    $active_users = [];
    $active_admins = [];
}

// Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="hisobot_' . date('Y-m-d') . '.xls"');

    echo "<table border='1'>";
    echo "<tr><th colspan='4'>FHDY Hisoboti - " . formatDate($start_date) . " dan " . formatDate($end_date) . " gacha</th></tr>";
    echo "<tr><th>Ko'rsatkich</th><th>Soni</th><th>Foiz</th><th>Izoh</th></tr>";

    $total = $overview_stats['total_applications'];
    foreach ($overview_stats as $key => $value) {
        $percentage = $total > 0 ? round(($value / $total) * 100, 2) : 0;
        echo "<tr><td>" . ucfirst(str_replace('_', ' ', $key)) . "</td><td>{$value}</td><td>{$percentage}%</td><td>-</td></tr>";
    }

    echo "</table>";
    exit;
}
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-chart-bar me-2"></i>Hisobotlar va statistika</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-success" onclick="exportReport()">
                <i class="fas fa-file-excel me-2"></i>Excel export
            </button>
            <button class="btn btn-info" onclick="printReport()">
                <i class="fas fa-print me-2"></i>Chop etish
            </button>
        </div>
    </div>

    <!-- Filtrlar -->
    <div class="feature-card mb-4">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="hisobotlar">

            <div class="col-md-3">
                <label class="form-label">Boshlanish sanasi</label>
                <input type="date" name="start_date" class="form-control"
                       value="<?php echo $start_date; ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tugash sanasi</label>
                <input type="date" name="end_date" class="form-control"
                       value="<?php echo $end_date; ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Hisobot turi</label>
                <select name="report_type" class="form-control">
                    <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Umumiy ko'rinish</option>
                    <option value="detailed" <?php echo $report_type === 'detailed' ? 'selected' : ''; ?>>Batafsil</option>
                    <option value="financial" <?php echo $report_type === 'financial' ? 'selected' : ''; ?>>Moliyaviy</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-line me-2"></i>Hisobot yaratish
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Asosiy statistika -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-primary">
                <div class="stats-number"><?php echo number_format($overview_stats['total_applications']); ?></div>
                <div class="stats-label">Jami arizalar</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-success">
                <div class="stats-number text-success"><?php echo number_format($overview_stats['completed_applications']); ?></div>
                <div class="stats-label">Tugallangan</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-warning">
                <div class="stats-number text-warning"><?php echo number_format($overview_stats['pending_applications']); ?></div>
                <div class="stats-label">Kutilmoqda</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-info">
                <div class="stats-number text-info"><?php echo formatMoney($overview_stats['total_payments']); ?></div>
                <div class="stats-label">Jami to'lovlar</div>
            </div>
        </div>
    </div>

    <!-- Turlar bo'yicha -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="feature-card text-center">
                <i class="fas fa-heart fa-3x text-success mb-3"></i>
                <h3><?php echo number_format($overview_stats['nikoh_applications']); ?></h3>
                <p class="text-muted mb-0">Nikoh arizalari</p>
                <?php if ($overview_stats['total_applications'] > 0): ?>
                    <small class="text-success">
                        <?php echo round(($overview_stats['nikoh_applications'] / $overview_stats['total_applications']) * 100, 1); ?>%
                    </small>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card text-center">
                <i class="fas fa-handshake-slash fa-3x text-warning mb-3"></i>
                <h3><?php echo number_format($overview_stats['ajralish_applications']); ?></h3>
                <p class="text-muted mb-0">Ajralish arizalari</p>
                <?php if ($overview_stats['total_applications'] > 0): ?>
                    <small class="text-warning">
                        <?php echo round(($overview_stats['ajralish_applications'] / $overview_stats['total_applications']) * 100, 1); ?>%
                    </small>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card text-center">
                <i class="fas fa-users fa-3x text-info mb-3"></i>
                <h3><?php echo number_format($overview_stats['new_users']); ?></h3>
                <p class="text-muted mb-0">Yangi foydalanuvchilar</p>
                <small class="text-info">Tanlangan davrda</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Kunlik statistika -->
        <div class="col-lg-8">
            <div class="feature-card mb-4">
                <h4 class="mb-3">Kunlik statistika</h4>

                <?php if (!empty($daily_stats)): ?>
                    <div class="table-container">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Sana</th>
                                <th>Jami</th>
                                <th>Nikoh</th>
                                <th>Ajralish</th>
                                <th>Tugallangan</th>
                                <th>Grafik</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $max_total = max(array_column($daily_stats, 'total'));
                            foreach ($daily_stats as $stat):
                                $percentage = $max_total > 0 ? ($stat['total'] / $max_total) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo formatDate($stat['date']); ?></td>
                                    <td><strong><?php echo $stat['total']; ?></strong></td>
                                    <td><span class="badge bg-success"><?php echo $stat['nikoh']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $stat['ajralish']; ?></span></td>
                                    <td><span class="badge bg-info"><?php echo $stat['completed']; ?></span></td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tanlangan davrda ma'lumot yo'q</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status bo'yicha -->
            <div class="feature-card">
                <h4 class="mb-3">Holat bo'yicha taqsimot</h4>

                <?php if (!empty($status_stats)): ?>
                    <div class="row">
                        <?php foreach ($status_stats as $stat): ?>
                            <?php $status_info = getApplicationStatus($stat['status']); ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="status-badge <?php echo $status_info['class']; ?>">
                                        <?php echo $status_info['label']; ?>
                                    </span>
                                    <div class="text-end">
                                        <strong><?php echo $stat['count']; ?></strong>
                                        <small class="text-muted">(<?php echo $stat['percentage']; ?>%)</small>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?php echo $stat['percentage']; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Ma'lumot yo'q</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- O'ng panel -->
        <div class="col-lg-4">
            <!-- Oylik trend -->
            <div class="feature-card mb-4">
                <h5 class="mb-3">12 oylik trend</h5>

                <?php if (!empty($monthly_trend)): ?>
                    <?php foreach ($monthly_trend as $trend): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo $trend['month']; ?>/<?php echo $trend['year']; ?></span>
                            <div class="text-end">
                                <strong><?php echo $trend['total']; ?></strong><br>
                                <small class="text-muted"><?php echo formatMoney($trend['total_payment']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Ma'lumot yo'q</p>
                <?php endif; ?>
            </div>

            <!-- Faol foydalanuvchilar -->
            <div class="feature-card mb-4">
                <h5 class="mb-3">Faol foydalanuvchilar</h5>

                <?php if (!empty($active_users)): ?>
                    <?php foreach (array_slice($active_users, 0, 5) as $user): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($user['passport_series']); ?></small>
                            </div>
                            <span class="badge bg-primary"><?php echo $user['application_count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Ma'lumot yo'q</p>
                <?php endif; ?>
            </div>

            <!-- Faol adminlar -->
            <div class="feature-card">
                <h5 class="mb-3">Faol xodimlar</h5>

                <?php if (!empty($active_admins)): ?>
                    <?php foreach ($active_admins as $admin_stat): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong><?php echo htmlspecialchars($admin_stat['full_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($admin_stat['position']); ?></small>
                            </div>
                            <span class="badge bg-success"><?php echo $admin_stat['reviewed_count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Ma'lumot yo'q</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Batafsil ma'lumotlar -->
    <?php if ($report_type === 'detailed'): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="feature-card">
                    <h4 class="mb-3">Batafsil hisobot</h4>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Vaqt bo'yicha taqsimot:</h6>
                            <ul class="list-unstyled">
                                <li>• Ertalab (06:00-12:00):
                                    <?php
                                    $morning = fetchOne("SELECT COUNT(*) as count FROM applications WHERE HOUR(created_at) BETWEEN 6 AND 11 AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0;
                                    echo $morning;
                                    ?>
                                </li>
                                <li>• Kunduzi (12:00-18:00):
                                    <?php
                                    $afternoon = fetchOne("SELECT COUNT(*) as count FROM applications WHERE HOUR(created_at) BETWEEN 12 AND 17 AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0;
                                    echo $afternoon;
                                    ?>
                                </li>
                                <li>• Kechqurun (18:00-00:00):
                                    <?php
                                    $evening = fetchOne("SELECT COUNT(*) as count FROM applications WHERE HOUR(created_at) BETWEEN 18 AND 23 AND created_at BETWEEN ? AND ?", [$start_date, $end_date . ' 23:59:59'])['count'] ?? 0;
                                    echo $evening;
                                    ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Hafta kunlari bo'yicha:</h6>
                            <ul class="list-unstyled">
                                <?php
                                $days = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'];
                                for ($i = 0; $i < 7; $i++):
                                    $day_count = fetchOne("SELECT COUNT(*) as count FROM applications WHERE DAYOFWEEK(created_at) = ? AND created_at BETWEEN ? AND ?", [$i + 1, $start_date, $end_date . ' 23:59:59'])['count'] ?? 0;
                                    ?>
                                    <li>• <?php echo $days[$i]; ?>: <?php echo $day_count; ?></li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Print uchun yashirin bo'lim -->
    <div id="printArea" style="display: none;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2>O'ZBEKISTON RESPUBLIKASI FHDY</h2>
            <h3>HISOBOT</h3>
            <p><?php echo formatDate($start_date); ?> dan <?php echo formatDate($end_date); ?> gacha</p>
        </div>

        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <tr style="background-color: #f8f9fa;">
                <th>Ko'rsatkich</th>
                <th>Soni</th>
                <th>Foiz</th>
            </tr>
            <?php foreach ($overview_stats as $key => $value): ?>
                <tr>
                    <td><?php echo ucfirst(str_replace('_', ' ', $key)); ?></td>
                    <td style="text-align: right;"><?php echo number_format($value); ?></td>
                    <td style="text-align: right;">
                        <?php echo $overview_stats['total_applications'] > 0 ? round(($value / $overview_stats['total_applications']) * 100, 1) : 0; ?>%
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p style="margin-top: 30px;">
            <strong>Hisobot yaratilgan:</strong> <?php echo formatDateTime(date('Y-m-d H:i:s')); ?><br>
            <strong>Yaratuvchi:</strong> <?php echo htmlspecialchars($admin['full_name']); ?>
        </p>
    </div>
</div>

<script>
    function exportReport() {
        const url = new URL(window.location);
        url.searchParams.set('export', 'excel');
        window.open(url.toString(), '_blank');
    }

    function printReport() {
        const printContent = document.getElementById('printArea').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;

        // Sahifani qayta yuklash
        location.reload();
    }

    // Grafik uchun (Chart.js bilan)
    document.addEventListener('DOMContentLoaded', function() {
        // Bu yerda kerak bo'lsa grafik qo'shish mumkin
    });
</script>