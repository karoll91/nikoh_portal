<?php
/**
 * includes/auth.php - Yakuniy tozalangan autentifikatsiya fayli
 * Barcha funksiya duplikatlarini olib tashlangan versiya
 */

if (!defined('CONFIG_LOADED')) {
    die('Access denied');
}

// Admin tizimga kirishi
function loginAdmin($username, $password) {
    global $pdo;

    try {
        // Admin topish (functions.php dagi getAdminByUsername funksiyasidan foydalanish)
        $admin = getAdminByUsername($username);

        if (!$admin) {
            throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
        }

        // Parolni tekshirish
        if (!password_verify($password, $admin['password_hash'])) {
            throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
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
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin['id']]);

        // Log yozish
        if (function_exists('logActivity')) {
            logActivity('admin_login', null, $admin['id'], [
                'username' => $username
            ]);
        }

        return $admin;

    } catch (PDOException $e) {
        error_log('Admin login database error: ' . $e->getMessage());
        throw new Exception('Tizimda xatolik yuz berdi');
    }
}

// Foydalanuvchi tizimga kirishi
function loginUser($passport, $password, $remember = false) {
    global $pdo;

    try {
        // Pasport formatini tekshirish
        if (!validatePassport($passport)) {
            throw new Exception('Noto\'g\'ri pasport formati');
        }

        // Foydalanuvchini topish (functions.php dagi funksiyadan foydalanish)
        $user = getUserByPassport($passport);

        if (!$user || !$user['is_verified']) {
            throw new Exception('Pasport yoki parol noto\'g\'ri');
        }

        // Parolni tekshirish
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Pasport yoki parol noto\'g\'ri');
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
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$token, $user['id']]);
        }

        // Oxirgi kirish vaqtini yangilash
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id']]);

        // Log yozish
        if (function_exists('logActivity')) {
            logActivity('user_login', $user['id'], null, [
                'passport' => $passport
            ]);
        }

        return $user;

    } catch (PDOException $e) {
        error_log('User login database error: ' . $e->getMessage());
        throw new Exception('Tizimda xatolik yuz berdi');
    }
}

// Foydalanuvchi ro'yxatdan o'tishi
function registerUser($data) {
    global $pdo;

    try {
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

        // Yosh tekshiruvi (18 yoshdan katta bo'lishi kerak)
        if (!empty($data['birth_date'])) {
            $birth_year = date('Y', strtotime($data['birth_date']));
            $current_year = date('Y');
            if (($current_year - $birth_year) < 18) {
                $errors[] = '18 yoshdan kichik shaxslar ro\'yxatdan o\'ta olmaydi';
            }
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
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['phone']]);
        if ($stmt->fetch()) {
            throw new Exception('Bu telefon raqami bilan allaqachon ro\'yxatdan o\'tilgan');
        }

        // Email dublikati tekshiruvi (agar kiritilgan bo'lsa)
        if (!empty($data['email'])) {
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
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
            $data['middle_name'] ?? '',
            $data['birth_date'] ?? null,
            $data['birth_place'] ?? '',
            $data['phone'],
            $data['email'] ?? null,
            $password_hash,
            $data['gender'],
            $data['address'] ?? '',
            $verification_token,
            $data['citizenship'] ?? 'O\'zbekiston'
        ];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $user_id = $pdo->lastInsertId();

        // Log yozish
        if (function_exists('logActivity')) {
            logActivity('user_register', $user_id, null, [
                'passport' => $data['passport_series'],
                'phone' => $data['phone']
            ]);
        }

        // SMS yuborish (tasdiqlash uchun)
        if (function_exists('sendNotification')) {
            $sms_message = "Nikoh portaliga xush kelibsiz! Akkauntingizni tasdiqlamoqchi bo'lsangiz, quyidagi koddan foydalaning: " . substr($verification_token, 0, 6);
            sendNotification($user_id, 'sms', $data['phone'], $sms_message);
        }

        return [
            'user_id' => $user_id,
            'verification_token' => $verification_token
        ];

    } catch (PDOException $e) {
        error_log('User registration database error: ' . $e->getMessage());
        throw new Exception('Tizimda xatolik yuz berdi');
    }
}

// Tizimdan chiqish
function logout() {
    $user_id = $_SESSION['user_id'] ?? null;
    $admin_id = $_SESSION['admin_id'] ?? null;

    // Log yozish
    if (function_exists('logActivity')) {
        if ($user_id) {
            logActivity('user_logout', $user_id, null);
        } elseif ($admin_id) {
            logActivity('admin_logout', null, $admin_id);
        }
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

    if (isset($_SESSION['admin_login_time'])) {
        if (time() - $_SESSION['admin_login_time'] > SESSION_LIFETIME) {
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
    // functions.php dagi funksiyadan foydalanish
    $user = getUserByVerificationToken($token);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_passport'] = $user['passport_series'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['login_time'] = time();

        // Oxirgi kirish vaqtini yangilash
        global $pdo;
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id']]);

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
    global $pdo;
    $new_hash = hashPassword($new_password);
    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_hash, $user_id]);

    // Log yozish
    if (function_exists('logActivity')) {
        logActivity('password_changed', $user_id, null);
    }

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
    if (isset($_SESSION['login_time'])) {
        $_SESSION['login_time'] = time();
    }
    if (isset($_SESSION['admin_login_time'])) {
        $_SESSION['admin_login_time'] = time();
    }
}

// Remember token tekshiruvi (agar session yo'q bo'lsa)
if (!checkSession() && isset($_COOKIE['remember_token'])) {
    loginByRememberToken();
}

?>