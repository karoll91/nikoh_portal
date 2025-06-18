<?php
/**
 * pages/about.php - Tizim haqida sahifa
 * Nikoh Portali
 */
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">Tizim haqida</h1>
            <p class="lead text-muted">
                O'zbekiston Respublikasi fuqarolik holati dalolatnomalarini yozish organlari -
                zamonaviy raqamli xizmatlar
            </p>
        </div>
    </div>

    <!-- Asosiy ma'lumot -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <div class="feature-card">
                <h2 class="mb-4">Nikoh Portali nima?</h2>
                <p class="mb-3">
                    Nikoh Portali - O'zbekiston Respublikasi fuqarolik holati dalolatnomalarini yozish
                    organlari (FHDY) tomonidan ishlab chiqilgan zamonaviy elektron tizimdir. Bu portal
                    orqali fuqarolar nikoh tuzish va nikohdan ajralish jarayonlarini onlayn ravishda
                    boshlashlari mumkin.
                </p>
                <p class="mb-3">
                    Tizim O'zbekiston Respublikasi Vazirlar Mahkamasining 2023-yil 20-oktabrdagi
                    550-son qarori asosida yaratilgan bo'lib, fuqarolik holati dalolatnomalarini
                    qayd etish sohasidagi normativ-huquqiy hujjatlarni tizimlashtirish maqsadida
                    ishlab chiqilgan.
                </p>
                <p class="mb-0">
                    Portal barcha zamonaviy xavfsizlik standartlariga javob beradi va fuqarolarning
                    shaxsiy ma'lumotlarini ishonchli himoya qiladi.
                </p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="feature-card bg-light">
                <h5 class="mb-3">Asosiy raqamlar</h5>
                <div class="row">
                    <div class="col-6 text-center mb-3">
                        <div class="h3 text-primary mb-0"><?php echo date('Y') - 2023; ?>+</div>
                        <small class="text-muted">Yil tajriba</small>
                    </div>
                    <div class="col-6 text-center mb-3">
                        <div class="h3 text-success mb-0">24/7</div>
                        <small class="text-muted">Xizmat vaqti</small>
                    </div>
                    <div class="col-6 text-center">
                        <div class="h3 text-info mb-0">100%</div>
                        <small class="text-muted">Xavfsizlik</small>
                    </div>
                    <div class="col-6 text-center">
                        <div class="h3 text-warning mb-0">3-5</div>
                        <small class="text-muted">Kun ichida</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Xizmatlar -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Bizning xizmatlarimiz</h2>
        </div>
        <div class="col-md-6 mb-4">
            <div class="feature-card h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="feature-icon me-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="mb-0">Nikoh tuzish</h4>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Onlayn ariza topshirish</li>
                    <li><i class="fas fa-check text-success me-2"></i> Hujjatlarni elektron yuklash</li>
                    <li><i class="fas fa-check text-success me-2"></i> Tibbiy ko'rik yo'llanmasi</li>
                    <li><i class="fas fa-check text-success me-2"></i> Tantanali marosim tashkil etish</li>
                    <li><i class="fas fa-check text-success me-2"></i> Elektron guvohnoma</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="feature-card h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="feature-icon me-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-handshake-slash"></i>
                    </div>
                    <h4 class="mb-0">Nikohdan ajralish</h4>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success me-2"></i> Onlayn ariza berish</li>
                    <li><i class="fas fa-check text-success me-2"></i> Mulkiy nizolarsiz ajralish</li>
                    <li><i class="fas fa-check text-success me-2"></i> Bolalar manfaatlarini himoya qilish</li>
                    <li><i class="fas fa-check text-success me-2"></i> Huquqiy maslahat</li>
                    <li><i class="fas fa-check text-success me-2"></i> Ajralish guvohnomasi</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Qonuniy asos -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="feature-card">
                <h2 class="mb-4">Qonuniy asos</h2>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">Asosiy qonunlar</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-gavel text-primary me-2"></i>
                                O'zbekiston Respublikasi Oila kodeksi
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-gavel text-primary me-2"></i>
                                Vazirlar Mahkamasining 550-son qarori (2023)
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-gavel text-primary me-2"></i>
                                Fuqarolik holati to'g'risidagi qonun
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-gavel text-primary me-2"></i>
                                Elektron hujjat aylanishi to'g'risidagi qonun
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-success mb-3">Asosiy qoidalar</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Nikoh yoshiga yetgan fuqarolar
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                O'zaro rozi bo'lish majburiy
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Tibbiy ko'rikdan o'tish
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Davlat bojini to'lash
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- To'lov tizimi -->
    <div class="row mb-5" id="payments">
        <div class="col-12">
            <div class="feature-card">
                <h2 class="mb-4">To'lov tizimi</h2>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">To'lov miqdorlari</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                <tr>
                                    <td><strong>Nikoh uchun davlat boji:</strong></td>
                                    <td class="text-end"><?php echo formatMoney(NIKOH_DAVLAT_BOJI); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Ajralish uchun davlat boji:</strong></td>
                                    <td class="text-end"><?php echo formatMoney(AJRALISH_DAVLAT_BOJI); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Gerb yig'imi:</strong></td>
                                    <td class="text-end">BHM ning <?php echo GERB_YIGIMI_FOIZ; ?>%</td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>BHM miqdori:</strong></td>
                                    <td class="text-end"><?php echo formatMoney(BHM_MIQDORI); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3">To'lov usullari</h5>
                        <div class="row">
                            <div class="col-6 text-center mb-3">
                                <img src="assets/images/payment/click.png" alt="Click" class="img-fluid mb-2" style="height: 40px;">
                                <div class="small">Click</div>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <img src="assets/images/payment/payme.png" alt="Payme" class="img-fluid mb-2" style="height: 40px;">
                                <div class="small">Payme</div>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <img src="assets/images/payment/uzcard.png" alt="UzCard" class="img-fluid mb-2" style="height: 40px;">
                                <div class="small">UzCard</div>
                            </div>
                            <div class="col-6 text-center mb-3">
                                <img src="assets/images/payment/humo.png" alt="Humo" class="img-fluid mb-2" style="height: 40px;">
                                <div class="small">Humo</div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Barcha to'lovlar xavfsiz va shifrlangan
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Xavfsizlik -->
    <div class="row mb-5" id="security">
        <div class="col-12">
            <div class="feature-card">
                <h2 class="mb-4">Xavfsizlik va himoya</h2>
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5>SSL shifrlash</h5>
                        <p class="text-muted">
                            Barcha ma'lumotlar 256-bit SSL sertifikat bilan himoyalangan
                        </p>
                    </div>
                    <div class="col-md-4 text-center mb-4">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5>Shaxsiy ma'lumotlar</h5>
                        <p class="text-muted">
                            Fuqarolarning shaxsiy ma'lumotlari maxfiy saqlanadi
                        </p>
                    </div>
                    <div class="col-md-4 text-center mb-4">
                        <div class="feature-icon mx-auto mb-3">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5>Ikki bosqichli tekshiruv</h5>
                        <p class="text-muted">
                            SMS kod orqali qo'shimcha xavfsizlik
                        </p>
                    </div>
                </div>

                <div class="alert alert-success">
                    <h6 class="alert-heading">
                        <i class="fas fa-certificate me-2"></i>Sertifikatlar
                    </h6>
                    <p class="mb-0">
                        Tizim ISO 27001 xavfsizlik standartiga javob beradi va davlat
                        ma'lumotlar bazalari bilan xavfsiz integratsiya qilingan.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Jarayon -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="feature-card">
                <h2 class="mb-4">Jarayon qanday kechadi?</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">1-qadam</div>
                        <div class="timeline-title">Ro'yxatdan o'tish</div>
                        <div class="timeline-description">
                            Pasport ma'lumotlaringiz bilan tizimga kirish va SMS orqali tasdiqlash
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">2-qadam</div>
                        <div class="timeline-title">Ariza to'ldirish</div>
                        <div class="timeline-description">
                            Zarur ma'lumotlarni kiritish va hujjatlarni elektron ko'rinishda yuklash
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">3-qadam</div>
                        <div class="timeline-title">To'lov qilish</div>
                        <div class="timeline-description">
                            Davlat bojini onlayn to'lov tizimlari orqali to'lash
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">4-qadam</div>
                        <div class="timeline-title">Ko'rib chiqish</div>
                        <div class="timeline-description">
                            FHDY xodimlari tomonidan arizani ko'rib chiqish va tasdiqlash
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">5-qadam</div>
                        <div class="timeline-title">Marosim</div>
                        <div class="timeline-description">
                            Belgilangan sana va vaqtda nikoh marosimida qatnashish (nikoh uchun)
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-date">6-qadam</div>
                        <div class="timeline-title">Guvohnoma olish</div>
                        <div class="timeline-description">
                            Tayyor elektron guvohnomani tizimdan yuklab olish
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Texnik talablar -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="feature-card h-100">
                <h3 class="mb-3">Texnik talablar</h3>
                <h6 class="text-primary">Brauzerlar:</h6>
                <ul class="list-unstyled mb-3">
                    <li><i class="fab fa-chrome text-warning me-2"></i> Chrome 90+</li>
                    <li><i class="fab fa-firefox text-orange me-2"></i> Firefox 88+</li>
                    <li><i class="fab fa-safari text-primary me-2"></i> Safari 14+</li>
                    <li><i class="fab fa-edge text-info me-2"></i> Edge 90+</li>
                </ul>

                <h6 class="text-primary">Fayl formatlari:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-file-image text-success me-2"></i> JPG, PNG (rasmlar)</li>
                    <li><i class="fas fa-file-pdf text-danger me-2"></i> PDF (hujjatlar)</li>
                    <li><i class="fas fa-weight-hanging text-warning me-2"></i> Maksimal hajm: 5MB</li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="feature-card h-100">
                <h3 class="mb-3">Qo'llab-quvvatlash</h3>
                <div class="mb-3">
                    <h6 class="text-primary">Ish vaqti:</h6>
                    <p class="mb-1">Dushanba - Juma: 09:00 - 18:00</p>
                    <p class="mb-0">Tushlik: 12:00 - 13:00</p>
                </div>

                <div class="mb-3">
                    <h6 class="text-primary">Bog'lanish:</h6>
                    <p class="mb-1">
                        <i class="fas fa-phone text-success me-2"></i>
                        <?php echo SITE_PHONE; ?>
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <?php echo SUPPORT_EMAIL; ?>
                    </p>
                    <p class="mb-0">
                        <i class="fab fa-telegram text-info me-2"></i>
                        @nikoh_portal_bot
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Foydali havolalar -->
    <div class="row">
        <div class="col-12">
            <div class="feature-card">
                <h3 class="mb-4">Foydali havolalar</h3>
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-primary">Qonunchilik</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" target="_blank" class="text-decoration-none">Oila kodeksi</a></li>
                            <li><a href="#" target="_blank" class="text-decoration-none">FHDY qoidalari</a></li>
                            <li><a href="#" target="_blank" class="text-decoration-none">Davlat boji to'g'risida</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-primary">Davlat organlari</h6>
                        <ul class="list-unstyled">
                            <li><a href="https://adliya.uz" target="_blank" class="text-decoration-none">Adliya vazirligi</a></li>
                            <li><a href="https://my.gov.uz" target="_blank" class="text-decoration-none">MyGov portal</a></li>
                            <li><a href="https://lex.uz" target="_blank" class="text-decoration-none">Qonunchilik ma'lumotnomasi</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-primary">Tibbiy xizmatlar</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" target="_blank" class="text-decoration-none">Tibbiy ko'rik</a></li>
                            <li><a href="#" target="_blank" class="text-decoration-none">Sog'liqni saqlash</a></li>
                            <li><a href="#" target="_blank" class="text-decoration-none">Oilaviy shifokorlar</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-primary">Qo'shimcha</h6>
                        <ul class="list-unstyled">
                            <li><a href="?page=contact" class="text-decoration-none">Bog'lanish</a></li>
                            <li><a href="?page=faq" class="text-decoration-none">FAQ</a></li>
                            <li><a href="?page=privacy" class="text-decoration-none">Maxfiylik siyosati</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>