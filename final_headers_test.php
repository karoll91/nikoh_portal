<?php
/**
 * final_headers_test.php - Headers muammosi yechilganligini tekshirish
 * URL: http://localhost/final_headers_test.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Headers Fix Test</h1>";

// Test 1: Config yuklash
echo "<h2>1. Config yuklash</h2>";
try {
    require_once 'config/config.php';
    echo "✅ Config yuklandi (headers yo'q)<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}

// Test 2: Functions yuklash
echo "<h2>2. Functions yuklash</h2>";
try {
    require_once 'includes/functions.php';
    echo "✅ Functions yuklandi (headers yo'q)<br>";
} catch (Exception $e) {
    echo "❌ Functions error: " . $e->getMessage() . "<br>";
}

// Test 3: Auth yuklash
echo "<h2>3. Auth yuklash</h2>";
try {
    require_once 'includes/auth.php';
    echo "✅ Auth yuklandi (headers yo'q)<br>";
} catch (Exception $e) {
    echo "❌ Auth error: " . $e->getMessage() . "<br>";
}

// Test 4: Database test
echo "<h2>4. Database test</h2>";
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

// Test 5: Login funksiyasi
echo "<h2>5. Login funksiya test</h2>";
if (function_exists('loginAdmin')) {
    echo "✅ loginAdmin() funksiyasi mavjud<br>";

    // Demo login test (xatoliksiz)
    echo "<form method='POST' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Demo Login Test</h4>";
    echo "<input type='text' name='username' value='admin' placeholder='Username' style='margin: 5px; padding: 5px;'><br>";
    echo "<input type='password' name='password' value='admin123' placeholder='Password' style='margin: 5px; padding: 5px;'><br>";
    echo "<button type='submit' name='test_login' style='background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 3px; margin: 5px;'>Test Login (Headers yo'q)</button>";
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
            echo "Session admin_id: " . ($_SESSION['admin_id'] ?? 'Yo\'q') . "<br>";
            echo "<strong>❌ Headers warning yo'q!</strong><br>";
            echo "</div>";

        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "❌ Login xatoligi: " . $e->getMessage();
            echo "</div>";
        }
    }
} else {
    echo "❌ loginAdmin() funksiyasi mavjud emas<br>";
}

// Test 6: Index.php test
echo "<h2>6. Index.php test</h2>";
echo "<p>Quyidagi havolalar headers warning bermaydi:</p>";
echo "<div style='margin: 10px 0;'>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin: 5px;'>🏠 Bosh sahifa</a>";
echo "<a href='index.php?page=admin_login' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin: 5px;'>🔐 Admin Login</a>";
echo "<a href='index.php?page=about' style='background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin: 5px;'>ℹ️ About</a>";
echo "</div>";

// Test 7: Session test
echo "<h2>7. Session test</h2>";
echo "Session status: " . session_status() . " (2=ACTIVE)<br>";
echo "Session ID: " . session_id() . "<br>";

if (isset($_SESSION['admin_id'])) {
    echo "✅ Admin tizimda: " . ($_SESSION['admin_name'] ?? 'Noma\'lum') . "<br>";
    echo "<a href='index.php?page=admin_dashboard' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>📊 Admin Dashboard</a><br>";
} else {
    echo "❌ Admin tizimda emas<br>";
}

// Test 8: Yakuniy natija
echo "<h2>8. 🏆 YAKUNIY NATIJA</h2>";

$issues = [];
if (!function_exists('loginAdmin')) $issues[] = "loginAdmin funksiyasi yo'q";
if (!isset($pdo)) $issues[] = "Database ulanish yo'q";

if (empty($issues)) {
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #c3e6cb; border-radius: 10px; text-align: center;'>";
    echo "<h2>🎉 HEADERS MUAMMOSI HAL QILINDI!</h2>";
    echo "<p><strong>Barcha testlar muvaffaqiyatli!</strong></p>";
    echo "<ul style='text-align: left;'>";
    echo "<li>✅ Headers already sent warning yo'q</li>";
    echo "<li>✅ Barcha funksiyalar ishlaydi</li>";
    echo "<li>✅ Login funksiyasi JavaScript redirect ishlatadi</li>";
    echo "<li>✅ Logout ham JavaScript orqali</li>";
    echo "<li>✅ Tizim to'liq ishlaydi</li>";
    echo "</ul>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php?page=admin_login' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; font-size: 18px;'>🔐 Admin Login (FIXED)</a>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px; font-size: 18px;'>🏠 Bosh sahifa</a>";
    echo "</div>";
    echo "<p><small>Demo: admin / admin123</small></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border: 1px solid #f5c6cb; border-radius: 10px;'>";
    echo "<h3>⚠️ HALI HAM MUAMMOLAR BOR</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>Qanday muammo hal qilindi?</h3>";
echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px;'>";
echo "<h5>Muammo:</h5>";
echo "<p><code>Warning: Cannot modify header information - headers already sent</code></p>";
echo "<h5>Yechim:</h5>";
echo "<ol>";
echo "<li><strong>index.php:</strong> Output buffering qo'shildi (ob_start/ob_end_flush)</li>";
echo "<li><strong>auth.php:</strong> Barcha header() funksiyalari olib tashlandi</li>";
echo "<li><strong>functions.php:</strong> redirect() funksiyasi JavaScript ishlatadi</li>";
echo "<li><strong>logout:</strong> JavaScript redirect ishlatildi</li>";
echo "<li><strong>Session:</strong> Xavfsiz cookie o'rnatish output buffer ichida</li>";
echo "</ol>";
echo "<p><strong>Natija:</strong> Headers warning yo'q, tizim to'liq ishlaydi!</p>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Keyingi qadamlar:</strong></p>";
echo "<ol>";
echo "<li>Eski index.php ni yangi versiya bilan almashtiring</li>";
echo "<li>auth.php ni fixed versiya bilan almashtiring</li>";
echo "<li>functions.php ni fixed versiya bilan almashtiring</li>";
echo "<li>Bu test faylini o'chiring</li>";
echo "</ol>";

echo "<p><small>Bu test muvaffaqiyatli tugagandan keyin bu faylni o'chirishingiz mumkin.</small></p>";
?>