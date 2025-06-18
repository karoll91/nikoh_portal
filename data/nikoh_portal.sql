-- Nikoh Portali Ma'lumotlar Bazasi
-- O'zbekiston Respublikasi qonunchiligiga muvofiq

CREATE DATABASE nikoh_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nikoh_portal;

-- 1. Foydalanuvchilar jadvali
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       passport_series VARCHAR(9) NOT NULL UNIQUE COMMENT 'Pasport seriyasi va raqami (AA1234567)',
                       first_name VARCHAR(50) NOT NULL COMMENT 'Ismi',
                       last_name VARCHAR(50) NOT NULL COMMENT 'Familiyasi',
                       middle_name VARCHAR(50) NOT NULL COMMENT 'Otasining ismi',
                       birth_date DATE NOT NULL COMMENT 'Tug\'ilgan sanasi',
    birth_place VARCHAR(200) NOT NULL COMMENT 'Tug\'ilgan joyi',
                       citizenship VARCHAR(50) DEFAULT 'O\'zbekiston' COMMENT 'Fuqaroligi',
    phone VARCHAR(13) NOT NULL COMMENT 'Telefon raqami (+998xxxxxxxxx)',
    email VARCHAR(100) UNIQUE COMMENT 'Email manzil',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Shifrlangan parol',
    gender ENUM('erkak', 'ayol') NOT NULL COMMENT 'Jinsi',
    marital_status ENUM('turmushga_chiqmagan', 'nikohda', 'ajrashgan', 'beva') DEFAULT 'turmushga_chiqmagan',
    address TEXT NOT NULL COMMENT 'Yashash manzili',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_verified BOOLEAN DEFAULT FALSE COMMENT 'Tasdiqlangan holati',
    verification_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. FHDY xodimlari jadvali
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL COMMENT 'To\'liq ismi',
                       position VARCHAR(100) NOT NULL COMMENT 'Lavozimi',
                       fhdy_organ VARCHAR(200) NOT NULL COMMENT 'FHDY organi nomi',
                       phone VARCHAR(13) NOT NULL,
                       email VARCHAR(100) NOT NULL,
                       role ENUM('operator', 'mudiri', 'admin') DEFAULT 'operator',
                       is_active BOOLEAN DEFAULT TRUE,
                       last_login TIMESTAMP NULL,
                       created_by INT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       FOREIGN KEY (created_by) REFERENCES admin_users(id)
);

