<?php
/**
 * index.php - TUZATILGAN VERSIYA
 * Asosiy kirish fayli
 */

// 1. Session boshlash (eng birinchi!)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Output buffering
ob_start();

// 3. Xatoliklarni ko'rsatish (development uchun)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // 4. Konfiguratsiyani yuklash
    require_once 'config/config.php';

    // 5. Ma'lumotlar bazasi bilan bog'lanish (global $pdo yaratiladi)
    if (!isset($pdo)) {
        die('Ma\'lumotlar bazasi ulanmadi. config/database.php ni tekshiring.');
    }

} catch (Exception $e) {
    die('Konfiguratsiya xatoligi: ' . $e->getMessage());
}

// 6. URL routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Ruxsat etilgan sahifalar
$allowed_pages = [
    'home', 'about', 'contact', 'login', 'register', 'logout',
    'user_dashboard', 'ariza_topshirish', 'ariza_holati', 'hujjat_olish',
    'admin_login', 'admin_dashboard', 'arizalar', 'hisobotlar'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// 7. Foydalanuvchi va admin ma'lumotlarini olish
$user = null;
$admin = null;

if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

if (isset($_SESSION['admin_id'])) {
    $admin = getAdminById($_SESSION['admin_id']);
}

// 8. Logout sahifasi
if ($page === 'logout') {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();

    echo '<script>window.location.href = "?page=home";</script>';
    exit;
}

// 9. Avtorizatsiya tekshiruvi
$redirect_needed = false;
$redirect_url = '';

// Foydalanuvchi sahifalari uchun login talab qilish
if (in_array($page, ['user_dashboard', 'ariza_topshirish', 'ariza_holati', 'hujjat_olish']) && !$user) {
    $redirect_needed = true;
    $redirect_url = '?page=login';
}

// Admin sahifalari uchun admin login talab qilish
if (in_array($page, ['admin_dashboard', 'arizalar', 'hisobotlar']) && !$admin) {
    $redirect_needed = true;
    $redirect_url = '?page=admin_login';
}

// Agar login qilgan bo'lsa, login sahifalarini ko'rsatmaslik
if (in_array($page, ['login', 'register']) && $user) {
    $redirect_needed = true;
    $redirect_url = '?page=user_dashboard';
}

if ($page === 'admin_login' && $admin) {
    $redirect_needed = true;
    $redirect_url = '?page=admin_dashboard';
}

// Redirect qilish (JavaScript orqali)
if ($redirect_needed) {
    echo '<script>window.location.href = "' . $redirect_url . '";</script>';
    exit;
}

// Page title funksiyasi
function getPageTitle($page) {
    $titles = [
        'home' => 'Bosh sahifa',
        'about' => 'Tizim haqida',
        'contact' => 'Bog\'lanish',
        'login' => 'Tizimga kirish',
        'register' => 'Ro\'yxatdan o\'tish',
        'user_dashboard' => 'Shaxsiy kabinet',
        'admin_login' => 'Xodimlar uchun kirish',
        'admin_dashboard' => 'Boshqaruv paneli',
        'ariza_topshirish' => 'Ariza topshirish',
        'ariza_holati' => 'Ariza holati',
        'hujjat_olish' => 'Hujjat olish',
        'arizalar' => 'Arizalar',
        'hisobotlar' => 'Hisobotlar'
    ];
    return isset($titles[$page]) ? $titles[$page] : 'Sahifa';
}
?>
    <!DOCTYPE html>
    <html lang="uz">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo getPageTitle($page); ?> - Nikoh Portali</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <!-- Custom CSS -->
        <link href="assets/css/style.css" rel="stylesheet">

        <meta name="description" content="O'zbekiston Respublikasi FHDY - Online nikoh va ajralish arizalari">
        <meta name="keywords" content="nikoh, ajralish, FHDY, O'zbekiston, fuqarolik holati">
    </head>
    <body>
    <!-- Header -->
    <header class="bg-primary text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-rings-wedding me-2"></i>
                    <strong>Nikoh Portali</strong>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'home' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-home me-1"></i> Bosh sahifa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'about' ? 'active' : ''; ?>" href="?page=about">
                                <i class="fas fa-info-circle me-1"></i> Ma'lumot
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'contact' ? 'active' : ''; ?>" href="?page=contact">
                                <i class="fas fa-phone me-1"></i> Aloqa
                            </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav">
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($user['first_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?page=user_dashboard">
                                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=ariza_topshirish">
                                            <i class="fas fa-file-alt me-1"></i> Ariza topshirish
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=ariza_holati">
                                            <i class="fas fa-search me-1"></i> Ariza holati
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=hujjat_olish">
                                            <i class="fas fa-download me-1"></i> Hujjat olish
                                        </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?page=logout">
                                            <i class="fas fa-sign-out-alt me-1"></i> Chiqish
                                        </a></li>
                                </ul>
                            </li>
                        <?php elseif ($admin): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($admin['full_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?page=admin_dashboard">
                                            <i class="fas fa-cogs me-1"></i> Admin panel
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=arizalar">
                                            <i class="fas fa-file-alt me-1"></i> Arizalar
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=hisobotlar">
                                            <i class="fas fa-chart-bar me-1"></i> Hisobotlar
                                        </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?page=logout">
                                            <i class="fas fa-sign-out-alt me-1"></i> Chiqish
                                        </a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=login">
                                    <i class="fas fa-sign-in-alt me-1"></i> Kirish
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=register">
                                    <i class="fas fa-user-plus me-1"></i> Ro'yxatdan o'tish
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=admin_login">
                                    <i class="fas fa-user-shield me-1"></i> Xodimlar
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container-fluid">
        <?php
        // Xabarlarni ko'rsatish
        if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php
        // Sahifa kontentini yuklash
        $page_file = '';
        switch ($page) {
            case 'home':
                $page_file = 'pages/home.php';
                break;
            case 'about':
                $page_file = 'pages/about.php';
                break;
            case 'contact':
                $page_file = 'pages/contact.php';
                break;
            case 'login':
                $page_file = 'pages/user/login.php';
                break;
            case 'register':
                $page_file = 'pages/user/register.php';
                break;
            case 'user_dashboard':
                $page_file = 'pages/user/dashboard.php';
                break;
            case 'admin_login':
                $page_file = 'pages/admin/login.php';
                break;
            case 'admin_dashboard':
                $page_file = 'pages/admin/dashboard.php';
                break;
            case 'ariza_topshirish':
                $page_file = 'pages/user/ariza_topshirish.php';
                break;
            case 'ariza_holati':
                $page_file = 'pages/user/ariza_holati.php';
                break;
            case 'hujjat_olish':
                $page_file = 'pages/user/hujjat_olish.php';
                break;
            case 'arizalar':
                $page_file = 'pages/admin/arizalar.php';
                break;
            case 'hisobotlar':
                $page_file = 'pages/admin/hisobotlar.php';
                break;
            default:
                $page_file = 'pages/404.php';
                break;
        }

        // Sahifa faylini yuklash
        if ($page_file && file_exists($page_file)) {
            include $page_file;
        } else {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-warning text-center">';
            echo '<h4><i class="fas fa-exclamation-triangle me-2"></i>404 - Sahifa topilmadi</h4>';
            echo '<p>Siz qidirayotgan sahifa mavjud emas.</p>';
            echo '<a href="index.php" class="btn btn-primary">Bosh sahifaga qaytish</a>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-rings-wedding me-2"></i> Nikoh Portali</h5>
                    <p class="mb-2">O'zbekiston Respublikasi FHDY - Fuqarolik holati organlari</p>
                    <p class="text-muted small">
                        Vazirlar Mahkamasining 2023-yil qarori asosida
                    </p>
                </div>
                <div class="col-md-3">
                    <h6>Foydali havolalar</h6>
                    <ul class="list-unstyled">
                        <li><a href="?page=about" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right fa-sm me-1"></i> Tizim haqida
                            </a></li>
                        <li><a href="?page=contact" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right fa-sm me-1"></i> Bog'lanish
                            </a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Aloqa</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-1"></i> <?php echo SITE_PHONE; ?></li>
                        <li><i class="fas fa-envelope me-1"></i> <?php echo SITE_EMAIL; ?></li>
                        <li><i class="fas fa-map-marker-alt me-1"></i> Toshkent sh.</li>
                    </ul>
                </div>
            </div>
            <hr class="my-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 small">&copy; <?php echo date('Y'); ?> Nikoh Portali. Barcha huquqlar himoyalangan.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 small">
                        <i class="fas fa-shield-alt text-success me-1"></i>
                        Xavfsiz aloqa
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (alert.querySelector('.btn-close')) {
                        alert.querySelector('.btn-close').click();
                    }
                });
            }, 5000);
        });
    </script>
    </body>
    </html>
<?php ob_end_flush(); ?>