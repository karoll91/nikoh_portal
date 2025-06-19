
-- Bu SQL skriptni ishga tushiring agar middle_name xatoligi chiqsa

USE nikoh_portal;

-- middle_name maydonini NULL ga ruxsat berish
ALTER TABLE users MODIFY COLUMN middle_name VARCHAR(50) DEFAULT NULL COMMENT 'Otasining ismi';

-- Boshqa default qiymatlarni ham tuzatish
ALTER TABLE users MODIFY COLUMN birth_date DATE DEFAULT NULL COMMENT 'Tug\'ilgan sanasi';
ALTER TABLE users MODIFY COLUMN birth_place VARCHAR(200) DEFAULT NULL COMMENT 'Tug\'ilgan joyi';
ALTER TABLE users MODIFY COLUMN email VARCHAR(100) DEFAULT NULL COMMENT 'Email manzil';
ALTER TABLE users MODIFY COLUMN gender ENUM('erkak', 'ayol') DEFAULT NULL COMMENT 'Jinsi';
ALTER TABLE users MODIFY COLUMN address TEXT DEFAULT NULL COMMENT 'Yashash manzili';

-- Agar jadval allaqachon mavjud bo'lsa va ma'lumotlar bor bo'lsa:
-- UPDATE users SET middle_name = 'Kiritilmagan' WHERE middle_name IS NULL OR middle_name = '';

-- MySQL ni SQL_MODE ni tekshirish (agar kerak bo'lsa)
-- SELECT @@sql_mode;

-- SQL_MODE ni o'zgartirish (faqat joriy session uchun)
-- SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

SHOW COLUMNS FROM users;