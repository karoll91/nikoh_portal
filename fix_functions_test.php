<?php
/**
 * fix_functions_test.php - Funksiya duplikati muammosini test qilish
 * URL: http://localhost/fix_functions_test.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Funksiya Duplikati Fix Test</h1>";

// 1. Config yuklash
echo "<h2>1. Config yuklash</h2>";
try {
    require_once 'config/config.php';
    echo "✅ Config.php yuklandi<br>";
} catch (Error $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
    exit;
} catch (Exception $e) {
    echo "❌ Config exception: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Functions yuklash
echo "<h2>2. Functions yuklash</h2>";
try {
    require_once 'includes/functions.php';
    echo "✅ Functions.php yuklandi<br>";
} catch (Error $e) {
    echo "❌ Functions error: " . $e->getMessage() . "<br>";
    exit;
} catch (Exception $e) {
    echo "❌ Functions exception: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Auth yuklash (bu yerda duplikat xatolik bo'lishi mumkin)
echo "<h2>3. Auth yuklash</h2>";
try {
    require_once 'includes/auth.php';
    echo "✅ Auth.php yuklandi<br>";
} catch (Error $e) {
    echo "❌ Auth error: " . $e->getMessage() . "<br>";
    echo "<p style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Muammo:</strong> Auth.php da funksiya duplikati bor.<br>";
    echo "<strong>Yechim:</strong> Auth.php faylini yangilangan versiya bilan almashtiring.";
    echo "</p>";
    exit;
} catch (Exception $e) {
    echo "❌ Auth exception: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Funksiyalar mavjudligini tekshirish
echo "<h2>4. Asosiy funksiyalar</h2>";
$required_functions = [
    'sanitize',
    'validatePassport',
    'validatePhone',
    'getUserById',
    'getUserByPassport',
    'getAdminById',
    'getAdminByUsername',
    'loginAdmin',
    'loginUser',
    'logout',
    'formatMoney',
    'calculatePaymentAmount'
];

$missing_functions = [];
foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func()<br>";
    } else {
        echo "❌ $func() - mavjud emas<br>";
        $missing_functions[] = $func;
    }
}

// 5. Database ulanish
echo "<h2>5. Database</h2>";
if (isset($pdo)) {
    echo "✅ PDO mavjud<br>";
    try {
        $admin_count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        echo "✅ Admin users: $admin_count ta<br>";
    } catch (Exception $e) {
        echo "❌ Database query error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ PDO mavjud emas<br>";
}

// 6. Login test (agar admin mavjud bo'lsa)
echo "<h2>6. Login test</h2>";
if (function_exists('loginAdmin') && isset($pdo)) {
    try {
        // Test admin mavjudligini tekshirish
        if (function_exists('getAdminByUsername')) {
            $test_admin = getAdminByUsername('admin');
            if ($test_admin) {
                echo "✅ Test admin mavjud<br>";
                echo "Username: " . htmlspecialchars($test_admin['username']) . "<br>";
                echo "Name: " . htmlspecialchars($test_admin['full_name']) . "<br>";
                echo "Role: " . htmlspecialchars($test_admin['role']) . "<br>";
            } else {
                echo "❌ Test admin mavjud emas<br>";
                echo "<a href='fix_admin.php' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Admin yaratish</a><br>";
            }
        } else {
            echo "❌ getAdminByUsername funksiyasi mavjud emas<br>";
        }
    } catch (Exception $e) {
        echo "❌ Login test error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ loginAdmin funksiyasi yoki PDO mavjud emas<br>";
}

// 7. Xulosa
echo "<h2>7. Xulosa</h2>";
if (empty($missing_functions) && isset($pdo) && function_exists('loginAdmin')) {
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3>🎉 MUVAFFAQIYAT!</h3>";
    echo "<p>Barcha funksiyalar to'g'ri yuklandi. Duplikat muammosi hal qilindi.</p>";
    echo "<p><strong>Endi admin login qilishingiz mumkin:</strong></p>";
    echo "<a href='index.php?page=admin_login' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Admin Login</a>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Bosh sahifa</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>⚠️ MUAMMOLAR MAVJUD</h3>";
    if (!empty($missing_functions)) {
        echo "<p>Quyidagi funksiyalar mavjud emas:</p>";
        echo "<ul>";
        foreach ($missing_functions as $func) {
            echo "<li>$func()</li>";
        }
        echo "</ul>";
    }
    if (!isset($pdo)) {
        echo "<p>Database ulanish yo'q</p>";
    }
    echo "<p><strong>Tavsiya:</strong></p>";
    echo "<ul>";
    echo "<li>functions.php faylini yangilang</li>";
    echo "<li>auth.php faylini yangilang</li>";
    echo "<li>config.php faylini tekshiring</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Bu test muvaffaqiyatli tugagandan keyin bu faylni o'chirishingiz mumkin.</small></p>";
?>