<?php
/**
 * Nikoh Portali - Bosh sahifa
 * O'zbekiston Respublikasi FHDY tizimi
 */

session_start();

// Xatoliklarni ko'rsatish (development uchun)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asosiy konfiguratsiyani yuklash
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

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
    'logout'
];

// Agar noto'g'ri sahifa so'ralsa, bosh sahifaga yo'naltirish
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

// Foydalanuvchi autentifikatsiyasini tekshirish
$user = null;
$admin = null;

if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

if (isset($_SESSION['admin_id'])) {
    $admin = getAdminById($_SESSION['admin_id']);
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
                <img src="assets/images/gerb.png" alt="O'zbekiston gerbi" width="40" height="40" class="me-2">
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
    switch ($page) {
        case 'home':
            include 'pages/home.php';
            break;
        case 'about':
            include 'pages/about.php';
            break;
        case 'contact':
            include 'pages/contact.php';
            break;
        case 'login':
            if ($user || $admin) {
                header('Location: index.php');
                exit;
            }
            include 'pages/user/login.php';
            break;
        case 'register':
            if ($user || $admin) {
                header('Location: index.php');
                exit;
            }
            include 'pages/user/register.php';
            break;
        case 'user_dashboard':
            if (!$user) {
                header('Location: ?page=login');
                exit;
            }
            include 'pages/user/dashboard.php';
            break;
        case 'admin_login':
            if ($admin) {
                header('Location: ?page=admin_dashboard');
                exit;
            }
            include 'pages/admin/login.php';
            break;
        case 'admin_dashboard':
            if (!$admin) {
                header('Location: ?page=admin_login');
                exit;
            }
            include 'pages/admin/dashboard.php';
            break;
        case 'ariza_topshirish':
            if (!$user) {
                header('Location: ?page=login');
                exit;
            }
            include 'pages/user/ariza_topshirish.php';
            break;
        case 'ariza_holati':
            if (!$user) {
                header('Location: ?page=login');
                exit;
            }
            include 'pages/user/ariza_holati.php';
            break;
        case 'hujjat_olish':
            if (!$user) {
                header('Location: ?page=login');
                exit;
            }
            include 'pages/user/hujjat_olish.php';
            break;
        case 'logout':
            include 'pages/logout.php';
            break;
        default:
            include 'pages/404.php';
            break;
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
                    <li><a href="#" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right fa-sm"></i> Qonunchilik
                        </a></li>
                    <li><a href="#" class="text-light text-decoration-none">
                            <i class="fas fa-chevron-right fa-sm"></i> Savollar
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
                <div class="mt-3">
                    <a href="#" class="text-light me-2"><i class="fab fa-telegram fa-lg"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-instagram fa-lg"></i></a>
                </div>
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
<script src="assets/js/main.js"></script>

<!-- Online/Offline holat indikatori -->
<div id="connection-status" class="position-fixed bottom-0 end-0 m-3" style="z-index: 1050;"></div>

<script>
    // Internet aloqasini tekshirish
    function updateConnectionStatus() {
        const status = document.getElementById('connection-status');
        if (navigator.onLine) {
            status.innerHTML = '<span class="badge bg-success"><i class="fas fa-wifi"></i> Onlayn</span>';
        } else {
            status.innerHTML = '<span class="badge bg-danger"><i class="fas fa-wifi"></i> Oflayn</span>';
        }
    }

    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    updateConnectionStatus();

    // Sahifa yuklanganda
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
</body>
</html>