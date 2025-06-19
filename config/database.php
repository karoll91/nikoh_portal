<?php
/**
 * config/database.php - TUZATILGAN VERSIYA
 * Ma'lumotlar bazasi sozlamalari va funksiyalar
 */

// Ma'lumotlar bazasi sozlamalari
define('DB_HOST', 'localhost');
define('DB_NAME', 'nikoh_portal');
define('DB_USER', 'root');
define('DB_PASS', '12345'); // O'zgartirishingiz kerak
define('DB_CHARSET', 'utf8mb4');

// PDO sozlamalari
$pdo_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
];

// Global $pdo o'zgaruvchisini yaratish
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);

    // Timezone o'rnatish
    $pdo->exec("SET time_zone = '+05:00'"); // Toshkent vaqti

} catch (PDOException $e) {
    // Xatolikni log qilish
    error_log('Database connection error: ' . $e->getMessage());

    // Xatolik sahifasini ko'rsatish
    die('
    <!DOCTYPE html>
    <html lang="uz">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ma\'lumotlar bazasi xatoligi</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; background-color: #f8f9fa; }
            .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 20px; }
            .error-title { color: #dc3545; margin-bottom: 20px; }
            .error-message { color: #6c757d; margin-bottom: 30px; }
            .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h2 class="error-title">Ma\'lumotlar bazasiga ulanishda xatolik</h2>
            <p class="error-message">
                Iltimos, konfiguratsiyani tekshiring yoki administrator bilan bog\'laning.
            </p>
            <a href="/" class="btn">Bosh sahifaga qaytish</a>
        </div>
    </body>
    </html>');
}

/**
 * Ma'lumotlar bazasi bilan ishlash funksiyalari
 */

// Bitta yozuvni olish
function fetchOne($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('fetchOne error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        return false;
    }
}

// Barcha yozuvlarni olish
function fetchAll($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('fetchAll error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        return [];
    }
}

// SQL so'rovni bajarish
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('executeQuery error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw new Exception('Ma\'lumotlar bazasida xatolik yuz berdi');
    }
}

// INSERT so'rovi
function insertRecord($table, $data) {
    global $pdo;
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('insertRecord error: ' . $e->getMessage() . ' | Table: ' . $table);
        throw new Exception('Ma\'lumot qo\'shishda xatolik yuz berdi');
    }
}

// UPDATE so'rovi
function updateRecord($table, $data, $where, $whereParams = []) {
    global $pdo;
    try {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);

        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('updateRecord error: ' . $e->getMessage() . ' | Table: ' . $table);
        throw new Exception('Ma\'lumot yangilashda xatolik yuz berdi');
    }
}

// DELETE so'rovi
function deleteRecord($table, $where, $params = []) {
    global $pdo;
    try {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('deleteRecord error: ' . $e->getMessage() . ' | Table: ' . $table);
        throw new Exception('Ma\'lumot o\'chirishda xatolik yuz berdi');
    }
}

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

// Ariza turini olish
function getApplicationType($type) {
    $types = [
        'nikoh' => ['label' => 'Nikoh', 'icon' => 'fa-heart', 'color' => 'success'],
        'ajralish' => ['label' => 'Ajralish', 'icon' => 'fa-handshake-slash', 'color' => 'warning']
    ];

    return $types[$type] ?? ['label' => $type, 'icon' => 'fa-file', 'color' => 'secondary'];
}

// To'lov miqdorini hisoblash
function calculatePaymentAmount($application_type) {
    $base_amount = ($application_type === 'nikoh') ? NIKOH_DAVLAT_BOJI : AJRALISH_DAVLAT_BOJI;
    $gerb_yigimi = (BHM_MIQDORI * GERB_YIGIMI_FOIZ) / 100;

    return $base_amount + $gerb_yigimi;
}

// Log yozish
function logActivity($action, $user_id = null, $admin_id = null, $details = []) {
    global $pdo;
    try {
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

// Xabarnoma yuborish
function sendNotification($user_id, $type, $recipient, $message, $subject = null) {
    global $pdo;
    try {
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

// Admin login funksiyasi
function loginAdmin($username, $password) {
    global $pdo;

    try {
        $admin = getAdminByUsername($username);

        if (!$admin) {
            throw new Exception('Foydalanuvchi nomi yoki parol noto\'g\'ri');
        }

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

        logActivity('admin_login', null, $admin['id'], ['username' => $username]);

        return $admin;

    } catch (Exception $e) {
        error_log('Admin login error: ' . $e->getMessage());
        throw $e;
    }
}

// Ma'lumotlar bazasi connection-ni tekshirish
function checkConnection() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

?>