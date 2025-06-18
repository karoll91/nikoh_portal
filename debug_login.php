<?php global $pdo;
/**
 * debug_login.php - Admin login muammosini aniqlash
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Login Debug</h1>";

// Ma'lumotlar bazasiga ulanish
try {
    require_once 'config/database.php';
    echo "✅ Database ulanish muvaffaqiyatli<br>";
} catch (Exception $e) {
    echo "❌ Database ulanish xatoligi: " . $e->getMessage() . "<br>";
    exit;
}

// Functions faylini yuklash
try {
    require_once 'includes/functions.php';
    echo "✅ Functions fayli yuklandi<br>";
} catch (Exception $e) {
    echo "❌ Functions fayli xatoligi: " . $e->getMessage() . "<br>";
    exit;
}

// Auth faylini yuklash
try {
    require_once 'includes/auth.php';
    echo "✅ Auth fayli yuklandi<br>";
} catch (Exception $e) {
    echo "❌ Auth fayli xatoligi: " . $e->getMessage() . "<br>";
    echo "Auth faylini yaratamiz...<br>";
}

echo "<hr>";

// Admin mavjudligini tekshirish
echo "<h2>Admin ma'lumotlari:</h2>";
try {
    $sql = "SELECT * FROM admin_users WHERE username = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        echo "✅ Admin topildi:<br>";
        echo "Username: " . htmlspecialchars($admin['username']) . "<br>";
        echo "Full name: " . htmlspecialchars($admin['full_name']) . "<br>";
        echo "Role: " . htmlspecialchars($admin['role']) . "<br>";
        echo "Is active: " . ($admin['is_active'] ? 'Ha' : 'Yo\'q') . "<br>";
        echo "Password hash: " . substr($admin['password_hash'], 0, 20) . "...<br>";

        // Parolni tekshirish
        if (password_verify('admin123', $admin['password_hash'])) {
            echo "✅ Parol to'g'ri<br>";
        } else {
            echo "❌ Parol noto'g'ri - Yangi hash yaratamiz<br>";

            // Yangi parol hash yaratish
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $update_sql = "UPDATE admin_users SET password_hash = ? WHERE username = 'admin'";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$new_hash]);
            echo "✅ Yangi parol hash yaratildi<br>";
        }
    } else {
        echo "❌ Admin topilmadi - Yaratamiz<br>";

        // Admin yaratish
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO admin_users (username, password_hash, full_name, position, fhdy_organ, phone, email, role, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'admin',
            $password_hash,
            'Tizim Administratori',
            'Tizim administratori',
            'Bosh FHDY organi',
            '+998901234567',
            'admin@nikoh.uz',
            'admin',
            1
        ]);
        echo "✅ Admin yaratildi<br>";
    }
} catch (Exception $e) {
    echo "❌ Admin tekshirish xatoligi: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Manual login test
echo "<h2>Manual Login Test:</h2>";
echo "<form method='POST'>";
echo "Username: <input type='text' name='test_username' value='admin'><br><br>";
echo "Password: <input type='password' name='test_password' value='admin123'><br><br>";
echo "<input type='submit' name='test_login' value='Test Login'>";
echo "</form>";

if (isset($_POST['test_login'])) {
    echo "<h3>Login natijasi:</h3>";

    $username = trim($_POST['test_username']);
    $password = trim($_POST['test_password']);

    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Password length: " . strlen($password) . "<br>";

    try {
        // Admin topish
        $sql = "SELECT * FROM admin_users WHERE username = ? AND is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin) {
            echo "✅ Admin topildi<br>";

            // Parolni tekshirish
            if (password_verify($password, $admin['password_hash'])) {
                echo "✅ Parol to'g'ri<br>";

                // Session o'rnatish
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_login_time'] = time();

                echo "✅ Session o'rnatildi<br>";
                echo "Admin ID: " . $_SESSION['admin_id'] . "<br>";
                echo "Admin Name: " . $_SESSION['admin_name'] . "<br>";

                echo "<p><strong>✅ Login muvaffaqiyatli!</strong></p>";
                echo "<a href='?page=admin_dashboard'>Admin Dashboard ga o'tish</a><br>";

            } else {
                echo "❌ Parol noto'g'ri<br>";
            }
        } else {
            echo "❌ Admin topilmadi yoki faol emas<br>";
        }

    } catch (Exception $e) {
        echo "❌ Login xatoligi: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<h3>Session ma'lumotlari:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Harakatlar:</h3>";
echo "<a href='index.php'>Bosh sahifa</a> | ";
echo "<a href='?page=admin_login'>Admin login sahifasi</a> | ";
if (isset($_SESSION['admin_id'])) {
    echo "<a href='?page=admin_dashboard'>Admin Dashboard</a>";
}
?>