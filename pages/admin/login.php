<?php
/**
 * Admin login sahifasi - TUZATILGAN
 */

// Debug rejimi (keyinchalik o'chirish kerak)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($admin) {
    header('Location: ?page=admin_dashboard');
    exit;
}

// Login jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Debug ma'lumotlari
        if (defined('DEVELOPMENT') && DEVELOPMENT) {
            echo "<!-- Debug: Username='$username', Password length=" . strlen($password) . " -->";
        }

        if (empty($username) || empty($password)) {
            throw new Exception('Barcha maydonlarni to\'ldiring');
        }

        // Auth faylini yuklash
        if (!function_exists('loginAdmin')) {
            require_once 'includes/auth.php';
        }

        // Admin login
        $admin_data = loginAdmin($username, $password);

        $_SESSION['success_message'] = 'Xush kelibsiz, ' . $admin_data['full_name'];

        // Redirect with JavaScript (header redirect muammosi uchun)
        echo "<script>window.location.href = '?page=admin_dashboard';</script>";
        echo "<meta http-equiv='refresh' content='0;url=?page=admin_dashboard'>";
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();

        // Debug ma'lumotlari
        if (defined('DEVELOPMENT') && DEVELOPMENT) {
            error_log('Admin login error: ' . $e->getMessage());
        }
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

                <!-- Xato xabarlari -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $_SESSION['error_message']; ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" onsubmit="return validateForm()">
                    <div class="form-group mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i>Foydalanuvchi nomi
                        </label>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               placeholder="Foydalanuvchi nomingiz"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               autocomplete="username"
                               required>
                        <div class="invalid-feedback"></div>
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
                                   placeholder="Parolingiz"
                                   autocomplete="current-password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" name="admin_login" class="btn btn-primary btn-lg" id="loginBtn">
                            <i class="fas fa-sign-in-alt me-2"></i>Kirish
                        </button>
                    </div>
                </form>

                <!-- Demo ma'lumotlar -->
                <?php if (defined('DEVELOPMENT') && DEVELOPMENT): ?>
                    <div class="alert alert-info">
                        <small>
                            <strong>Demo uchun:</strong><br>
                            Username: admin<br>
                            Password: admin123
                        </small>
                    </div>
                <?php endif; ?>

                <!-- Test tugmasi (faqat development) -->
                <?php if (defined('DEVELOPMENT') && DEVELOPMENT): ?>
                    <div class="text-center mb-3">
                        <button onclick="fillDemoData()" class="btn btn-outline-info btn-sm">
                            Demo ma'lumotlarni to'ldirish
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Foydalanuvchilar uchun -->
                <div class="text-center mt-4">
                    <p class="text-muted">Fuqaro sifatida?</p>
                    <a href="?page=login" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>Fuqarolar uchun kirish
                    </a>
                </div>

                <!-- Debug ma'lumotlari (faqat development) -->
                <?php if (defined('DEVELOPMENT') && DEVELOPMENT && isset($_POST['admin_login'])): ?>
                    <div class="alert alert-warning mt-3">
                        <small>
                            <strong>Debug:</strong><br>
                            POST data mavjud: <?php echo isset($_POST['admin_login']) ? 'Ha' : 'Yo\'q'; ?><br>
                            Username: <?php echo htmlspecialchars($_POST['username'] ?? 'Bo\'sh'); ?><br>
                            Password length: <?php echo strlen($_POST['password'] ?? ''); ?><br>
                            Session admin_id: <?php echo $_SESSION['admin_id'] ?? 'Yo\'q'; ?>
                        </small>
                    </div>
                <?php endif; ?>

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

        // Auto-focus
        const usernameInput = document.getElementById('username');
        if (usernameInput && !usernameInput.value) {
            usernameInput.focus();
        }
    });

    function validateForm() {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const loginBtn = document.getElementById('loginBtn');

        if (!username || !password) {
            alert('Barcha maydonlarni to\'ldiring');
            return false;
        }

        // Loading animation
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kirish...';

        return true;
    }

    <?php if (defined('DEVELOPMENT') && DEVELOPMENT): ?>
    function fillDemoData() {
        document.getElementById('username').value = 'admin';
        document.getElementById('password').value = 'admin123';
    }
    <?php endif; ?>
</script>