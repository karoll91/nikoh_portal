<?php
/**
 * pages/admin/arizalar.php - SYNTAX TUZATILGAN
 * Admin - Arizalar boshqaruvi
 */

// PDO va admin tekshiruvi
global $pdo, $admin;

if (!$admin) {
    echo '<script>window.location.href = "?page=admin_login";</script>';
    exit;
}

// Statistika API endpoint (AJAX uchun)
if (isset($_GET['get_stats']) && $_GET['get_stats'] == '1') {
    $fresh_stats = [
        'yangi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'yangi'")['count'] ?? 0,
        'korib_chiqilmoqda' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'korib_chiqilmoqda'")['count'] ?? 0,
        'tasdiqlandi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'tasdiqlandi'")['count'] ?? 0,
        'tugallandi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'tugallandi'")['count'] ?? 0,
        'rad_etildi' => fetchOne("SELECT COUNT(*) as count FROM applications WHERE status = 'rad_etildi'")['count'] ?? 0
    ];

    $fresh_total = array_sum($fresh_stats);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'stats' => $fresh_stats,
        'total' => $fresh_total
    ]);
    exit;
}

// Ariza tafsilotlarini ko'rish (AJAX uchun)
if (isset($_GET['view_application']) && isset($_GET['id'])) {
    $app_id = (int)$_GET['id'];
    $application = fetchOne("
        SELECT a.*, u.first_name, u.last_name, u.passport_series, u.phone, u.email, u.birth_date,
               ad.full_name as reviewed_by_name
        FROM applications a 
        JOIN users u ON a.applicant_id = u.id 
        LEFT JOIN admin_users ad ON a.reviewed_by = ad.id
        WHERE a.id = ?
    ", [$app_id]);

    if ($application) {
        $status_info = getApplicationStatus($application['status']);
        $type_info = getApplicationType($application['application_type']);
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6>Ariza ma'lumotlari</h6>
                <table class="table table-sm">
                    <tr><td><strong>Raqam:</strong></td><td>#<?php echo $application['application_number']; ?></td></tr>
                    <tr><td><strong>Turi:</strong></td><td><span class="badge bg-<?php echo $type_info['color']; ?>"><?php echo $type_info['label']; ?></span></td></tr>
                    <tr><td><strong>Holati:</strong></td><td><span class="status-badge <?php echo $status_info['class']; ?>"><?php echo $status_info['label']; ?></span></td></tr>
                    <tr><td><strong>Sana:</strong></td><td><?php echo formatDateTime($application['created_at']); ?></td></tr>
                    <tr><td><strong>To'lov:</strong></td><td><?php echo formatMoney($application['payment_required']); ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Ariza beruvchi</h6>
                <table class="table table-sm">
                    <tr><td><strong>Ism:</strong></td><td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td></tr>
                    <tr><td><strong>Pasport:</strong></td><td><?php echo htmlspecialchars($application['passport_series']); ?></td></tr>
                    <tr><td><strong>Telefon:</strong></td><td><?php echo htmlspecialchars($application['phone']); ?></td></tr>
                    <?php if ($application['email']): ?>
                        <tr><td><strong>Email:</strong></td><td><?php echo htmlspecialchars($application['email']); ?></td></tr>
                    <?php endif; ?>
                    <tr><td><strong>Tug'ilgan sana:</strong></td><td><?php echo $application['birth_date'] ? formatDate($application['birth_date']) : '-'; ?></td></tr>
                </table>
            </div>
        </div>

        <?php if ($application['application_type'] == 'nikoh'): ?>
            <h6>Nikoh ma'lumotlari</h6>
            <table class="table table-sm">
                <tr><td><strong>Sherik ismi:</strong></td><td><?php echo htmlspecialchars($application['partner_name'] ?? '-'); ?></td></tr>
                <tr><td><strong>Sherik pasporti:</strong></td><td><?php echo htmlspecialchars($application['partner_passport'] ?? '-'); ?></td></tr>
                <tr><td><strong>Istagan sana:</strong></td><td><?php echo $application['preferred_date'] ? formatDate($application['preferred_date']) : '-'; ?></td></tr>
            </table>
        <?php else: ?>
            <h6>Ajralish ma'lumotlari</h6>
            <table class="table table-sm">
                <tr><td><strong>Nikoh sanasi:</strong></td><td><?php echo $application['marriage_date'] ? formatDate($application['marriage_date']) : '-'; ?></td></tr>
                <tr><td><strong>Ajralish sababi:</strong></td><td><?php echo htmlspecialchars($application['divorce_reason'] ?? '-'); ?></td></tr>
            </table>
        <?php endif; ?>

        <?php if ($application['review_notes']): ?>
            <h6>Ko'rib chiqish izohi</h6>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($application['review_notes']); ?>
                <br><small>Ko'rib chiquvchi: <?php echo htmlspecialchars($application['reviewed_by_name'] ?? 'Noma\'lum'); ?></small>
            </div>
        <?php endif; ?>
        <?php
    } else {
        echo '<div class="alert alert-danger">Ariza topilmadi</div>';
    }
    exit;
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

        // Eski statusni olish
        $old_app = fetchOne("SELECT status FROM applications WHERE id = ?", [$application_id]);
        $old_status = $old_app['status'] ?? '';

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
            'data' => [
                'application_id' => $application_id,
                'new_status' => $new_status,
                'old_status' => $old_status
            ]
        ]);

        $_SESSION['success_message'] = 'Ariza holati "' . getApplicationStatus($new_status)['label'] . '" ga o\'zgartirildi';

        // SMS yuborish (agar kerak bo'lsa)
        if ($new_status === 'tasdiqlandi') {
            $app = fetchOne("SELECT a.*, u.phone, u.first_name FROM applications a JOIN users u ON a.applicant_id = u.id WHERE a.id = ?", [$application_id]);
            if ($app) {
                $sms_message = "Hurmatli {$app['first_name']}, #{$app['application_number']} raqamli arizangiz tasdiqlandi. Tafsilotlar uchun tizimga kiring.";
                sendNotification($app['applicant_id'], 'sms', $app['phone'], $sms_message);
            }
        }

        // AJAX so'rov bo'lsa JSON javob
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Holat yangilandi']);
            exit;
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();

        // AJAX so'rov bo'lsa JSON javob
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Redirect to prevent form resubmission
    echo '<script>window.location.href = "?page=arizalar";</script>';
    exit;
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
            <button class="btn btn-info" onclick="printTable()">
                <i class="fas fa-print me-2"></i>Chop etish
            </button>
        </div>
    </div>

    <!-- Statistika -->
    <div class="row mb-4" id="statsContainer">
        <div class="col-xl-2 col-md-4 mb-2">
            <div class="stats-card border-info">
                <div class="stats-number text-info" id="stat-yangi"><?php echo number_format($stats['yangi']); ?></div>
                <div class="stats-label">Yangi</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-2">
            <div class="stats-card border-warning">
                <div class="stats-number text-warning" id="stat-korib_chiqilmoqda"><?php echo number_format($stats['korib_chiqilmoqda']); ?></div>
                <div class="stats-label">Ko'rilmoqda</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-2">
            <div class="stats-card border-primary">
                <div class="stats-number text-primary" id="stat-tasdiqlandi"><?php echo number_format($stats['tasdiqlandi']); ?></div>
                <div class="stats-label">Tasdiqlandi</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-2">
            <div class="stats-card border-success">
                <div class="stats-number text-success" id="stat-tugallandi"><?php echo number_format($stats['tugallandi']); ?></div>
                <div class="stats-label">Tugallandi</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-2">
            <div class="stats-card border-danger">
                <div class="stats-number text-danger" id="stat-rad_etildi"><?php echo number_format($stats['rad_etildi']); ?></div>
                <div class="stats-label">Rad etildi</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-2">
            <div class="stats-card border-dark">
                <div class="stats-number" id="stat-total"><?php echo number_format($total_count); ?></div>
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
            <div class="table-responsive">
                <table class="table" id="applicationsTable">
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
                            <td id="status-<?php echo $app['id']; ?>">
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
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            onclick="viewApplication(<?php echo $app['id']; ?>)"
                                            title="Ko'rish">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-outline-success"
                                            onclick="updateStatus(<?php echo $app['id']; ?>, '<?php echo $app['status']; ?>')"
                                            title="Holat o'zgartirish">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($app['status'] === 'tugallandi'): ?>
                                        <button type="button"
                                                class="btn btn-outline-info"
                                                onclick="generateDocument(<?php echo $app['id']; ?>)"
                                                title="Guvohnoma">
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
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">
                    <i class="fas fa-edit me-2"></i>Ariza holatini yangilash
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="statusForm">
                <?php echo csrfInput(); ?>
                <input type="hidden" name="application_id" id="modal_application_id">

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="modal_status" class="form-label">Yangi holat</label>
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
                        <label for="review_notes" class="form-label">Izoh</label>
                        <textarea name="review_notes" id="review_notes" class="form-control" rows="3"
                                  placeholder="Qo'shimcha izoh (ixtiyoriy)"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" name="update_status" class="btn btn-primary" id="saveStatusBtn">
                        <i class="fas fa-save me-2"></i>Saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ariza ko'rish modali -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">
                    <i class="fas fa-eye me-2"></i>Ariza tafsilotlari
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_content">
                <!-- AJAX orqali yuklanadi -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yuklanmoqda...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal obyektlarini global qilish
    let statusModal, viewModal;

    document.addEventListener('DOMContentLoaded', function() {
        // Bootstrap modal obyektlarini yaratish
        statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

        // Form submit handler
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Formni to'xtatish

            const saveBtn = document.getElementById('saveStatusBtn');
            const formData = new FormData(this);

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saqlanmoqda...';

            // AJAX orqali yuborish
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Modal yopish
                        statusModal.hide();

                        // Statistikani yangilash
                        updateStatistics();

                        // Jadvalda status o'zgartirish
                        const appId = formData.get('application_id');
                        const newStatus = formData.get('status');
                        updateRowStatus(appId, newStatus);

                        // Success xabari
                        showNotification('Ariza holati muvaffaqiyatli yangilandi!', 'success');
                    } else {
                        showNotification(data.message || 'Xatolik yuz berdi', 'error');
                    }

                    // Reset button
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Saqlash';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Xatolik yuz berdi. Qaytadan urinib ko\'ring.', 'error');

                    // Reset button
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Saqlash';
                });
        });
    });

    function updateStatus(applicationId, currentStatus) {
        document.getElementById('modal_application_id').value = applicationId;
        document.getElementById('modal_status').value = currentStatus;
        document.getElementById('review_notes').value = '';

        // Reset button
        const saveBtn = document.getElementById('saveStatusBtn');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Saqlash';

        statusModal.show();
    }

    function viewApplication(applicationId) {
        const modalContent = document.getElementById('modal_content');
        modalContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yuklanmoqda...</span></div></div>';

        viewModal.show();

        // AJAX so'rov
        fetch(`?page=arizalar&view_application=1&id=${applicationId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                modalContent.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Ma\'lumotlarni yuklashda xatolik yuz berdi. Iltimos, qaytadan urinib ko\'ring.</div>';
            });
    }

    function generateDocument(applicationId) {
        if (confirm('Guvohnoma yaratishni xohlaysizmi?')) {
            alert('Guvohnoma yaratish funksiyasi hali ishlab chiqilmagan. Demo rejimda.');
            console.log('Generate document for application:', applicationId);
        }
    }

    function exportToExcel() {
        alert('Excel export funksiyasi hali ishlab chiqilmagan. Demo rejimda.');
        console.log('Export to Excel');
    }

    function printTable() {
        window.print();
    }

    // Notification ko'rsatish funksiyasi
    function showNotification(message, type = 'success') {
        // Toast notification yaratish
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';

        toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(toast);

        // 5 soniyadan keyin o'chirish
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    // Statistikani yangilash funksiyasi
    function updateStatistics() {
        fetch('?page=arizalar&get_stats=1')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.stats) {
                    // Har bir statistika raqamini yangilash
                    Object.keys(data.stats).forEach(status => {
                        const element = document.getElementById('stat-' + status);
                        if (element) {
                            // Animatsiya bilan yangilash
                            element.style.transform = 'scale(1.2)';
                            element.style.transition = 'transform 0.3s';
                            element.textContent = new Intl.NumberFormat().format(data.stats[status]);

                            setTimeout(() => {
                                element.style.transform = 'scale(1)';
                            }, 300);
                        }
                    });

                    // Jami statistikani yangilash
                    const totalElement = document.getElementById('stat-total');
                    if (totalElement && data.total) {
                        totalElement.style.transform = 'scale(1.2)';
                        totalElement.style.transition = 'transform 0.3s';
                        totalElement.textContent = new Intl.NumberFormat().format(data.total);

                        setTimeout(() => {
                            totalElement.style.transform = 'scale(1)';
                        }, 300);
                    }
                }
            })
            .catch(error => {
                console.error('Statistikani yangilashda xatolik:', error);
            });
    }

    // Jadval qatorida statusni yangilash
    function updateRowStatus(appId, newStatus) {
        const statusCell = document.getElementById('status-' + appId);
        if (statusCell) {
            // Status ma'lumotlarini olish
            const statusInfo = getStatusInfo(newStatus);

            // Yangi status badge yaratish
            statusCell.innerHTML = `
            <span class="status-badge ${statusInfo.class}">
                <i class="${statusInfo.icon} me-1"></i>
                ${statusInfo.label}
            </span>
        `;

            // Animatsiya
            statusCell.style.backgroundColor = '#d4edda';
            setTimeout(() => {
                statusCell.style.backgroundColor = '';
            }, 2000);
        }
    }

    // Status ma'lumotlarini JavaScript da olish
    function getStatusInfo(status) {
        const statuses = {
            'yangi': { label: 'Yangi', class: 'status-yangi', icon: 'fas fa-file' },
            'korib_chiqilmoqda': { label: 'Ko\'rib chiqilmoqda', class: 'status-korib-chiqilmoqda', icon: 'fas fa-eye' },
            'qoshimcha_hujjat_kerak': { label: 'Qo\'shimcha hujjat kerak', class: 'status-warning', icon: 'fas fa-exclamation-triangle' },
            'tasdiqlandi': { label: 'Tasdiqlandi', class: 'status-tasdiqlandi', icon: 'fas fa-check' },
            'rad_etildi': { label: 'Rad etildi', class: 'status-rad-etildi', icon: 'fas fa-times' },
            'tugallandi': { label: 'Tugallandi', class: 'status-tugallandi', icon: 'fas fa-flag-checkered' }
        };

        return statuses[status] || { label: status, class: 'badge-secondary', icon: 'fas fa-question' };
    }
</script>

<!-- Qo'shimcha CSS -->
<style>
    .stats-card {
        transition: transform 0.2s;
        cursor: pointer;
    }

    .stats-card:hover {
        transform: translateY(-2px);
    }

    .btn-group .btn {
        border-radius: 0;
    }

    .btn-group .btn:first-child {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }

    .btn-group .btn:last-child {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }

    .table-responsive {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .modal-header {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    /* Status badge rang */
    .status-yangi {
        background-color: #e3f2fd;
        color: #1976d2;
    }

    .status-korib-chiqilmoqda {
        background-color: #fff3e0;
        color: #f57c00;
    }

    .status-tasdiqlandi {
        background-color: #e8f5e8;
        color: #388e3c;
    }

    .status-rad-etildi {
        background-color: #ffebee;
        color: #d32f2f;
    }

    .status-tugallandi {
        background-color: #f3e5f5;
        color: #7b1fa2;
    }

    .status-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    @media (max-width: 768px) {
        .stats-card {
            margin-bottom: 1rem;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.25rem;
        }

        .modal-dialog {
            margin: 0.5rem;
        }
    }

    /* Toast animatsiya */
    .alert.position-fixed {
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>