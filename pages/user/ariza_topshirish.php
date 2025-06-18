<?php
/**
 * Ariza topshirish sahifasi
 */

if (!$user) {
    redirect('login');
}

$application_type = sanitize($_GET['type'] ?? 'nikoh');
if (!in_array($application_type, ['nikoh', 'ajralish'])) {
    $application_type = 'nikoh';
}

// Ariza topshirish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    try {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Xavfsizlik xatosi. Sahifani yangilang.');
        }

        $data = [
            'application_type' => sanitize($_POST['application_type']),
            'partner_passport' => sanitize($_POST['partner_passport'] ?? ''),
            'partner_name' => sanitize($_POST['partner_name'] ?? ''),
            'partner_birth_date' => sanitize($_POST['partner_birth_date'] ?? ''),
            'partner_phone' => sanitize($_POST['partner_phone'] ?? ''),
            'preferred_date' => sanitize($_POST['preferred_date'] ?? ''),
            'ceremony_type' => sanitize($_POST['ceremony_type'] ?? 'oddiy'),
            'divorce_reason' => sanitize($_POST['divorce_reason'] ?? ''),
            'marriage_certificate_number' => sanitize($_POST['marriage_certificate_number'] ?? '')
        ];

        // Validatsiya
        $errors = [];

        if ($data['application_type'] === 'nikoh') {
            if (empty($data['partner_passport']) || !validatePassport($data['partner_passport'])) {
                $errors[] = 'Sherik pasport ma\'lumotlari noto\'g\'ri';
            }
            if (empty($data['partner_name'])) {
                $errors[] = 'Sherik ismini kiriting';
            }
            if (empty($data['preferred_date'])) {
                $errors[] = 'Nikoh sanasini kiriting';
            }
        }

        if ($data['application_type'] === 'ajralish') {
            if (empty($data['marriage_certificate_number'])) {
                $errors[] = 'Nikoh guvohnomasi raqamini kiriting';
            }
            if (empty($data['divorce_reason'])) {
                $errors[] = 'Ajralish sababini kiriting';
            }
        }

        if (empty($errors)) {
            $payment_amount = calculatePaymentAmount($data['application_type']);

            $application_data = [
                'application_type' => $data['application_type'],
                'applicant_id' => $user['id'],
                'partner_passport' => $data['partner_passport'],
                'partner_name' => $data['partner_name'],
                'partner_birth_date' => $data['partner_birth_date'],
                'partner_phone' => $data['partner_phone'],
                'preferred_date' => $data['preferred_date'],
                'ceremony_type' => $data['ceremony_type'],
                'divorce_reason' => $data['divorce_reason'],
                'marriage_certificate_number' => $data['marriage_certificate_number'],
                'payment_required' => $payment_amount,
                'status' => 'yangi'
            ];

            $application_id = insertRecord('applications', $application_data);

            // Ariza raqamini yangilash
            $application_number = date('Y') . str_pad($application_id, 6, '0', STR_PAD_LEFT);
            updateRecord('applications',
                ['application_number' => $application_number],
                'id = ?',
                [$application_id]
            );

            $_SESSION['success_message'] = 'Arizangiz muvaffaqiyatli topshirildi!';
            redirect('user_dashboard');
        } else {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-container">
                <div class="form-header">
                    <h2>
                        <i class="fas fa-<?php echo $application_type === 'nikoh' ? 'heart' : 'handshake-slash'; ?> me-2"></i>
                        <?php echo $application_type === 'nikoh' ? 'Nikoh uchun ariza' : 'Ajralish uchun ariza'; ?>
                    </h2>
                </div>

                <!-- Ariza turi tanlash -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="?page=ariza_topshirish&type=nikoh"
                           class="btn <?php echo $application_type === 'nikoh' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100 py-3">
                            <i class="fas fa-heart fa-2x d-block mb-2"></i>
                            <strong>Nikoh uchun</strong>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="?page=ariza_topshirish&type=ajralish"
                           class="btn <?php echo $application_type === 'ajralish' ? 'btn-warning' : 'btn-outline-warning'; ?> w-100 py-3">
                            <i class="fas fa-handshake-slash fa-2x d-block mb-2"></i>
                            <strong>Ajralish uchun</strong>
                        </a>
                    </div>
                </div>

                <form method="POST" data-validate="true">
                    <?php echo csrfInput(); ?>
                    <input type="hidden" name="application_type" value="<?php echo $application_type; ?>">

                    <?php if ($application_type === 'nikoh'): ?>
                        <!-- Nikoh uchun -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-user-friends me-2"></i>Sherik ma'lumotlari</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Sherik pasporti *</label>
                                            <input type="text" class="form-control" name="partner_passport"
                                                   placeholder="AA1234567" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Telefon raqami *</label>
                                            <input type="tel" class="form-control" name="partner_phone"
                                                   placeholder="+998901234567" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-3">
                                            <label class="form-label">To'liq ismi *</label>
                                            <input type="text" class="form-control" name="partner_name"
                                                   placeholder="Familiya Ism Sharif" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Tug'ilgan sana *</label>
                                            <input type="date" class="form-control" name="partner_birth_date" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-calendar me-2"></i>Nikoh tafsilotlari</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Istagan nikoh sanasi *</label>
                                            <input type="date" class="form-control" name="preferred_date"
                                                   min="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                                            <div class="form-text">Kamida 30 kun oldin</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Marosim turi *</label>
                                            <select class="form-control" name="ceremony_type" required>
                                                <option value="oddiy">Oddiy marosim</option>
                                                <option value="tantanali">Tantanali marosim</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Ajralish uchun -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-certificate me-2"></i>Nikoh ma'lumotlari</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label class="form-label">Nikoh guvohnomasi raqami *</label>
                                    <input type="text" class="form-control" name="marriage_certificate_number"
                                           placeholder="N1234567" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Ajralish sababi *</label>
                                    <textarea class="form-control" name="divorce_reason" rows="4"
                                              placeholder="Ajralish sababini batafsil yozing" required></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- To'lov ma'lumotlari -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-credit-card me-2"></i>To'lov ma'lumotlari</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Davlat boji:</strong> <?php echo formatMoney($application_type === 'nikoh' ? NIKOH_DAVLAT_BOJI : AJRALISH_DAVLAT_BOJI); ?></p>
                                    <p><strong>Gerb yig'imi:</strong> <?php echo formatMoney((BHM_MIQDORI * GERB_YIGIMI_FOIZ) / 100); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Jami to'lov:</strong>
                                        <span class="h5 text-primary">
                                            <?php echo formatMoney(calculatePaymentAmount($application_type)); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="submit_application" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Ariza topshirish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>