-- 3. Arizalar jadvali
CREATE TABLE applications (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              application_number VARCHAR(20) NOT NULL UNIQUE COMMENT 'Ariza raqami',
                              application_type ENUM('nikoh', 'ajralish') NOT NULL COMMENT 'Ariza turi',

    -- Asosiy ariza beruvchi ma'lumotlari
                              applicant_id INT NOT NULL,

    -- Ikkinchi tomon (nikoh uchun majburiy, ajralish uchun ixtiyoriy)
                              partner_id INT NULL COMMENT 'Sherik ID (nikoh uchun majburiy)',
                              partner_passport VARCHAR(9) NULL COMMENT 'Sherik pasporti (tashqi foydalanuvchi uchun)',
                              partner_name VARCHAR(150) NULL COMMENT 'Sherik to\'liq ismi',
    partner_birth_date DATE NULL,
    partner_phone VARCHAR(13) NULL,

    -- Nikoh/ajralish ma'lumotlari
    marriage_date DATE NULL COMMENT 'Nikoh sanasi (ajralish uchun)',
                              marriage_certificate_number VARCHAR(50) NULL COMMENT 'Nikoh guvohnomasi raqami',
                              divorce_reason TEXT NULL COMMENT 'Ajralish sababi',

    -- Familiya tanlovi
                              desired_surname_male VARCHAR(50) NULL COMMENT 'Erkak uchun kerakli familiya',
                              desired_surname_female VARCHAR(50) NULL COMMENT 'Ayol uchun kerakli familiya',

    -- Ariza holati
                              status ENUM(
        'yangi',
        'korib_chiqilmoqda',
        'qoshimcha_hujjat_kerak',
        'tasdiqlandi',
        'rad_etildi',
        'tugallandi'
    ) DEFAULT 'yangi',

    -- Sanalar
                              preferred_date DATE NULL COMMENT 'Nikoh uchun istagan sana',
                              appointment_date DATETIME NULL COMMENT 'Belgilangan vaqt',

    -- Xodim ma'lumotlari
                              assigned_admin_id INT NULL COMMENT 'Mas\'ul xodim',
    reviewed_by INT NULL COMMENT 'Ko\'rib chiqgan xodim',
                              review_date TIMESTAMP NULL,
                              review_notes TEXT NULL COMMENT 'Ko\'rib chiqish izohlari',

    -- Xizmat ko'rsatish
    ceremony_type ENUM('oddiy', 'tantanali', 'uyga_chiqib') DEFAULT 'oddiy',
                              ceremony_location VARCHAR(200) NULL COMMENT 'Marosim o\'tkaziladigan joy',

    -- Tibbiy ko'rik
    medical_check_required BOOLEAN DEFAULT TRUE,
                              medical_check_completed BOOLEAN DEFAULT FALSE,
                              medical_check_date DATE NULL,

    -- To'lov
                              payment_required DECIMAL(10,2) DEFAULT 0.00 COMMENT 'To\'lov miqdori (so\'m)',
    payment_status ENUM('kutilmoqda', 'tolandi', 'qaytarildi') DEFAULT 'kutilmoqda',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (applicant_id) REFERENCES users(id),
    FOREIGN KEY (partner_id) REFERENCES users(id),
    FOREIGN KEY (assigned_admin_id) REFERENCES admin_users(id),
    FOREIGN KEY (reviewed_by) REFERENCES admin_users(id)
);

-- 4. Yuklangan hujjatlar jadvali
CREATE TABLE uploaded_documents (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    application_id INT NOT NULL,
                                    document_type ENUM(
        'pasport_nusxasi',
        'tug_guvohnoma',
        'tibbiy_spravka',
        'ajralish_guvohnomasi',
        'olim_guvohnomasi',
        'boshqa'
    ) NOT NULL,
                                    original_filename VARCHAR(255) NOT NULL,
                                    stored_filename VARCHAR(255) NOT NULL,
                                    file_path VARCHAR(500) NOT NULL,
                                    file_size INT NOT NULL,
                                    mime_type VARCHAR(100) NOT NULL,
                                    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    is_verified BOOLEAN DEFAULT FALSE,
                                    verified_by INT NULL,
                                    verification_notes TEXT NULL,

                                    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
                                    FOREIGN KEY (verified_by) REFERENCES admin_users(id)
);

-- 5. Tayyor hujjatlar jadvali
CREATE TABLE generated_documents (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     application_id INT NOT NULL,
                                     document_type ENUM('nikoh_guvohnomasi', 'ajralish_guvohnomasi') NOT NULL,
                                     certificate_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'Guvohnoma raqami',
                                     series VARCHAR(10) NOT NULL COMMENT 'Seriya',
                                     issue_date DATE NOT NULL COMMENT 'Berilgan sana',
                                     issued_by VARCHAR(200) NOT NULL COMMENT 'Bergan organ',
                                     file_path VARCHAR(500) NULL COMMENT 'PDF fayl yo\'li',
    qr_code VARCHAR(500) NULL COMMENT 'QR kod ma\'lumoti',
                                     is_downloaded BOOLEAN DEFAULT FALSE,
                                     download_count INT DEFAULT 0,
                                     download_date TIMESTAMP NULL,

                                     FOREIGN KEY (application_id) REFERENCES applications(id)
);

