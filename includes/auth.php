<?php
/**
 * includes/auth.php - Autentifikatsiya va avtorizatsiya funksiyalari
 */

if (!defined('CONFIG_LOADED')) {
    die('Access denied');
}

// Foydalanuvchi tizimga kirishi
function loginUser($passport, $password, $remember = false) {
    // Pasport formatini tekshirish
    if (!validatePassport($passport)) {
        throw new Exception('Noto\'g\'ri pasport formati');
    }

    // Foydalanuvchini topish
    $user = getUserByPassport($passport);
    if (!$user) {
        // Xavfsizlik uchun umumiy xabar
        throw new Exception('Pasport yoki parol noto\'g\'ri');
    }

    // Parolni tekshirish
    if (!verifyPassword($password, $user['password_hash'])) {
        throw new Exception('Pasport yoki parol noto\'g\'ri');
    }

    // Akkaunt faolligi tekshiruvi
    if (!$user['is_verified']) {
        throw new Exception('Akkauntingiz hali tasdiqlanmagan');
    }

    // Session yaratish
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_passport'] = $user['passport_series'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['login_time'] = time();

    // "Meni eslab qol" funksiyasi
    if ($remember) {
        $token = generateToken();
        $expires = time() + (30 * 24 * 60 * 60); // 30 kun

        setcookie('remember_token', $token, $expires, '/', '', isset($_SERVER['HTTPS']), true);

        // Tokenni ma'lumotlar bazasiga saqlash
        $sql = "UPDATE users SET verification_token = ? WHERE id = ?";
        executeQuery($sql, [$token, $user['id']]);
    }

    // Oxirgi kirish vaqtini yangilash
    $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
    executeQuery($sql, [$user['id']]);

    // Log yozish
    logActivity('user_login', $user['id'], null, [
        'passport' => $passport
    ]);

    return $user;
}

// Admin tizimga kirishi
function loginAdmin($username, $password) {
    // Admin topish
    $admin = getAdminByUsername($username);
    if (!$admin) {
        throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
    }

    // Parolni tekshirish
    if (!verifyPassword($password, $admin['password_hash'])) {
        throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
    }

    // Faollik tekshiruvi
    if (!$admin['is_active']) {
        throw new Exception('Akkauntingiz bloklangan');
    }

    // Session yaratish
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_login_time'] = time();

    // Oxirgi kirish vaqtini yangilash
    $sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
    executeQuery($sql, [$admin['id']]);

    // Log yozish
    logActivity('admin_login', null, $admin['id'], [
        'username' => $username
    ]);

    return $admin;
}

