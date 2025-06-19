<?php
/**
 * config/config.php - TUZATILGAN VERSIYA
 * Asosiy konfiguratsiya fayli
 */

// Xavfsizlik uchun konstanta
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

// === MUHIT SOZLAMALARI ===
if (!defined('DEVELOPMENT')) {
    define('DEVELOPMENT', true); // Production da false qiling!
}

// Xatoliklarni ko'rsatish
if (DEVELOPMENT) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// === MA'LUMOTLAR BAZASI SOZLAMALARI ===
require_once __DIR__ . '/database.php';

// === SAYT SOZLAMALARI ===
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Nikoh Portali');
}
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost');
}
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

// === XAVFSIZLIK ===
if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', 'your-secret-key-change-this');
}
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 1800); // 30 daqiqa
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'NIKOH_PORTAL_SESSION');
}

// Session sozlamalari
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', SESSION_NAME);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}

// === FAYL YUKLASH ===
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../uploads/');
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}

// Upload papkasini yaratish
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// === TO'LOV SOZLAMALARI ===
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

// === VAQT SOZLAMALARI ===
date_default_timezone_set('Asia/Tashkent');
if (!defined('DATE_FORMAT')) {
    define('DATE_FORMAT', 'd.m.Y');
}
if (!defined('DATETIME_FORMAT')) {
    define('DATETIME_FORMAT', 'd.m.Y H:i');
}

// === QONUNIY SOZLAMALAR ===
if (!defined('MIN_MARRIAGE_AGE_MALE')) {
    define('MIN_MARRIAGE_AGE_MALE', 18);
}
if (!defined('MIN_MARRIAGE_AGE_FEMALE')) {
    define('MIN_MARRIAGE_AGE_FEMALE', 17);
}

// === HELPER FUNKSIYALAR ===

// Pul miqdorini formatlash
if (!function_exists('formatMoney')) {
    function formatMoney($amount) {
        return number_format($amount, 0, '.', ' ') . ' so\'m';
    }
}

// Sanani formatlash
if (!function_exists('formatDate')) {
    function formatDate($date, $format = null) {
        if (!$date) return '';
        $format = $format ?: DATE_FORMAT;
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format($format);
    }
}

// Vaqtni formatlash
if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = null) {
        if (!$datetime) return '';
        $format = $format ?: DATETIME_FORMAT;
        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }
        return $datetime->format($format);
    }
}

// Ma'lumotlarni tozalash
if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// CSRF Token yaratish
if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// CSRF Input yaratish
if (!function_exists('csrfInput')) {
    function csrfInput() {
        return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
    }
}

// CSRF Token tekshirish
if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Pasport validatsiyasi
if (!function_exists('validatePassport')) {
    function validatePassport($passport) {
        $passport = strtoupper(trim($passport));
        return preg_match('/^[A-Z]{2}\d{7}$/', $passport);
    }
}

// Telefon validatsiyasi
if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return preg_match('/^\+998\d{9}$/', $phone);
    }
}

// Email validatsiyasi
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

// Parol hashlash
if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

// Redirect funksiyasi (JavaScript orqali)
if (!function_exists('redirect')) {
    function redirect($page, $params = []) {
        $url = '?page=' . $page;
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        echo '<script>window.location.href = "' . $url . '";</script>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
        exit;
    }
}

// IP manzilni olish
if (!function_exists('getRealIP')) {
    function getRealIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// User Agent ma'lumotini olish
if (!function_exists('getUserAgent')) {
    function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}

// Nisbiy vaqt (timeAgo) funksiyasi
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        if (!$datetime) return '';

        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }

        $now = new DateTime();
        $diff = $now->diff($datetime);

        if ($diff->days > 30) {
            return formatDate($datetime);
        } elseif ($diff->days > 0) {
            return $diff->days . ' kun oldin';
        } elseif ($diff->h > 0) {
            return $diff->h . ' soat oldin';
        } elseif ($diff->i > 0) {
            return $diff->i . ' daqiqa oldin';
        } else {
            return 'Hozir';
        }
    }
}

// Ariza statusini olish (agar database.php da bo'lmasa)
if (!function_exists('getApplicationStatus')) {
    function getApplicationStatus($status) {
        $statuses = [
            'yangi' => ['label' => 'Yangi', 'class' => 'status-yangi', 'icon' => 'fa-file'],
            'korib_chiqilmoqda' => ['label' => 'Ko\'rib chiqilmoqda', 'class' => 'status-korib-chiqilmoqda', 'icon' => 'fa-eye'],
            'qoshimcha_hujjat_kerak' => ['label' => 'Qo\'shimcha hujjat kerak', 'class' => 'status-warning', 'icon' => 'fa-exclamation-triangle'],
            'tasdiqlandi' => ['label' => 'Tasdiqlandi', 'class' => 'status-tasdiqlandi', 'icon' => 'fa-check'],
            'rad_etildi' => ['label' => 'Rad etildi', 'class' => 'status-rad-etildi', 'icon' => 'fa-times'],
            'tugallandi' => ['label' => 'Tugallandi', 'class' => 'status-tugallandi', 'icon' => 'fa-flag-checkered']
        ];

        return $statuses[$status] ?? ['label' => $status, 'class' => 'badge-secondary', 'icon' => 'fa-question'];
    }
}

// Ariza turini olish (agar database.php da bo'lmasa)
if (!function_exists('getApplicationType')) {
    function getApplicationType($type) {
        $types = [
            'nikoh' => ['label' => 'Nikoh', 'icon' => 'fa-heart', 'color' => 'success'],
            'ajralish' => ['label' => 'Ajralish', 'icon' => 'fa-handshake-slash', 'color' => 'warning']
        ];

        return $types[$type] ?? ['label' => $type, 'icon' => 'fa-file', 'color' => 'secondary'];
    }
}

// To'lov miqdorini hisoblash (agar database.php da bo'lmasa)
if (!function_exists('calculatePaymentAmount')) {
    function calculatePaymentAmount($application_type) {
        $base_amount = ($application_type === 'nikoh') ? NIKOH_DAVLAT_BOJI : AJRALISH_DAVLAT_BOJI;
        $gerb_yigimi = (BHM_MIQDORI * GERB_YIGIMI_FOIZ) / 100;

        return $base_amount + $gerb_yigimi;
    }
}

// Log yozish funksiyasi (agar database.php da bo'lmasa)
if (!function_exists('logActivity')) {
    function logActivity($action, $user_id = null, $admin_id = null, $details = []) {
        global $pdo;
        try {
            if (!$pdo) return false;

            $sql = "INSERT INTO system_logs (user_id, admin_id, action, new_values, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $params = [
                $user_id,
                $admin_id,
                $action,
                isset($details['data']) ? json_encode($details['data'], JSON_UNESCAPED_UNICODE) : null,
                getRealIP(),
                getUserAgent()
            ];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return true;
        } catch (Exception $e) {
            error_log('Log write error: ' . $e->getMessage());
            return false;
        }
    }
}

// Xabarnoma yuborish funksiyasi (agar database.php da bo'lmasa)
if (!function_exists('sendNotification')) {
    function sendNotification($user_id, $type, $recipient, $message, $subject = null) {
        global $pdo;
        try {
            if (!$pdo) return false;

            $sql = "INSERT INTO notifications (user_id, notification_type, recipient, subject, message, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, 'kutilmoqda', NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $type, $recipient, $subject, $message]);

            return true;
        } catch (Exception $e) {
            error_log('Notification send error: ' . $e->getMessage());
            return false;
        }
    }
}