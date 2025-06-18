<?php
/**
 * pages/user/dashboard.php - Foydalanuvchi shaxsiy kabineti
 */

if (!$user) {
    redirect('login');
}

// Foydalanuvchi arizalarini olish
$sql = "SELECT * FROM applications WHERE applicant_id = ? ORDER BY created_at DESC";
$user_applications = fetchAll($sql, [$user['id']]);

// Statistika
$stats = [
    'total_applications' => count($user_applications),
    'pending_applications' => count(array_filter($user_applications, function($app) {
        return in_array($app['status'], ['yangi', 'korib_chiqilmoqda']);
    })),
    'completed_applications' => count(array_filter($user_applications, function($app) {
        return $app['status'] === 'tugallandi';
    }))
];
?>

<div class="container py-4">
    <!-- Xush kelibsiz -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-section" style="padding: 40px 0;">
                <div class="container">
                    <h1 class="hero-title" style="font-size: 2.5rem;">
                        Xush kelibsiz, <?php echo htmlspecialchars($user['first_name']); ?>!
                    </h1>
                    <p class="hero-subtitle">
                        Shaxsiy kabinetingizdan barcha xizmatlardan foydalaning
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistika -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stats-card border-primary">
                <div class="stats-number"><?php echo $stats['total_applications']; ?></div>
                <div class="stats-label">Jami arizalar</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card border-warning">
                <div class="stats-number text-warning"><?php echo $stats['pending_applications']; ?></div>
                <div class="stats-label">Kutilmoqda</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card border-success">
                <div class="stats-number text-success"><?php echo $stats['completed_applications']; ?></div>
                <div class="stats-label">Tugallangan</div>
            </div>
        </div>
    </div>

    <!-- Tezkor harakatlar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="feature-card">
                <h3 class="mb-3">Tezkor harakatlar</h3>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="?page=ariza_topshirish&type=nikoh" class="btn btn-primary w-100 py-3">
                            <i class="fas fa-heart fa-2x d-block mb-2"></i>
                            <strong>Nikoh uchun ariza</strong>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=ariza_topshirish&type=ajralish" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-handshake-slash fa-2x d-block mb-2"></i>
                            <strong>Ajralish uchun ariza</strong>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=ariza_holati" class="btn btn-info w-100 py-3">
                            <i class="fas fa-search fa-2x d-block mb-2"></i>
                            <strong>Ariza holati</strong>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="?page=hujjat_olish" class="btn btn-success w-100 py-3">
                            <i class="fas fa-download fa-2x d-block mb-2"></i>
                            <strong>Hujjat olish</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Arizalar ro'yxati -->
        <div class="col-lg-8">
            <div class="feature-card">
                <h3 class="mb-3">Sizning arizalaringiz</h3>

                <?php if (empty($user_applications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Hali arizalar yo'q</h5>
                        <p class="text-muted">Birinchi arizangizni topshiring</p>
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
                                <th>Sana</th>
                                <th>Holati</th>
                                <th>Harakatlar</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($user_applications as $app): ?>
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
                                                <i class="<?php echo $type_info['icon']; ?> me-1"></i>
                                                <?php echo $type_info['label']; ?>
                                            </span>
                                    </td>
                                    <td><?php echo formatDate($app['created_at']); ?></td>
                                    <td>
                                            <span class="status-badge <?php echo $status_info['class']; ?>">
                                                <i class="<?php echo $status_info['icon']; ?> me-1"></i>
                                                <?php echo $status_info['label']; ?>
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
            </div>
        </div>

        <!-- O'ng tomonda ma'lumotlar -->
        <div class="col-lg-4">
            <!-- Profil ma'lumotlari -->
            <div class="feature-card mb-4">
                <h5 class="mb-3">Profil ma'lumotlari</h5>
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <strong>Ismi:</strong><br>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <strong>Pasport:</strong><br>
                        <?php echo htmlspecialchars($user['passport_series']); ?>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <strong>Telefon:</strong><br>
                        <?php echo htmlspecialchars($user['phone']); ?>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <strong>Holati:</strong><br>
                        <span class="badge bg-<?php echo $user['is_verified'] ? 'success' : 'warning'; ?>">
                            <?php echo $user['is_verified'] ? 'Tasdiqlangan' : 'Tasdiqlanmagan'; ?>
                        </span>
                    </div>
                </div>
                <a href="?page=profile_edit" class="btn btn-outline-primary btn-sm w-100 mt-3">
                    <i class="fas fa-edit me-2"></i>Tahrirlash
                </a>
            </div>

            <!-- Yordam -->
            <div class="feature-card">
                <h5 class="mb-3">Yordam kerakmi?</h5>
                <div class="d-grid gap-2">
                    <a href="?page=contact" class="btn btn-outline-primary">
                        <i class="fas fa-phone me-2"></i>Bog'lanish
                    </a>
                    <a href="?page=about" class="btn btn-outline-info">
                        <i class="fas fa-question-circle me-2"></i>FAQ
                    </a>
                    <a href="tel:<?php echo str_replace([' ', '-'], '', SITE_PHONE); ?>" class="btn btn-outline-success">
                        <i class="fas fa-phone me-2"></i><?php echo SITE_PHONE; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>