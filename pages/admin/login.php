<?php
/**
 * pages/admin/login.php - DEBUG VERSIYA
 * Admin login sahifasi
 */

// PDO ni olish
global $pdo, $admin;

// Agar allaqachon admin bo'lsa
if ($admin) {
    echo '<script>window.location.href = "?page=admin_dashboard";</script>';
    exit;
}

$debug_info = [];

// Login jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $debug_info[] = "POST ma'lumotlari keldi";
        $debug_info[] = "Username: " . htmlspecialchars($username);
        $debug_info[] = "Password length: " . strlen($password);

        if (empty($username) || empty($password)) {
            throw new Exception('Barcha maydonlarni to\'ldiring');
        }

        $debug_info[] = "Validatsiya o'tdi";

        // Database connection tekshiruvi
        if (!$pdo) {
            throw new Exception('Ma\'lumotlar bazasiga ulanmadi');
        }

        $debug_info[] = "Database connection bor";

        // Admin topish
        $sql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $admin_data = $stmt->fetch();

        $debug_info[] = "SQL bajarildi";

        if (!$admin_data) {
            $debug_info[] = "Admin topilmadi";
            throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
        }

        $debug_info[] = "Admin topildi: " . $admin_data['full_name'];

        // Parol tekshiruvi
        if (!password_verify($password, $admin_data['password_hash'])) {
            $debug_info[] = "Parol mos kelmadi";
            throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
        }

        $debug_info[] = "Parol to'g'ri";

        // Session yaratish
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin_data['id'];
        $_SESSION['admin_username'] = $admin_data['username'];
        $_SESSION['admin_name'] = $admin_data['full_name'];
        $_SESSION['admin_role'] = $admin_data['role'];
        $_SESSION['admin_login_time'] = time();

        $debug_info[] = "Session yaratildi";

        // Oxirgi kirish vaqtini yangilash
        $sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_data['id']]);

        $debug_info[] = "Last login yangilandi";

        $_SESSION['success_message'] = 'Xush kelibsiz, ' . $admin_data['full_name'];

        // Redirect
        $debug_info[] = "Redirect qilinmoqda...";
        echo '<script>console.log("Redirect to admin dashboard"); window.location.href = "?page=admin_dashboard";</script>';
        echo '<meta http-equiv="refresh" content="1;url=?page=admin_dashboard">';
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        $debug_info[] = "Xatolik: " . $e->getMessage();
    }
}

// Database connection test
$db_test = false;
$admin_count = 0;
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
        $result = $stmt->fetch();
        $admin_count = $result['count'];
        $db_test = true;
    }
} catch (Exception $e) {
    $debug_info[] = "DB Test xatolik: " . $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-container">
                <div class="form-header text-center">
                    <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                    <h2>FHDY Xodimlari</h2>
                    <p class="text-muted">Admin panelga kirish</p>
                </div>

                <!-- Debug ma'lumotlari (faqat development) -->
                <?php if (defined('DEVELOPMENT') && DEVELOPMENT && !empty($debug_info)): ?>
                    <div class="alert alert-info">
                        <h6>Debug ma'lumotlari:</h6>
                        <ul class="mb-0">
                            <?php foreach ($debug_info as $info): ?>
                                <li><?php echo htmlspecialchars($info); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Database holati -->
                <div class="alert alert-<?php echo $db_test ? 'success' : 'danger'; ?>">
                    <strong>Database holati:</strong>
                    <?php if ($db_test): ?>
                        ✅ Ulangan (<?php echo $admin_count; ?> admin mavjud)
                    <?php else: ?>
                        ❌ Ulanmagan
                    <?php endif; ?>
                </div>

                <!-- Xato xabarlari -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="adminLoginForm">
                    <div class="form-group mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i>Foydalanuvchi nomi
                        </label>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               placeholder="Username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required>
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
                                   placeholder="Password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" name="admin_login" class="btn btn-primary btn-lg" id="loginBtn">
                            <i class="fas fa-sign-in-alt me-2"></i>Kirish
                        </button>
                    </div>
                </form>

                <!-- Demo ma'lumotlar -->
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Demo ma'lumotlar</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Username:</strong> admin</p>
                        <p class="mb-2"><strong>Password:</strong> password</p>
                        <button onclick="fillDemo()" class="btn btn-outline-info btn-sm">
                            Demo ma'lumotlarni to'ldirish
                        </button>
                    </div>
                </div>

                <!-- Test tugmasi -->
                <div class="text-center mt-3">
                    <button onclick="testLogin()" class="btn btn-warning btn-sm">
                        <i class="fas fa-bug me-1"></i>Login ni test qilish
                    </button>
                </div>

                <!-- Foydalanuvchilar uchun -->
                <div class="text-center mt-4">
                    <p class="text-muted">Fuqaro sifatida?</p>
                    <a href="?page=login" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>Fuqarolar uchun
                    </a>
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

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // Form submit
        const form = document.getElementById('adminLoginForm');
        form.addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');

            // Loading animation
            setTimeout(() => {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kirilmoqda...';
            }, 100);
        });
    });

    function fillDemo() {
        document.getElementById('username').value = 'admin';
        document.getElementById('password').value = 'password';
    }

    function testLogin() {
        // AJAX test
        const formData = new FormData();
        formData.append('username', 'admin');
        formData.append('password', 'password');
        formData.append('admin_login', '1');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                console.log('Test response:', data);
                alert('Test tugadi. Console ni tekshiring.');
            })
            .catch(error => {
                console.error('Test error:', error);
                alert('Test xatolik: ' + error);
            });
    }
</script>