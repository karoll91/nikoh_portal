<?php
/**
 * pages/user/register.php - TUZATILGAN VERSIYA
 * Ro'yxatdan o'tish sahifasi
 */

// PDO ni olish
global $pdo, $user;

// Agar allaqachon tizimda bo'lsa, dashboard ga yo'naltirish
if ($user) {
    echo '<script>window.location.href = "?page=user_dashboard";</script>';
    exit;
}

// Ro'yxatdan o'tish jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passport = strtoupper(trim($_POST['passport'] ?? ''));
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? ''); // Otasining ismi
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Validatsiya
    if (empty($passport)) {
        $errors[] = 'Pasport seriyasi va raqamini kiriting';
    } elseif (!preg_match('/^[A-Z]{2}\d{7}$/', $passport)) {
        $errors[] = 'Pasport formati noto\'g\'ri (masalan: AA1234567)';
    }

    if (empty($first_name) || strlen($first_name) < 2) {
        $errors[] = 'Ismingizni to\'g\'ri kiriting (kamida 2 harf)';
    }

    if (empty($last_name) || strlen($last_name) < 2) {
        $errors[] = 'Familiyangizni to\'g\'ri kiriting (kamida 2 harf)';
    }

    if (empty($middle_name) || strlen($middle_name) < 2) {
        $errors[] = 'Otangizning ismini kiriting (kamida 2 harf)';
    }

    if (empty($phone)) {
        $errors[] = 'Telefon raqamini kiriting';
    } elseif (!preg_match('/^\+998\d{9}$/', $phone)) {
        $errors[] = 'Telefon raqami noto\'g\'ri (+998901234567)';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email manzil noto\'g\'ri';
    }

    if (empty($gender) || !in_array($gender, ['erkak', 'ayol'])) {
        $errors[] = 'Jinsingizni tanlang';
    }

    if (empty($birth_date)) {
        $errors[] = 'Tug\'ilgan sanangizni kiriting';
    } else {
        // Yosh tekshiruvi (18 yoshdan katta bo'lishi kerak)
        $birth_year = date('Y', strtotime($birth_date));
        $current_year = date('Y');
        if (($current_year - $birth_year) < 18) {
            $errors[] = '18 yoshdan kichik shaxslar ro\'yxatdan o\'ta olmaydi';
        }
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Parol kamida 6 ta belgidan iborat bo\'lishi kerak';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Parollar mos kelmaydi';
    }

    // Dublikat tekshiruvi
    if (empty($errors)) {
        try {
            // Pasport dublikati
            $sql = "SELECT id FROM users WHERE passport_series = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$passport]);
            if ($stmt->fetch()) {
                $errors[] = 'Bu pasport raqami bilan allaqachon ro\'yxatdan o\'tilgan';
            }

            // Telefon dublikati
            $sql = "SELECT id FROM users WHERE phone = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $errors[] = 'Bu telefon raqami bilan allaqachon ro\'yxatdan o\'tilgan';
            }

            // Email dublikati (agar kiritilgan bo'lsa)
            if (!empty($email)) {
                $sql = "SELECT id FROM users WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = 'Bu email manzil bilan allaqachon ro\'yxatdan o\'tilgan';
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Tekshirishda xatolik yuz berdi';
        }
    }

    // Xatolik bo'lmasa saqlash
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (
                passport_series, first_name, last_name, middle_name, 
                phone, email, gender, birth_date, password_hash, is_verified
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $passport,
                $first_name,
                $last_name,
                $middle_name,
                $phone,
                $email ?: null, // Bo'sh bo'lsa null qilib yuborish
                $gender,
                $birth_date,
                $password_hash
            ]);

            if ($result) {
                $_SESSION['success_message'] = 'Muvaffaqiyatli ro\'yxatdan o\'tdingiz! Endi tizimga kirishingiz mumkin.';
                echo '<script>window.location.href = "?page=login";</script>';
                exit;
            } else {
                $errors[] = 'Ro\'yxatdan o\'tishda xatolik yuz berdi';
            }
        } catch (Exception $e) {
            $errors[] = 'Ma\'lumotlar bazasida xatolik: ' . $e->getMessage();
        }
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-container">
                <div class="form-header">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h2>Ro'yxatdan o'tish</h2>
                    <p class="text-muted">Nikoh Portaliga xush kelibsiz</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>Xatoliklar:
                        </h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate="true">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="passport" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>Pasport seriyasi va raqami *
                                </label>
                                <input type="text"
                                       id="passport"
                                       name="passport"
                                       class="form-control"
                                       placeholder="AA1234567"
                                       value="<?php echo htmlspecialchars($passport ?? ''); ?>"
                                       maxlength="9"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>Telefon raqami *
                                </label>
                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       class="form-control"
                                       placeholder="+998901234567"
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Ismingiz *
                                </label>
                                <input type="text"
                                       id="first_name"
                                       name="first_name"
                                       class="form-control"
                                       placeholder="Ismingiz"
                                       value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Familiyangiz *
                                </label>
                                <input type="text"
                                       id="last_name"
                                       name="last_name"
                                       class="form-control"
                                       placeholder="Familiyangiz"
                                       value="<?php echo htmlspecialchars($last_name ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="middle_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Otangizning ismi *
                                </label>
                                <input type="text"
                                       id="middle_name"
                                       name="middle_name"
                                       class="form-control"
                                       placeholder="Otangizning ismi"
                                       value="<?php echo htmlspecialchars($middle_name ?? ''); ?>"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email manzil (ixtiyoriy)
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       class="form-control"
                                       placeholder="email@example.com"
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="gender" class="form-label">
                                    <i class="fas fa-venus-mars me-1"></i>Jinsingiz *
                                </label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="">Tanlang</option>
                                    <option value="erkak" <?php echo ($gender ?? '') === 'erkak' ? 'selected' : ''; ?>>Erkak</option>
                                    <option value="ayol" <?php echo ($gender ?? '') === 'ayol' ? 'selected' : ''; ?>>Ayol</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="birth_date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Tug'ilgan sana *
                                </label>
                                <input type="date"
                                       id="birth_date"
                                       name="birth_date"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($birth_date ?? ''); ?>"
                                       max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Parol *
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           id="password"
                                           name="password"
                                           class="form-control"
                                           placeholder="Kamida 6 ta belgi"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Parolni tasdiqlang *
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           id="confirm_password"
                                           name="confirm_password"
                                           class="form-control"
                                           placeholder="Parolni qaytaring"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                Foydalanish shartlari va <a href="?page=privacy" target="_blank">maxfiylik siyosati</a>ga roziman *
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                            <i class="fas fa-user-plus me-2"></i>Ro'yxatdan o'tish
                        </button>
                    </div>
                </form>

                <div class="text-center">
                    <p class="text-muted">Allaqachon hisobingiz bormi?</p>
                    <a href="?page=login" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Tizimga kirish
                    </a>
                </div>

                <!-- Xavfsizlik ma'lumoti -->
                <div class="card border-0 bg-light mt-4">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                        <h6>Ma'lumotlaringiz himoyalangan</h6>
                        <small class="text-muted">
                            Barcha shaxsiy ma'lumotlar shifrlangan va xavfsiz saqlanadi
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password ko'rsatish/yashirish
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Pasport formatini avtomatik sozlash
        const passportInput = document.getElementById('passport');
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

        // Telefon formatini avtomatik sozlash
        const phoneInput = document.getElementById('phone');
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

        // Parol mos kelishini tekshirish
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (confirmPassword && password !== confirmPassword) {
                confirmPasswordInput.setCustomValidity('Parollar mos kelmaydi');
                confirmPasswordInput.classList.add('is-invalid');
            } else {
                confirmPasswordInput.setCustomValidity('');
                confirmPasswordInput.classList.remove('is-invalid');
                if (confirmPassword) {
                    confirmPasswordInput.classList.add('is-valid');
                }
            }
        }

        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        // Form yuborish
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const registerBtn = document.getElementById('registerBtn');

            // Barcha talablar bajarilganligini tekshirish
            if (!document.getElementById('agree_terms').checked) {
                e.preventDefault();
                alert('Foydalanish shartlariga rozilik berish majburiy');
                return;
            }

            // Loading animation
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ro\'yxatdan o\'tilmoqda...';
        });
    });
</script>