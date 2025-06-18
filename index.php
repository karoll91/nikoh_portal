<?php
/**
 * Nikoh Portali - Bosh sahifa (FIXED VERSION)
 * O'zbekiston Respublikasi FHDY tizimi
 *
 * YECHIM: Headers already sent muammosini hal qilish uchun
 */

// 1. Hech qanday output OLDIN bo'lmasligi kerak
// 2. Session ni eng birinchi boshlash
// 3. Barcha include'lar outputsiz bo'lishi kerak

// Session boshlash (eng birinchi!)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Output buffering boshlash (headers muammosini hal qilish uchun)
ob_start();

// Xatoliklarni ko'rsatish (development uchun)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Asosiy konfiguratsiyani yuklash
    require_once 'config/config.php';
    require_once 'includes/functions.php';
    // Auth faylini yuklash (agar mavjud bo'lsa)
    if (file_exists('includes/auth.php')) {
        require_once 'includes/auth.php';
    }
} catch (Exception $e) {
    // Agar konfiguratsiya xatoligi bo'lsa
    die('Configuration error: ' . $e->getMessage());
}

// URL routing - qaysi sahifa ko'rsatilishi kerak
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Xavfsizlik uchun ruxsat etilgan sahifalar ro'yxati
$allowed_pages = [
    'home',
    'about',
    'contact',
    'login',
    'register',
    'user_dashboard',
    'admin_login',
    'admin_dashboard',
    'ariza_topshirish',
    'ariza_holati',
    'hujjat_olish',
    'logout',
    'arizalar',
    'foydalanuvchilar',
    'hisobotlar',
    'sozlamalar'
];

// Agar noto'g'ri sahifa so'ralsa, bosh sahifaga yo'naltirish
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// Foydalanuvchi autentifikatsiyasini tekshirish
$user = null;
$admin = null;

if (isset($_SESSION['user_id']) && function_exists('getUserById')) {
    $user = getUserById($_SESSION['user_id']);
}

if (isset($_SESSION['admin_id']) && function_exists('getAdminById')) {
    $admin = getAdminById($_SESSION['admin_id']);
}

// Page title funksiyasi (agar mavjud bo'lmasa)
if (!function_exists('getPageTitle')) {
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
            'foydalanuvchilar' => 'Foydalanuvchilar',
            'hisobotlar' => 'Hisobotlar',
            'sozlamalar' => 'Sozlamalar'
        ];
        return isset($titles[$page]) ? $titles[$page] : 'Sahifa';
    }
}

// Logout sahifasi (headers yuborishdan oldin)
if ($page === 'logout') {
    // Session tozalash
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    session_destroy();

    // JavaScript redirect (header redirect o'rniga)
    echo '<script>
        window.location.href = "?page=home";
    </script>';
    echo '<meta http-equiv="refresh" content="0;url=?page=home">';
    exit;
}

// Auth sahifalari uchun redirect (JavaScript orqali)
$redirect_needed = false;
$redirect_url = '';

if (in_array($page, ['login', 'register']) && ($user || $admin)) {
    $redirect_needed = true;
    $redirect_url = 'index.php';
} elseif ($page === 'user_dashboard' && !$user) {
    $redirect_needed = true;
    $redirect_url = '?page=login';
} elseif ($page === 'admin_login' && $admin) {
    $redirect_needed = true;
    $redirect_url = '?page=admin_dashboard';
} elseif ($page === 'admin_dashboard' && !$admin) {
    $redirect_needed = true;
    $redirect_url = '?page=admin_login';
} elseif (in_array($page, ['ariza_topshirish', 'ariza_holati', 'hujjat_olish']) && !$user) {
    $redirect_needed = true;
    $redirect_url = '?page=login';
}

