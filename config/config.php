<?php
/**
 * config/config.php - Asosiy konfiguratsiya fayli
 * Nikoh Portali - O'zbekiston Respublikasi FHDY tizimi
 */

// Xavfsizlik uchun konstanta
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// === MUHIT SOZLAMALARI ===
define('DEVELOPMENT', true); // Production da false qiling!
define('DEBUG_MODE', DEVELOPMENT);

// Xatoliklarni ko'rsatish (faqat development)
if (DEVELOPMENT) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// === ASOSIY SAYT SOZLAMALARI ===
define('SITE_NAME', 'Nikoh Portali');
define('SITE_DESCRIPTION', 'O\'zbekiston Respublikasi FHDY - Nikoh va ajralish uchun onlayn portal');
define('SITE_KEYWORDS', 'nikoh, ajralish, FHDY, O\'zbekiston, fuqarolik holati, guvohnoma');
define('SITE_AUTHOR', 'O\'zbekiston Respublikasi Adliya vazirligi');

// URL sozlamalari (o'zgartirishingiz kerak)
define('SITE_URL', 'http://localhost/nikoh_portal');
define('SITE_PATH', '/nikoh_portal');
define('ADMIN_URL', SITE_URL . '/admin');

// Bog'lanish ma'lumotlari
define('SITE_EMAIL', 'info@nikoh.uz');
define('ADMIN_EMAIL', 'admin@nikoh.uz');
define('SUPPORT_EMAIL', 'support@nikoh.uz');
define('SITE_PHONE', '+998 71 123-45-67');

// === XAVFSIZLIK SOZLAMALARI ===
define('SECRET_KEY', 'your-super-secret-key-change-this-32-chars'); // O'zgartirishingiz SHART!
define('ENCRYPTION_KEY', 'your-encryption-key-must-be-32-chars-long'); // O'zgartirishingiz SHART!
define('HASH_ALGORITHM', 'sha256');
define('PASSWORD_MIN_LENGTH', 6);

// Session sozlamalari
define('SESSION_LIFETIME', 1800); // 30 daqiqa
define('SESSION_NAME', 'NIKOH_PORTAL_SESSION');
define('REMEMBER_TOKEN_LIFETIME', 2592000); // 30 kun

// Session xavfsizligi
ini_set('session.name', SESSION_NAME);
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// === FAYL YUKLASH SOZLAMALARI ===
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'application/pdf'
]);

// Upload papkalarini yaratish
$upload_dirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . 'documents/',
    UPLOAD_PATH . 'certificates/',
    UPLOAD_PATH . 'temp/',
    UPLOAD_PATH . 'avatars/'
];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        // .htaccess fayli yaratish (xavfsizlik uchun)
        file_put_contents($dir . '.htaccess', "Options -Indexes\nDeny from all");
    }
}

// === TO'LOV TIZIMI SOZLAMALARI ===

// Click to'lov tizimi
define('CLICK_MERCHANT_ID', 'your_click_merchant_id'); // O'zgartirishingiz kerak
define('CLICK_SECRET_KEY', 'your_click_secret_key'); // O'zgartirishingiz kerak
define('CLICK_API_URL', 'https://api.click.uz/v2/merchant');

// Payme to'lov tizimi
define('PAYME_MERCHANT_ID', 'your_payme_merchant_id'); // O'zgartirishingiz kerak
define('PAYME_SECRET_KEY', 'your_payme_secret_key'); // O'zgartirishingiz kerak
define('PAYME_API_URL', 'https://checkout.paycom.uz/api');

// UzCard to'lov tizimi
define('UZCARD_MERCHANT_ID', 'your_uzcard_merchant_id');
define('UZCARD_SECRET_KEY', 'your_uzcard_secret_key');

// To'lov miqdorlari (so'm)
define('NIKOH_DAVLAT_BOJI', 51000); // Nikoh uchun davlat boji
define('AJRALISH_DAVLAT_BOJI', 85000); // Ajralish uchun davlat boji
define('BHM_MIQDORI', 340000); // Bazaviy hisoblash miqdori
define('GERB_YIGIMI_FOIZ', 15); // Gerb yig'imi foizi

// === SMS XABARNOMA SOZLAMALARI ===
define('SMS_PROVIDER', 'playmobile'); // playmobile, smsclub, eskiz
define('SMS_LOGIN', 'your_sms_login'); // O'zgartirishingiz kerak
define('SMS_PASSWORD', 'your_sms_password'); // O'zgartirishingiz kerak
define('SMS_API_URL', 'https://send.smsxabar.uz/broker-api/send');
define('SMS_SENDER', 'NIKOH.UZ');

// === EMAIL SOZLAMALARI ===
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_email@gmail.com'); // O'zgartirishingiz kerak
define('MAIL_PASSWORD', 'your_app_password'); // O'zgartirishingiz kerak
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_FROM_NAME', SITE_NAME);
define('MAIL_FROM_EMAIL', SITE_EMAIL);

// === VAQT SOZLAMALARI ===
date_default_timezone_set('Asia/Tashkent');
define('TIMEZONE', 'Asia/Tashkent');
define('DATE_FORMAT', 'd.m.Y');
define('DATETIME_FORMAT', 'd.m.Y H:i');
define('TIME_FORMAT', 'H:i');

// Ish vaqti sozlamalari
define('WORK_DAYS', [1, 2, 3, 4, 5]); // Dushanba-Juma
define('WORK_START_TIME', '09:00');
define('WORK_END_TIME', '18:00');
define('LUNCH_START_TIME', '12:00');
define('LUNCH_END_TIME', '13:00');