-- 6. To'lovlar jadvali
CREATE TABLE payments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          application_id INT NOT NULL,
                          amount DECIMAL(10,2) NOT NULL COMMENT 'To\'lov miqdori (so\'m)',
    payment_type ENUM('davlat_boji', 'gerb_yigimi', 'xizmat_haqi') NOT NULL,
    payment_method ENUM('click', 'payme', 'uzcard', 'humo', 'naqd') NOT NULL,
    transaction_id VARCHAR(100) NULL COMMENT 'Tranzaksiya ID',
    payment_status ENUM('kutilmoqda', 'jarayonda', 'muvaffaqiyatli', 'bekor_qilindi') DEFAULT 'kutilmoqda',
    payment_date TIMESTAMP NULL,
    receipt_number VARCHAR(50) NULL COMMENT 'Kvitansiya raqami',
    notes TEXT NULL,

    FOREIGN KEY (application_id) REFERENCES applications(id)
);

-- 7. Tizim loglar jadvali
CREATE TABLE system_logs (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             user_id INT NULL,
                             admin_id INT NULL,
                             action VARCHAR(100) NOT NULL COMMENT 'Amalga oshirilgan amal',
                             table_name VARCHAR(50) NULL,
                             record_id INT NULL,
                             old_values JSON NULL,
                             new_values JSON NULL,
                             ip_address VARCHAR(45) NULL,
                             user_agent TEXT NULL,
                             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                             FOREIGN KEY (user_id) REFERENCES users(id),
                             FOREIGN KEY (admin_id) REFERENCES admin_users(id)
);

-- 8. Sozlamalar jadvali
CREATE TABLE settings (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          setting_key VARCHAR(100) NOT NULL UNIQUE,
                          setting_value TEXT NOT NULL,
                          description TEXT NULL,
                          updated_by INT NULL,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                          FOREIGN KEY (updated_by) REFERENCES admin_users(id)
);

-- 9. SMS va Email xabarnomalar jadvali
CREATE TABLE notifications (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               user_id INT NULL,
                               notification_type ENUM('sms', 'email') NOT NULL,
                               recipient VARCHAR(100) NOT NULL COMMENT 'Telefon yoki email',
                               subject VARCHAR(200) NULL,
                               message TEXT NOT NULL,
                               status ENUM('kutilmoqda', 'yuborildi', 'yetkazildi', 'xatolik') DEFAULT 'kutilmoqda',
                               sent_date TIMESTAMP NULL,
                               error_message TEXT NULL,
                               created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                               FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Indekslar
CREATE INDEX idx_users_passport ON users(passport_series);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_date ON applications(created_at);
CREATE INDEX idx_applications_number ON applications(application_number);

-- Boshlang'ich ma'lumotlar
INSERT INTO settings (setting_key, setting_value, description) VALUES
    ('gerb_yigimi_foiz', '15', 'Gerb yig\'imi foizi (BHM dan)'),
('bhm_miqdori', '340000', 'Bazaviy hisoblash miqdori (so\'m)'),
     ('nikoh_davlat_boji', '51000', 'Nikoh uchun davlat boji (so\'m)'),
('ajralish_davlat_boji', '85000', 'Ajralish uchun davlat boji (so\'m)'),
     ('ish_kunlari', 'du,se,ch,pa,ju', 'Ish kunlari'),
     ('ish_vaqti_boshi', '09:00', 'Ish vaqti boshlanishi'),
     ('ish_vaqti_oxiri', '18:00', 'Ish vaqti tugashi'),
     ('max_file_size', '5242880', 'Maksimal fayl o\'lchami (5MB)'),
('allowed_file_types', 'jpg,jpeg,png,pdf', 'Ruxsat etilgan fayl turlari');

-- Demo admin yaratish
INSERT INTO admin_users (username, password_hash, full_name, position, fhdy_organ, phone, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tizim Administratori', 'Tizim administratori', 'Bosh FHDY organi', '+998901234567', 'admin@nikoh.uz', 'admin');

-- Trigger: Ariza raqami avtomatik yaratish
DELIMITER //
CREATE TRIGGER generate_application_number
BEFORE INSERT ON applications
FOR EACH ROW
BEGIN
    DECLARE next_number INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(application_number, 5) AS UNSIGNED)), 0) + 1
    INTO next_number
    FROM applications
    WHERE YEAR(created_at) = YEAR(NOW());

    SET NEW.application_number = CONCAT(YEAR(NOW()), LPAD(next_number, 6, '0'));
END//
DELIMITER ;