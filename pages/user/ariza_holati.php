<?php global $user;
/**
 * pages/user/ariza_holati.php - Ariza holatini ko'rish
 */

// PDO ni olish
global $pdo;

// Foydalanuvchi kirganligini tekshirish
if (!$user) {
    echo '<script>window.location.href = "?page=login";</script>';
    exit;
}

// Ariza qidirish
$search_query = '';
$applications = [];

// Foydalanuvchining barcha arizalari
try {
    $sql = "SELECT * FROM applications WHERE applicant_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['id']]);
    $applications = $stmt->fetchAll();
} catch (Exception $e) {
    $applications = [];
}

// Ariza raqami bo'yicha qidirish
if ($_GET['search'] ?? false) {
    $search_query = sanitize($_GET['search']);
    try {
        $sql = "SELECT * FROM applications WHERE applicant_id = ? AND application_number LIKE ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id'], "%$search_query%"]);
        $applications = $stmt->fetchAll();
    } catch (Exception $e) {
        $applications = [];
    }
}

// Status bo'yicha rang
function getStatusColor($status) {
    $colors = [
        'yangi' => 'primary',
        'korib_chiqilmoqda' => 'warning',
        'qoshimcha_hujjat_kerak' => 'info',
        'tasdiqlandi' => 'success',
        'rad_etildi' => 'danger',
        'tugallandi' => 'success'
    ];
    return $colors[$status] ?? 'secondary';
}

// Status bo'yicha matn
function getStatusText($status) {
    $texts = [
        'yangi' => 'Yangi',
        'korib_chiqilmoqda' => 'Ko\'rib chiqilmoqda',
        'qoshimcha_hujjat_kerak' => 'Qo\'shimcha hujjat kerak',
        'tasdiqlandi' => 'Tasdiqlandi',
        'rad_etildi' => 'Rad etildi',
        'tugallandi' => 'Tugallandi'
    ];
    return $texts[$status] ?? $status;
}
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-search me-2"></i>Ariza holati</h2>
            <p class="text-muted">Topshirgan arizalaringiz holatini bu yerda kuzatishingiz mumkin</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="?page=ariza_topshirish" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Yangi ariza
            </a>
        </div>
    </div>

    <!-- Qidiruv -->
    <div class="feature-card mb-4">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="ariza_holati">
            <div class="col-md-8">
                <label class="form-label">Ariza raqami bo'yicha qidirish</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Masalan: 2025000001"
                       value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Qidirish
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Arizalar ro'yxati -->
    <div class="feature-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Sizning arizalaringiz</h4>
            <span class="badge bg-primary"><?php echo count($applications); ?> ta</span>
        </div>

        <?php if (empty($applications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Arizalar topilmadi</h5>
                <?php if ($search_query): ?>
                    <p class="text-muted">Qidiruv natijasi bo'yicha ariza topilmadi</p>
                    <a href="?page=ariza_holati" class="btn btn-outline-primary">Barcha arizalarni ko'rish</a>
                <?php else: ?>
                    <p class="text-muted">Hali hech qanday ariza topshirmagan ekansiz</p>
                    <a href="?page=ariza_topshirish" class="btn btn-primary">Birinchi arizani topshirish</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Ariza raqami</th>
                        <th>Turi</th>
                        <th>Topshirilgan sana</th>
                        <th>Holati</th>
                        <th>To'lov</th>
                        <th>Harakat</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo htmlspecialchars($app['application_number']); ?></strong>
                            </td>
                            <td>
                                <?php if ($app['application_type'] == 'nikoh'): ?>
                                    <span class="badge bg-success">
                                            <i class="fas fa-heart me-1"></i>Nikoh
                                        </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                            <i class="fas fa-handshake-slash me-1"></i>Ajralish
                                        </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d.m.Y', strtotime($app['created_at'])); ?><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($app['created_at'])); ?></small>
                            </td>
                            <td>
                                    <span class="badge bg-<?php echo getStatusColor($app['status']); ?>">
                                        <?php echo getStatusText($app['status']); ?>
                                    </span>
                            </td>
                            <td>
                                <strong><?php echo number_format($app['payment_required'], 0, '', ' '); ?> so'm</strong><br>
                                <?php if ($app['payment_status'] == 'tolandi'): ?>
                                    <small class="text-success">To'langan</small>
                                <?php else: ?>
                                    <small class="text-danger">Kutilmoqda</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                            onclick="viewDetails('<?php echo $app['id']; ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($app['payment_status'] != 'tolandi' && in_array($app['status'], ['yangi', 'tasdiqlandi'])): ?>
                                        <button class="btn btn-outline-success"
                                                onclick="payApplication('<?php echo $app['id']; ?>')">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($app['status'] == 'tugallandi'): ?>
                                        <a href="?page=hujjat_olish&id=<?php echo $app['id']; ?>"
                                           class="btn btn-outline-info">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ariza tafsilotlari modali -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ariza tafsilotlari</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modal-body">
                    <!-- AJAX orqali yuklanadi -->
                </div>
            </div>
        </div>
    </div>

    <!-- To'lov modali -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">To'lov qilish</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>To'lov usulini tanlang:</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="payWith('click')">
                            <i class="fas fa-credit-card me-2"></i>Click orqali
                        </button>
                        <button class="btn btn-outline-success" onclick="payWith('payme')">
                            <i class="fas fa-mobile-alt me-2"></i>Payme orqali
                        </button>
                        <button class="btn btn-outline-info" onclick="payWith('uzcard')">
                            <i class="fas fa-credit-card me-2"></i>UzCard orqali
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentApplicationId = null;

    function viewDetails(appId) {
        currentApplicationId = appId;
        document.getElementById('modal-body').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Yuklanmoqda...</div>';

        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();

        // AJAX orqali ma'lumot olish (oddiy usul)
        fetch('?page=ariza_details&id=' + appId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modal-body').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('modal-body').innerHTML = '<div class="alert alert-danger">Xatolik yuz berdi</div>';
            });
    }

    function payApplication(appId) {
        currentApplicationId = appId;
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }

    function payWith(method) {
        alert('To\'lov tizimi hali ishlamaydi. Demo rejimda');
        // Bu yerda to'lov tizimiga yo'naltirish bo'lishi kerak
    }
</script>