// === CACHE SOZLAMALARI ===
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 soat
define('CACHE_PATH', __DIR__ . '/../cache/');

// Cache papkasini yaratish
if (!is_dir(CACHE_PATH)) {
    mkdir(CACHE_PATH, 0755, true);
}

// === LOG SOZLAMALARI ===
define('LOG_ENABLED', true);
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_MAX_FILES', 30); // 30 ta fayl

// Log papkasini yaratish
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// === API SOZLAMALARI ===
define('API_ENABLED', true);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // Soatiga so'rovlar soni
define('API_KEY_LENGTH', 32);

// === TIZIM SOZLAMALARI ===
define('PAGINATION_LIMIT', 20); // Sahifalash uchun
define('SEARCH_MIN_LENGTH', 3); // Qidiruv uchun minimal uzunlik
define('AUTO_LOGOUT_TIME', 1800); // 30 daqiqa faolsizlikdan keyin
define('MAX_LOGIN_ATTEMPTS', 5); // Maksimal kirish urinishlari
define('LOGIN_BLOCK_TIME', 900); // 15 daqiqa bloklash

// === QONUNIY SOZLAMALAR ===

// Nikoh uchun minimal yosh
define('MIN_MARRIAGE_AGE_MALE', 18);
define('MIN_MARRIAGE_AGE_FEMALE', 17);

// Nikoh oldin kutish muddati (kunlar)
define('MARRIAGE_WAITING_PERIOD', 30);

// Hujjat saqlash muddati (yillar)
define('DOCUMENT_RETENTION_PERIOD', 75);

// Guvohnoma raqam formatlari
define('MARRIAGE_CERT_PREFIX', 'N');
define('DIVORCE_CERT_PREFIX', 'A');
define('CERT_NUMBER_LENGTH', 7);

// === INTEGRATSIYA SOZLAMALARI ===

// MyGov integratsiyasi
define('MYGOV_API_URL', 'https://api.my.gov.uz');
define('MYGOV_CLIENT_ID', 'your_mygov_client_id');
define('MYGOV_CLIENT_SECRET', 'your_mygov_client_secret');

// E-IMZO integratsiyasi
define('EIMZO_ENABLED', false);
define('EIMZO_API_URL', 'https://eimzo.uz/api');

// === BACKUP SOZLAMALARI ===
define('BACKUP_ENABLED', true);
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('BACKUP_SCHEDULE', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION_DAYS', 30);

// Backup papkasini yaratish
if (!is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0755, true);
}

// === MONITORING SOZLAMALARI ===
define('MONITORING_ENABLED', true);
define('HEALTH_CHECK_URL', SITE_URL . '/api/health');
define('UPTIME_CHECK_INTERVAL', 300); // 5 daqiqa

// === SOZLAMALAR MASSIVI ===
$config = [
    'app' => [
        'name' => SITE_NAME,
        'url' => SITE_URL,
        'timezone' => TIMEZONE,
        'debug' => DEBUG_MODE
    ],
    'database' => [
        'host' => DB_HOST ?? 'localhost',
        'name' => DB_NAME ?? 'nikoh_portal',
        'charset' => DB_CHARSET ?? 'utf8mb4'
    ],
    'security' => [
        'secret_key' => SECRET_KEY,
        'session_lifetime' => SESSION_LIFETIME,
        'max_login_attempts' => MAX_LOGIN_ATTEMPTS
    ],
    'upload' => [
        'path' => UPLOAD_PATH,
        'max_size' => MAX_FILE_SIZE,
        'allowed_types' => ALLOWED_FILE_TYPES
    ],
    'payment' => [
        'bhm_amount' => BHM_MIQDORI,
        'marriage_fee' => NIKOH_DAVLAT_BOJI,
        'divorce_fee' => AJRALISH_DAVLAT_BOJI
    ],
    'business' => [
        'min_marriage_age_male' => MIN_MARRIAGE_AGE_MALE,
        'min_marriage_age_female' => MIN_MARRIAGE_AGE_FEMALE,
        'waiting_period' => MARRIAGE_WAITING_PERIOD
    ]
];

// === HELPER FUNKSIYALAR ===

// Konfiguratsiya qiymatini olish
function getConfig($key, $default = null) {
    global $config;
    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }

    return $value;
}

// Konfiguratsiya qiymatini o'rnatish
function setConfig($key, $value) {
    global $config;
    $keys = explode('.', $key);
    $current = &$config;

    foreach ($keys as $k) {
        if (!isset($current[$k]) || !is_array($current[$k])) {
            $current[$k] = [];
        }
        $current = &$current[$k];
    }

    $current = $value;
}

// URL yaratish
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

// Asset URL yaratish
function asset($path) {
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

// Upload URL yaratish
function uploadUrl($path) {
    return UPLOAD_URL . ltrim($path, '/');
}

// === AUTO-LOADER ===
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// === MUHIT OZGARUVCHILARI ===
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
}

// === XAVFSIZLIK TEKSHIRUVLARI ===

// HTTPS majburlash (production)
if (!DEVELOPMENT && !isset($_SERVER['HTTPS'])) {
    $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $redirect_url, true, 301);
    exit;
}

// X-Frame-Options (clickjacking himoyasi)
header('X-Frame-Options: SAMEORIGIN');

// XSS himoyasi
header('X-XSS-Protection: 1; mode=block');

// Content-Type himoyasi
header('X-Content-Type-Options: nosniff');

// Referrer Policy
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSP (Content Security Policy) - development uchun yumshoq
if (!DEVELOPMENT) {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com;");
}

?>