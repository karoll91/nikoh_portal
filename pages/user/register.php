<?php
/**
 * pages/user/register.php - Foydalanuvchi ro'yxatdan o'tish
 * Nikoh Portali
 */

// Agar foydalanuvchi allaqachon kirib olgan bo'lsa
if ($user) {
    redirect('user_dashboard');
}

// Ro'yxatdan o'tish jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    try {
        // CSRF token tekshiruvi
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Xavfsizlik xatosi. Sahifani yangilang.');
        }

        $data = [
            'passport_series' => sanitize($_POST['passport_series'] ?? ''),
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'middle_name' => sanitize($_POST['middle_name'] ?? ''),
            'birth_date' => sanitize($_POST['birth_date'] ?? ''),
            'birth_place' => sanitize($_POST['birth_place'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'gender' => sanitize($_POST['gender'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'citizenship' => sanitize($_POST['citizenship'] ?? 'O\'zbekiston')
        ];

        // Shartlarga rozilik tekshiruvi
        if (!isset($_POST['terms_agree'])) {
            throw new Exception('Foydalanish shartlariga rozilik berish majburiy');
        }

        // Ro'yxatdan o'tish
        $result = registerUser($data);

        $_SESSION['success_message'] = 'Ro\'yxatdan o\'tish muvaffaqiyatli! SMS orqali tasdiqlash kodi yuborildi.';
        $_SESSION['temp_passport'] = $data['passport_series'];
        redirect('verify_account');

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Viloyatlar ro'yxati
$regions = [
    'Toshkent shahri', 'Toshkent viloyati', 'Andijon viloyati', 'Buxoro viloyati',
    'Jizzax viloyati', 'Qashqadaryo viloyati', 'Navoiy viloyati', 'Namangan viloyati',
    'Samarqand viloyati', 'Surxondaryo viloyati', 'Sirdaryo viloyati', 'Farg\'ona viloyati',
    'Xorazm viloyati', 'Qoraqalpog\'iston Respublikasi'
];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-user-plus me-2"></i>Ro'yxatdan o'tish</h2>
                    <p class="text-muted">Nikoh portalida hisobingizni yarating</p>
                </div>

                <form method="POST" data-validate="true" autocomplete="on">
                    <?php echo csrfInput(); ?>

                    <!-- Pasport ma'lumotlari -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Pasport ma'lumotlari</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="passport_series" class="form-label">
                                            Pasport seriyasi va raqami *
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="passport_series"
                                               name="passport_series"
                                               placeholder="AA1234567"
                                               value="<?php echo htmlspecialchars($_POST['passport_series'] ?? ''); ?>"
                                               data-validate="passport"
                                               autocomplete="off"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="citizenship" class="form-label">
                                            Fuqaroligi *
                                        </label>
                                        <select class="form-control" id="citizenship" name="citizenship" required>
                                            <option value="O'zbekiston" <?php echo ($_POST['citizenship'] ?? 'O\'zbekiston') === 'O\'zbekiston' ? 'selected' : ''; ?>>
                                                O'zbekiston
                                            </option>
                                            <option value="Qozog'iston" <?php echo ($_POST['citizenship'] ?? '') === 'Qozog\'iston' ? 'selected' : ''; ?>>
                                                Qozog'iston
                                            </option>
                                            <option value="Qirg'iziston" <?php echo ($_POST['citizenship'] ?? '') === 'Qirg\'iziston' ? 'selected' : ''; ?>>
                                                Qirg'iziston
                                            </option>
                                            <option value="Tojikiston" <?php echo ($_POST['citizenship'] ?? '') === 'Tojikiston' ? 'selected' : ''; ?>>
                                                Tojikiston
                                            </option>
                                            <option value="Turkmaniston" <?php echo ($_POST['citizenship'] ?? '') === 'Turkmaniston' ? 'selected' : ''; ?>>
                                                Turkmaniston
                                            </option>
                                            <option value="Boshqa" <?php echo ($_POST['citizenship'] ?? '') === 'Boshqa' ? 'selected' : ''; ?>>
                                                Boshqa
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shaxsiy ma'lumotlar -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Shaxsiy ma'lumotlar</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="last_name" class="form-label">
                                            Familiyasi *
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="last_name"
                                               name="last_name"
                                               placeholder="Familiyangiz"
                                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                               autocomplete="family-name"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="first_name" class="form-label">
                                            Ismi *
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="first_name"
                                               name="first_name"
                                               placeholder="Ismingiz"
                                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                               autocomplete="given-name"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="middle_name" class="form-label">
                                            Otasining ismi *
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="middle_name"
                                               name="middle_name"
                                               placeholder="Otangizning ismi"
                                               value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>"
                                               autocomplete="additional-name"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="birth_date" class="form-label">
                                            Tug'ilgan sanasi *
                                        </label>
                                        <input type="date"
                                               class="form-control"
                                               id="birth_date"
                                               name="birth_date"
                                               value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>"
                                               max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                               autocomplete="bday"
                                               required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="gender" class="form-label">
                                            Jinsi *
                                        </label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="">Jinsni tanlang</option>
                                            <option value="erkak" <?php echo ($_POST['gender'] ?? '') === 'erkak' ? 'selected' : ''; ?>>
                                                Erkak
                                            </option>
                                            <option value="ayol" <?php echo ($_POST['gender'] ?? '') === 'ayol' ? 'selected' : ''; ?>>
                                                Ayol
                                            </option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="birth_place" class="form-label">
                                            Tug'ilgan joyi *
                                        </label>
                                        <select class="form-control" id="birth_place" name="birth_place" required>
                                            <option value="">Viloyatni tanlang</option>
                                            <?php foreach ($regions as $region): ?>
                                                <option value="<?php echo $region; ?>"
                                                    <?php echo ($_POST['birth_place'] ?? '') === $region ? 'selected' : ''; ?>>
                                                    <?php echo $region; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bog'lanish ma'lumotlari -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Bog'lanish ma'lumotlari</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">
                                            Telefon raqami *
                                        </label>
                                        <input type="tel"
                                               class="form-control"
                                               id="phone"
                                               name="phone"
                                               placeholder="+998901234567"
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                               data-validate="phone"
                                               autocomplete="tel"
                                               required>
                                        <div class="invalid-feedback"></div>
                                        <div class="form-text">
                                            <i class="fas fa-sms me-1"></i>
                                            Tasdiqlash kodi shu raqamga yuboriladi
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">
                                            Email manzil
                                        </label>
                                        <input type="email"
                                               class="form-control"
                                               id="email"
                                               name="email"
                                               placeholder="email@example.com"
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                               data-validate="email"
                                               autocomplete="email">
                                        <div class="invalid-feedback"></div>
                                        <div class="form-text">Ixtiyoriy</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="address" class="form-label">
                                    Yashash manzili *
                                </label>
                                <textarea class="form-control"
                                          id="address"
                                          name="address"
                                          rows="2"
                                          placeholder="To'liq yashash manzilingizni kiriting"
                                          autocomplete="street-address"
                                          required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Xavfsizlik -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Xavfsizlik</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="password" class="form-label">
                                            Parol *
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                   class="form-control"
                                                   id="password"
                                                   name="password"
                                                   placeholder="Parolingizni o'ylab toping"
                                                   data-validate="password"
                                                   autocomplete="new-password"
                                                   required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                        <div class="form-text">
                                            Kamida 6 ta belgi, harflar va raqamlar
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="confirm_password" class="form-label">
                                            Parolni tasdiqlang *
                                        </label>
                                        <div class="input-group">
                                            <input type="password"
                                                   class="form-control"
                                                   id="confirm_password"
                                                   name="confirm_password"
                                                   placeholder="Parolni qayta kiriting"
                                                   data-validate="confirm-password"
                                                   autocomplete="new-password"
                                                   required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rozilik -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms_agree" name="terms_agree" required>
                                <label class="form-check-label" for="terms_agree">
                                    <a href="?page=terms" target="_blank">Foydalanish shartlari</a> va
                                    <a href="?page=privacy" target="_blank">Maxfiylik siyosati</a>ga roziman *
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Yangiliklar va muhim xabarnomalarni email orqali olishni xohlayman
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" name="register" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Ro'yxatdan o'tish
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0">
                            Allaqachon hisobingiz bormi?
                            <a href="?page=login" class="text-decoration-none">
                                <strong>Kirish</strong>
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggles
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');

        function setupPasswordToggle(toggleBtn, input) {
            if (toggleBtn && input) {
                toggleBtn.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);

                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }
        }

        setupPasswordToggle(togglePassword, passwordInput);
        setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);

        // Pasport formatini avtomatik sozlash
        const passportInput = document.getElementById('passport_series');
        if (passportInput) {
            passportInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

                if (value.length > 2) {
                    value = value.substring(0, 2) + value.substring(2).replace(/[^0-9]/g, '');
                }

                if (value.length > 9) {
                    value = value.substring(0, 9);
                }

                e.target.value = value;
            });
        }

        // Telefon formatini avtomatik sozlash
        const phoneInput = document.getElementById('phone');
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

                if (value.length > 13) {
                    value = value.substring(0, 13);
                }

                e.target.value = value;
            });
        }

        // Yosh tekshiruvi
        const birthDateInput = document.getElementById('birth_date');
        if (birthDateInput) {
            birthDateInput.addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();

                if (age < 18) {
                    this.setCustomValidity('18 yoshdan kichik bo\'lish mumkin emas');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }

        // Form submit
        const form = document.querySelector('form[data-validate="true"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ro\'yxatdan o\'tkazilmoqda...';
                }
            });
        }
    });
</script>