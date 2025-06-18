<?php
/**
 * pages/user/login.php - Foydalanuvchi tizimga kirish
 * Nikoh Portali
 */

// Agar foydalanuvchi allaqachon kirib olgan bo'lsa
if ($user) {
    redirect('user_dashboard');
}

// Login jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    try {
        // CSRF token tekshiruvi
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Xavfsizlik xatosi. Sahifani yangilang.');
        }

        $passport = sanitize($_POST['passport'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validatsiya
        if (empty($passport)) {
            throw new Exception('Pasport seriyasi va raqamini kiriting');
        }

        if (empty($password)) {
            throw new Exception('Parolni kiriting');
        }

        // Login urinishlarini tekshirish
        $ip = getRealIP();
        $sql = "SELECT COUNT(*) as attempts FROM system_logs 
                WHERE action = 'failed_login' 
                AND ip_address = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $result = fetchOne($sql, [$ip]);

        if (($result['attempts'] ?? 0) >= MAX_LOGIN_ATTEMPTS) {
            throw new Exception('Juda ko\'p noto\'g\'ri urinishlar. 15 daqiqa kutib turing.');
        }

        // Login qilish
        $user_data = loginUser($passport, $password, $remember);

        $_SESSION['success_message'] = 'Xush kelibsiz, ' . $user_data['first_name'] . '!';
        redirect('user_dashboard');

    } catch (Exception $e) {
        // Noto'g'ri login urinishini log qilish
        logActivity('failed_login', null, null, [
            'passport' => $passport ?? 'unknown',
            'ip' => getRealIP(),
            'error' => $e->getMessage()
        ]);

        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Remember token orqali avtomatik kirish
if (isset($_COOKIE['remember_token']) && !$user) {
    try {
        loginByRememberToken();
        redirect('user_dashboard');
    } catch (Exception $e) {
        // Cookie ni o'chirish
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-sign-in-alt me-2"></i>Tizimga kirish</h2>
                    <p class="text-muted">Pasport ma'lumotlaringiz bilan kiring</p>
                </div>

                <form method="POST" data-validate="true" autocomplete="on">
                    <?php echo csrfInput(); ?>

                    <div class="form-group mb-3">
                        <label for="passport" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Pasport seriyasi va raqami
                        </label>
                        <input type="text"
                               class="form-control"
                               id="passport"
                               name="passport"
                               placeholder="AA1234567"
                               value="<?php echo htmlspecialchars($_POST['passport'] ?? ''); ?>"
                               data-validate="passport"
                               autocomplete="username"
                               required>
                        <div class="invalid-feedback"></div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Pasportingizdagi seriya va raqamni kiriting (masalan: AA1234567)
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Parol
                        </label>
                        <div class="input-group">
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="Parolingizni kiriting"
                                   autocomplete="current-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="remember"
                                           name="remember">
                                    <label class="form-check-label" for="remember">
                                        Meni eslab qol (30 kun)
                                    </label>
                                </div>
                            </div>
                            <div class="col text-end">
                                <a href="?page=forgot_password" class="text-decoration-none">
                                    Parolni unutdingizmi?
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" name="login" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Kirish
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0">
                            Hisobingiz yo'qmi?
                            <a href="?page=register" class="text-decoration-none">
                                <strong>Ro'yxatdan o'ting</strong>
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Qo'shimcha ma'lumotlar -->
            <div class="mt-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                                <h6>Xavfsiz kirish</h6>
                                <small class="text-muted">SSL himoyasi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-clock text-info fa-2x mb-2"></i>
                                <h6>24/7 xizmat</h6>
                                <small class="text-muted">Doimo ochiq</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yordam -->
            <div class="card border-0 bg-light mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-question-circle me-2"></i>Yordam kerakmi?
                    </h6>
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1">
                                <i class="fas fa-phone text-success me-1"></i>
                                <small><?php echo SITE_PHONE; ?></small>
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1">
                                <i class="fab fa-telegram text-info me-1"></i>
                                <small>@nikoh_portal_bot</small>
                            </p>
                        </div>
                    </div>
                    <p class="mb-0">
                        <i class="fas fa-envelope text-primary me-1"></i>
                        <small><?php echo SUPPORT_EMAIL; ?></small>
                    </p>
                </div>
            </div>
        </div>

        <!-- O'ng tomonda ma'lumotlar -->
        <div class="col-md-6 col-lg-4 offset-lg-1 d-none d-md-block">
            <div class="sticky-top" style="top: 100px;">
                <h4 class="mb-4">Tizimga kirish orqali:</h4>

                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Ariza topshiring</h6>
                                <small class="text-muted">Nikoh yoki ajralish uchun</small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Holatni kuzating</h6>
                                <small class="text-muted">Real vaqtda yangilanish</small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-download"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Hujjat oling</h6>
                                <small class="text-muted">Elektron guvohnoma</small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="feature-icon me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Onlayn to'lang</h6>
                                <small class="text-muted">Click, Payme, UzCard</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Maslahat
                    </h6>
                    <p class="mb-0">
                        Birinchi marta kirish uchun avval
                        <a href="?page=register" class="alert-link">ro'yxatdan o'ting</a>.
                        Pasport ma'lumotlaringiz bilan tizimga kirishingiz mumkin.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Parolni ko'rsatish/yashirish
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Pasport formatini avtomatik sozlash
        const passportInput = document.getElementById('passport');
        if (passportInput) {
            passportInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

                // AA1234567 formatini ta'minlash
                if (value.length > 2) {
                    value = value.substring(0, 2) + value.substring(2).replace(/[^0-9]/g, '');
                }

                if (value.length > 9) {
                    value = value.substring(0, 9);
                }

                e.target.value = value;
            });

            // Paste event uchun
            passportInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    const event = new Event('input', { bubbles: true });
                    e.target.dispatchEvent(event);
                }, 10);
            });
        }

        // Form validation
        const form = document.querySelector('form[data-validate="true"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const passport = passportInput.value.trim();
                const password = passwordInput.value.trim();

                if (!passport || !password) {
                    e.preventDefault();
                    alert('Barcha maydonlarni to\'ldiring');
                    return;
                }

                if (!/^[A-Z]{2}\d{7}$/.test(passport)) {
                    e.preventDefault();
                    alert('Pasport formati noto\'g\'ri (AA1234567)');
                    passportInput.focus();
                    return;
                }

                // Loading animation
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kirish...';
                }
            });
        }

        // Auto-focus first empty field
        if (passportInput && !passportInput.value) {
            passportInput.focus();
        } else if (passwordInput && !passwordInput.value) {
            passwordInput.focus();
        }

        // Enter key navigation
        passportInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                passwordInput.focus();
            }
        });

        passwordInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.querySelector('button[type="submit"]')?.click();
            }
        });
    });
</script>