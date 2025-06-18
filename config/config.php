<?php
/**
 * config/config.php - Konstantalar muammosini hal qilgan versiya
 */

// Xavfsizlik uchun konstanta
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// === MA'LUMOTLAR BAZASI SOZLAMALARI (AVVAL) ===
require_once __DIR__ . '/database.php';

// === MUHIT SOZLAMALARI ===
if (!defined('DEVELOPMENT')) {
    define('DEVELOPMENT', true); // Production da false qiling!
}
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', DEVELOPMENT);
}

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
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Nikoh Portali');
}
if (!defined('SITE_DESCRIPTION')) {
    define('SITE_DESCRIPTION', 'O\'zbekiston Respublikasi FHDY - Nikoh va ajralish uchun onlayn portal');
}
if (!defined('SITE_KEYWORDS')) {
    define('SITE_KEYWORDS', 'nikoh, ajralish, FHDY, O\'zbekiston, fuqarolik holati, guvohnoma');
}
if (!defined('SITE_AUTHOR')) {
    define('SITE_AUTHOR', 'O\'zbekiston Respublikasi Adliya vazirligi');
}

// URL sozlamalari - SIZNING LOYIHANGIZ UCHUN TO'G'RI
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost');
}
if (!defined('SITE_PATH')) {
    define('SITE_PATH', '/');
}
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', SITE_URL . '/admin');
}

// Bog'lanish ma'lumotlari
if (!defined('SITE_EMAIL')) {
    define('SITE_EMAIL', 'info@nikoh.uz');
}
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@nikoh.uz');
}
if (!defined('SUPPORT_EMAIL')) {
    define('SUPPORT_EMAIL', 'support@nikoh.uz');
}
if (!defined('SITE_PHONE')) {
    define('SITE_PHONE', '+998 71 123-45-67');
}

// === XAVFSIZLIK SOZLAMALARI ===
if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', 'your-super-secret-key-change-this-32-chars');
}
if (!defined('ENCRYPTION_KEY')) {
    define('ENCRYPTION_KEY', 'your-encryption-key-must-be-32-chars-long');
}
if (!defined('HASH_ALGORITHM')) {
    define('HASH_ALGORITHM', 'sha256');
}
if (!defined('PASSWORD_MIN_LENGTH')) {
    define('PASSWORD_MIN_LENGTH', 6);
}

// Session sozlamalari
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 1800); // 30 daqiqa
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'NIKOH_PORTAL_SESSION');
}
if (!defined('REMEMBER_TOKEN_LIFETIME')) {
    define('REMEMBER_TOKEN_LIFETIME', 2592000); // 30 kun
}

// Session xavfsizligi - faqat session boshlanmagan bo'lsa
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', SESSION_NAME);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// === FAYL YUKLASH SOZLAMALARI ===
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../uploads/');
}
if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', SITE_URL . '/uploads/');
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}
if (!defined('ALLOWED_FILE_TYPES')) {
    define('ALLOWED_FILE_TYPES', serialize(['jpg', 'jpeg', 'png', 'pdf']));
}
if (!defined('ALLOWED_MIME_TYPES')) {
    define('ALLOWED_MIME_TYPES', serialize([
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/pdf'
    ]));
}

// Array konstantalarini function sifatida ishlatish
function getAllowedFileTypes() {
    return unserialize(ALLOWED_FILE_TYPES);
}

function getAllowedMimeTypes() {
    return unserialize(ALLOWED_MIME_TYPES);
}

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
if (!defined('CLICK_MERCHANT_ID')) {
    define('CLICK_MERCHANT_ID', 'your_click_merchant_id');
}
if (!defined('CLICK_SECRET_KEY')) {
    define('CLICK_SECRET_KEY', 'your_click_secret_key');
}
if (!defined('CLICK_API_URL')) {
    define('CLICK_API_URL', 'https://api.click.uz/v2/merchant');
}

if (!defined('PAYME_MERCHANT_ID')) {
    define('PAYME_MERCHANT_ID', 'your_payme_merchant_id');
}
if (!defined('PAYME_SECRET_KEY')) {
    define('PAYME_SECRET_KEY', 'your_payme_secret_key');
}
if (!defined('PAYME_API_URL')) {
    define('PAYME_API_URL', 'https://checkout.paycom.uz/api');
}

// To'lov miqdorlari (so'm)
if (!defined('NIKOH_DAVLAT_BOJI')) {
    define('NIKOH_DAVLAT_BOJI', 51000);
}
if (!defined('AJRALISH_DAVLAT_BOJI')) {
    define('AJRALISH_DAVLAT_BOJI', 85000);
}
if (!defined('BHM_MIQDORI')) {
    define('BHM_MIQDORI', 340000);
}
if (!defined('GERB_YIGIMI_FOIZ')) {
    define('GERB_YIGIMI_FOIZ', 15);
}

