<?php
/**
 * includes/functions.php - FIXED VERSION (Header muammosiz)
 * Barcha header() redirect'lar olib tashlangan
 */

if (!defined('CONFIG_LOADED')) {
    die('Access denied');
}

// === XAVFSIZLIK FUNKSIYALARI ===

// Ma'lumotlarni tozalash va xavfsiz qilish
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// SQL injection himoyasi uchun
function sanitizeForDb($data) {
    return trim(strip_tags($data));
}

// Token yaratish
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Parol hashlash
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Parol tekshirish
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// CSRF Token funksiyalari
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// CSRF input yaratish
function csrfInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// === FOYDALANUVCHI FUNKSIYALARI ===

// Foydalanuvchini ID bo'yicha olish
function getUserById($id) {
    global $pdo;
    try {
        $sql = "SELECT * FROM users WHERE id = ? AND is_verified = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('getUserById error: ' . $e->getMessage());
        return false;
    }
}

// Foydalanuvchini pasport bo'yicha olish
function getUserByPassport($passport) {
    global $pdo;
    try {
        $sql = "SELECT * FROM users WHERE passport_series = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$passport]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('getUserByPassport error: ' . $e->getMessage());
        return false;
    }
}

// Foydalanuvchini verification token bo'yicha olish
function getUserByVerificationToken($token) {
    global $pdo;
    try {
        $sql = "SELECT * FROM users WHERE verification_token = ? AND is_verified = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('getUserByVerificationToken error: ' . $e->getMessage());
        return false;
    }
}

// Foydalanuvchini telefon bo'yicha olish
function getUserByPhone($phone) {
    global $pdo;
    try {
        $sql = "SELECT * FROM users WHERE phone = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$phone]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('getUserByPhone error: ' . $e->getMessage());
        return false;
    }
}

// Admin foydalanuvchini ID bo'yicha olish
function getAdminById($id) {
    global $pdo;
    try {
        $sql = "SELECT * FROM admin_users WHERE id = ? AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('getAdminById error: ' . $e->getMessage());
        return false;
    }
}

// Admin foydalanuvchini username bo'yicha olish
function getAdminByUsername($username) {
    global $pdo;
    try {
        $sql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('getAdminByUsername error: ' . $e->getMessage());
        return false;
    }
}

// Foydalanuvchi tizimda borligini tekshirish
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Admin tizimda borligini tekshirish
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Joriy foydalanuvchini olish
function getCurrentUser() {
    if (isUserLoggedIn()) {
        return getUserById($_SESSION['user_id']);
    }
    return null;
}

// Joriy adminni olish
function getCurrentAdmin() {
    if (isAdminLoggedIn()) {
        return getAdminById($_SESSION['admin_id']);
    }
    return null;
}

// === SAHIFA FUNKSIYALARI ===

// Sahifa sarlavhalarini olish
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

// URL yaratish
function createUrl($page, $params = []) {
    $url = '?page=' . $page;
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    return $url;
}

// Redirect funksiyasi (JavaScript orqali, header() o'rniga)
function redirect($page, $params = []) {
    $url = createUrl($page, $params);
    echo '<script>window.location.href = "' . $url . '";</script>';
    echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
    exit;
}

// === VALIDATSIYA FUNKSIYALARI ===

// O'zbekiston pasport validatsiyasi
function validatePassport($passport) {
    $passport = strtoupper(trim($passport));
    return preg_match('/^[A-Z]{2}\d{7}$/', $passport);
}

// Telefon raqam validatsiyasi
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^\+998\d{9}$/', $phone);
}

// Email validatsiyasi
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Sana validatsiyasi
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Yosh tekshirish
function calculateAge($birthDate) {
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    return $birth->diff($today)->y;
}

// Nikoh uchun yosh tekshirish
function isMarriageAgeValid($birthDate, $gender) {
    $age = calculateAge($birthDate);
    $minAge = ($gender === 'erkak') ? MIN_MARRIAGE_AGE_MALE : MIN_MARRIAGE_AGE_FEMALE;
    return $age >= $minAge;
}

// === FAYL YUKLASH FUNKSIYALARI ===

