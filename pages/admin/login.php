<?php
/**
 * Admin login sahifasi
 */

if ($admin) {
    redirect('admin_dashboard');
}

// Login jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    try {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Xavfsizlik xatosi. Sahifani yangilang.');
        }

        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            throw new Exception('Barcha maydonlarni to\'ldiring');
        }

        // Admin login
        require_once 'includes/auth.php';
        $admin_data = loginAdmin($username, $password);

        $_SESSION['success_message'] = 'Xush kelibsiz, ' . $admin_data['full_name'];
        redirect('admin_dashboard');

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-container">
                <div class="form-header text-center">
                    <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                    <h2>FHDY Xodimlari</h2>
                    <p class="text-muted">Tizimga kirish</p>
                </div>

                <form method="POST" data-validate="true">
                    <?php echo csrfInput(); ?>

                    <div class="form-group mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i>Foydalanuvchi nomi
                        </label>
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Foydalanuvchi nomingiz"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               autocomplete="username" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Parol
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Parolingiz" autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" name="admin_login" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Kirish
                        </button>
                    </div>
                </form>

                <!-- Demo ma'lumotlar -->
                <div class="alert alert-info">
                    <small>
                        <strong>Demo uchun:</strong><br>
                        Username: admin<br>
                        Password: admin123
                    </small>
                </div>

                <!-- Foydalanuvchilar uchun -->
                <div class="text-center mt-4">
                    <p class="text-muted">Fuqaro sifatida?</p>
                    <a href="?page=login" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>Fuqarolar uchun kirish
                    </a>
                </div>

                <!-- Xavfsizlik -->
                <div class="card border-0 bg-light mt-4">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                        <h6>Xavfsiz kirish</h6>
                        <small class="text-muted">
                            Barcha ma'lumotlar himoyalangan.<br>
                            Faqat vakolatli xodimlar uchun.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle
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

        // Form submit
        const form = document.querySelector('form[data-validate="true"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kirish...';
                }
            });
        }
    });
</script>