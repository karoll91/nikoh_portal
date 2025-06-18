<?php
/**
 * Oddiy ariza topshirish
 */

if (!$user) {
    header('Location: ?page=login');
    exit;
}

$application_type = $_GET['type'] ?? 'nikoh'; // nikoh yoki ajralish

// Ariza topshirish
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if ($application_type == 'nikoh') {
        $partner_name = trim($_POST['partner_name']);
        $partner_passport = strtoupper(trim($_POST['partner_passport']));
        $preferred_date = $_POST['preferred_date'];

        if (empty($partner_name)) $errors[] = 'Sherik ismini kiriting';
        if (empty($partner_passport)) $errors[] = 'Sherik pasportini kiriting';
        if (empty($preferred_date)) $errors[] = 'Nikoh sanasini tanlang';

    } else {
        $marriage_date = $_POST['marriage_date'];
        $divorce_reason = trim($_POST['divorce_reason']);

        if (empty($marriage_date)) $errors[] = 'Nikoh sanasini kiriting';
        if (empty($divorce_reason)) $errors[] = 'Ajralish sababini yozing';
    }

    // Xatolik bo'lmasa saqlash
    if (empty($errors)) {
        // To'lov miqdorini hisoblash
        $payment_amount = ($application_type == 'nikoh') ? 51000 : 85000;

        $sql = "INSERT INTO applications (application_type, applicant_id, partner_name, partner_passport, 
                preferred_date, marriage_date, divorce_reason, payment_required, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'yangi', NOW())";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $application_type,
            $user['id'],
            $partner_name ?? null,
            $partner_passport ?? null,
            $preferred_date ?? null,
            $marriage_date ?? null,
            $divorce_reason ?? null,
            $payment_amount
        ]);

        if ($result) {
            $application_id = $pdo->lastInsertId();
            $application_number = date('Y') . str_pad($application_id, 6, '0', STR_PAD_LEFT);

            // Ariza raqamini yangilash
            $sql = "UPDATE applications SET application_number = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$application_number, $application_id]);

            $success = "Ariza muvaffaqiyatli topshirildi! Ariza raqami: #$application_number";
        } else {
            $errors[] = 'Xatolik yuz berdi';
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <!-- Ariza turi tanlash -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Ariza turi</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="?page=ariza_topshirish&type=nikoh"
                               class="btn <?php echo $application_type == 'nikoh' ? 'btn-success' : 'btn-outline-success'; ?> w-100 mb-2">
                                Nikoh tuzish
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="?page=ariza_topshirish&type=ajralish"
                               class="btn <?php echo $application_type == 'ajralish' ? 'btn-warning' : 'btn-outline-warning'; ?> w-100 mb-2">
                                Nikohdan ajralish
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ariza formasi -->
            <div class="card">
                <div class="card-header">
                    <h4><?php echo $application_type == 'nikoh' ? 'Nikoh' : 'Ajralish'; ?> arizasi</h4>
                </div>
                <div class="card-body">

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <div class="text-center">
                            <a href="?page=ariza_holati" class="btn btn-primary">Ariza holatini ko'rish</a>
                            <a href="?page=user_dashboard" class="btn btn-secondary">Dashboard ga qaytish</a>
                        </div>
                    <?php else: ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo $error; ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Ariza beruvchi ma'lumotlari -->
                        <div class="mb-4">
                            <h5>Ariza beruvchi ma'lumotlari</h5>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Ismi:</strong> <?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
                                <p><strong>Pasport:</strong> <?php echo $user['passport_series']; ?></p>
                                <p><strong>Telefon:</strong> <?php echo $user['phone']; ?></p>
                            </div>
                        </div>

                        <form method="POST">

                            <?php if ($application_type == 'nikoh'): ?>
                                <!-- Nikoh uchun -->
                                <h5>Sherik ma'lumotlari</h5>

                                <div class="mb-3">
                                    <label class="form-label">Sherik to'liq ismi *</label>
                                    <input type="text"
                                           name="partner_name"
                                           class="form-control"
                                           placeholder="Familiya Ismi Otasining ismi"
                                           value="<?php echo $_POST['partner_name'] ?? ''; ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Sherik pasport ma'lumotlari *</label>
                                    <input type="text"
                                           name="partner_passport"
                                           class="form-control"
                                           placeholder="AA1234567"
                                           value="<?php echo $_POST['partner_passport'] ?? ''; ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Istagan nikoh sanasi *</label>
                                    <input type="date"
                                           name="preferred_date"
                                           class="form-control"
                                           min="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                                           value="<?php echo $_POST['preferred_date'] ?? ''; ?>"
                                           required>
                                    <small class="text-muted">Kamida 30 kun keyin</small>
                                </div>

                                <div class="alert alert-info">
                                    <strong>To'lov miqdori:</strong> 51,000 so'm
                                </div>

                            <?php else: ?>
                                <!-- Ajralish uchun -->
                                <h5>Ajralish ma'lumotlari</h5>

                                <div class="mb-3">
                                    <label class="form-label">Nikoh tuzilgan sana *</label>
                                    <input type="date"
                                           name="marriage_date"
                                           class="form-control"
                                           value="<?php echo $_POST['marriage_date'] ?? ''; ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ajralish sababi *</label>
                                    <textarea name="divorce_reason"
                                              class="form-control"
                                              rows="4"
                                              placeholder="Ajralish sababini batafsil yozing..."
                                              required><?php echo $_POST['divorce_reason'] ?? ''; ?></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <strong>To'lov miqdori:</strong> 85,000 so'm
                                </div>

                            <?php endif; ?>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree" required>
                                    <label class="form-check-label" for="agree">
                                        Barcha ma'lumotlar to'g'ri ekanligini tasdiqlayaman va shartlarga roziman
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Arizani topshirish
                            </button>
                        </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>