// Fayl yuklash
function uploadFile($file, $directory = 'documents/', $allowed_types = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Fayl yuklanmadi yoki xatolik yuz berdi');
    }

    // Allowed types ni olish (config dan)
    $allowed_types = $allowed_types ?: getAllowedFileTypes();
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Fayl turi ruxsat etilmagan. Ruxsat etilgan: ' . implode(', ', $allowed_types));
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Fayl hajmi juda katta. Maksimal: ' . formatFileSize(MAX_FILE_SIZE));
    }

    // MIME type tekshirish
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime_types = getAllowedMimeTypes();
    if (!in_array($mime_type, $allowed_mime_types)) {
        throw new Exception('Fayl MIME turi noto\'g\'ri');
    }

    $upload_dir = UPLOAD_PATH . $directory . date('Y/m/');
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Faylni saqlashda xatolik yuz berdi');
    }

    return [
        'original_name' => $file['name'],
        'stored_name' => $new_filename,
        'path' => $upload_path,
        'relative_path' => $directory . date('Y/m/') . $new_filename,
        'size' => $file['size'],
        'type' => $mime_type,
        'extension' => $file_extension
    ];
}

// Fayl o'chirish
function deleteFile($file_path) {
    $full_path = UPLOAD_PATH . $file_path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}

// Fayl hajmini formatlash
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// === ARIZA FUNKSIYALARI ===

// Ariza raqamini yaratish
function generateApplicationNumber() {
    $year = date('Y');
    global $pdo;
    try {
        $sql = "SELECT COUNT(*) as count FROM applications WHERE YEAR(created_at) = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$year]);
        $result = $stmt->fetch();
        $next_number = ($result['count'] ?? 0) + 1;
        return $year . str_pad($next_number, 6, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        return $year . str_pad(1, 6, '0', STR_PAD_LEFT);
    }
}

// Ariza statusini olish
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

// Ariza turi
function getApplicationType($type) {
    $types = [
        'nikoh' => ['label' => 'Nikoh', 'icon' => 'fa-heart', 'color' => 'success'],
        'ajralish' => ['label' => 'Ajralish', 'icon' => 'fa-handshake-slash', 'color' => 'warning']
    ];

    return $types[$type] ?? ['label' => $type, 'icon' => 'fa-file', 'color' => 'secondary'];
}

// === TO'LOV FUNKSIYALARI ===

// To'lov miqdorini hisoblash
function calculatePaymentAmount($application_type) {
    $base_amount = ($application_type === 'nikoh') ? NIKOH_DAVLAT_BOJI : AJRALISH_DAVLAT_BOJI;
    $gerb_yigimi = (BHM_MIQDORI * GERB_YIGIMI_FOIZ) / 100;

    return $base_amount + $gerb_yigimi;
}

// Pul miqdorini formatlash
function formatMoney($amount) {
    return number_format($amount, 0, '.', ' ') . ' so\'m';
}

// === XABARNOMA FUNKSIYALARI ===

// SMS yuborish
function sendSMS($phone, $message) {
    global $pdo;
    try {
        // SMS API bilan integratsiya
        // Bu yerda real SMS provider bilan bog'lanish kodi bo'lishi kerak

        // Hozircha faqat ma'lumotlar bazasiga yozamiz
        $sql = "INSERT INTO notifications (notification_type, recipient, message, status) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['sms', $phone, $message, 'kutilmoqda']);

        return true;
    } catch (Exception $e) {
        error_log('SMS send error: ' . $e->getMessage());
        return false;
    }
}

// Email yuborish
function sendEmail($email, $subject, $message, $is_html = true) {
    global $pdo;
    try {
        // Email yuborish kodi
        // Bu yerda PHPMailer yoki boshqa email library ishlatish mumkin

        // Hozircha faqat ma'lumotlar bazasiga yozamiz
        $sql = "INSERT INTO notifications (notification_type, recipient, subject, message, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email', $email, $subject, $message, 'kutilmoqda']);

        return true;
    } catch (Exception $e) {
        error_log('Email send error: ' . $e->getMessage());
        return false;
    }
}

// Xabarnoma yuborish (universal)
function sendNotification($user_id, $type, $recipient, $message, $subject = null) {
    global $pdo;
    try {
        $sql = "INSERT INTO notifications (user_id, notification_type, recipient, subject, message, status) 
                VALUES (?, ?, ?, ?, ?, 'kutilmoqda')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $type, $recipient, $subject, $message]);

        return true;
    } catch (Exception $e) {
        error_log('Notification send error: ' . $e->getMessage());
        return false;
    }
}

// === LOG FUNKSIYALARI ===

