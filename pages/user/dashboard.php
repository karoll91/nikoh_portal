<?php
/**
 * Oddiy dashboard sahifasi
 */

if (!$user) {
    header('Location: ?page=login');
    exit;
}

// Foydalanuvchi arizalarini olish
$sql = "SELECT * FROM applications WHERE applicant_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$applications = $stmt->fetchAll();

// Statistika
$total_applications = count($applications);
$completed_applications = 0;
$pending_applications = 0;

foreach ($applications as $app) {
    if ($app['status'] == 'tugallandi') {
        $completed_applications++;
    } elseif (in_array($app['status'], ['yangi', 'korib_chiqilmoqda'])) {
        $pending_applications++;
    }
}
?>

<div class="container mt-4">
    <!-- Xush kelibsiz -->
    <div class="row mb-4">
        <div class="col-12">
            <h2>Xush kelibsiz, <?php echo $user['first_name']; ?>!</h2>
            <p class="text-muted">Shaxsiy kabinetingizga xush kelibsiz</p>
        </div>
    </div>

    <!-- Statistika -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo $total_applications; ?></h3>
                    <p>Jami arizalar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo $pending_applications; ?></h3>
                    <p>Kutilmoqda</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3><?php echo $completed_applications; ?></h3>
                    <p>Tugallangan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <a href="?page=ariza_topshirish" class="btn btn-primary">Yangi ariza</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tezkor harakatlar -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Nikoh arizasi</h5>
                    <p class="text-muted">Nikoh tuzish uchun ariza topshiring</p>
                    <a href="?page=ariza_topshirish&type=nikoh" class="btn btn-success">Ariza topshirish</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Ajralish arizasi</h5>
                    <p class="text-muted">Nikohdan ajralish uchun ariza</p>
                    <a href="?page=ariza_topshirish&type=ajralish" class="btn btn-warning">Ariza topshirish</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>Ariza holati</h5>
                    <p class="text-muted">Arizalaringiz holatini ko'ring</p>
                    <a href="?page=ariza_holati" class="btn btn-info">Holat ko'rish</a>
                </div>
            </div>
        </div>
    </div>

    <!-- So'nggi arizalar -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>So'nggi arizalar</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($applications)): ?>
                        <p class="text-center text-muted">Hech qanday ariza yo'q</p>
                        <div class="text-center">
                            <a href="?page=ariza_topshirish" class="btn btn-primary">Birinchi arizani topshirish</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Raqam</th>
                                    <th>Turi</th>
                                    <th>Sana</th>
                                    <th>Holati</th>
                                    <th>Harakat</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach (array_slice($applications, 0, 5) as $app): ?>
                                    <tr>
                                        <td>#<?php echo $app['application_number']; ?></td>
                                        <td>
                                            <?php if ($app['application_type'] == 'nikoh'): ?>
                                                <span class="badge bg-success">Nikoh</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Ajralish</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d.m.Y', strtotime($app['created_at'])); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'yangi' => 'primary',
                                                'korib_chiqilmoqda' => 'warning',
                                                'tasdiqlandi' => 'info',
                                                'tugallandi' => 'success',
                                                'rad_etildi' => 'danger'
                                            ];
                                            $color = $status_colors[$app['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                        </td>
                                        <td>
                                            <a href="?page=ariza_holati&id=<?php echo $app['id']; ?>"
                                               class="btn btn-sm btn-outline-primary">Ko'rish</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (count($applications) > 5): ?>
                            <div class="text-center">
                                <a href="?page=ariza_holati" class="btn btn-outline-primary">Barchasini ko'rish</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>