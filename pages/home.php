<?php
/**
 * pages/home.php - Bosh sahifa
 * Nikoh Portali
 */

// Statistikani olish
$stats = [
    'total_applications' => 0,
    'completed_marriages' => 0,
    'completed_divorces' => 0,
    'active_users' => 0
];

try {
    $stats['total_applications'] = fetchOne("SELECT COUNT(*) as count FROM applications")['count'] ?? 0;
    $stats['completed_marriages'] = fetchOne("SELECT COUNT(*) as count FROM applications WHERE application_type = 'nikoh' AND status = 'tugallandi'")['count'] ?? 0;
    $stats['completed_divorces'] = fetchOne("SELECT COUNT(*) as count FROM applications WHERE application_type = 'ajralish' AND status = 'tugallandi'")['count'] ?? 0;
    $stats['active_users'] = fetchOne("SELECT COUNT(*) as count FROM users WHERE is_verified = 1")['count'] ?? 0;
} catch (Exception $e) {
    // Statistikani olib bo'lmasa, default qiymatlar qoladi
}

// So'nggi yangiliklarni olish (demo uchun)
$news = [
    [
        'title' => 'Elektron guvohnomalar tizimi ishga tushirildi',
        'date' => '2023-10-15',
        'summary' => 'Endi nikoh va ajralish guvohnomalarini elektron ko\'rinishda olish mumkin'
    ],
    [
        'title' => 'Yangi to\'lov usullari qo\'shildi',
        'date' => '2023-10-10',
        'summary' => 'Click, Payme va UzCard orqali to\'lov qilish imkoniyati'
    ],
    [
        'title' => 'Tizim texnik ishlardan chiqdi',
        'date' => '2023-10-05',
        'summary' => 'Barcha xizmatlar normal rejimda ishlaydi'
    ]
];
?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">Nikoh Portali</h1>
                    <p class="hero-subtitle">
                        O'zbekiston Respublikasi fuqarolik holati dalolatnomalarini yozish organlari -
                        nikoh va ajralish uchun onlayn arizalar tizimi
                    </p>
                    <div class="hero-buttons">
                        <?php if ($user): ?>
                            <a href="?page=ariza_topshirish" class="btn btn-light btn-lg">
                                <i class="fas fa-file-alt"></i> Ariza topshirish
                            </a>
                            <a href="?page=user_dashboard" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Shaxsiy kabinet
                            </a>
                        <?php else: ?>
                            <a href="?page=register" class="btn btn-light btn-lg">
                                <i class="fas fa-user-plus"></i> Ro'yxatdan o'tish
                            </a>
                            <a href="?page=login" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Tizimga kirish
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="assets/images/home_img.png" alt="Nikoh Portali" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Statistika -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="stats-card border-primary">
                        <div class="stats-number"><?php echo number_format($stats['total_applications']); ?></div>
                        <div class="stats-label">Jami arizalar</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card border-success">
                        <div class="stats-number text-success"><?php echo number_format($stats['completed_marriages']); ?></div>
                        <div class="stats-label">Nikoh tuzildi</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card border-warning">
                        <div class="stats-number text-warning"><?php echo number_format($stats['completed_divorces']); ?></div>
                        <div class="stats-label">Ajralish qayd etildi</div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card border-info">
                        <div class="stats-number text-info"><?php echo number_format($stats['active_users']); ?></div>
                        <div class="stats-label">Faol foydalanuvchilar</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Xizmatlar -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="mb-3">Bizning xizmatlarimiz</h2>
                    <p class="text-muted">Qonuniy va xavfsiz onlayn xizmatlar</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4 class="feature-title">Nikoh tuzish</h4>
                        <p class="feature-description">
                            Nikoh tuzish uchun onlayn ariza topshiring. Barcha jarayonlar qonuniy
                            tartibda amalga oshiriladi.
                        </p>
                        <?php if ($user): ?>
                            <a href="?page=ariza_topshirish&type=nikoh" class="btn btn-primary">
                                Ariza topshirish
                            </a>
                        <?php else: ?>
                            <a href="?page=register" class="btn btn-outline-primary">
                                Ro'yxatdan o'tish
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-handshake-slash"></i>
                        </div>
                        <h4 class="feature-title">Nikohdan ajralish</h4>
                        <p class="feature-description">
                            Nikohdan ajralish jarayonini onlayn ravishda boshlang.
                            Barcha qonuniy talablar hisobga olinadi.
                        </p>
                        <?php if ($user): ?>
                            <a href="?page=ariza_topshirish&type=ajralish" class="btn btn-primary">
                                Ariza topshirish
                            </a>
                        <?php else: ?>
                            <a href="?page=register" class="btn btn-outline-primary">
                                Ro'yxatdan o'tish
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h4 class="feature-title">Hujjat olish</h4>
                        <p class="feature-description">
                            Tayyor guvohnomalarni elektron ko'rinishda yuklab oling.
                            QR kod bilan tasdiqlanadi.
                        </p>
                        <?php if ($user): ?>
                            <a href="?page=hujjat_olish" class="btn btn-primary">
                                Hujjat olish
                            </a>
                        <?php else: ?>
                            <a href="?page=register" class="btn btn-outline-primary">
                                Ro'yxatdan o'tish
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="feature-title">Ariza holati</h4>
                        <p class="feature-description">
                            Topshirgan arizalaringizning holatini real vaqtda kuzatib boring.
                            SMS orqali xabarnomalar oling.
                        </p>
                        <?php if ($user): ?>
                            <a href="?page=ariza_holati" class="btn btn-primary">
                                Holatni tekshirish
                            </a>
                        <?php else: ?>
                            <a href="?page=register" class="btn btn-outline-primary">
                                Ro'yxatdan o'tish
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4 class="feature-title">Onlayn to'lov</h4>
                        <p class="feature-description">
                            Davlat bojini Click, Payme, UzCard orqali to'lang.
                            Xavfsiz va tez to'lov tizimi.
                        </p>
                        <a href="?page=about#payments" class="btn btn-outline-primary">
                            Batafsil
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="feature-title">Xavfsizlik</h4>
                        <p class="feature-description">
                            Shaxsiy ma'lumotlaringiz himoyalangan. SSL sertifikat
                            va zamonaviy shifrlash texnologiyalari.
                        </p>
                        <a href="?page=about#security" class="btn btn-outline-primary">
                            Batafsil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Jarayon qadamlari -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="mb-3">Qanday ishlaydi?</h2>
                    <p class="text-muted">Oddiy 4 qadamda arizangizni topshiring</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <span class="badge bg-primary" style="font-size: 1.5rem;">1</span>
                        </div>
                        <h5>Ro'yxatdan o'ting</h5>
                        <p class="text-muted">Pasport ma'lumotlaringiz bilan tizimga kirish</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <span class="badge bg-primary" style="font-size: 1.5rem;">2</span>
                        </div>
                        <h5>Ariza to'ldiring</h5>
                        <p class="text-muted">Zarur ma'lumotlar va hujjatlarni yuklang</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <span class="badge bg-primary" style="font-size: 1.5rem;">3</span>
                        </div>
                        <h5>To'lov qiling</h5>
                        <p class="text-muted">Davlat bojini onlayn to'lang</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <span class="badge bg-primary" style="font-size: 1.5rem;">4</span>
                        </div>
                        <h5>Guvohnoma oling</h5>
                        <p class="text-muted">Tayyor hujjatni yuklab oling</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Yangiliklarr va e'lonlar -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="mb-4">So'nggi yangiliklarr</h3>
                    <?php foreach ($news as $item): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($item['summary']); ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo formatDate($item['date']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-center">
                        <a href="?page=news" class="btn btn-outline-primary">
                            <i class="fas fa-newspaper"></i> Barcha yangiliklar
                        </a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <h3 class="mb-4">Muhim ma'lumotlar</h3>

                    <!-- Ish vaqti -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-clock"></i> Ish vaqti</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Dushanba-Juma:</strong> 09:00 - 18:00</p>
                            <p class="mb-1"><strong>Tushlik tanaffusi:</strong> 12:00 - 13:00</p>
                            <p class="mb-0"><strong>Dam olish:</strong> Shanba, Yakshanba</p>
                        </div>
                    </div>

                    <!-- To'lov miqdorlari -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-money-bill"></i> To'lov miqdorlari</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Nikoh:</strong> <?php echo formatMoney(NIKOH_DAVLAT_BOJI); ?></p>
                            <p class="mb-1"><strong>Ajralish:</strong> <?php echo formatMoney(AJRALISH_DAVLAT_BOJI); ?></p>
                            <p class="mb-0"><strong>Gerb yig'imi:</strong> BHM ning 15%</p>
                        </div>
                    </div>

                    <!-- Bog'lanish -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-phone"></i> Bog'lanish</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Telefon:</strong> <?php echo SITE_PHONE; ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo SITE_EMAIL; ?></p>
                            <p class="mb-0"><strong>Qo'llab-quvvatlash:</strong> <?php echo SUPPORT_EMAIL; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>Tez-tez beriladigan savollar</h2>
                    <p class="text-muted">Eng ko'p so'raladigan savollar va javoblar</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    Onlayn ariza topshirish qanday amalga oshiriladi?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Avval tizimga ro'yxatdan o'ting, keyin "Ariza topshirish" bo'limiga o'tib,
                                    zarur ma'lumotlarni to'ldiring va hujjatlarni yuklang. To'lovni amalga oshirgandan
                                    so'ng arizangiz ko'rib chiqiladi.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    Qanday hujjatlar kerak?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Nikoh uchun: pasport nusxalari, tibbiy ma'lumotnoma, tug'ilganlik haqidagi guvohnoma.
                                    Ajralish uchun: pasportlar, nikoh guvohnomasi, bolalar haqida ma'lumotlar (agar bor bo'lsa).
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    To'lov qanday amalga oshiriladi?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Click, Payme, UzCard va Humo kartalar orqali onlayn to'lov qilishingiz mumkin.
                                    Naqd to'lov ham FHDY organlarida amalga oshiriladi.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                    Guvohnoma qachon tayyor bo'ladi?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Barcha hujjatlar to'g'ri topshirilgan va to'lov amalga oshirilgandan so'ng,
                                    guvohnoma 3-5 ish kuni ichida tayyor bo'ladi. SMS orqali xabar beriladi.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="?page=contact" class="btn btn-primary">
                    <i class="fas fa-question-circle"></i> Boshqa savollar
                </a>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
<?php if (!$user): ?>
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="mb-3">Bugun boshlang!</h2>
                    <p class="lead mb-4">
                        Nikoh yoki ajralish jarayonini onlayn ravishda boshlash uchun
                        ro'yxatdan o'ting va vaqtingizni tejang.
                    </p>
                    <a href="?page=register" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-user-plus"></i> Ro'yxatdan o'tish
                    </a>
                    <a href="?page=about" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle"></i> Batafsil ma'lumot
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>