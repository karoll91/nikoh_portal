<?php
/**
 * simple_admin_test.php - Eng oddiy admin login test
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Oddiy Admin Login Test</h1>";

// Database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=nikoh_portal;charset=utf8mb4', 'root', '12345');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database OK<br>";
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
    exit;
}

// Login test
if (isset($_POST['test'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    echo "<h2>Login Test:</h2>";
    echo "Username: $username<br>";
    echo "Password: " . strlen($password) . " chars<br>";

    // Admin topish
    $sql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin) {
        echo "✅ Admin topildi<br>";

        // Parol tekshiruvi
        if (password_verify($password, $admin['password_hash'])) {
            echo "✅ Parol to'g'ri<br>";

            // Session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];

            echo "✅ Session o'rnatildi<br>";
            echo "<strong>🎉 LOGIN MUVAFFAQIYATLI!</strong><br>";
            echo "<a href='index.php?page=admin_dashboard'>Admin Dashboard ga o'tish</a><br>";

        } else {
            echo "❌ Parol noto'g'ri<br>";

            // Yangi hash yaratish
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            echo "Yangi hash: " . substr($new_hash, 0, 30) . "...<br>";

            // Hash yangilash
            $update_sql = "UPDATE admin_users SET password_hash = ? WHERE username = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$new_hash, $username]);
            echo "✅ Parol yangilandi. Qaytadan urinib ko'ring.<br>";
        }
    } else {
        echo "❌ Admin topilmadi<br>";

        // Admin yaratish
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO admin_users (username, password_hash, full_name, position, fhdy_organ, phone, email, role, is_active) 
                VALUES (?, ?, 'Test Admin', 'Administrator', 'FHDY', '+998901234567', 'admin@test.uz', 'admin', 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $hash]);
        echo "✅ Admin yaratildi. Qaytadan urinib ko'ring.<br>";
    }
}

// Mavjud session
if (isset($_SESSION['admin_id'])) {
    echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Siz allaqachon tizimdasiz!</h3>";
    echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
    echo "Admin Name: " . ($_SESSION['admin_name'] ?? 'Noma\'lum') . "<br>";
    echo "Role: " . ($_SESSION['admin_role'] ?? 'Noma\'lum') . "<br>";
    echo "<a href='index.php?page=admin_dashboard' class='btn'>Admin Dashboard</a>";
    echo "</div>";
}
?>

<!-- Login Form -->
<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3>Admin Login Test</h3>
    <p>
        <label>Username:</label><br>
        <input type="text" name="username" value="admin" style="width: 200px; padding: 5px;">
    </p>
    <p>
        <label>Password:</label><br>
        <input type="password" name="password" value="admin123" style="width: 200px; padding: 5px;">
    </p>
    <p>
        <button type="submit" name="test" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px;">
            Test Login
        </button>
    </p>
</form>

<!-- Database Ma'lumotlari -->
<h3>Database Ma'lumotlari:</h3>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
    <tr>
        <th>Username</th>
        <th>Full Name</th>
        <th>Role</th>
        <th>Active</th>
        <th>Password Hash (first 20 chars)</th>
    </tr>
    <?php
    try {
        $stmt = $pdo->query("SELECT username, full_name, role, is_active, password_hash FROM admin_users");
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td>" . ($row['is_active'] ? '✅' : '❌') . "</td>";
            echo "<td>" . substr($row['password_hash'], 0, 20) . "...</td>";
            echo "</tr>";
        }
    } catch (Exception $e) {
        echo "<tr><td colspan='5'>❌ Error: " . $e->getMessage() . "</td></tr>";
    }
    ?>
</table>

<!-- Session Ma'lumotlari -->
<h3>Session Ma'lumotlari:</h3>
<pre style="background: #f1f1f1; padding: 10px; border-radius: 3px;">
<?php print_r($_SESSION); ?>
</pre>

<!-- Quick Fix tugmalari -->
<h3>Quick Fix:</h3>
<div style="margin: 20px 0;">
    <a href="?fix=create_admin" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">
        Admin Yaratish
    </a>
    <a href="?fix=reset_password" style="background: #ffc107; color: black; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">
        Parolni Reset Qilish
    </a>
    <a href="?fix=clear_session" style="background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">
        Session Tozalash
    </a>
</div>

<?php
// Quick Fix amallar
if (isset($_GET['fix'])) {
    echo "<h3>Quick Fix Natijasi:</h3>";

    switch ($_GET['fix']) {
        case 'create_admin':
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin_users (username, password_hash, full_name, position, fhdy_organ, phone, email, role, is_active) 
                    VALUES ('admin', ?, 'System Administrator', 'Administrator', 'Main FHDY', '+998901234567', 'admin@nikoh.uz', 'admin', 1)
                    ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), is_active = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hash]);
            echo "✅ Admin yaratildi/yangilandi<br>";
            echo "<a href='?'>Sahifani yangilash</a><br>";
            break;

        case 'reset_password':
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "UPDATE admin_users SET password_hash = ? WHERE username = 'admin'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hash]);
            echo "✅ Admin paroli reset qilindi (admin123)<br>";
            echo "<a href='?'>Sahifani yangilash</a><br>";
            break;

        case 'clear_session':
            session_destroy();
            session_start();
            echo "✅ Session tozalandi<br>";
            echo "<a href='?'>Sahifani yangilash</a><br>";
            break;
    }
}
?>

<hr>
<p>
    <a href="index.php">🏠 Bosh sahifa</a> |
    <a href="index.php?page=admin_login">🔐 Rasmiy Admin Login</a>
    <?php if (isset($_SESSION['admin_id'])): ?>
        | <a href="index.php?page=admin_dashboard">📊 Admin Dashboard</a>
    <?php endif; ?>
</p>