<?php
/**
 * final_fix_test.php - Yakuniy tuzatish testi
 * URL: http://localhost/final_fix_test.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🎯 Yakuniy Tuzatish Testi</h1>";

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

    // Funksiyalar ro'yxati
    $functions_count = 0;
    $test_functions = [
        'sanitize', 'validatePassport', 'validatePhone', 'getUserById',
        'getUserByPassport', 'getAdminById', 'getAdminByUsername',
        'getUserByVerificationToken', 'formatMoney', 'calculatePaymentAmount'
    ];

    foreach ($test_functions as $func) {
        if (function_exists($func)) {
            $functions_count++;
        }
    }

    echo "✅ Functions mavjud: $functions_count/" . count($test_functions) . "<br>";

} catch (Error $e) {
    echo "❌ Functions error: " . $e->getMessage() . "<br>";
    exit;
} catch (Exception $e) {
    echo "❌ Functions exception: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Auth yuklash (MUHIM TEST)
echo "<h2>3. Auth yuklash</h2>";
try {
    require_once 'includes/auth.php';
    echo "✅ Auth.php MUVAFFAQIYATLI yuklandi<br>";

    // Auth funksiyalari
    $auth_functions = ['loginAdmin', 'loginUser', 'registerUser', 'logout', 'checkSession'];
    $auth_count = 0;
    foreach ($auth_functions as $func) {
        if (function_exists($func)) {
            echo "✅ $func() mavjud<br>";
            $auth_count++;
        } else {
            echo "❌ $func() mavjud emas<br>";
        }
    }

    echo "<strong>Auth funksiyalar: $auth_count/" . count($auth_functions) . "</strong><br>";

} catch (Error $e) {
    echo "❌ Auth FATAL ERROR: " . $e->getMessage() . "<br>";
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>❌ FUNKSIYA DUPLIKATI MUAMMOSI</h4>";
    echo "<p>Auth.php faylida hali ham funksiya duplikatlari bor.</p>";
    echo "<p><strong>Yechim:</strong> Auth.php ni yuqoridagi 'final_cleaned_auth' versiyasi bilan almashtiring.</p>";
    echo "</div>";
    exit;
} catch (Exception $e) {
    echo "❌ Auth exception: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Database test
echo "<h2>4. Database test</h2>";
if (isset($pdo)) {
    echo "✅ PDO mavjud<br>";
    try {
        $version = $pdo->query("SELECT VERSION() as version")->fetch()['version'];
        echo "✅ MySQL version: $version<br>";

        $admin_count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        echo "✅ Admin users: $admin_count ta<br>";

        if ($admin_count == 0) {
            echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7; border-radius: 5px;'>";
            echo "⚠️ Adminlar yo'q. <a href='fix_admin.php' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Admin yaratish</a>";
            echo "</div>";
        }

    } catch (Exception $e) {
        echo "❌ Database query error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ PDO mavjud emas<br>";
}

// 5. Session test
echo "<h2>5. Session test</h2>";
echo "Session status: " . session_status() . " (2=ACTIVE)<br>";
echo "Session ID: " . session_id() . "<br>";

if (isset($_SESSION['admin_id'])) {
    echo "✅ Admin tizimda: " . ($_SESSION['admin_name'] ?? 'Noma\'lum') . "<br>";
} else {
    echo "❌ Admin tizimda emas<br>";
}

// 6. Test login (agar admin mavjud bo'lsa)
echo "<h2>6. Test login</h2>";
if (function_exists('loginAdmin') && isset($pdo) && $admin_count > 0) {
    echo "<form method='POST' style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Admin login test</h4>";
    echo "<input type='text' name='username' value='admin' placeholder='Username' style='margin: 5px; padding: 5px;'><br>";
    echo "<input type='password' name='password' value='admin123' placeholder='Password' style='margin: 5px; padding: 5px;'><br>";
    echo "<button type='submit' name='test_login' style='background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 3px; margin: 5px;'>Test Login</button>";
    echo "</form>";

    if (isset($_POST['test_login'])) {
        try {
            $username = $_POST['username'];
            $password = $_POST['password'];

            echo "<h4>Login test natijasi:</h4>";
            $admin = loginAdmin($username, $password);

            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "🎉 <strong>LOGIN MUVAFFAQIYATLI!</strong><br>";
            echo "Admin: " . htmlspecialchars($admin['full_name']) . "<br>";
            echo "Role: " . htmlspecialchars($admin['role']) . "<br>";
            echo "</div>";

        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "❌ Login xatoligi: " . $e->getMessage();
            echo "</div>";
        }
    }
} else {
    echo "❌ Login test imkonsiz (loginAdmin funksiyasi, PDO yoki admin mavjud emas)<br>";
}

// 7. YAKUNIY XULOSA
echo "<h2>7. 🏆 YAKUNIY XULOSA</h2>";

$all_good = true;
$issues = [];

// Tekshiruvlar
if (!function_exists('loginAdmin')) {
    $all_good = false;
    $issues[] = "loginAdmin funksiyasi mavjud emas";
}
if (!isset($pdo)) {
    $all_good = false;
    $issues[] = "Database ulanish yo'q";
}
if ($admin_count == 0) {
    $issues[] = "Admin foydalanuvchilar yo'q";
}

if ($all_good && empty($issues)) {
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #c3e6cb; border-radius: 10px; text-align: center;'>";
    echo "<h2>🎉 MUKAMMAL!</h2>";
    echo "<p><strong>Barcha testlar muvaffaqiyatli o'tdi!</strong></p>";
    echo "<p>Funksiya duplikatlari hal qilindi, tizim to'liq ishlaydi.</p>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php?page=admin_login' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; font-size: 18px;'>🔐 Admin Login</a>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; font-size: 18px;'>🏠 Bosh sahifa</a>";
    echo "</div>";
    echo "<p><small>Demo: admin / admin123</small></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border: 1px solid #f5c6cb; border-radius: 10px;'>";
    echo "<h3>⚠️ HALI HAM MUAMMOLAR BOR</h3>";
    if (!empty($issues)) {
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo "</ul>";
    }
    echo "<p><strong>Tavsiyalar:</strong></p>";
    echo "<ul>";
    echo "<li>auth.php ni 'final_cleaned_auth' versiyasi bilan almashtiring</li>";
    echo "<li><a href='fix_admin.php'>Admin yarating</a></li>";
    echo "<li>Database konfiguratsiyasini tekshiring</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Fayllar holati:</strong></p>";
echo "<ul>";
echo "<li>✅ config/config.php - yuklandi</li>";
echo "<li>✅ includes/functions.php - yuklandi</li>";
echo "<li>" . (function_exists('loginAdmin') ? "✅" : "❌") . " includes/auth.php - " . (function_exists('loginAdmin') ? "muvaffaqiyatli" : "duplikat muammosi") . "</li>";
echo "</ul>";

echo "<p><small>Bu test muvaffaqiyatli tugagandan keyin bu faylni o'chirishingiz mumkin.</small></p>";
?>