<?php
/**
 * final_test.php - Yakuniy test (konstantalar muammosi hal qilingandan keyin)
 * URL: http://localhost/final_test.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Yakuniy Test - Konstantalar Fix</h1>";

// 1. Config yuklash
echo "<h2>1. Config fayli test</h2>";
try {
    require_once 'config/config.php';
    echo "✅ Config.php muvaffaqiyatli yuklandi<br>";

    // Asosiy konstantalar
    $constants = ['SITE_NAME', 'UPLOAD_PATH', 'UPLOAD_URL', 'MAX_FILE_SIZE', 'NIKOH_DAVLAT_BOJI'];
    foreach ($constants as $const) {
        if (defined($const)) {
            echo "✅ $const: " . constant($const) . "<br>";
        } else {
            echo "❌ $const: Aniqlanmagan<br>";
        }
    }

    // Array konstantalar
    echo "<h3>Array konstantalar:</h3>";
    $file_types = getAllowedFileTypes();
    echo "✅ Allowed file types: " . implode(', ', $file_types) . "<br>";

    $mime_types = getAllowedMimeTypes();
    echo "✅ Allowed MIME types: " . count($mime_types) . " ta<br>";

} catch (Exception $e) {
    echo "❌ Config xatoligi: " . $e->getMessage() . "<br>";
}

// 2. Functions yuklash
echo "<h2>2. Functions fayli test</h2>";
try {
    require_once 'includes/functions.php';
    echo "✅ Functions.php muvaffaqiyatli yuklandi<br>";

    // Ba'zi funksiyalarni test qilish
    $test_functions = [
        'sanitize' => 'Test string',
        'validatePassport' => 'AA1234567',
        'validatePhone' => '+998901234567',
        'formatMoney' => 100000
    ];

    foreach ($test_functions as $func => $param) {
        if (function_exists($func)) {
            $result = $func($param);
            echo "✅ $func('$param') = " . (is_bool($result) ? ($result ? 'true' : 'false') : $result) . "<br>";
        } else {
            echo "❌ $func: Funksiya mavjud emas<br>";
        }
    }

} catch (Exception $e) {
    echo "❌ Functions xatoligi: " . $e->getMessage() . "<br>";
}

// 3. Database test
echo "<h2>3. Database test</h2>";
try {
    if (isset($pdo)) {
        echo "✅ PDO mavjud<br>";

        // Admin test
        $admin_count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        echo "✅ Admin users: $admin_count ta<br>";

        if ($admin_count == 0) {
            echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7; border-radius: 5px;'>";
            echo "⚠️ Adminlar yo'q. <a href='fix_admin.php'>Admin yaratish</a>";
            echo "</div>";
        }

    } else {
        echo "❌ PDO mavjud emas<br>";
    }
} catch (Exception $e) {
    echo "❌ Database xatoligi: " . $e->getMessage() . "<br>";
}

// 4. Session test
echo "<h2>4. Session test</h2>";
echo "Session status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";

if (isset($_SESSION['admin_id'])) {
    echo "✅ Admin tizimda: " . ($_SESSION['admin_name'] ?? 'Noma\'lum') . "<br>";
    echo "<a href='index.php?page=admin_dashboard' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>📊 Admin Dashboard</a><br>";
} else {
    echo "❌ Admin tizimda emas<br>";
    echo "<a href='fix_admin.php' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔧 Admin yaratish</a><br>";
}

// 5. File permissions test
echo "<h2>5. Fayl ruxsatlari test</h2>";
$directories = [
    'uploads' => UPLOAD_PATH ?? 'uploads/',
    'cache' => CACHE_PATH ?? 'cache/',
    'logs' => LOG_PATH ?? 'logs/'
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $writable = is_writable($path);
        echo ($writable ? "✅" : "❌") . " $name: " . ($writable ? "Yozish mumkin" : "Yozish mumkin emas") . "<br>";
    } else {
        echo "❌ $name: Papka mavjud emas ($path)<br>";
    }
}

// 6. URL test
echo "<h2>6. URL test</h2>";
$test_urls = [
    'Bosh sahifa' => 'index.php',
    'Admin login' => 'index.php?page=admin_login',
    'User login' => 'index.php?page=login',
    'About' => 'index.php?page=about'
];

foreach ($test_urls as $name => $url) {
    echo "🔗 <a href='$url' target='_blank'>$name</a><br>";
}

// 7. Xulosa
echo "<h2>7. Xulosa</h2>";
$issues = 0;

// Konstantalar tekshiruvi
if (!defined('SITE_NAME')) $issues++;
if (!defined('UPLOAD_PATH')) $issues++;
if (!function_exists('getAllowedFileTypes')) $issues++;

// Database tekshiruvi
if (!isset($pdo)) $issues++;

if ($issues == 0) {
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>🎉 HAMMASI TAYYOR!</h3>";
    echo "<p>Barcha testlar muvaffaqiyatli o'tdi. Tizim ishga tayyor.</p>";
    echo "<p><strong>Keyingi qadamlar:</strong></p>";
    echo "<ul>";
    echo "<li>Admin yarating (agar yo'q bo'lsa): <a href='fix_admin.php'>fix_admin.php</a></li>";
    echo "<li>Admin panel: <a href='index.php?page=admin_login'>Admin Login</a></li>";
    echo "<li>Asosiy sayt: <a href='index.php'>Bosh sahifa</a></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>⚠️ MUAMMOLAR BOR</h3>";
    echo "<p>$issues ta muammo topildi. Yuqoridagi xatoliklarni tuzating.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Bu test faylini ishlatgandan keyin o'chirishingiz mumkin.</small></p>";
echo "<p>";
echo "<a href='index.php'>🏠 Bosh sahifa</a> | ";
echo "<a href='fix_admin.php'>🔧 Admin fix</a> | ";
echo "<a href='index.php?page=admin_login'>🔐 Admin login</a>";
echo "</p>";
?>