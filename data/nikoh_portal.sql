-- Nikoh Portali - TUZATILGAN O'rnatish SQL skripti
-- Bu faylni phpMyAdmin yoki MySQL da ishga tushiring

CREATE DATABASE IF NOT EXISTS nikoh_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nikoh_portal;

-- 1. Foydalanuvchilar jadvali
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       passport_series VARCHAR(9) NOT NULL UNIQUE COMMENT 'Pasport seriyasi (AA1234567)',
                       first_name VARCHAR(50) NOT NULL COMMENT 'Ismi',
                       last_name VARCHAR(50) NOT NULL COMMENT 'Familiyasi',
                       middle_name VARCHAR(50) DEFAULT NULL COMMENT 'Otasining ismi',
                       birth_date DATE DEFAULT NULL COMMENT 'Tug\'ilgan sanasi',
                       birth_place VARCHAR(200) DEFAULT NULL COMMENT 'Tug\'ilgan joyi',
                       citizenship VARCHAR(50) DEFAULT 'O\'zbekiston' COMMENT 'Fuqaroligi',
                       phone VARCHAR(13) NOT NULL COMMENT 'Telefon raqami (+998xxxxxxxxx)',
                       email VARCHAR(100) DEFAULT NULL COMMENT 'Email manzil',
                       password_hash VARCHAR(255) NOT NULL COMMENT 'Shifrlangan parol',
                       gender ENUM('erkak', 'ayol') DEFAULT NULL COMMENT 'Jinsi',
                       marital_status ENUM('turmushga_chiqmagan', 'nikohda', 'ajrashgan', 'beva') DEFAULT 'turmushga_chiqmagan',
                       address TEXT DEFAULT NULL COMMENT 'Yashash manzili',
                       registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       last_login TIMESTAMP NULL,
                       is_verified BOOLEAN DEFAULT TRUE COMMENT 'Tasdiqlangan holati',
                       verification_token VARCHAR(100) NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Admin foydalanuvchilar jadvali (FOREIGN KEY ni keyinroq qo'shamiz)
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
                             updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Arizalar jadvali
CREATE TABLE applications (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              application_number VARCHAR(20) NOT NULL UNIQUE COMMENT 'Ariza raqami',
                              application_type ENUM('nikoh', 'ajralish') NOT NULL COMMENT 'Ariza turi',

    -- Asosiy ariza beruvchi
                              applicant_id INT NOT NULL,

    -- Ikkinchi tomon ma'lumotlari
                              partner_id INT NULL COMMENT 'Sherik ID (agar tizimda ro\'yxatdan o\'tgan bo\'lsa)',
                              partner_passport VARCHAR(9) NULL COMMENT 'Sherik pasporti',
                              partner_name VARCHAR(150) NULL COMMENT 'Sherik to\'liq ismi',
                              partner_birth_date DATE NULL,
                              partner_phone VARCHAR(13) NULL,

    -- Nikoh/ajralish ma'lumotlari
                              marriage_date DATE NULL COMMENT 'Nikoh sanasi (ajralish uchun)',
                              marriage_certificate_number VARCHAR(50) NULL COMMENT 'Nikoh guvohnomasi raqami',
                              divorce_reason TEXT NULL COMMENT 'Ajralish sababi',

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

    -- Xizmat
                              ceremony_type ENUM('oddiy', 'tantanali', 'uyga_chiqib') DEFAULT 'oddiy',
                              ceremony_location VARCHAR(200) NULL COMMENT 'Marosim joyi',

    -- To'lov
                              payment_required DECIMAL(10,2) DEFAULT 0.00 COMMENT 'To\'lov miqdori (so\'m)',
                              payment_status ENUM('kutilmoqda', 'tolandi', 'qaytarildi') DEFAULT 'kutilmoqda',

                              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                              updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
                                    verification_notes TEXT NULL
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
                                     download_date TIMESTAMP NULL
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
                          notes TEXT NULL
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
                             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Xabarnomalar jadvali
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
                               created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Bog'lanish xabarlari jadvali
CREATE TABLE contact_messages (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  name VARCHAR(100) NOT NULL,
                                  email VARCHAR(100) NOT NULL,
                                  phone VARCHAR(13) NULL,
                                  subject VARCHAR(200) NOT NULL,
                                  message TEXT NOT NULL,
                                  ip_address VARCHAR(45) NULL,
                                  user_agent TEXT NULL,
                                  is_read BOOLEAN DEFAULT FALSE,
                                  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FOREIGN KEY qo'shish (jadvallar yaratilgandan keyin)
ALTER TABLE admin_users ADD CONSTRAINT fk_admin_created_by FOREIGN KEY (created_by) REFERENCES admin_users(id);
ALTER TABLE applications ADD CONSTRAINT fk_app_applicant FOREIGN KEY (applicant_id) REFERENCES users(id);
ALTER TABLE applications ADD CONSTRAINT fk_app_partner FOREIGN KEY (partner_id) REFERENCES users(id);
ALTER TABLE applications ADD CONSTRAINT fk_app_assigned_admin FOREIGN KEY (assigned_admin_id) REFERENCES admin_users(id);
ALTER TABLE applications ADD CONSTRAINT fk_app_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES admin_users(id);
ALTER TABLE uploaded_documents ADD CONSTRAINT fk_doc_application FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE;
ALTER TABLE uploaded_documents ADD CONSTRAINT fk_doc_verified_by FOREIGN KEY (verified_by) REFERENCES admin_users(id);
ALTER TABLE generated_documents ADD CONSTRAINT fk_gen_doc_application FOREIGN KEY (application_id) REFERENCES applications(id);
ALTER TABLE payments ADD CONSTRAINT fk_payment_application FOREIGN KEY (application_id) REFERENCES applications(id);
ALTER TABLE system_logs ADD CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE system_logs ADD CONSTRAINT fk_log_admin FOREIGN KEY (admin_id) REFERENCES admin_users(id);
ALTER TABLE notifications ADD CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id);

-- Indekslar qo'shish
CREATE INDEX idx_users_passport ON users(passport_series);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_date ON applications(created_at);
CREATE INDEX idx_applications_number ON applications(application_number);
CREATE INDEX idx_applications_type ON applications(application_type);

-- Demo admin yaratish
INSERT INTO admin_users (username, password_hash, full_name, position, fhdy_organ, phone, email, role, is_active)
VALUES (
           'admin',
           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
           'Tizim Administratori',
           'Tizim administratori',
           'Bosh FHDY organi',
           '+998901234567',
           'admin@nikoh.uz',
           'admin',
           1
       );

-- Demo foydalanuvchi yaratish
INSERT INTO users (passport_series, first_name, last_name, middle_name, phone, password_hash, is_verified, gender, birth_date)
VALUES (
           'AA1234567',
           'Test',
           'Foydalanuvchi',
           'Testovich',
           '+998901234567',
           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
           1,
           'erkak',
           '1990-01-01'
       );

-- Yana bir demo foydalanuvchi (ayol)
INSERT INTO users (passport_series, first_name, last_name, middle_name, phone, password_hash, is_verified, gender, birth_date)
VALUES (
           'BB7777777',
           'Demo',
           'Ayol',
           'Demovna',
           '+998901234568',
           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
           1,
           'ayol',
           '1995-05-15'
       );

-- Demo ariza yaratish
INSERT INTO applications (application_number, application_type, applicant_id, partner_name, partner_passport, preferred_date, payment_required, status)
VALUES (
           '2025000001',
           'nikoh',
           1,
           'Demo Sherik',
           'CC9999999',
           '2025-07-15',
           102000,
           'yangi'
       );

-- Ariza raqamini avtomatik yaratish uchun trigger (agar MySQL versiyasi qo'llab-quvvatlasa)
DELIMITER //
CREATE TRIGGER IF NOT EXISTS generate_application_number
    BEFORE INSERT ON applications
    FOR EACH ROW
BEGIN
    DECLARE next_number INT DEFAULT 1;

    -- Agar application_number berilmagan bo'lsa
    IF NEW.application_number IS NULL OR NEW.application_number = '' THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(application_number, 5) AS UNSIGNED)), 0) + 1
        INTO next_number
        FROM applications
        WHERE YEAR(created_at) = YEAR(NOW());

        SET NEW.application_number = CONCAT(YEAR(NOW()), LPAD(next_number, 6, '0'));
    END IF;
END//
DELIMITER ;

-- Ma'lumotlar bazasi yaratildi!
SELECT 'Nikoh Portali ma\'lumotlar bazasi muvaffaqiyatli yaratildi!' AS Result;