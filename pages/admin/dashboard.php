<?php global $admin;
/**
 * Admin dashboard
 */

if (!$admin) {
    redirect('admin_login');
}

// Statistikalarni olish
try {
    $stats = [
        'total_users' => fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        'total_applications' => fetchOne("SELECT COUNT(*) as count FROM applications")['count'] ?? 0,
        'pending_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status IN ('yangi', 'korib_chiqilmoqda')")['count'] ?? 0,
        'completed_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'tugallandi'")['count'] ?? 0,
        'today_applications' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'marriages' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE application_type = 'nikoh' AND status = 'tugallandi'")['count'] ?? 0,
        'divorces' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE application_type = 'ajralish' AND status = 'tugallandi'")['count'] ?? 0,
        'total_payments' => fetchOne("SELECT COALESCE(SUM(payment_required), 0) as total FROM applications WHERE payment_status = 'tolandi'")['total'] ?? 0
    ];

    // So'nggi arizalar
    $recent_applications = fetchAll("
        SELECT a.*, u.first_name, u.last_name, u.passport_series 
        FROM applications a 
        JOIN users u ON a.applicant_id = u.id 
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");

    // Oylik statistika
    $monthly_stats = fetchAll("
        SELECT 
            MONTH(created_at) as month,
            YEAR(created_at) as year,
            COUNT(*) as total,
            SUM(CASE WHEN application_type = 'nikoh' THEN 1 ELSE 0 END) as marriages,
            SUM(CASE WHEN application_type = 'ajralish' THEN 1 ELSE 0 END) as divorces
        FROM applications 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY year DESC, month DESC
    ");

} catch (Exception $e) {
    // Xatolik bo'lsa default qiymatlar
    $stats = array_fill_keys(['total_users', 'total_applications', 'pending_applications', 'completed_applications', 'today_applications', 'marriages', 'divorces', 'total_payments'], 0);
    $recent_applications = [];
    $monthly_stats = [];
}
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Boshqaruv paneli</h1>
            <p class="text-muted">
                Xush kelibsiz, <?php echo htmlspecialchars($admin['full_name']); ?>
                <span class="badge bg-<?php echo $admin['role'] === 'admin' ? 'danger' : ($admin['role'] === 'mudiri' ? 'warning' : 'primary'); ?>">
                    <?php echo ucfirst($admin['role']); ?>
                </span>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <small class="text-muted">
                Oxirgi kirish: <?php echo $admin['last_login'] ? formatDateTime($admin['last_login']) : 'Birinchi marta'; ?>
            </small>
        </div>
    </div>

    <!-- Asosiy statistika -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-primary">
                <div class="stats-number"><?php echo number_format($stats['total_applications']); ?></div>
                <div class="stats-label">Jami arizalar</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-warning">
                <div class="stats-number text-warning"><?php echo number_format($stats['pending_applications']); ?></div>
                <div class="stats-label">Kutilmoqda</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-success">
                <div class="stats-number text-success"><?php echo number_format($stats['completed_applications']); ?></div>
                <div class="stats-label">Tugallangan</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card border-info">
                <div class="stats-number text-info"><?php echo number_format($stats['today_applications']); ?></div>
                <div class="stats-label">Bugungi arizalar</div>
            </div>
        </div>
    </div>

    <!-- Qo'shimcha statistika -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="feature-card text-center">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h4><?php echo number_format($stats['total_users']); ?></h4>
                <p class="text-muted mb-0">Foydalanuvchilar</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="feature-card text-center">
                <i class="fas fa-heart fa-2x text-success mb-2"></i>
                <h4><?php echo number_format($stats['marriages']); ?></h4>
                <p class="text-muted mb-0">Nikohlar</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="feature-card text-center">
                <i class="fas fa-handshake-slash fa-2x text-warning mb-2"></i>
                <h4><?php echo number_format($stats['divorces']); ?></h4>
                <p class="text-muted mb-0">Ajralishlar</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="feature-card text-center">
                <i class="fas fa-money-bill fa-2x text-info mb-2"></i>
                <h4><?php echo formatMoney($stats['total_payments']); ?></h4>
                <p class="text-muted mb-0">Jami to'lovlar</p>
            </div>
        </div>
    </div>

    <!-- Tezkor harakatlar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="feature-card">
                <h3 class="mb-3">Tezkor harakatlar</h3>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="?page=arizalar&filter=yangi" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-file-alt fa-2x d-block mb-2"></i>
                            <strong>Yangi arizalar</strong>
                            <small class="d-block"><?php echo $stats['pending_applications']; ?> ta</small>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=arizalar" class="btn btn-primary w-100 py-3">
                            <i class="fas fa-list fa-2x d-block mb-2"></i>
                            <strong>Barcha arizalar</strong>
                            <small class="d-block">Boshqarish</small>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=foydalanuvchilar" class="btn btn-info w-100 py-3">
                            <i class="fas fa-users fa-2x d-block mb-2"></i>
                            <strong>Foydalanuvchilar</strong>
                            <small class="d-block"><?php echo $stats['total_users']; ?> ta</small>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=hisobotlar" class="btn btn-success w-100 py-3">
                            <i class="fas fa-chart-bar fa-2x d-block mb-2"></i>
                            <strong>Hisobotlar</strong>
                            <small class="d-block">Statistika</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- So'nggi arizalar -->
        <div class="col-lg-8">
            <div class="feature-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>So'nggi arizalar</h3>
                    <a href="?page=arizalar" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>Barchasini ko'rish
                    </a>
                </div>

                <?php if (empty($recent_applications)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Arizalar topilmadi</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Raqam</th>
                                <th>Ariza beruvchi</th>
                                <th>Turi</th>
                                <th>Sana</th>
                                <th>Holati</th>
                                <th>Harakat</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recent_applications as $app): ?>
                                <?php
                                $status_info = getApplicationStatus($app['status']);
                                $type_info = getApplicationType($app['application_type']);
                                ?>
                                <tr>
                                    <td>
                                        <small class="text-muted">#<?php echo htmlspecialchars($app['application_number']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['passport_series']); ?></small>
                                    </td>
                                    <td>
                                            <span class="badge bg-<?php echo $type_info['color']; ?>">
                                                <?php echo $type_info['label']; ?>
                                            </span>
                                    </td>
                                    <td>
                                        <?php echo formatDate($app['created_at']); ?><br>
                                        <small class="text-muted"><?php echo timeAgo($app['created_at']); ?></small>
                                    </td>
                                    <td>
                                            <span class="status-badge <?php echo $status_info['class']; ?>">
                                                <?php echo $status_info['label']; ?>
                                            </span>
                                    </td>
                                    <td>
                                        <a href="?page=ariza_korish&id=<?php echo $app['id']; ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- O'ng panel -->
        <div class="col-lg-4">
            <!-- Oylik statistika -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-success"></i>6 oylik trend
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($monthly_stats)): ?>
                        <?php foreach ($monthly_stats as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fw-semibold">
                                        <?php echo $month_names[$stat['month']] ?? $stat['month']; ?> <?php echo $stat['year']; ?>
                                    </div>
                                    <small class="text-muted">
                                        Nikoh: <?php echo $stat['marriages']; ?> • Ajralish: <?php echo $stat['divorces']; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary"><?php echo $stat['total']; ?></div>
                                    <div class="progress" style="width: 60px; height: 4px;">
                                        <div class="progress-bar bg-primary"
                                             style="width: <?php echo min(100, ($stat['total'] / max(1, max(array_column($monthly_stats, 'total')))) * 100); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                            <p class="text-muted small mb-0">Ma'lumot yo'q</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tizim holati -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-server me-2 text-info"></i>Tizim holati
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-database text-success me-2"></i>
                                <span class="small">Ma'lumotlar bazasi</span>
                            </div>
                            <span class="badge bg-success">Faol</span>
                        </div>
                        <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-credit-card text-success me-2"></i>
                                <span class="small">To'lov tizimi</span>
                            </div>
                            <span class="badge bg-success">Ishlaydi</span>
                        </div>
                        <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-sms text-warning me-2"></i>
                                <span class="small">SMS xizmati</span>
                            </div>
                            <span class="badge bg-warning">Tekshirilmoqda</span>
                        </div>
                        <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-success me-2"></i>
                                <span class="small">Email xizmati</span>
                            </div>
                            <span class="badge bg-success">Faol</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin harakatlar -->
            <?php if ($admin['role'] === 'admin'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cog me-2 text-secondary"></i>Administrator
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="?page=sozlamalar" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-cog me-2"></i>Sozlamalar
                            </a>
                            <a href="?page=backup" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-database me-2"></i>Zaxira nusxa
                            </a>
                            <a href="?page=logs" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-file-alt me-2"></i>Tizim loglar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>