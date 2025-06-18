<?php global $user;
/**
 * Oddiy ro'yxatdan o'tish sahifasi
 */

if ($user) {
    header('Location: ?page=user_dashboard');
    exit;
}

// Ro'yxatdan o'tish jarayoni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passport = strtoupper(trim($_POST['passport']));
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Oddiy tekshiruv
    if (empty($passport)) $errors[] = 'Pasport kiritilmagan';
    if (empty($first_name)) $errors[] = 'Ism kiritilmagan';
    if (empty($last_name)) $errors[] = 'Familiya kiritilmagan';
    if (empty($phone)) $errors[] = 'Telefon kiritilmagan';
    if (empty($password)) $errors[] = 'Parol kiritilmagan';
    if ($password !== $confirm_password) $errors[] = 'Parollar mos kelmaydi';

    // Pasport tekshiruvi
    if (!preg_match('/^[A-Z]{2}\d{7}$/', $passport)) {
        $errors[] = 'Pasport formati noto\'g\'ri (AA1234567)';
    }

    // Mavjudligini tekshirish
    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE passport_series = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$passport]);
        if ($stmt->fetch()) {
            $errors[] = 'Bu pasport bilan allaqachon ro\'yxatdan o\'tilgan';
        }
    }

    // Xatolik bo'lmasa saqlash
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (passport_series, first_name, last_name, phone, password_hash, is_verified) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$passport, $first_name, $last_name, $phone, $password_hash])) {
            $success = 'Muvaffaqiyatli ro\'yxatdan o\'tdingiz! Endi tizimga kirishingiz mumkin.';
        } else {
            $errors[] = 'Xatolik yuz berdi';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header text-center">
                    <h3>Ro'yxatdan o'tish</h3>
                </div>
                <div class="card-body">

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <div class="text-center">
                            <a href="?page=login" class="btn btn-primary">Tizimga kirish</a>
                        </div>
                    <?php else: ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo $error; ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pasport seriyasi va raqami *</label>
                                        <input type="text"
                                               name="passport"
                                               class="form-control"
                                               placeholder="AA1234567"
                                               value="<?php echo $_POST['passport'] ?? ''; ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Telefon raqami *</label>
                                        <input type="text"
                                               name="phone"
                                               class="form-control"
                                               placeholder="+998901234567"
                                               value="<?php echo $_POST['phone'] ?? ''; ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ismi *</label>
                                        <input type="text"
                                               name="first_name"
                                               class="form-control"
                                               value="<?php echo $_POST['first_name'] ?? ''; ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Familiyasi *</label>
                                        <input type="text"
                                               name="last_name"
                                               class="form-control"
                                               value="<?php echo $_POST['last_name'] ?? ''; ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Parol *</label>
                                        <input type="password"
                                               name="password"
                                               class="form-control"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Parolni tasdiqlang *</label>
                                        <input type="password"
                                               name="confirm_password"
                                               class="form-control"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Ro'yxatdan o'tish</button>
                        </form>

                        <div class="text-center mt-3">
                            <p>Allaqachon hisobingiz bormi? <a href="?page=login">Tizimga kiring</a></p>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>