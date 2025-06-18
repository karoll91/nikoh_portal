<?php
/**
 * Hujjat olish sahifasi
 */

if (!$user) {
    redirect('login');
}

// Tayyor guvohnomalarni olish
$sql = "SELECT gd.*, a.application_number, a.application_type 
        FROM generated_documents gd 
        JOIN applications a ON gd.application_id = a.id 
        WHERE a.applicant_id = ? 
        ORDER BY gd.issue_date DESC";
$documents = fetchAll($sql, [$user['id']]);

// Tugallangan arizalarni olish (guvohnoma yaratish mumkin)
$sql = "SELECT * FROM applications 
        WHERE applicant_id = ? AND status = 'tugallandi' AND payment_status = 'tolandi'
        ORDER BY created_at DESC";
$completed_applications = fetchAll($sql, [$user['id']]);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="feature-card">
                <h2 class="mb-4">
                    <i class="fas fa-download me-2"></i>Hujjat olish
                </h2>

                <!-- Tayyor guvohnomalar -->
                <?php if (!empty($documents)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-certificate me-2"></i>Tayyor guvohnomalar</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>Guvohnoma raqami</th>
                                        <th>Turi</th>
                                        <th>Berilgan sana</th>
                                        <th>Holati</th>
                                        <th>Harakat</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($doc['certificate_number']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $type_info = getApplicationType($doc['application_type']);
                                                ?>
                                                <span class="badge bg-<?php echo $type_info['color']; ?>">
                                                        <?php echo $type_info['label']; ?> guvohnomasi
                                                    </span>
                                            </td>
                                            <td><?php echo formatDate($doc['issue_date']); ?></td>
                                            <td>
                                                <span class="badge bg-success">Tayyor</span>
                                            </td>
                                            <td>
                                                <a href="?action=download&doc_id=<?php echo $doc['id']; ?>"
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-download me-1"></i>Yuklab olish
                                                </a>
                                                <button class="btn btn-sm btn-outline-info"
                                                        onclick="showQRCode('<?php echo htmlspecialchars($doc['qr_code']); ?>')">
                                                    <i class="fas fa-qrcode me-1"></i>QR
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tugallangan arizalar -->
                <?php if (!empty($completed_applications)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-clock me-2"></i>Guvohnoma tayyorlanmoqda</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>Ariza raqami</th>
                                        <th>Turi</th>
                                        <th>Tugallangan sana</th>
                                        <th>Holati</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($completed_applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['application_number']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $type_info = getApplicationType($app['application_type']);
                                                ?>
                                                <span class="badge bg-<?php echo $type_info['color']; ?>">
                                                        <?php echo $type_info['label']; ?>
                                                    </span>
                                            </td>
                                            <td><?php echo formatDate($app['updated_at']); ?></td>
                                            <td>
                                                <span class="badge bg-warning">Tayyorlanmoqda</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Guvohnomalar 1-2 ish kuni ichida tayyor bo'ladi. SMS orqali xabar beriladi.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Agar hech narsa bo'lmasa -->
                <?php if (empty($documents) && empty($completed_applications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Hujjatlar topilmadi</h4>
                        <p class="text-muted">Siz hali hech qanday guvohnoma olmagan yoki arizangiz tugallanmagan</p>
                        <a href="?page=ariza_topshirish" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ariza topshirish
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Ma'lumot -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Muhim ma'lumot</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fas fa-check text-success me-2"></i>Elektron guvohnomalar qonuniy kuchga ega</li>
                                    <li><i class="fas fa-check text-success me-2"></i>QR kod orqali tasdiqlash mumkin</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Nusxa ko'paytirish mumkin</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Muddati cheklanmagan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Ehtiyot choralar</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li><i class="fas fa-shield-alt text-primary me-2"></i>Maxfiy saqlang</li>
                                    <li><i class="fas fa-share-alt text-warning me-2"></i>Boshqalar bilan baham qilmang</li>
                                    <li><i class="fas fa-backup text-info me-2"></i>Zaxira nusxa saqlang</li>
                                    <li><i class="fas fa-print text-secondary me-2"></i>Zarur bo'lsa chop eting</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Yordam -->
                <div class="alert alert-light border mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-question-circle me-2"></i>Yordam kerakmi?
                    </h6>
                    <p class="mb-2">
                        Guvohnoma olishda muammo bo'lsa yoki savolaringiz bo'lsa:
                    </p>
                    <div class="btn-group" role="group">
                        <a href="?page=contact" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Xabar yuborish
                        </a>
                        <a href="tel:<?php echo str_replace([' ', '-'], '', SITE_PHONE); ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>Qo'ng'iroq qilish
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Kod</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode"></div>
                <p class="mt-3 text-muted">Bu QR kod bilan guvohnomangizni tasdiqlash mumkin</p>
            </div>
        </div>
    </div>
</div>

<script>
    function showQRCode(qrData) {
        document.getElementById('qrcode').innerHTML = '';
        // QR kod yaratish (oddiy text ko'rinishida)
        document.getElementById('qrcode').innerHTML = '<div class="border p-3"><code>' + qrData + '</code></div>';

        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }
</script>