// Faoliyat logini yozish
function logActivity($action, $user_id = null, $admin_id = null, $details = []) {
    global $pdo;
    try {
        $sql = "INSERT INTO system_logs (user_id, admin_id, action, table_name, record_id, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $user_id,
            $admin_id,
            $action,
            $details['table_name'] ?? null,
            $details['record_id'] ?? null,
            isset($details['data']) ? json_encode($details['data'], JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return true;
    } catch (Exception $e) {
        // Log yozishda xatolik bo'lsa, faylga yozish
        error_log('Log write error: ' . $e->getMessage());
        return false;
    }
}

// Faylga log yozish
function writeLog($message, $level = 'INFO', $file = 'app.log') {
    if (!LOG_ENABLED) return;

    $log_file = LOG_PATH . $file;
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 'guest';

    $log_entry = "[{$timestamp}] [{$level}] [IP:{$ip}] [User:{$user_id}] {$message}" . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// === SOZLAMALAR FUNKSIYALARI ===

// Sozlamani olish
function getSetting($key, $default = null) {
    static $settings_cache = [];
    global $pdo;

    if (!isset($settings_cache[$key])) {
        try {
            $sql = "SELECT setting_value FROM settings WHERE setting_key = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            $settings_cache[$key] = $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            $settings_cache[$key] = $default;
        }
    }

    return $settings_cache[$key];
}

// Sozlamani o'rnatish
function setSetting($key, $value, $admin_id = null) {
    global $pdo;
    try {
        $sql = "INSERT INTO settings (setting_key, setting_value, updated_by) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                updated_by = VALUES(updated_by),
                updated_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$key, $value, $admin_id]);

        // Cache tozalash
        unset($GLOBALS['settings_cache'][$key]);
        return true;
    } catch (Exception $e) {
        error_log('setSetting error: ' . $e->getMessage());
        return false;
    }
}

// === FORMAT FUNKSIYALARI ===

// Sanani formatlash
function formatDate($date, $format = null) {
    if (!$date) return '';

    $format = $format ?: DATE_FORMAT;
    if (is_string($date)) {
        $date = new DateTime($date);
    }

    return $date->format($format);
}

// Vaqtni formatlash
function formatDateTime($datetime, $format = null) {
    if (!$datetime) return '';

    $format = $format ?: DATETIME_FORMAT;
    if (is_string($datetime)) {
        $datetime = new DateTime($datetime);
    }

    return $datetime->format($format);
}

// Nisbiy vaqt (2 soat oldin, kecha, ...)
function timeAgo($datetime) {
    if (is_string($datetime)) {
        $datetime = new DateTime($datetime);
    }

    $now = new DateTime();
    $diff = $now->diff($datetime);

    if ($diff->days > 7) {
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

// === CACHE FUNKSIYALARI ===

// Cache qiymatini olish
function getCache($key) {
    if (!CACHE_ENABLED) return null;

    $cache_file = CACHE_PATH . md5($key) . '.cache';

    if (!file_exists($cache_file)) {
        return null;
    }

    $cache_data = unserialize(file_get_contents($cache_file));

    if ($cache_data['expires'] < time()) {
        unlink($cache_file);
        return null;
    }

    return $cache_data['data'];
}

// Cache qiymatini saqlash
function setCache($key, $data, $lifetime = null) {
    if (!CACHE_ENABLED) return false;

    $lifetime = $lifetime ?: CACHE_LIFETIME;
    $cache_file = CACHE_PATH . md5($key) . '.cache';

    $cache_data = [
        'data' => $data,
        'expires' => time() + $lifetime
    ];

    return file_put_contents($cache_file, serialize($cache_data), LOCK_EX) !== false;
}

// Cache tozalash
function clearCache($pattern = '*') {
    if (!CACHE_ENABLED) return;

    $files = glob(CACHE_PATH . $pattern . '.cache');
    foreach ($files as $file) {
        unlink($file);
    }
}

// === HELPER FUNKSIYALAR ===

// Array dan biror qiymatni xavfsiz olish
function getArrayValue($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

// String ni qisqartirish
function truncateString($string, $length = 100, $suffix = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }

    return substr($string, 0, $length) . $suffix;
}

// IP manzilni olish
function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// User Agent ma'lumotini olish
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
}

// Browser ma'lumotini aniqlash
function getBrowserInfo($user_agent = null) {
    $user_agent = $user_agent ?: getUserAgent();

    $browsers = [
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox',
        'Safari' => 'Safari',
        'Edge' => 'Edge',
        'Opera' => 'Opera'
    ];

    foreach ($browsers as $pattern => $name) {
        if (strpos($user_agent, $pattern) !== false) {
            return $name;
        }
    }

    return 'Unknown';
}

?>