// Agar redirect kerak bo'lsa, JavaScript orqali
if ($redirect_needed) {
    echo '<script>window.location.href = "' . $redirect_url . '";</script>';
    echo '<meta http-equiv="refresh" content="0;url=' . $redirect_url . '">';
    exit;
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

        <meta name="description" content="O'zbekiston Respublikasi Fuqarolik holati dalolatnomalarini yozish organlari - Online nikoh va ajralish arizalari">
        <meta name="keywords" content="nikoh, ajralish, FHDY, O'zbekiston, fuqarolik holati, guvohnoma">
        <meta name="author" content="FHDY O'zbekiston">
    </head>
    <body>
    <!-- Header -->
    <header class="bg-primary text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-rings-wedding"></i>
                    <strong>Nikoh Portali</strong>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'home' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-home"></i> Bosh sahifa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'about' ? 'active' : ''; ?>" href="?page=about">
                                <i class="fas fa-info-circle"></i> Ma'lumot
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'contact' ? 'active' : ''; ?>" href="?page=contact">
                                <i class="fas fa-phone"></i> Aloqa
                            </a>
                        </li>
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> Xizmatlar
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?page=ariza_topshirish">
                                            <i class="fas fa-file-alt"></i> Ariza topshirish
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=ariza_holati">
                                            <i class="fas fa-search"></i> Ariza holati
                                        </a></li>
                                    <li><a class="dropdown-item" href="?page=hujjat_olish">
                                            <i class="fas fa-download"></i> Hujjat olish
                                        </a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <ul class="navbar-nav">
                        <?php if ($user): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profileMenu" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['first_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?page=user_dashboard">
                                            <i class="fas fa-tachometer-alt"></i> Shaxsiy kabinet
                                        </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?page=logout">
                                            <i class="fas fa-sign-out-alt"></i> Chiqish
                                        </a></li>
                                </ul>
                            </li>
                        <?php elseif ($admin): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($admin['full_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?page=admin_dashboard">
                                            <i class="fas fa-cogs"></i> Admin panel
                                        </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?page=logout">
                                            <i class="fas fa-sign-out-alt"></i> Chiqish
                                        </a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=login">
                                    <i class="fas fa-sign-in-alt"></i> Kirish
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=register">
                                    <i class="fas fa-user-plus"></i> Ro'yxatdan o'tish
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=admin_login">
                                    <i class="fas fa-user-shield"></i> Xodimlar uchun
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Breadcrumb -->
        <?php if ($page != 'home'): ?>
            <div class="container mt-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent text-white-50 mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php" class="text-white-50">Bosh sahifa</a>
                        </li>
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            <?php echo getPageTitle($page); ?>
                        </li>
                    </ol>
                </nav>
            </div>
        <?php endif; ?>
    </header>

    <!-- Main Content -->
    <main class="container my-4">
        <?php
        // Xabarlarni ko'rsatish (muvaffaqiyat yoki xato)
        if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?>
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
            echo '<div class="alert alert-warning">';
            echo '<h4>404 - Sahifa topilmadi</h4>';
            echo '<p>Siz qidirayotgan sahifa mavjud emas.</p>';
            echo '<a href="index.php" class="btn btn-primary">Bosh sahifaga qaytish</a>';
            echo '</div>';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-rings-wedding"></i> Nikoh Portali</h5>
                    <p class="mb-2">O'zbekiston Respublikasi Fuqarolik holati dalolatnomalarini yozish organlari</p>
                    <p class="text-muted small">
                        Vazirlar Mahkamasining 2023-yil 20-oktabrdagi 550-son qarori asosida
                    </p>
                </div>
                <div class="col-md-3">
                    <h6>Foydali havolalar</h6>
                    <ul class="list-unstyled">
                        <li><a href="?page=about" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right fa-sm"></i> Tizim haqida
                            </a></li>
                        <li><a href="?page=contact" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right fa-sm"></i> Bog'lanish
                            </a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Aloqa</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone"></i> +998 71 123-45-67</li>
                        <li><i class="fas fa-envelope"></i> info@nikoh.uz</li>
                        <li><i class="fas fa-map-marker-alt"></i> Toshkent sh.</li>
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
                        <i class="fas fa-shield-alt text-success"></i>
                        Xavfsiz aloqa - SSL sertifikat
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>

    <script>
        // Sahifa yuklanganda
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    if (window.bootstrap && window.bootstrap.Alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });
    </script>
    </body>
    </html>
<?php
// Output buffering tugashi
ob_end_flush();
?>