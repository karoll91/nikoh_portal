<?php
/**
 * pages/contact.php - Bog'lanish sahifasi
 * Nikoh Portali
 */

// Xabar yuborish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    try {
        // CSRF token tekshiruvi
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Xavfsizlik xatosi. Sahifani yangilang.');
        }

        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        // Validatsiya
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Ismingizni kiriting';
        }

        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'To\'g\'ri email manzil kiriting';
        }

        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'To\'g\'ri telefon raqami kiriting (+998xxxxxxxxx)';
        }

        if (empty($subject)) {
            $errors[] = 'Xabar mavzusini kiriting';
        }

        if (empty($message)) {
            $errors[] = 'Xabar matnini kiriting';
        }

        if (empty($errors)) {
            // Xabarni ma'lumotlar bazasiga saqlash (contacts jadvalini yaratish kerak)
            $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            executeQuery($sql, [
                $name,
                $email,
                $phone,
                $subject,
                $message,
                getRealIP(),
                getUserAgent()
            ]);

            // Admin emailga xabar yuborish
            $admin_message = "Yangi xabar:\n\n";
            $admin_message .= "Ism: {$name}\n";
            $admin_message .= "Email: {$email}\n";
            $admin_message .= "Telefon: {$phone}\n";
            $admin_message .= "Mavzu: {$subject}\n\n";
            $admin_message .= "Xabar:\n{$message}";

            sendEmail(ADMIN_EMAIL, 'Yangi xabar - Nikoh Portali', $admin_message);

            // Log yozish
            logActivity('contact_message_sent', $user['id'] ?? null, null, [
                'email' => $email,
                'subject' => $subject
            ]);

            $_SESSION['success_message'] = 'Xabaringiz muvaffaqiyatli yuborildi. Tez orada javob beramiz.';
            redirect('contact');
        } else {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">Bog'lanish</h1>
            <p class="lead text-muted">
                Savollaringiz bo'lsa, biz bilan bog'laning. Sizga yordam berishdan mamnunmiz.
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Bog'lanish ma'lumotlari -->
        <div class="col-lg-4 mb-4">
            <div class="feature-card h-100">
                <h3 class="mb-4">Bog'lanish ma'lumotlari</h3>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="feature-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Manzil</h6>
                            <p class="text-muted mb-0">Toshkent shahri, Yunusobod tumani</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="feature-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Telefon</h6>
                            <p class="text-muted mb-0"><?php echo SITE_PHONE; ?></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="feature-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Email</h6>
                            <p class="text-muted mb-0"><?php echo SITE_EMAIL; ?></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="feature-icon me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fab fa-telegram"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Telegram</h6>
                            <p class="text-muted mb-0">@nikoh_portal_bot</p>
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Ish vaqti</h5>
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Dushanba - Juma</strong></p>
                        <p class="text-muted small">09:00 - 18:00</p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><strong>Tushlik</strong></p>
                        <p class="text-muted small">12:00 - 13:00</p>
                    </div>
                </div>

                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-clock me-1"></i>
                        Dam olish kunlari: Shanba, Yakshanba
                    </small>
                </div>
            </div>
        </div>

        <!-- Xabar yuborish formasi -->
        <div class="col-lg-8">
            <div class="feature-card">
                <h3 class="mb-4">Xabar yuborish</h3>

                <form method="POST" data-validate="true">
                    <?php echo csrfInput(); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Ismingiz *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email manzil *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       data-validate="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">Telefon raqami</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="+998901234567" data-validate="phone">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="subject" class="form-label">Mavzu *</label>
                                <select class="form-control" id="subject" name="subject" required>
                                    <option value="">Mavzuni tanlang</option>
                                    <option value="Nikoh haqida savol" <?php echo ($_POST['subject'] ?? '') === 'Nikoh haqida savol' ? 'selected' : ''; ?>>
                                        Nikoh haqida savol
                                    </option>
                                    <option value="Ajralish haqida savol" <?php echo ($_POST['subject'] ?? '') === 'Ajralish haqida savol' ? 'selected' : ''; ?>>
                                        Ajralish haqida savol
                                    </option>
                                    <option value="To'lov bilan bog'liq" <?php echo ($_POST['subject'] ?? '') === 'To\'lov bilan bog\'liq' ? 'selected' : ''; ?>>
                                        To'lov bilan bog'liq
                                    </option>
                                    <option value="Texnik muammo" <?php echo ($_POST['subject'] ?? '') === 'Texnik muammo' ? 'selected' : ''; ?>>
                                        Texnik muammo
                                    </option>
                                    <option value="Hujjat olish" <?php echo ($_POST['subject'] ?? '') === 'Hujjat olish' ? 'selected' : ''; ?>>
                                        Hujjat olish
                                    </option>
                                    <option value="Boshqa" <?php echo ($_POST['subject'] ?? '') === 'Boshqa' ? 'selected' : ''; ?>>
                                        Boshqa
                                    </option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="message" class="form-label">Xabar matni *</label>
                        <textarea class="form-control" id="message" name="message" rows="5"
                                  placeholder="Savolingizni batafsil yozing..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="privacy_agree" required>
                            <label class="form-check-label" for="privacy_agree">
                                <a href="?page=privacy" target="_blank">Maxfiylik siyosati</a>ga roziman *
                            </label>
                        </div>
                    </div>

                    <button type="submit" name="send_message" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Xabar yuborish
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tez-tez beriladigan savollar -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="feature-card">
                <h3 class="mb-4">Tez-tez beriladigan savollar</h3>

                <div class="accordion" id="contactFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                Arizam qancha vaqtda ko'rib chiqiladi?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                Barcha hujjatlar to'g'ri topshirilgan va to'lov amalga oshirilgandan so'ng,
                                arizangiz 3-5 ish kuni ichida ko'rib chiqiladi. Jarayonning har bir bosqichi
                                haqida SMS orqali xabar beriladi.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                To'lovda muammo bo'lsa qanday qilaman?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                To'lov bilan bog'liq muammolar uchun qo'llab-quvvatlash xizmatiga murojaat qiling.
                                Telefon: <?php echo SITE_PHONE; ?> yoki Email: <?php echo SUPPORT_EMAIL; ?>.
                                To'lov chekini saqlang.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                Guvohnomani qanday olaman?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                Guvohnoma tayyor bo'lgandan so'ng SMS xabar keladi. Shaxsiy kabinetingizdan
                                "Hujjat olish" bo'limiga o'tib, elektron guvohnomani yuklab olishingiz mumkin.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                Arizamni bekor qila olamanmi?
                            </button>
                        </h2>
                        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                Ariza ko'rib chiqilishdan oldin bekor qilish mumkin. Bundan keyin to'lov
                                qaytariladi. Ko'rib chiqilgandan keyin bekor qilish faqat FHDY organida
                                shaxsan murojaat qilish orqali amalga oshiriladi.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bog'lanish usullari -->
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="feature-card text-center h-100">
                <div class="feature-icon mx-auto mb-3">
                    <i class="fas fa-phone"></i>
                </div>
                <h5>Telefon orqali</h5>
                <p class="text-muted mb-3">
                    Tezkor javob olish uchun to'g'ridan-to'g'ri qo'ng'iroq qiling
                </p>
                <a href="tel:<?php echo str_replace([' ', '-'], '', SITE_PHONE); ?>" class="btn btn-outline-primary">
                    <i class="fas fa-phone me-2"></i>Qo'ng'iroq qilish
                </a>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="feature-card text-center h-100">
                <div class="feature-icon mx-auto mb-3">
                    <i class="fab fa-telegram"></i>
                </div>
                <h5>Telegram bot</h5>
                <p class="text-muted mb-3">
                    24/7 avtomatik javob va asosiy ma'lumotlar
                </p>
                <a href="https://t.me/nikoh_portal_bot" target="_blank" class="btn btn-outline-primary">
                    <i class="fab fa-telegram me-2"></i>Botga yozish
                </a>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="feature-card text-center h-100">
                <div class="feature-icon mx-auto mb-3">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h5>Shaxsan tashrif</h5>
                <p class="text-muted mb-3">
                    FHDY organiga shaxsan tashrif buyuring
                </p>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#officesModal">
                    <i class="fas fa-map-marker-alt me-2"></i>Manzillar
                </button>
            </div>
        </div>
    </div>

    <!-- Xizmat ko'rsatish soatlari -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="feature-card">
                <h3 class="mb-4">Xizmat ko'rsatish vaqtlari</h3>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Onlayn xizmatlar</h5>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Ariza topshirish</span>
                            <span class="badge bg-success">24/7</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>To'lov qilish</span>
                            <span class="badge bg-success">24/7</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Hujjat olish</span>
                            <span class="badge bg-success">24/7</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span>Holat kuzatish</span>
                            <span class="badge bg-success">24/7</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Qo'llab-quvvatlash</h5>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Telefon qo'llab-quvvatlash</span>
                            <span class="badge bg-info">09:00-18:00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Email javoblari</span>
                            <span class="badge bg-info">24 soat ichida</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span>Telegram bot</span>
                            <span class="badge bg-success">24/7</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span>Shaxsiy maslahat</span>
                            <span class="badge bg-warning">Oldindan kelishuv</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FHDY organlari modali -->
<div class="modal fade" id="officesModal" tabindex="-1" aria-labelledby="officesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="officesModalLabel">
                    <i class="fas fa-map-marker-alt me-2"></i>FHDY organlari manzillari
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Toshkent shahar FHDY</h6>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    Yunusobod tumani, Amir Temur ko'chasi, 42-uy
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    +998 71 123-45-67
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    09:00 - 18:00 (Du-Ju)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Samarqand viloyat FHDY</h6>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    Samarqand shahri, Registon ko'chasi, 15-uy
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    +998 66 234-56-78
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    09:00 - 18:00 (Du-Ju)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Buxoro viloyat FHDY</h6>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    Buxoro shahri, Mustaqillik ko'chasi, 28-uy
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    +998 65 345-67-89
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    09:00 - 18:00 (Du-Ju)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Farg'ona viloyat FHDY</h6>
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    Farg'ona shahri, Istiqlol ko'chasi, 85-uy
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    +998 73 456-78-90
                                </p>
                                <p class="card-text">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    09:00 - 18:00 (Du-Ju)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Eslatma:</strong> Shaxsan tashrif buyurishdan oldin telefon orqali
                    oldindan vaqt belgilab olishni tavsiya etamiz.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Yopish</button>
                <a href="https://maps.google.com" target="_blank" class="btn btn-primary">
                    <i class="fas fa-map me-2"></i>Google Maps da ochish
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Contact form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[data-validate="true"]');
        const phoneInput = document.getElementById('phone');

        // Telefon raqam formatini avtomatik sozlash
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.startsWith('998')) {
                    value = '+' + value;
                } else if (value.startsWith('0')) {
                    value = '+998' + value.substring(1);
                } else if (value.length > 0 && !value.startsWith('+998')) {
                    value = '+998' + value;
                }

                // Formatni cheklash
                if (value.length > 13) {
                    value = value.substring(0, 13);
                }

                e.target.value = value;
            });
        }

        // Form yuborishdan oldin so'nggi tekshiruv
        if (form) {
            form.addEventListener('submit', function(e) {
                const privacyCheck = document.getElementById('privacy_agree');
                if (!privacyCheck.checked) {
                    e.preventDefault();
                    alert('Maxfiylik siyosatiga rozilik berish majburiy');
                    privacyCheck.focus();
                }
            });
        }
    });
</script>