// Foydalanuvchi ro'yxatdan o'tishi
function registerUser($data) {
    // Validatsiya
    $errors = [];

    if (empty($data['passport_series']) || !validatePassport($data['passport_series'])) {
        $errors[] = 'Noto\'g\'ri pasport formati (masalan: AA1234567)';
    }

    if (empty($data['first_name']) || strlen($data['first_name']) < 2) {
        $errors[] = 'Ism kamida 2 ta harfdan iborat bo\'lishi kerak';
    }

    if (empty($data['last_name']) || strlen($data['last_name']) < 2) {
        $errors[] = 'Familiya kamida 2 ta harfdan iborat bo\'lishi kerak';
    }

    if (empty($data['middle_name']) || strlen($data['middle_name']) < 2) {
        $errors[] = 'Otasining ismi kamida 2 ta harfdan iborat bo\'lishi kerak';
    }

    if (empty($data['birth_date']) || !validateDate($data['birth_date'])) {
        $errors[] = 'Noto\'g\'ri tug\'ilgan sana';
    }

    if (empty($data['phone']) || !validatePhone($data['phone'])) {
        $errors[] = 'Noto\'g\'ri telefon raqami (+998901234567)';
    }

    if (!empty($data['email']) && !validateEmail($data['email'])) {
        $errors[] = 'Noto\'g\'ri email manzil';
    }

    if (empty($data['password']) || strlen($data['password']) < 6) {
        $errors[] = 'Parol kamida 6 ta belgidan iborat bo\'lishi kerak';
    }

    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = 'Parollar mos kelmaydi';
    }

    if (!in_array($data['gender'], ['erkak', 'ayol'])) {
        $errors[] = 'Jinsni tanlang';
    }

    if (empty($data['address'])) {
        $errors[] = 'Yashash manzilini kiriting';
    }

    // Yosh tekshiruvi (18 yoshdan katta bo'lishi kerak)
    $birth_year = date('Y', strtotime($data['birth_date']));
    $current_year = date('Y');
    if (($current_year - $birth_year) < 18) {
        $errors[] = '18 yoshdan kichik shaxslar ro\'yxatdan o\'ta olmaydi';
    }

    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    // Pasport dublikati tekshiruvi
    $existing = getUserByPassport($data['passport_series']);
    if ($existing) {
        throw new Exception('Bu pasport raqami bilan allaqachon ro\'yxatdan o\'tilgan');
    }

    // Telefon dublikati tekshiruvi
    $sql = "SELECT id FROM users WHERE phone = ?";
    $existing_phone = fetchOne($sql, [$data['phone']]);
    if ($existing_phone) {
        throw new Exception('Bu telefon raqami bilan allaqachon ro\'yxatdan o\'tilgan');
    }

    // Email dublikati tekshiruvi (agar kiritilgan bo'lsa)
    if (!empty($data['email'])) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $existing_email = fetchOne($sql, [$data['email']]);
        if ($existing_email) {
            throw new Exception('Bu email manzil bilan allaqachon ro\'yxatdan o\'tilgan');
        }
    }

    // Parolni hashlash
    $password_hash = hashPassword($data['password']);

    // Tasdiqlash tokenini yaratish
    $verification_token = generateToken();

    // Ma'lumotlar bazasiga yozish
    $sql = "INSERT INTO users (
        passport_series, first_name, last_name, middle_name, birth_date, 
        birth_place, phone, email, password_hash, gender, address, 
        verification_token, citizenship
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $data['passport_series'],
        $data['first_name'],
        $data['last_name'],
        $data['middle_name'],
        $data['birth_date'],
        $data['birth_place'] ?? '',
        $data['phone'],
        $data['email'] ?? null,
        $password_hash,
        $data['gender'],
        $data['address'],
        $verification_token,
        $data['citizenship'] ?? 'O\'zbekiston'
    ];

    executeQuery($sql, $params);
    $user_id = getLastInsertId();

    // Log yozish
    logActivity('user_register', $user_id, null, [
        'passport' => $data['passport_series'],
        'phone' => $data['phone']
    ]);

    // SMS yuborish (tasdiqlash uchun)
    $sms_message = "Nikoh portaliga xush kelibsiz! Akkauntingizni tasdiqlamoqchi bo'lsangiz, quyidagi koddan foydalaning: " . substr($verification_token, 0, 6);
    sendNotification($user_id, 'sms', $data['phone'], $sms_message);

    return [
        'user_id' => $user_id,
        'verification_token' => $verification_token
    ];
}

// Akkauntni tasdiqlash
function verifyAccount($passport, $verification_code) {
    $user = getUserByPassport($passport);
    if (!$user) {
        throw new Exception('Foydalanuvchi topilmadi');
    }

    // Tasdiqlash kodini tekshirish (token ning birinchi 6 ta belgisi)
    $expected_code = substr($user['verification_token'], 0, 6);
    if ($verification_code !== $expected_code) {
        throw new Exception('Noto\'g\'ri tasdiqlash kodi');
    }

    // Akkauntni faollashtirish
    $sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?";
    executeQuery($sql, [$user['id']]);

    // Log yozish
    logActivity('account_verified', $user['id'], null);

    return true;
}