// === SMS XABARNOMA SOZLAMALARI ===
if (!defined('SMS_PROVIDER')) {
    define('SMS_PROVIDER', 'playmobile');
}
if (!defined('SMS_LOGIN')) {
    define('SMS_LOGIN', 'your_sms_login');
}
if (!defined('SMS_PASSWORD')) {
    define('SMS_PASSWORD', 'your_sms_password');
}
if (!defined('SMS_API_URL')) {
    define('SMS_API_URL', 'https://send.smsxabar.uz/broker-api/send');
}
if (!defined('SMS_SENDER')) {
    define('SMS_SENDER', 'NIKOH.UZ');
}

// === EMAIL SOZLAMALARI ===
if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', 'smtp.gmail.com');
}
if (!defined('MAIL_PORT')) {
    define('MAIL_PORT', 587);
}
if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', 'your_email@gmail.com');
}
if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', 'your_app_password');
}

// === VAQT SOZLAMALARI ===
date_default_timezone_set('Asia/Tashkent');
if (!defined('TIMEZONE')) {
    define('TIMEZONE', 'Asia/Tashkent');
}
if (!defined('DATE_FORMAT')) {
    define('DATE_FORMAT', 'd.m.Y');
}
if (!defined('DATETIME_FORMAT')) {
    define('DATETIME_FORMAT', 'd.m.Y H:i');
}

// === CACHE SOZLAMALARI ===
if (!defined('CACHE_ENABLED')) {
    define('CACHE_ENABLED', true);
}
if (!defined('CACHE_LIFETIME')) {
    define('CACHE_LIFETIME', 3600);
}
if (!defined('CACHE_PATH')) {
    define('CACHE_PATH', __DIR__ . '/../cache/');
}

// Cache papkasini yaratish
if (!is_dir(CACHE_PATH)) {
    mkdir(CACHE_PATH, 0755, true);
}

// === LOG SOZLAMALARI ===
if (!defined('LOG_ENABLED')) {
    define('LOG_ENABLED', true);
}
if (!defined('LOG_PATH')) {
    define('LOG_PATH', __DIR__ . '/../logs/');
}
if (!defined('LOG_MAX_SIZE')) {
    define('LOG_MAX_SIZE', 10 * 1024 * 1024);
}

// Log papkasini yaratish
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// === TIZIM SOZLAMALARI ===
if (!defined('PAGINATION_LIMIT')) {
    define('PAGINATION_LIMIT', 20);
}
if (!defined('SEARCH_MIN_LENGTH')) {
    define('SEARCH_MIN_LENGTH', 3);
}
if (!defined('AUTO_LOGOUT_TIME')) {
    define('AUTO_LOGOUT_TIME', 1800);
}
if (!defined('MAX_LOGIN_ATTEMPTS')) {
    define('MAX_LOGIN_ATTEMPTS', 5);
}

// === QONUNIY SOZLAMALAR ===
if (!defined('MIN_MARRIAGE_AGE_MALE')) {
    define('MIN_MARRIAGE_AGE_MALE', 18);
}
if (!defined('MIN_MARRIAGE_AGE_FEMALE')) {
    define('MIN_MARRIAGE_AGE_FEMALE', 17);
}
if (!defined('MARRIAGE_WAITING_PERIOD')) {
    define('MARRIAGE_WAITING_PERIOD', 30);
}

// === BACKUP SOZLAMALARI ===
if (!defined('BACKUP_ENABLED')) {
    define('BACKUP_ENABLED', true);
}
if (!defined('BACKUP_PATH')) {
    define('BACKUP_PATH', __DIR__ . '/../backups/');
}

// Backup papkasini yaratish
if (!is_dir(BACKUP_PATH)) {
    mkdir(BACKUP_PATH, 0755, true);
}

// === SOZLAMALAR MASSIVI ===
$config = [
    'app' => [
        'name' => SITE_NAME,
        'url' => SITE_URL,
        'timezone' => TIMEZONE,
        'debug' => DEBUG_MODE
    ],
    'database' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'charset' => DB_CHARSET
    ],
    'security' => [
        'secret_key' => SECRET_KEY,
        'session_lifetime' => SESSION_LIFETIME,
        'max_login_attempts' => MAX_LOGIN_ATTEMPTS
    ],
    'upload' => [
        'path' => UPLOAD_PATH,
        'max_size' => MAX_FILE_SIZE,
        'allowed_types' => getAllowedFileTypes()
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

// === XAVFSIZLIK TEKSHIRUVLARI ===
// Faqat headers yuborilmagan bo'lsa
if (!headers_sent()) {
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
}

?>