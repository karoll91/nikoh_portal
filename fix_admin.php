<?php
/**
 * fix_admin.php - Admin login muammolarini hal qilish
 * URL: http://localhost/fix_admin.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Login Fix</h1>";

// 1. Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nikoh_portal;charset=utf8mb4', 'root', '12345');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database ulanish muvaffaqiyatli<br>";
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    echo "<p>Avval ma'lumotlar bazasini yarating:</p>";
    echo "<ol>";
    echo "<li>phpMyAdmin ga kiring</li>";
    echo "<li>nikoh_portal nomli database yarating</li>";
    echo "<li>data/nikoh_portal.sql faylini import qiling</li>";
    echo "</ol>";
    exit;
}

// 2. Admin yaratish/yangilash
echo "<h2>Admin yaratish/yangilash</h2>";
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO admin_users (username, password_hash, full_name, position, fhdy_organ, phone, email, role, is_active) 
        VALUES ('admin', ?, 'System Administrator', 'Administrator', 'Main FHDY', '+998901234567', 'admin@nikoh.uz', 'admin', 1)
        ON DUPLICATE KEY UPDATE 
        password_hash = VALUES(password_hash), 
        is_active = 1,
        full_name = VALUES(full_name)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$password_hash]);
    echo "✅ Admin muvaffaqiyatli yaratildi/yangilandi<br>";
} catch (Exception $e) {
    echo "❌ Admin yaratishda xatolik: " . $e->getMessage() . "<br>";
}

// 3. Login test
echo "<h2>Login test</h2>";
$username = 'admin';
$password = 'admin123';

try {
    $sql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin) {
        echo "✅ Admin topildi: " . htmlspecialchars($admin['full_name']) . "<br>";

        if (password_verify($password, $admin['password_hash'])) {
            echo "✅ Parol to'g'ri<br>";

            // Session o'rnatish
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_login_time'] = time();

            echo "✅ Session o'rnatildi<br>";
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>🎉 MUVAFFAQIYAT!</h3>";
            echo "<p><strong>Kirish ma'lumotlari:</strong></p>";
            echo "<ul>";
            echo "<li>Username: <strong>admin</strong></li>";
            echo "<li>Password: <strong>admin123</strong></li>";
            echo "</ul>";
            echo "<p><strong>Endi quyidagi havolalardan foydalaning:</strong></p>";
            echo "<p>";
            echo "<a href='index.php?page=admin_dashboard' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📊 Admin Dashboard</a>";
            echo "<a href='index.php?page=admin_login' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Login sahifasi</a>";
            echo "</p>";
            echo "</div>";

        } else {
            echo "❌ Parol noto'g'ri<br>";
        }
    } else {
        echo "❌ Admin topilmadi<br>";
    }
} catch (Exception $e) {
    echo "❌ Login test xatoligi: " . $e->getMessage() . "<br>";
}

// 4. Ma'lumotlar ko'rsatish
echo "<h2>Database ma'lumotlari</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Active</th><th>Created</th>";
echo "</tr>";

try {
    $stmt = $pdo->query("SELECT id, username, full_name, role, is_active, created_at FROM admin_users ORDER BY id");
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . ($row['is_active'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($row['created_at'] ? date('d.m.Y H:i', strtotime($row['created_at'])) : '-') . "</td>";
        echo "</tr>";
    }
} catch (Exception $e) {
    echo "<tr><td colspan='6'>❌ Error: " . $e->getMessage() . "</td></tr>";
}
echo "</table>";

// 5. Session ma'lumotlari
echo "<h2>Session ma'lumotlari</h2>";
if (empty($_SESSION)) {
    echo "<p>Session bo'sh</p>";
} else {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    foreach ($_SESSION as $key => $value) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 6. Quick actions
echo "<h2>Tezkor harakatlar</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='?action=reset' style='background: #ffc107; color: black; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>🔄 Parolni reset qilish</a>";
echo "<a href='?action=clear_session' style='background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>🗑️ Session tozalash</a>";
echo "<a href='index.php' style='background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🏠 Bosh sahifa</a>";
echo "</div>";

// Quick actions handler
if (isset($_GET['action'])) {
    echo "<h3>Harakat natijasi:</h3>";

    switch ($_GET['action']) {
        case 'reset':
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "UPDATE admin_users SET password_hash = ? WHERE username = 'admin'";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$new_hash])) {
                echo "<div style='color: green;'>✅ Admin paroli reset qilindi (admin123)</div>";
            } else {
                echo "<div style='color: red;'>❌ Parol reset qilishda xatolik</div>";
            }
            echo "<a href='fix_admin.php'>Orqaga</a>";
            break;

        case 'clear_session':
            session_destroy();
            session_start();
            echo "<div style='color: green;'>✅ Session tozalandi</div>";
            echo "<a href='fix_admin.php'>Orqaga</a>";
            break;
    }
}

echo "<hr>";
echo "<p><small>Bu fayl faqat test uchun. Keyinchalik o'chiring yoki nomini o'zgartiring.</small></p>";
?>