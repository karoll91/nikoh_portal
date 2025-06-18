<?php
/**
 * config/database.php - Ma'lumotlar bazasi sozlamalari
 * Nikoh Portali
 */

// Xavfsizlik tekshiruvi
if (!defined('CONFIG_LOADED')) {
    die('Access denied - Config not loaded');
}

// Ma'lumotlar bazasi sozlamalari
define('DB_HOST', 'localhost');
define('DB_NAME', 'nikoh_portal');
define('DB_USER', 'root'); // O'zgartirishingiz kerak
define('DB_PASS', ''); // O'zgartirishingiz kerak
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

    // Production va development uchun turli xabarlar
    if (defined('DEVELOPMENT') && DEVELOPMENT) {
        die('Ma\'lumotlar bazasiga ulanishda xatolik: ' . $e->getMessage());
    } else {
        // Production da foydalanuvchiga texnik ma'lumot bermaslik
        die('<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizim xatoligi</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        .error-container { max-width: 500px; margin: 0 auto; }
        .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 20px; }
        .error-title { color: #dc3545; margin-bottom: 20px; }
        .error-message { color: #6c757d; margin-bottom: 30px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h2 class="error-title">Tizimda texnik xatolik</h2>
        <p class="error-message">
            Hozirda tizim texnik ishlar olib borilmoqda. 
            Iltimos, keyinroq qayta urinib ko\'ring.
        </p>
        <a href="/" class="btn">Bosh sahifaga qaytish</a>
    </div>
</body>
</html>');
    }
}

/**
 * Ma'lumotlar bazasi bilan ishlash funksiyalari
 */

// PDO connection olish
function getDbConnection() {
    global $pdo;
    return $pdo;
}

// SQL so'rovni bajarish
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('SQL Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw new Exception('Ma\'lumotlar bazasida xatolik yuz berdi');
    }
}

// Bitta yozuvni olish
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Barcha yozuvlarni olish
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

// Oxirgi qo'shilgan ID ni olish
function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Yozuvlar sonini olish
function getRowCount($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

// INSERT so'rovi
function insertRecord($table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    executeQuery($sql, $data);

    return getLastInsertId();
}

// UPDATE so'rovi
function updateRecord($table, $data, $where, $whereParams = []) {
    $setParts = [];
    foreach (array_keys($data) as $column) {
        $setParts[] = "{$column} = :{$column}";
    }
    $setClause = implode(', ', $setParts);

    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $params = array_merge($data, $whereParams);

    return executeQuery($sql, $params);
}

// DELETE so'rovi
function deleteRecord($table, $where, $params = []) {
    $sql = "DELETE FROM {$table} WHERE {$where}";
    return executeQuery($sql, $params);
}

// Tranzaksiya boshlash
function beginTransaction() {
    global $pdo;
    return $pdo->beginTransaction();
}

// Tranzaksiyani tasdiqlash
function commitTransaction() {
    global $pdo;
    return $pdo->commit();
}

// Tranzaksiyani bekor qilish
function rollbackTransaction() {
    global $pdo;
    return $pdo->rollback();
}

// Ma'lumotlar bazasi mavjudligini tekshirish
function checkDatabaseExists() {
    try {
        global $pdo;
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Jadval mavjudligini tekshirish
function checkTableExists($tableName) {
    try {
        $sql = "SHOW TABLES LIKE ?";
        $result = fetchOne($sql, [$tableName]);
        return !empty($result);
    } catch (Exception $e) {
        return false;
    }
}

// Ma'lumotlar bazasi versiyasini olish
function getDatabaseVersion() {
    try {
        global $pdo;
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        return $result['version'] ?? 'Unknown';
    } catch (Exception $e) {
        return 'Unknown';
    }
}

// Connection holatini tekshirish
function checkConnection() {
    try {
        global $pdo;
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Debug uchun so'nggi SQL xatoliklarni olish
function getLastError() {
    global $pdo;
    $errorInfo = $pdo->errorInfo();
    return $errorInfo[2] ?? 'No error';
}

// Ma'lumotlar bazasi statistikasi
function getDatabaseStats() {
    try {
        $stats = [];

        // Jadvallar soni
        $result = fetchAll("SHOW TABLES");
        $stats['tables_count'] = count($result);

        // Ma'lumotlar bazasi hajmi
        $sql = "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?";
        $result = fetchOne($sql, [DB_NAME]);
        $stats['size_mb'] = $result['size_mb'] ?? 0;

        // Foydalanuvchilar soni
        $stats['users_count'] = fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;

        // Arizalar soni
        $stats['applications_count'] = fetchOne("SELECT COUNT(*) as count FROM applications")['count'] ?? 0;

        return $stats;

    } catch (Exception $e) {
        return [
            'tables_count' => 0,
            'size_mb' => 0,
            'users_count' => 0,
            'applications_count' => 0
        ];
    }
}

// Backup yaratish (oddiy)
function createSimpleBackup($backupDir = 'backups/') {
    try {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

        // mysqldump buyrug'i (agar mavjud bo'lsa)
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            escapeshellarg($filename)
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filename)) {
            return $filename;
        } else {
            throw new Exception('Backup yaratishda xatolik');
        }

    } catch (Exception $e) {
        error_log('Backup error: ' . $e->getMessage());
        return false;
    }
}

// Ma'lumotlar bazasini tozalash (test uchun)
function cleanupTestData() {
    if (!defined('DEVELOPMENT') || !DEVELOPMENT) {
        throw new Exception('Bu funksiya faqat development muhitida ishlaydi');
    }

    try {
        beginTransaction();

        // Test ma'lumotlarni o'chirish
        executeQuery("DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        executeQuery("DELETE FROM notifications WHERE status = 'yuborildi' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");

        commitTransaction();
        return true;

    } catch (Exception $e) {
        rollbackTransaction();
        throw $e;
    }
}

?>