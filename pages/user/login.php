<?php
/**
 * Oddiy kirish sahifasi
 */

// Agar allaqachon tizimda bo'lsa, dashboard ga yo'naltirish
if ($user) {
    header('Location: ?page=user_dashboard');
    exit;
}

// Login jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passport = strtoupper(trim($_POST['passport']));
    $password = $_POST['password'];

    // Oddiy tekshiruv
    if (empty($passport) || empty($password)) {
        $error = 'Barcha maydonlarni to\'ldiring';
    } else {
        // Ma'lumotlar bazasidan foydalanuvchini topish
        $sql = "SELECT * FROM users WHERE passport_series = ? AND is_verified = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$passport]);
        $user_data = $stmt->fetch();

        if ($user_data && password_verify($password, $user_data['password_hash'])) {
            // Muvaffaqiyatli kirish
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_name'] = $user_data['first_name'];

            header('Location: ?page=user_dashboard');
            exit;
        } else {
            $error = 'Pasport yoki parol noto\'g\'ri';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header text-center">
                    <h3>Tizimga kirish</h3>
                </div>
                <div class="card-body">

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Pasport seriyasi va raqami</label>
                            <input type="text"
                                   name="passport"
                                   class="form-control"
                                   placeholder="AA1234567"
                                   value="<?php echo $_POST['passport'] ?? ''; ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parol</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Kirish</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Hisobingiz yo'qmi? <a href="?page=register">Ro'yxatdan o'ting</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>