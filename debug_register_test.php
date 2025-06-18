<?php
/**
 * debug_register_test.php - Ro'yxatdan o'tish muammosini tekshirish
 * URL: http://localhost/debug_register_test.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Ro'yxatdan O'tish Debug Test</h1>";

// Test 1: Config va functions yuklash
echo "<h2>1. Asosiy fayllar test</h2>";
try {
    require_once 'config/config.php';
    echo "✅ Config yuklandi<br>";

    require_once 'includes/functions.php';
    echo "✅ Functions yuklandi<br>";

    if (file_exists('includes/auth.php')) {
        require_once 'includes/auth.php';
        echo "✅ Auth yuklandi<br>";
    } else {
        echo "❌ Auth fayli yo'q<br>";
    }
} catch (Exception $e) {
    echo "❌ Yuklash xatoligi: " . $e->getMessage() . "<br>";
}

// Test 2: Database connection
echo "<h2>2. Database test</h2>";
if (isset($pdo)) {
    echo "✅ PDO mavjud<br>";
    try {
        $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "✅ Users table: $user_count ta foydalanuvchi<br>";

        // Table structure test
        $columns = $pdo->query("DESCRIBE users")->fetchAll();
        echo "✅ Users table columns: " . count($columns) . " ta<br>";

    } catch (Exception $e) {
        echo "❌ Database xatoligi: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ PDO mavjud emas<br>";
}

// Test 3: Validation functions
echo "<h2>3. Validation functions test</h2>";
$test_data = [
    'validatePassport' => ['AA1234567', 'INVALID'],
    'validatePhone' => ['+998901234567', '901234567'],
    'validateEmail' => ['test@example.com', 'invalid-email']
];

foreach ($test_data as $func => $values) {
    if (function_exists($func)) {
        echo "✅ $func() mavjud: ";
        echo $func($values[0]) ? "✅ valid" : "❌ invalid";
        echo " | ";
        echo $func($values[1]) ? "✅ valid" : "❌ invalid";
        echo "<br>";
    } else {
        echo "❌ $func() mavjud emas<br>";
    }
}

// Test 4: registerUser function
echo "<h2>4. registerUser function test</h2>";
if (function_exists('registerUser')) {
    echo "✅ registerUser() funksiyasi mavjud<br>";
} else {
    echo "❌ registerUser() funksiyasi mavjud emas<br>";
}

// Test 5: Manual registration test
echo "<h2>5. Manual Registration Test</h2>";

if (isset($_POST['test_register'])) {
    echo "<h3>Registration Test Natijasi:</h3>";

    try {
        $test_data = [
            'passport_series' => $_POST['passport_series'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'middle_name' => $_POST['middle_name'],
            'birth_date' => $_POST['birth_date'],
            'birth_place' => $_POST['birth_place'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'confirm_password' => $_POST['confirm_password'],
            'gender' => $_POST['gender'],
            'address' => $_POST['address'],
            'citizenship' => 'O\'zbekiston'
        ];

        echo "<div style='background: #e2e3e5; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Yuborilgan ma'lumotlar:</strong><br>";
        foreach ($test_data as $key => $value) {
            if ($key !== 'password' && $key !== 'confirm_password') {
                echo "$key: " . htmlspecialchars($value) . "<br>";
            } else {
                echo "$key: " . str_repeat('*', strlen($value)) . "<br>";
            }
        }
        echo "</div>";

        // registerUser funksiyasini chaqirish
        if (function_exists('registerUser')) {
            $result = registerUser($test_data);

            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "<h4>✅ RO'YXATDAN O'TISH MUVAFFAQIYATLI!</h4>";
            echo "User ID: " . $result['user_id'] . "<br>";
            echo "Verification Token: " . substr($result['verification_token'], 0, 10) . "...<br>";
            echo "</div>";

        } else {
            throw new Exception('registerUser funksiyasi mavjud emas');
        }

    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h4>❌ XATOLIK:</h4>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

// Test form
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🧪 Test Registration Form</h3>";
echo "<form method='POST'>";
echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;'>";
echo "<input type='text' name='passport_series' placeholder='AA1234567' required>";
echo "<input type='text' name='first_name' placeholder='Ism' required>";
echo "<input type='text' name='last_name' placeholder='Familiya' required>";
echo "<input type='text' name='middle_name' placeholder='Otasining ismi' required>";
echo "<input type='date' name='birth_date' value='1990-01-01' required>";
echo "<input type='text' name='birth_place' placeholder='Tug\\'ilgan joy' required>";
echo "<input type='tel' name='phone' placeholder='+998901234567' required>";
echo "<input type='email' name='email' placeholder='email@example.com'>";
echo "<input type='password' name='password' placeholder='Parol' required>";
echo "<input type='password' name='confirm_password' placeholder='Parolni tasdiqlang' required>";
echo "<select name='gender' required><option value=''>Jins</option><option value='erkak'>Erkak</option><option value='ayol'>Ayol</option></select>";
echo "<input type='text' name='address' placeholder='Manzil' required>";
echo "</div>";
echo "<button type='submit' name='test_register' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; width: 100%;'>Test Registration</button>";
echo "</form>";
echo "</div>";

// Test 6: Session ma'lumotlari
echo "<h2>6. Session ma'lumotlari</h2>";
if (empty($_SESSION)) {
    echo "Session bo'sh<br>";
} else {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// Test 7: POST ma'lumotlari
if (!empty($_POST)) {
    echo "<h2>7. POST ma'lumotlari</h2>";
    echo "<pre>";
    foreach ($_POST as $key => $value) {
        if ($key !== 'password' && $key !== 'confirm_password') {
            echo "$key: " . htmlspecialchars($value) . "\n";
        } else {
            echo "$key: " . str_repeat('*', strlen($value)) . "\n";
        }
    }
    echo "</pre>";
}

// Muhim fayllar ro'yxati
echo "<h2>8. Muhim fayllar mavjudligi</h2>";
$important_files = [
    'config/config.php',
    'config/database.php',
    'includes/functions.php',
    'includes/auth.php',
    'pages/user/register.php'
];

foreach ($important_files as $file) {
    echo file_exists($file) ? "✅" : "❌";
    echo " $file<br>";
}

?>

<hr>
<div style="margin: 20px 0;">
    <h3>🔧 Tezkor yechimlar:</h3>
    <div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;">
        <h5>Agar ro'yxatdan o'tish ishlamasa:</h5>
        <ol>
            <li><strong>auth.php</strong> faylini tekshiring</li>
            <li><strong>registerUser</strong> funksiyasi mavjudligini tekshiring</li>
            <li><strong>Database</strong> ulanishini tekshiring</li>
            <li><strong>users</strong> jadvalini tekshiring</li>
            <li><strong>Validation</strong> funksiyalarini tekshiring</li>
        </ol>
    </div>

    <div style="margin-top: 15px;">
        <a href="index.php?page=register" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">📝 Haqiqiy Register</a>
        <a href="index.php" style="background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">🏠 Bosh sahifa</a>
    </div>
</div>

<p><small>Bu debug fayl muammoni aniqlash uchun. Hal qilingandan keyin o'chiring.</small></p>