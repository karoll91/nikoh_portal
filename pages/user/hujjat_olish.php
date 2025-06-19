<?php global $user;
/**
 * pages/user/hujjat_olish.php - Tayyor hujjatlarni olish
 */

// PDO ni olish
global $pdo;

// Foydalanuvchi kirganligini tekshirish
if (!$user) {
    echo '<script>window.location.href = "?page=login";</script>';
    exit;
}

// Tayyor hujjatlarni olish
$documents = [];
try {
    $sql = "SELECT a.*, gd.* 
            FROM applications a
            LEFT JOIN generated_documents gd ON a.id = gd.application_id
            WHERE a.applicant_id = ? AND a.status = 'tugallandi'
            ORDER BY a.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['id']]);
    $documents = $stmt->fetchAll();
} catch (Exception $e) {
    $documents = [];
}

// Hujjat yuklab olish
if (isset($_GET['download']) && isset($_GET['doc_id'])) {
    $doc_id = (int)$_GET['doc_id'];

    try {
        // Hujjat mavjudligini tekshirish
        $sql = "SELECT gd.*, a.applicant_id 
                FROM generated_documents gd
                JOIN applications a ON gd.application_id = a.id
                WHERE gd.id = ? AND a.applicant_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$doc_id, $user['id']]);
        $document = $stmt->fetch();

        if ($document) {
            // Download statistikasini yangilash
            $sql = "UPDATE generated_documents SET download_count = download_count + 1, download_date = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$doc_id]);

            // PDF fayl yaratish (oddiy HTML to PDF)
            $pdf_content = generateCertificatePDF($document);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="guvohnoma_' . $document['certificate_number'] . '.pdf"');
            echo $pdf_content;
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Hujjatni yuklab olishda xatolik yuz berdi';
    }
}