// Tizimdan chiqish
function logout() {
    $user_id = $_SESSION['user_id'] ?? null;
    $admin_id = $_SESSION['admin_id'] ?? null;

    // Log yozish
    if ($user_id) {
        logActivity('user_logout', $user_id, null);
    } elseif ($admin_id) {
        logActivity('admin_logout', null, $admin_id);
    }

    // Session tozalash
    $_SESSION = [];

    // Session cookie ni o'chirish
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Remember token cookie ni o'chirish
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    session_destroy();
}

// Session tekshiruvi
function checkSession() {
    // Session vaqtini tekshirish
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
            logout();
            return false;
        }
    }

    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

// Remember token orqali kirish
function loginByRememberToken() {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }

    $token = $_COOKIE['remember_token'];
    $sql = "SELECT * FROM users WHERE verification_token = ? AND is_verified = 1";
    $user = fetchOne($sql, [$token]);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_passport'] = $user['passport_series'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['login_time'] = time();

        // Oxirgi kirish vaqtini yangilash
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        executeQuery($sql, [$user['id']]);

        return true;
    }

    return false;
}

// Parolni o'zgartirish
function changePassword($user_id, $old_password, $new_password) {
    // Hozirgi parolni tekshirish
    $user = getUserById($user_id);
    if (!$user || !verifyPassword($old_password, $user['password_hash'])) {
        throw new Exception('Hozirgi parol noto\'g\'ri');
    }

    // Yangi parol validatsiyasi
    if (strlen($new_password) < 6) {
        throw new Exception('Yangi parol kamida 6 ta belgidan iborat bo\'lishi kerak');
    }

    // Parolni yangilash
    $new_hash = hashPassword($new_password);
    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    executeQuery($sql, [$new_hash, $user_id]);

    // Log yozish
    logActivity('password_changed', $user_id, null);

    return true;
}

// Parolni tiklash (SMS orqali)
function resetPassword($passport, $phone) {
    // Foydalanuvchini topish
    $sql = "SELECT * FROM users WHERE passport_series = ? AND phone = ?";
    $user = fetchOne($sql, [$passport, $phone]);

    if (!$user) {
        throw new Exception('Pasport yoki telefon raqami noto\'g\'ri');
    }

    // Yangi parol yaratish
    $new_password = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
    $password_hash = hashPassword($new_password);

    // Parolni yangilash
    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    executeQuery($sql, [$password_hash, $user['id']]);

    // SMS yuborish
    $sms_message = "Yangi parolingiz: " . $new_password . " Tizimga kirgandan so'ng parolni o'zgartirishni unutmang!";
    sendNotification($user['id'], 'sms', $phone, $sms_message);

    // Log yozish
    logActivity('password_reset', $user['id'], null);

    return true;
}

// Ruxsat tekshiruvi
function hasPermission($required_role) {
    if (!isset($_SESSION['admin_role'])) {
        return false;
    }

    $roles = ['operator' => 1, 'mudiri' => 2, 'admin' => 3];
    $user_level = $roles[$_SESSION['admin_role']] ?? 0;
    $required_level = $roles[$required_role] ?? 0;

    return $user_level >= $required_level;
}

// Foydalanuvchi login tekshiruvi
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?page=login');
        exit;
    }
}

// Admin login tekshiruvi
function requireAdminLogin($required_role = 'operator') {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ?page=admin_login');
        exit;
    }

    if (!hasPermission($required_role)) {
        $_SESSION['error_message'] = 'Bu sahifani ko\'rish uchun ruxsatingiz yo\'q';
        header('Location: ?page=admin_dashboard');
        exit;
    }
}

// Session avtomatik uzaytirish
if (checkSession()) {
    $_SESSION['login_time'] = time();
}

// Remember token tekshiruvi (agar session yo'q bo'lsa)
if (!checkSession() && isset($_COOKIE['remember_token'])) {
    loginByRememberToken();
}