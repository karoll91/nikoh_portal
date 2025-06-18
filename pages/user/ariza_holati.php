<?php
/**
 * Ariza holati tekshirish
 */

if (!$user) {
    redirect('login');
}

// Foydalanuvchi arizalarini olish
$sql = "SELECT * FROM applications WHERE applicant_id = ? ORDER BY created_at DESC";
$applications = fetchAll($sql, [$user['id']]);

// Bitta arizani ko'rish
$selected_application = null;
if (isset($_GET['id'])) {
    $app_id = (int)$_GET['id'];
    $sql = "SELECT * FROM applications WHERE id = ? AND applicant_id = ?";
    $selected_application = fetchOne($sql, [$app_id, $user['id']]);
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="feature-card">
                <h2 class="mb-4">
                    <i class="fas fa-search me-2"></i>Ariza holati
                </h2>

                <?php if ($selected_application): ?>
                    <!-- Bitta ariza tafsilotlari -->
                    <?php
                    $status_info = getApplicationStatus($selected_application['status']);
                    $type_info = getApplicationType($selected_application['application_type']);
                    ?>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Ariza #{<?php echo htmlspecialchars($selected_application['application_number']); ?>}</h4>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="?page=ariza_holati" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Orqaga
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Ariza ma'lumotlari -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Asosiy ma'lumotlar</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p><strong>Ariza turi:</strong>
                                                <span class="badge bg-<?php echo $type_info['color']; ?>">
                                                    <?php echo $type_info['label']; ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p><strong>Holati:</strong>
                                                <span class="status-badge <?php echo $status_info['class']; ?>">
                                                    <?php echo $status_info['label']; ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p><strong>Topshirilgan:</strong> <?php echo formatDateTime($selected_application['created_at']); ?></p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p><strong>To'lov:</strong> <?php echo formatMoney($selected_application['payment_required']); ?></p>
                                        </div>
                                    </div>

                                    <?php if ($selected_application['application_type'] === 'nikoh'): ?>
                                        <hr>
                                        <h6>Nikoh ma'lumotlari</h6>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p><strong>Sherik:</strong> <?php echo htmlspecialchars($selected_application['partner_name']); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Istagan sana:</strong> <?php echo formatDate($selected_application['preferred_date']); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p><strong>Marosim turi:</strong> <?php echo ucfirst($selected_application['ceremony_type']); ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Jarayon timeline -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Jarayon holati</h5>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-date">1-qadam</div>
                                            <div class="timeline-title">Ariza topshirildi</div>
                                            <div class="timeline-description">
                                                <?php echo formatDateTime($selected_application['created_at']); ?>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-date">2-qadam</div>
                                            <div class="timeline-title">To'lov kutilmoqda</div>
                                            <div class="timeline-description">
                                                <?php if ($selected_application['payment_status'] === 'tolandi'): ?>
                                                    <span class="text-success">✓ To'lov amalga oshirildi</span>
                                                <?php else: ?>
                                                    <span class="text-warning">To'lov kutilmoqda</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-date">3-qadam</div>
                                            <div class="timeline-title">Ko'rib chiqilmoqda</div>
                                            <div class="timeline-description">
                                                <?php if (in_array($selected_application['status'], ['korib_chiqilmoqda', 'tasdiqlandi', 'tugallandi'])): ?>
                                                    <span class="text-success">✓ Ko'rib chiqildi</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Kutilmoqda</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="timeline-item">
                                            <div class="timeline-date">4-qadam</div>
                                            <div class="timeline-title">Tugallandi</div>
                                            <div class="timeline-description">
                                                <?php if ($selected_application['status'] === 'tugallandi'): ?>
                                                    <span class="text-success">✓ Jarayon tugallandi</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Kutilmoqda</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Harakatlar -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Harakatlar</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <?php if ($selected_application['payment_status'] !== 'tolandi'): ?>
                                            <a href="?page=payment&id=<?php echo $selected_application['id']; ?>"
                                               class="btn btn-primary">
                                                <i class="fas fa-credit-card me-2"></i>To'lov qilish
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($selected_application['status'] === 'tugallandi'): ?>
                                            <a href="?page=hujjat_olish&id=<?php echo $selected_application['id']; ?>"
                                               class="btn btn-success">
                                                <i class="fas fa-download me-2"></i>Guvohnoma olish
                                            </a>
                                        <?php endif; ?>

                                        <a href="?page=contact" class="btn btn-outline-info">
                                            <i class="fas fa-question-circle me-2"></i>Yordam
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Barcha arizalar ro'yxati -->
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Arizalar topilmadi</h4>
                            <p class="text-muted">Siz hali ariza topshirmagansiz</p>
                            <a href="?page=ariza_topshirish" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Ariza topshirish
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Ariza raqami</th>
                                    <th>Turi</th>
                                    <th>Topshirilgan</th>
                                    <th>Holati</th>
                                    <th>To'lov</th>
                                    <th>Harakat</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <?php
                                    $status_info = getApplicationStatus($app['status']);
                                    $type_info = getApplicationType($app['application_type']);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($app['application_number']); ?></strong>
                                        </td>
                                        <td>
                                                <span class="badge bg-<?php echo $type_info['color']; ?>">
                                                    <?php echo $type_info['label']; ?>
                                                </span>
                                        </td>
                                        <td><?php echo formatDate($app['created_at']); ?></td>
                                        <td>
                                                <span class="status-badge <?php echo $status_info['class']; ?>">
                                                    <?php echo $status_info['label']; ?>
                                                </span>
                                        </td>
                                        <td>
                                                <span class="badge bg-<?php echo $app['payment_status'] === 'tolandi' ? 'success' : 'warning'; ?>">
                                                    <?php echo $app['payment_status'] === 'tolandi' ? 'To\'langan' : 'Kutilmoqda'; ?>
                                                </span>
                                        </td>
                                        <td>
                                            <a href="?page=ariza_holati&id=<?php echo $app['id']; ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Ko'rish
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>