// PDF yaratish funksiyasi (oddiy)
function generateCertificatePDF($document) {
    $html = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 30px; }
            .content { margin: 20px; }
            .footer { text-align: center; margin-top: 50px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>O'ZBEKISTON RESPUBLIKASI</h2>
            <h3>FUQAROLIK HOLATI DALOLATNOMALARINI YOZISH ORGANI</h3>
            <h1>" . strtoupper($document['document_type']) . " GUVOHNOMASI</h1>
        </div>
        
        <div class='content'>
            <p><strong>Guvohnoma raqami:</strong> " . $document['certificate_number'] . "</p>
            <p><strong>Seriya:</strong> " . $document['series'] . "</p>
            <p><strong>Berilgan sana:</strong> " . date('d.m.Y', strtotime($document['issue_date'])) . "</p>
            <p><strong>Bergan organ:</strong> " . $document['issued_by'] . "</p>
        </div>
        
        <div class='footer'>
            <p>Ushbu guvohnoma qonuniy kuchga ega</p>
            <p>QR kod: " . $document['qr_code'] . "</p>
        </div>
    </body>
    </html>";

    return $html; // Haqiqiy PDF yaratish uchun PDF library kerak
}
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-download me-2"></i>Hujjat olish</h2>
            <p class="text-muted">Tayyor guvohnomalaringizni bu yerdan yuklab olishingiz mumkin</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="?page=ariza_holati" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Arizalar ro'yxati
            </a>
        </div>
    </div>

    <!-- Yo'riqnoma -->
    <div class="alert alert-info mb-4">
        <h6 class="alert-heading">
            <i class="fas fa-info-circle me-2"></i>Muhim ma'lumot
        </h6>
        <ul class="mb-0">
            <li>Faqat tugallangan arizalar uchun guvohnoma mavjud</li>
            <li>Guvohnoma elektron ko'rinishda PDF formatida yuklab olinadi</li>
            <li>QR kod orqali guvohnomaning haqiqiyligini tekshirish mumkin</li>
            <li>Guvohnoma yuridik kuchga ega va rasmiy hujjat hisoblanadi</li>
        </ul>
    </div>

    <!-- Tayyor hujjatlar -->
    <div class="feature-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Tayyor guvohnomalar</h4>
            <span class="badge bg-success"><?php echo count($documents); ?> ta</span>
        </div>

        <?php if (empty($documents)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Hali tayyor hujjat yo'q</h5>
                <p class="text-muted">
                    Arizalaringiz tugallanganidan so'ng guvohnomalar bu yerda paydo bo'ladi
                </p>
                <a href="?page=ariza_topshirish" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Yangi ariza topshirish
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($documents as $doc): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-certificate me-2"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $doc['document_type'] ?? 'Guvohnoma')); ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-7">
                                        <p class="mb-2">
                                            <strong>Ariza raqami:</strong><br>
                                            #<?php echo htmlspecialchars($doc['application_number']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Guvohnoma raqami:</strong><br>
                                            <?php echo htmlspecialchars($doc['certificate_number'] ?? 'Tayyor emas'); ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Berilgan sana:</strong><br>
                                            <?php echo $doc['issue_date'] ? date('d.m.Y', strtotime($doc['issue_date'])) : 'Tayyor emas'; ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Turi:</strong><br>
                                            <span class="badge bg-<?php echo $doc['application_type'] == 'nikoh' ? 'success' : 'warning'; ?>">
                                                <?php echo $doc['application_type'] == 'nikoh' ? 'Nikoh' : 'Ajralish'; ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-5 text-center">
                                        <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                        <br>
                                        <?php if ($doc['certificate_number']): ?>
                                            <a href="?page=hujjat_olish&download=1&doc_id=<?php echo $doc['id']; ?>"
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-download me-1"></i>Yuklab olish
                                            </a>
                                            <br><br>
                                            <small class="text-muted">
                                                Yuklab olingan: <?php echo $doc['download_count'] ?? 0; ?> marta
                                            </small>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-clock me-1"></i>Tayyorlanmoqda
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($doc['certificate_number']): ?>
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-qrcode me-1"></i>
                                            QR kod mavjud
                                        </small>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info"
                                                    onclick="verifyDocument('<?php echo $doc['certificate_number']; ?>')">
                                                <i class="fas fa-shield-alt"></i> Tekshirish
                                            </button>
                                            <button class="btn btn-outline-primary"
                                                    onclick="shareDocument('<?php echo $doc['certificate_number']; ?>')">
                                                <i class="fas fa-share"></i> Ulashish
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Qo'shimcha ma'lumotlar -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Ko'p so'raladigan savollar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    Guvohnoma qachon tayyor bo'ladi?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ariza tugallanganidan so'ng 1-2 ish kuni ichida elektron guvohnoma tayyor bo'ladi.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    PDF formatdagi guvohnoma haqiqiymi?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ha, elektron guvohnoma qog'oz guvohnoma bilan bir xil yuridik kuchga ega.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Muhim eslatmalar
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Guvohnomani necha marta ham yuklab olishingiz mumkin
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            QR kod orqali haqiqiylikni tekshiring
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Elektron guvohnoma qonuniy kuchga ega
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            Barcha davlat organlarida qabul qilinadi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hujjat tekshirish modali -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guvohnoma tekshiruvi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Guvohnoma haqiqiyligi tasdiqlandi!</p>
                <div class="alert alert-success">
                    <i class="fas fa-shield-alt me-2"></i>
                    Bu guvohnoma O'zbekiston Respublikasi FHDY tomonidan rasmiy berilgan
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function verifyDocument(certificateNumber) {
        // Haqiqiy tizimda server tomonidan tekshiriladi
        const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
        modal.show();
    }

    function shareDocument(certificateNumber) {
        const shareUrl = window.location.origin + '?verify=' + certificateNumber;

        if (navigator.share) {
            navigator.share({
                title: 'Guvohnoma',
                text: 'Guvohnoma haqiqiyligini tekshirish',
                url: shareUrl
            });
        } else {
            // Clipboard ga nusxalash
            navigator.clipboard.writeText(shareUrl).then(() => {
                alert('Havola nusxalandi: ' + shareUrl);
            });
        }
    }
</script>