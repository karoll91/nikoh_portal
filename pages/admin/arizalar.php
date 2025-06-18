<?php
/**
 * Admin - Arizalar boshqaruvi
 */

if (!$admin) {
    redirect('admin_login');
}

// Arizani yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Xavfsizlik xatosi');
        }

        $application_id = (int)$_POST['application_id'];
        $new_status = sanitize($_POST['status']);
        $review_notes = sanitize($_POST['review_notes'] ?? '');

        // Arizani yangilash
        $update_data = [
            'status' => $new_status,
            'reviewed_by' => $admin['id'],
            'review_date' => date('Y-m-d H:i:s'),
            'review_notes' => $review_notes
        ];

        updateRecord('applications', $update_data, 'id = ?', [$application_id]);

        // Log yozish
        logActivity('application_status_updated', null, $admin['id'], [
            'application_id' => $application_id,
            'new_status' => $new_status
        ]);

        $_SESSION['success_message'] = 'Ariza holati yangilandi';

        // SMS yuborish (agar kerak bo'lsa)
        if ($new_status === 'tasdiqlandi') {
            $app = fetchOne("SELECT a.*, u.phone FROM applications a JOIN users u ON a.applicant_id = u.id WHERE a.id = ?", [$application_id]);
            if ($app) {
                $sms_message = "Sizning #{$app['application_number']} raqamli arizangiz tasdiqlandi. Tafsilotlar uchun tizimga kiring.";
                sendNotification($app['applicant_id'], 'sms', $app['phone'], $sms_message);
            }
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Filtrlar
$filter_status = sanitize($_GET['status'] ?? '');
$filter_type = sanitize($_GET['type'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page_num = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

// SQL so'rov yaratish
$where_conditions = [];
$params = [];

if ($filter_status) {
    $where_conditions[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_type) {
    $where_conditions[] = "a.application_type = ?";
    $params[] = $filter_type;
}

if ($search) {
    $where_conditions[] = "(a.application_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.passport_series LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Arizalarni olish
$sql = "SELECT a.*, u.first_name, u.last_name, u.passport_series, u.phone,
               ad.full_name as reviewed_by_name
        FROM applications a 
        JOIN users u ON a.applicant_id = u.id 
        LEFT JOIN admin_users ad ON a.reviewed_by = ad.id
        {$where_clause}
        ORDER BY a.created_at DESC 
        LIMIT {$per_page} OFFSET {$offset}";

$applications = fetchAll($sql, $params);

// Umumiy soni
$count_sql = "SELECT COUNT(*) as total FROM applications a JOIN users u ON a.applicant_id = u.id {$where_clause}";
$total_count = fetchOne($count_sql, $params)['total'] ?? 0;
$total_pages = ceil($total_count / $per_page);

// Statistika
$stats = [
    'yangi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'yangi'")['count'] ?? 0,
    'korib_chiqilmoqda' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'korib_chiqilmoqda'")['count'] ?? 0,
    'tasdiqlandi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'tasdiqlandi'")['count'] ?? 0,
    'tugallandi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'tugallandi'")['count'] ?? 0,
    'rad_etildi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'rad_etildi'")['count'] ?? 0
];
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-file-alt me-2"></i>Arizalar boshqaruvi</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-success" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-2"></i>Excel export
            </button>
        </div>
    </div>

    <!-- Statistika -->
    <div class="row mb-4">
        <div class="col-md-2 mb-2">
            <div class="stats-card border-info">
                <div class="stats-number text-info"><?php echo $stats['yangi']; ?></div>
                <div class="stats-label">Yangi</div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="stats-card border-warning">
                <div class="stats-number text-warning"><?php echo $stats['korib_chiqilmoqda']; ?></div>
                <div class="stats-label">Ko'rilmoqda</div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="stats-card border-primary">
                <div class="stats-number text-primary"><?php echo $stats['tasdiqlandi']; ?></div>
                <div class="stats-label">Tasdiqlandi</div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="stats-card border-success">
                <div class="stats-number text-success"><?php echo $stats['tugallandi']; ?></div>
                <div class="stats-label">Tugallandi</div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="stats-card border-danger">
                <div class="stats-number text-danger"><?php echo $stats['rad_etildi']; ?></div>
                <div class="stats-label">Rad etildi</div>
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <div class="stats-card border-dark">
                <div class="stats-number"><?php echo number_format($total_count); ?></div>
                <div class="stats-label">Jami</div>
            </div>
        </div>
    </div>

    <!-- Filtrlar -->
    <div class="feature-card mb-4">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="arizalar">

            <div class="col-md-3">
                <label class="form-label">Holat bo'yicha</label>
                <select name="status" class="form-control">
                    <option value="">Barcha holatlar</option>
                    <option value="yangi" <?php echo $filter_status === 'yangi' ? 'selected' : ''; ?>>Yangi</option>
                    <option value="korib_chiqilmoqda" <?php echo $filter_status === 'korib_chiqilmoqda' ? 'selected' : ''; ?>>Ko'rib chiqilmoqda</option>
                    <option value="tasdiqlandi" <?php echo $filter_status === 'tasdiqlandi' ? 'selected' : ''; ?>>Tasdiqlandi</option>
                    <option value="tugallandi" <?php echo $filter_status === 'tugallandi' ? 'selected' : ''; ?>>Tugallandi</option>
                    <option value="rad_etildi" <?php echo $filter_status === 'rad_etildi' ? 'selected' : ''; ?>>Rad etildi</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Tur bo'yicha</label>
                <select name="type" class="form-control">
                    <option value="">Barcha turlar</option>
                    <option value="nikoh" <?php echo $filter_type === 'nikoh' ? 'selected' : ''; ?>>Nikoh</option>
                    <option value="ajralish" <?php echo $filter_type === 'ajralish' ? 'selected' : ''; ?>>Ajralish</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Qidiruv</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Ariza raqami, ism, pasport..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Qidirish
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Arizalar jadvali -->
    <div class="feature-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Arizalar ro'yxati</h4>
            <small class="text-muted">Jami: <?php echo number_format($total_count); ?> ta</small>
        </div>

        <?php if (empty($applications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Arizalar topilmadi</h5>
                <p class="text-muted">Filtrlarni o'zgartiring yoki qidiruvni kengaytiring</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Ariza raqami</th>
                        <th>Ariza beruvchi</th>
                        <th>Turi</th>
                        <th>Sana</th>
                        <th>Holati</th>
                        <th>Ko'rib chiquvchi</th>
                        <th>Harakatlar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($applications as $app): ?>
                        <?php
                        $status_info = getApplicationStatus($app['status']);
                        $type_info = getApplicationType($app['application_type']);
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo htmlspecialchars($app['application_number']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($app['passport_series']); ?> •
                                    <?php echo htmlspecialchars($app['phone']); ?>
                                </small>
                            </td>
                            <td>
                                    <span class="badge bg-<?php echo $type_info['color']; ?>">
                                        <i class="<?php echo $type_info['icon']; ?> me-1"></i>
                                        <?php echo $type_info['label']; ?>
                                    </span>
                            </td>
                            <td>
                                <?php echo formatDate($app['created_at']); ?><br>
                                <small class="text-muted"><?php echo timeAgo($app['created_at']); ?></small>
                            </td>
                            <td>
                                    <span class="status-badge <?php echo $status_info['class']; ?>">
                                        <i class="<?php echo $status_info['icon']; ?> me-1"></i>
                                        <?php echo $status_info['label']; ?>
                                    </span>
                            </td>
                            <td>
                                <?php if ($app['reviewed_by_name']): ?>
                                    <small><?php echo htmlspecialchars($app['reviewed_by_name']); ?></small><br>
                                    <small class="text-muted"><?php echo formatDate($app['review_date']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                            onclick="viewApplication(<?php echo $app['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success"
                                            onclick="updateStatus(<?php echo $app['id']; ?>, '<?php echo $app['status']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($app['status'] === 'tugallandi'): ?>
                                        <button class="btn btn-outline-info"
                                                onclick="generateDocument(<?php echo $app['id']; ?>)">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page_num > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=arizalar&page_num=<?php echo $page_num - 1; ?>&status=<?php echo $filter_status; ?>&type=<?php echo $filter_type; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page_num - 2); $i <= min($total_pages, $page_num + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page_num ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=arizalar&page_num=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&type=<?php echo $filter_type; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page_num < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=arizalar&page_num=<?php echo $page_num + 1; ?>&status=<?php echo $filter_status; ?>&type=<?php echo $filter_type; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Status yangilash modali -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ariza holatini yangilash</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="application_id" id="modal_application_id">

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Yangi holat</label>
                        <select name="status" id="modal_status" class="form-control" required>
                            <option value="yangi">Yangi</option>
                            <option value="korib_chiqilmoqda">Ko'rib chiqilmoqda</option>
                            <option value="qoshimcha_hujjat_kerak">Qo'shimcha hujjat kerak</option>
                            <option value="tasdiqlandi">Tasdiqlandi</option>
                            <option value="rad_etildi">Rad etildi</option>
                            <option value="tugallandi">Tugallandi</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Izoh</label>
                        <textarea name="review_notes" class="form-control" rows="3"
                                  placeholder="Qo'shimcha izoh (ixtiyoriy)"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ariza ko'rish modali -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ariza tafsilotlari</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal_content">
                <!-- AJAX orqali yuklanadi -->
            </div>
        </div>
    </div>
</div>

<script>
    function updateStatus(applicationId, currentStatus) {
        document.getElementById('modal_application_id').value = applicationId;
        document.getElementById('modal_status').value = currentStatus;

        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        modal.show();
    }

    function viewApplication(applicationId) {
        const modal = new bootstrap.Modal(document.getElementById('viewModal'));
        document.getElementById('modal_content').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Yuklanmoqda...</div>';

        // AJAX so'rov (oddiy fetch bilan)
        fetch('?page=ariza_view&id=' + applicationId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modal_content').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('modal_content').innerHTML = '<div class="alert alert-danger">Xatolik yuz berdi</div>';
            });

        modal.show();
    }

    function generateDocument(applicationId) {
        if (confirm('Guvohnoma yaratishni xohlaysizmi?')) {
            window.location.href = '?page=generate_document&id=' + applicationId;
        }
    }

    function exportToExcel() {
        const url = new URL(window.location);
        url.searchParams.set('export', 'excel');
        window.open(url.toString(), '_blank');
    }
</script>