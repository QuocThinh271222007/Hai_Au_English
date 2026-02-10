-- =============================================
-- HẢI ÂU ENGLISH - DATABASE HOÀN CHỈNH
-- File này gộp tất cả các database SQL vào 1 file
-- Chạy file này trong phpMyAdmin để tạo toàn bộ cấu trúc và dữ liệu
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;
SET time_zone = "+07:00";

-- Tắt foreign key check để tránh lỗi khi DROP TABLE
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- TẠO DATABASE (nếu cần)
-- =============================================
-- CREATE DATABASE IF NOT EXISTS `hai_au_english` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `hai_au_english`;

-- =============================================
-- XÓA TẤT CẢ CÁC BẢNG THEO THỨ TỰ
-- =============================================
DROP TABLE IF EXISTS `trash`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `site_content`;
DROP TABLE IF EXISTS `teacher_reviews`;
DROP TABLE IF EXISTS `course_fee_items`;
DROP TABLE IF EXISTS `student_achievements`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `schedules`;
DROP TABLE IF EXISTS `class_schedules`;
DROP TABLE IF EXISTS `scores`;
DROP TABLE IF EXISTS `feedback`;
DROP TABLE IF EXISTS `enrollments`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `users`;

-- Bật lại foreign key check
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- PHẦN 1: TẠO CÁC BẢNG CƠ BẢN
-- =============================================

-- =============================================
-- 1. BẢNG CONTACTS (liên hệ)
-- =============================================
CREATE TABLE `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `contact_type` ENUM('consultation', 'registration', 'feedback', 'general') DEFAULT 'general',
  `course_interest` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('new', 'contacted', 'converted', 'closed') DEFAULT 'new',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. BẢNG USERS (người dùng)
-- =============================================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(500) DEFAULT NULL,
  `role` ENUM('admin', 'teacher', 'user') DEFAULT 'user',
  `is_active` TINYINT(1) DEFAULT 1,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. BẢNG COURSES (khóa học)
-- =============================================
CREATE TABLE `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `level` ENUM('beginner', 'elementary', 'intermediate', 'upper_intermediate', 'advanced', 'all') DEFAULT 'beginner',
  `duration` VARCHAR(50) DEFAULT NULL,
  `price` DECIMAL(15,0) DEFAULT 0,
  `price_unit` VARCHAR(20) DEFAULT '/tháng',
  `category` ENUM('group', 'private', 'online', 'intensive') DEFAULT 'group',
  `badge` VARCHAR(50) DEFAULT NULL,
  `features` TEXT DEFAULT NULL,
  `target` VARCHAR(100) DEFAULT NULL,
  `total_sessions` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `display_order` INT DEFAULT 0,
  `age_group` VARCHAR(50) DEFAULT NULL,
  `curriculum` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_level` (`level`),
  INDEX `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. BẢNG CLASSES (lớp học)
-- =============================================
CREATE TABLE `classes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'Tên lớp: VD: IELTS 6.5 - Lớp A',
  `course_id` INT UNSIGNED NOT NULL COMMENT 'Khóa học tương ứng',
  `teacher_id` INT UNSIGNED DEFAULT NULL COMMENT 'Giảng viên phụ trách',
  `max_students` INT DEFAULT 20 COMMENT 'Số học viên tối đa',
  `schedule` VARCHAR(255) DEFAULT NULL COMMENT 'Lịch học: VD: T2, T4, T6 (18:00-20:00)',
  `room` VARCHAR(50) DEFAULT NULL COMMENT 'Phòng học',
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `academic_year` VARCHAR(20) DEFAULT NULL COMMENT 'Năm học: VD: 2025-2026',
  `semester` VARCHAR(50) DEFAULT NULL COMMENT 'Học kỳ',
  `status` ENUM('upcoming', 'active', 'completed', 'cancelled') DEFAULT 'upcoming',
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_course` (`course_id`),
  INDEX `idx_teacher` (`teacher_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_academic_year` (`academic_year`),
  CONSTRAINT `fk_class_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_class_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. BẢNG ENROLLMENTS (đăng ký học)
-- =============================================
CREATE TABLE `enrollments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `class_id` INT UNSIGNED DEFAULT NULL COMMENT 'Lớp học được phân vào',
  `academic_year` VARCHAR(20) DEFAULT NULL,
  `semester` VARCHAR(50) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `status` ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
  `progress` INT DEFAULT 0,
  `payment_status` ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_course` (`course_id`),
  INDEX `idx_class` (`class_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_enrollment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollment_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_enrollment_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. BẢNG SCORES (điểm số)
-- =============================================
CREATE TABLE `scores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `listening` DECIMAL(3,1) DEFAULT NULL,
  `reading` DECIMAL(3,1) DEFAULT NULL,
  `writing` DECIMAL(3,1) DEFAULT NULL,
  `speaking` DECIMAL(3,1) DEFAULT NULL,
  `overall` DECIMAL(3,1) DEFAULT NULL,
  `test_date` DATE DEFAULT NULL,
  `test_type` ENUM('placement', 'midterm', 'final', 'mock', 'official') DEFAULT 'mock',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_enrollment` (`enrollment_id`),
  INDEX `idx_user` (`user_id`),
  CONSTRAINT `fk_score_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_score_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. BẢNG FEEDBACK (phản hồi)
-- =============================================
CREATE TABLE `feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL,
  `content` TEXT DEFAULT NULL,
  `feedback_type` ENUM('teacher', 'course', 'general') DEFAULT 'general',
  `is_anonymous` TINYINT(1) DEFAULT 0,
  `status` ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_teacher` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. BẢNG TEACHERS (giảng viên)
-- =============================================
CREATE TABLE `teachers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100) DEFAULT NULL,
  `degree` VARCHAR(50) DEFAULT NULL COMMENT 'Học vị: Cử nhân, Thạc sĩ, Tiến sĩ, Phó Giáo sư, Giáo sư',
  `description` TEXT DEFAULT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `ielts_score` DECIMAL(2,1) DEFAULT NULL,
  `experience_years` INT DEFAULT NULL,
  `students_count` INT DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 5.0,
  `specialties` VARCHAR(255) DEFAULT NULL,
  `qualifications` TEXT DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_featured` (`is_featured`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7.1. BẢNG CLASS_SCHEDULES (thời khóa biểu theo lớp)
-- =============================================
CREATE TABLE `class_schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL COMMENT 'Lớp học',
  `course_id` INT UNSIGNED NOT NULL COMMENT 'Khóa học',
  `teacher_id` INT UNSIGNED DEFAULT NULL COMMENT 'Giảng viên dạy buổi này',
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
  `start_time` TIME NOT NULL COMMENT 'Giờ bắt đầu',
  `end_time` TIME NOT NULL COMMENT 'Giờ kết thúc',
  `room` VARCHAR(50) DEFAULT NULL COMMENT 'Phòng học',
  `is_online` TINYINT(1) DEFAULT 0 COMMENT 'Học online hay offline',
  `meeting_link` VARCHAR(500) DEFAULT NULL COMMENT 'Link Zoom/Meet nếu online',
  `subject` VARCHAR(100) DEFAULT NULL COMMENT 'Môn học: Speaking, Writing, Reading, Listening',
  `notes` TEXT DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT '#1e40af' COMMENT 'Màu hiển thị trên lịch',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_class` (`class_id`),
  INDEX `idx_course` (`course_id`),
  INDEX `idx_teacher` (`teacher_id`),
  INDEX `idx_day` (`day_of_week`),
  CONSTRAINT `fk_class_schedule_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_class_schedule_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_class_schedule_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 8. BẢNG SCHEDULES (thời khóa biểu)
-- =============================================
CREATE TABLE `schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room` VARCHAR(50) DEFAULT NULL,
  `is_online` TINYINT(1) DEFAULT 0,
  `meeting_url` VARCHAR(500) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT '#1e40af',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_enrollment` (`enrollment_id`),
  INDEX `idx_day` (`day_of_week`),
  CONSTRAINT `fk_schedule_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 9. BẢNG REVIEWS (đánh giá chung)
-- =============================================
CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `comment` TEXT NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_rating` (`rating`),
  INDEX `idx_approved` (`is_approved`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 10. BẢNG STUDENT_ACHIEVEMENTS (thành tích học viên)
-- =============================================
CREATE TABLE `student_achievements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_name` VARCHAR(255) NOT NULL,
  `achievement_title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500) NOT NULL,
  `score` VARCHAR(50) DEFAULT NULL,
  `course_name` VARCHAR(255) DEFAULT NULL,
  `achievement_date` DATE DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `display_order` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_featured` (`is_featured`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 11. BẢNG TRASH (thùng rác)
-- =============================================
CREATE TABLE `trash` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_table` VARCHAR(50) NOT NULL,
  `original_id` INT UNSIGNED NOT NULL,
  `data` JSON NOT NULL,
  `deleted_by` INT UNSIGNED DEFAULT NULL,
  `deleted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `is_restored` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX (`original_table`),
  INDEX (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 12. BẢNG SITE_CONTENT (nội dung trang web)
-- =============================================
CREATE TABLE `site_content` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` VARCHAR(50) NOT NULL COMMENT 'home, about, courses, teachers, contact',
  `section` VARCHAR(100) NOT NULL COMMENT 'hero, features, stats, about_intro, etc.',
  `content_key` VARCHAR(100) NOT NULL COMMENT 'title, subtitle, description, image_url, etc.',
  `content_value` TEXT DEFAULT NULL,
  `content_type` ENUM('text', 'html', 'image', 'json') DEFAULT 'text',
  `is_active` TINYINT(1) DEFAULT 1,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_content` (`page`, `section`, `content_key`),
  INDEX (`page`),
  INDEX (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 13. BẢNG SITE_SETTINGS (cài đặt chung)
-- =============================================
CREATE TABLE `site_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 14. BẢNG TEACHER_REVIEWS (đánh giá giảng viên)
-- =============================================
CREATE TABLE `teacher_reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID người dùng đã đăng nhập, NULL nếu admin thêm',
  `teacher_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID giảng viên được đánh giá',
  `reviewer_name` VARCHAR(100) NOT NULL,
  `reviewer_avatar` VARCHAR(10) DEFAULT NULL COMMENT 'Chữ viết tắt tên',
  `reviewer_info` VARCHAR(255) DEFAULT NULL COMMENT 'VD: Học viên lớp Advanced',
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `comment` TEXT NOT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_rating` (`rating`),
  INDEX `idx_approved` (`is_approved`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_teacher_id` (`teacher_id`),
  INDEX `idx_display_order` (`display_order`),
  CONSTRAINT `fk_teacher_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_teacher_review_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 15. BẢNG COURSE_FEE_ITEMS (bảng học phí chi tiết)
-- =============================================
CREATE TABLE `course_fee_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(50) NOT NULL COMMENT 'tieuhoc, thcs, ielts',
  `level` VARCHAR(100) NOT NULL COMMENT 'Lớp 1, Lớp 2, IELTS Foundation...',
  `curriculum` VARCHAR(255) DEFAULT NULL COMMENT 'Giáo trình sử dụng',
  `duration` VARCHAR(100) DEFAULT NULL COMMENT 'Thời lượng khóa học',
  `fee` VARCHAR(100) DEFAULT NULL COMMENT 'Học phí (text)',
  `is_highlight` TINYINT(1) DEFAULT 0 COMMENT 'Đánh dấu nổi bật',
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- PHẦN 2: DỮ LIỆU MẪU - USERS
-- Mật khẩu: admin123 (admin), password (user)
-- =============================================
INSERT INTO `users` (`fullname`, `email`, `password`, `phone`, `role`, `date_of_birth`, `gender`) VALUES
('Admin Hải Âu', 'admin@haiau.edu.vn', '$2y$10$5gxbzcC7TGNDH6PAeJc9BuklbXTy40nLB2p.sy2R01Ctn6bXFBQt.', '0901234567', 'admin', '1990-01-15', 'male'),
('Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user', '2000-05-20', 'male'),
('Trần Thị B', 'tranthib@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'user', '2001-08-10', 'female');

-- =============================================
-- PHẦN 3: DỮ LIỆU MẪU - COURSES (21 khóa học đầy đủ)
-- =============================================
INSERT INTO `courses` (`name`, `description`, `image_url`, `level`, `duration`, `price`, `price_unit`, `category`, `badge`, `features`, `target`, `total_sessions`, `age_group`, `curriculum`, `display_order`) VALUES
-- TIỂU HỌC (7 khóa)
('Tiếng Anh Lớp 1', 'Chương trình tiếng Anh căn bản dành cho học sinh lớp 1, tập trung phát triển kỹ năng nghe nói và làm quen với bảng chữ cái tiếng Anh.', '/frontend/assets/images/uploads/courses/class1.jpg', 'beginner', '9 tháng', 600000, '/tháng', 'group', 'Mới', 'Học qua trò chơi|Phát âm chuẩn|Bảng chữ cái|Từ vựng cơ bản', 'Học sinh lớp 1', 72, 'tieuhoc', 'Tháng 1-3: Làm quen bảng chữ cái, số đếm 1-20\nTháng 4-6: Từ vựng gia đình, màu sắc, động vật\nTháng 7-9: Câu giao tiếp đơn giản, bài hát tiếng Anh', 1),
('Tiếng Anh Lớp 2', 'Nâng cao vốn từ vựng và phát triển kỹ năng nghe nói cho học sinh lớp 2 với các chủ đề gần gũi cuộc sống.', '/frontend/assets/images/uploads/courses/class2.jpg', 'beginner', '9 tháng', 650000, '/tháng', 'group', NULL, 'Từ vựng phong phú|Nghe hiểu|Hội thoại ngắn|Đọc câu đơn giản', 'Học sinh lớp 2', 72, 'tieuhoc', 'Tháng 1-3: Mở rộng từ vựng về trường học, thức ăn\nTháng 4-6: Hội thoại về sở thích, thời tiết\nTháng 7-9: Đọc hiểu đoạn văn ngắn, viết câu đơn giản', 2),
('Tiếng Anh Lớp 3', 'Phát triển toàn diện 4 kỹ năng nghe, nói, đọc, viết ở mức cơ bản cho học sinh lớp 3.', '/frontend/assets/images/uploads/courses/class3.jpg', 'beginner', '9 tháng', 700000, '/tháng', 'group', NULL, 'Ngữ pháp căn bản|4 kỹ năng|Phản xạ tiếng Anh|Trò chơi ngôn ngữ', 'Học sinh lớp 3', 72, 'tieuhoc', 'Tháng 1-3: Ngữ pháp cơ bản (to be, have/has)\nTháng 4-6: Đọc hiểu và viết đoạn văn ngắn\nTháng 7-9: Thực hành giao tiếp theo tình huống', 3),
('Tiếng Anh Lớp 4', 'Củng cố nền tảng ngữ pháp và mở rộng vốn từ vựng theo các chủ đề đa dạng.', '/frontend/assets/images/uploads/courses/class4.jpg', 'elementary', '9 tháng', 750000, '/tháng', 'group', NULL, 'Ngữ pháp nâng cao|Từ vựng mở rộng|Viết đoạn văn|Thuyết trình', 'Học sinh lớp 4', 72, 'tieuhoc', 'Tháng 1-3: Thì hiện tại đơn và hiện tại tiếp diễn\nTháng 4-6: Từ vựng về du lịch, nghề nghiệp, môi trường\nTháng 7-9: Viết email đơn giản, thuyết trình ngắn', 4),
('Tiếng Anh Lớp 5', 'Hoàn thiện kỹ năng tiếng Anh tiểu học, chuẩn bị chuyển cấp THCS.', '/frontend/assets/images/uploads/courses/class5.jpg', 'elementary', '9 tháng', 800000, '/tháng', 'group', 'Chuyển cấp', 'Luyện thi cuối cấp|Ngữ pháp tổng hợp|Đọc hiểu|Viết luận ngắn', 'Học sinh lớp 5', 72, 'tieuhoc', 'Tháng 1-3: Ôn tập và củng cố ngữ pháp tiểu học\nTháng 4-6: Luyện đọc hiểu, viết đoạn văn 80-100 từ\nTháng 7-9: Luyện đề thi cuối cấp, chuẩn bị THCS', 5),
('Tiếng Anh Tiểu Học Nâng Cao', 'Chương trình nâng cao dành cho học sinh có nền tảng tốt, muốn phát triển thêm.', '/frontend/assets/images/uploads/courses/primary-advanced.jpg', 'elementary', '9 tháng', 900000, '/tháng', 'group', 'Nâng cao', 'Cambridge Starters|Speaking focus|Tư duy tiếng Anh|Học qua dự án', 'Học sinh tiểu học (7-11 tuổi)', 72, 'tieuhoc', 'Tháng 1-3: Luyện thi Cambridge Starters\nTháng 4-6: Speaking và Listening chuyên sâu\nTháng 7-9: Học qua dự án, thuyết trình bằng tiếng Anh', 6),
('Tiếng Anh Tiểu Học 1 kèm 1', 'Học 1 kèm 1 với giáo viên, lộ trình cá nhân hóa 100% theo nhu cầu học sinh.', '/frontend/assets/images/uploads/courses/primary-1-1.jpg', 'all', 'Linh hoạt', 250000, '/giờ', 'private', 'VIP', 'Lịch linh hoạt|Cá nhân hóa 100%|Tiến bộ nhanh|Báo cáo chi tiết', 'Học sinh tiểu học (6-11 tuổi)', 20, 'tieuhoc', 'Lộ trình được thiết kế riêng theo:\n- Trình độ hiện tại của học sinh\n- Mục tiêu cần đạt\n- Thời gian có thể học', 7),

-- THCS (7 khóa)
('Tiếng Anh Lớp 6', 'Chương trình tiếng Anh THCS, nâng cao ngữ pháp và từ vựng theo sách giáo khoa mới.', '/frontend/assets/images/uploads/courses/class6.jpg', 'elementary', '9 tháng', 850000, '/tháng', 'group', NULL, 'Theo SGK mới|Ngữ pháp THCS|Từ vựng học thuật|Luyện đề', 'Học sinh lớp 6', 72, 'thcs', 'Tháng 1-3: Ôn tập tiểu học, làm quen chương trình THCS\nTháng 4-6: Thì quá khứ đơn, tương lai đơn\nTháng 7-9: Đọc hiểu văn bản, viết email và thư', 8),
('Tiếng Anh Lớp 7', 'Phát triển kỹ năng đọc hiểu và viết học thuật, mở rộng từ vựng chuyên ngành.', '/frontend/assets/images/uploads/courses/class7.jpg', 'intermediate', '9 tháng', 900000, '/tháng', 'group', NULL, 'Đọc hiểu nâng cao|Viết học thuật|Từ vựng chuyên ngành|Nghe BBC', 'Học sinh lớp 7', 72, 'thcs', 'Tháng 1-3: Câu điều kiện loại 1, so sánh\nTháng 4-6: Từ vựng về công nghệ, môi trường, sức khỏe\nTháng 7-9: Viết bài luận 120-150 từ', 9),
('Tiếng Anh Lớp 8', 'Củng cố ngữ pháp nâng cao và phát triển kỹ năng giao tiếp tự tin.', '/frontend/assets/images/uploads/courses/class8.jpg', 'intermediate', '9 tháng', 950000, '/tháng', 'group', NULL, 'Ngữ pháp nâng cao|Giao tiếp tự tin|Thuyết trình|Làm việc nhóm', 'Học sinh lớp 8', 72, 'thcs', 'Tháng 1-3: Câu bị động, mệnh đề quan hệ\nTháng 4-6: Thực hành giao tiếp theo chủ đề\nTháng 7-9: Thuyết trình nhóm, debate cơ bản', 10),
('Tiếng Anh Lớp 9', 'Ôn thi vào 10 và chuẩn bị nền tảng cho IELTS với ngữ pháp tổng hợp.', '/frontend/assets/images/uploads/courses/class9.jpg', 'intermediate', '9 tháng', 1000000, '/tháng', 'group', 'Luyện thi', 'Ôn thi vào 10|Ngữ pháp tổng hợp|Đề thi thử|Kỹ năng làm bài', 'Học sinh lớp 9', 72, 'thcs', 'Tháng 1-3: Ôn tập toàn bộ ngữ pháp THCS\nTháng 4-6: Luyện đề thi vào 10 các tỉnh\nTháng 7-9: Rèn kỹ năng làm bài, chiến thuật thi', 11),
('Tiếng Anh THCS Nâng Cao', 'Chương trình nâng cao dành cho học sinh muốn đạt điểm cao và chuẩn bị IELTS sớm.', '/frontend/assets/images/uploads/courses/secondary-advanced.jpg', 'intermediate', '9 tháng', 1100000, '/tháng', 'group', 'Nâng cao', 'Cambridge KET/PET|Academic English|Critical thinking|IELTS Foundation', 'Học sinh THCS (11-15 tuổi)', 72, 'thcs', 'Tháng 1-3: Luyện thi Cambridge KET/PET\nTháng 4-6: Academic English, đọc báo tiếng Anh\nTháng 7-9: Nhập môn IELTS, làm quen format thi', 12),
('Luyện Thi Vào 10 Chuyên Anh', 'Chương trình luyện thi chuyên sâu cho kỳ thi vào lớp 10 chuyên Anh.', '/frontend/assets/images/uploads/courses/chuyen-anh.jpg', 'advanced', '6 tháng', 1500000, '/tháng', 'intensive', 'Hot', 'Đề thi chuyên|Ngữ pháp chuyên sâu|Từ vựng nâng cao|Mock test hàng tuần', 'Học sinh lớp 9', 48, 'thcs', 'Tháng 1-2: Ngữ pháp nâng cao (đảo ngữ, mệnh đề...)\nTháng 3-4: Từ vựng học thuật, phrasal verbs\nTháng 5-6: Luyện đề, mock test, chữa bài chi tiết', 13),
('Tiếng Anh THCS 1 kèm 1', 'Học 1 kèm 1 với giáo viên chuyên môn cao, phù hợp học sinh cần bổ trợ hoặc nâng cao.', '/frontend/assets/images/uploads/courses/secondary-1-1.jpg', 'all', 'Linh hoạt', 300000, '/giờ', 'private', 'VIP', 'Lịch linh hoạt|Chương trình riêng|Sửa lỗi chi tiết|Tiến bộ nhanh', 'Học sinh THCS (11-15 tuổi)', 20, 'thcs', 'Lộ trình được thiết kế riêng theo:\n- Điểm yếu cần cải thiện\n- Mục tiêu điểm số\n- Kỳ thi sắp tới', 14),

-- IELTS (7 khóa)
('IELTS Foundation', 'Khóa học nền tảng dành cho người mới bắt đầu, xây dựng kiến thức cơ bản về IELTS.', '/frontend/assets/images/uploads/courses/ielts-foundation.jpg', 'beginner', '3 tháng', 3500000, '/khóa', 'group', 'Phổ biến', '36 buổi (72 giờ)|Lớp 8-10 HV|Giáo trình độc quyền|Cam kết 5.0-5.5', '5.0-5.5', 36, 'ielts', 'Tháng 1: Làm quen format IELTS, từ vựng cơ bản\nTháng 2: Ngữ pháp nền tảng, Listening & Reading cơ bản\nTháng 3: Writing Task 1-2 cơ bản, Speaking Part 1-2', 15),
('IELTS Intermediate', 'Nâng cao kỹ năng 4 skills, luyện đề Cambridge chuyên sâu.', '/frontend/assets/images/uploads/courses/ielts-intermediate.jpg', 'intermediate', '3 tháng', 4500000, '/khóa', 'group', NULL, '40 buổi (80 giờ)|Lớp 8-10 HV|Luyện đề Cambridge|Cam kết 6.0-6.5', '6.0-6.5', 40, 'ielts', 'Tháng 1: Listening & Reading strategies\nTháng 2: Writing Task 1-2 band 6.0+\nTháng 3: Speaking Part 1-3, Mock test', 16),
('IELTS Advanced', 'Hoàn thiện kỹ năng và chiến thuật thi, hướng đến band điểm cao.', '/frontend/assets/images/uploads/courses/ielts-advanced.jpg', 'advanced', '2 tháng', 5500000, '/khóa', 'group', 'Premium', '32 buổi (64 giờ)|Lớp 6-8 HV|Chấm Speaking/Writing|Cam kết 7.0-7.5', '7.0-7.5', 32, 'ielts', 'Tháng 1: Advanced strategies, Collocations, Idioms\nTháng 2: Full mock tests, Feedback chi tiết, Kỹ thuật xử lý đề khó', 17),
('IELTS Intensive', 'Khóa học cấp tốc cho người có ít thời gian, học 5 buổi/tuần.', '/frontend/assets/images/uploads/courses/ielts-intensive.jpg', 'intermediate', '1.5 tháng', 6000000, '/khóa', 'intensive', 'Hot', '30 buổi (60 giờ)|Học 5 buổi/tuần|Luyện đề mỗi ngày|Cam kết tăng 1.0 band', 'Tăng 1.0 band', 30, 'ielts', 'Tuần 1-2: Ôn tập ngữ pháp, từ vựng chuyên sâu\nTuần 3-4: Luyện 4 kỹ năng song song\nTuần 5-6: Mock test + Feedback + Chữa lỗi', 18),
('IELTS 1 kèm 1', 'Học 1 kèm 1 với giảng viên 8.0+, lộ trình 100% cá nhân hóa.', '/frontend/assets/images/uploads/courses/ielts-1-1.jpg', 'all', 'Linh hoạt', 500000, '/giờ', 'private', 'VIP', 'Lịch linh hoạt|1 kèm 1|Giảng viên 8.0+|Lộ trình cá nhân', 'Mọi mục tiêu', 20, 'ielts', 'Lộ trình được thiết kế 100% theo:\n- Điểm số mục tiêu\n- Thời gian có thể học\n- Điểm yếu cần cải thiện\n- Deadline thi thật', 19),
('IELTS Online', 'Học trực tuyến qua Zoom với giảng viên, tiết kiệm thời gian di chuyển.', '/frontend/assets/images/uploads/courses/ielts-online.jpg', 'all', '3 tháng', 3000000, '/khóa', 'online', NULL, '36 buổi online|Lớp 8-12 HV|Tài liệu điện tử|Học mọi lúc mọi nơi', 'Mọi trình độ', 36, 'ielts', 'Tháng 1: Foundation + Listening/Reading online\nTháng 2: Writing online với feedback qua video\nTháng 3: Speaking practice qua Zoom 1-1', 20),
('IELTS Writing Chuyên Sâu', 'Khóa học chuyên sâu về Writing, luyện Task 1 & Task 2 với chấm chi tiết.', '/frontend/assets/images/uploads/courses/ielts-writing.jpg', 'intermediate', '1.5 tháng', 4000000, '/khóa', 'group', 'Mới', '24 buổi (48 giờ)|Lớp 6-8 HV|Chấm bài chi tiết|200+ mẫu essays', 'Writing 6.5+', 24, 'ielts', 'Tuần 1-2: Task 1 - Line, Bar, Pie, Table, Process, Map\nTuần 3-4: Task 2 - Opinion, Discussion, Problem-Solution\nTuần 5-6: Practice + Feedback + Band 7.0 techniques', 21);

-- =============================================
-- PHẦN 4: DỮ LIỆU MẪU - TEACHERS
-- =============================================
INSERT INTO `teachers` (`name`, `title`, `description`, `image_url`, `ielts_score`, `experience_years`, `students_count`, `rating`, `specialties`, `is_featured`) VALUES
('Ms. Nguyễn Thu Hà', 'Trưởng bộ môn Speaking', 'Thạc sĩ Ngôn ngữ Anh - ĐH Ngoại ngữ Hà Nội. 10 năm kinh nghiệm giảng dạy IELTS, đặc biệt chuyên sâu Speaking.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=400&fit=crop', 8.5, 10, 500, 4.9, 'Speaking, Pronunciation', 1),
('Mr. Trần Minh Đức', 'Giám đốc học thuật', 'Thạc sĩ TESOL - ĐH Cambridge. 12 năm kinh nghiệm, chuyên gia Writing và Reading với phương pháp độc quyền.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop', 9.0, 12, 800, 5.0, 'Writing, Reading', 1),
('Ms. Lê Thị Mai', 'Chuyên gia Writing', 'Cử nhân Ngôn ngữ Anh - ĐH KHXH&NV. 8 năm kinh nghiệm chuyên sâu IELTS Academic Writing.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&h=400&fit=crop', 8.5, 8, 600, 4.8, 'Writing, Grammar', 1),
('Mr. Phạm Văn Hoàng', 'Chuyên gia Listening', 'Thạc sĩ Giáo dục - ĐH Sư phạm TP.HCM. Chuyên gia luyện nghe với kỹ thuật note-taking hiệu quả.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop', 8.0, 6, 400, 4.7, 'Listening, Vocabulary', 1);

-- =============================================
-- PHẦN 5: DỮ LIỆU MẪU - ENROLLMENTS
-- =============================================
INSERT INTO `enrollments` (`user_id`, `course_id`, `academic_year`, `semester`, `start_date`, `end_date`, `status`, `progress`) VALUES
(2, 1, '2025-2026', 'Học kỳ 1', '2025-09-01', '2025-12-01', 'completed', 100),
(2, 2, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-04-10', 'active', 35),
(3, 3, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-03-10', 'active', 20);

-- =============================================
-- PHẦN 6: DỮ LIỆU MẪU - SCORES
-- =============================================
INSERT INTO `scores` (`enrollment_id`, `user_id`, `listening`, `reading`, `writing`, `speaking`, `overall`, `test_date`, `test_type`) VALUES
(1, 2, 6.0, 5.5, 5.5, 6.0, 5.5, '2025-12-01', 'final'),
(2, 2, 6.0, 5.5, 5.5, 6.0, 5.5, '2026-01-15', 'placement');

-- =============================================
-- PHẦN 7: DỮ LIỆU MẪU - SCHEDULES
-- =============================================
INSERT INTO `schedules` (`enrollment_id`, `teacher_id`, `title`, `day_of_week`, `start_time`, `end_time`, `room`, `is_online`, `color`, `is_active`) VALUES
(2, 1, 'IELTS Intermediate - Speaking', 'monday', '09:00:00', '11:00:00', 'P.101', 0, '#1e40af', 1),
(2, 2, 'IELTS Intermediate - Writing', 'wednesday', '09:00:00', '11:00:00', 'P.102', 0, '#059669', 1),
(2, 3, 'IELTS Intermediate - Reading', 'friday', '14:00:00', '16:00:00', 'P.101', 0, '#dc2626', 1),
(3, 2, 'IELTS Advanced - Writing', 'tuesday', '09:00:00', '11:00:00', 'P.201', 0, '#059669', 1),
(3, 4, 'IELTS Advanced - Listening', 'thursday', '14:00:00', '16:00:00', 'P.202', 0, '#7c3aed', 1),
(3, 1, 'IELTS Advanced - Speaking', 'saturday', '09:00:00', '11:00:00', 'P.201', 0, '#1e40af', 1);

-- =============================================
-- PHẦN 8: DỮ LIỆU MẪU - REVIEWS (20 đánh giá)
-- =============================================
INSERT INTO `reviews` (`user_name`, `rating`, `comment`, `image_url`, `is_approved`, `created_at`) VALUES
('Nguyễn Văn Hùng', 5, 'Trung tâm rất tuyệt vời! Giáo viên nhiệt tình, tận tâm. Mình từ 5.0 lên 7.0 sau 3 tháng học tại đây.', '/frontend/assets/images/uploads/reviews/review1.jpg', 1, '2026-01-15 10:30:00'),
('Trần Thị Mai', 5, 'Phương pháp giảng dạy hiệu quả, đặc biệt là kỹ năng Speaking. Thầy cô rất thân thiện và luôn sẵn sàng giải đáp.', '/frontend/assets/images/uploads/reviews/review2.jpg', 1, '2026-01-18 14:20:00'),
('Lê Minh Đức', 5, 'Cơ sở vật chất hiện đại, lớp học nhỏ nên được chú ý nhiều. Tài liệu học tập phong phú.', NULL, 1, '2026-01-20 09:15:00'),
('Phạm Thị Lan', 4, 'Học ở đây 6 tháng, cảm thấy rất hài lòng với chất lượng giảng dạy. Thỉnh thoảng lớp đông một chút.', '/frontend/assets/images/uploads/reviews/review3.jpg', 1, '2026-01-22 16:45:00'),
('Hoàng Văn Sơn', 5, 'Đi làm bận rộn nhưng vẫn đạt 7.5 IELTS nhờ lộ trình học linh hoạt của trung tâm.', NULL, 1, '2026-01-25 11:00:00'),
('Đặng Thị Hương', 5, 'Lần đầu thi IELTS đã đạt 7.0, vượt mục tiêu 6.5. Rất biết ơn các thầy cô!', '/frontend/assets/images/uploads/reviews/review4.jpg', 1, '2026-01-27 08:30:00'),
('Ngô Quang Minh', 5, 'Đội ngũ giáo viên chuyên nghiệp, đều có chứng chỉ 8.0+. Cách dạy Writing rất hay!', NULL, 1, '2026-01-28 13:20:00'),
('Bùi Thị Ngọc', 4, 'Học phí hợp lý so với chất lượng. Trung tâm cam kết đầu ra rõ ràng.', '/frontend/assets/images/uploads/reviews/review5.jpg', 1, '2026-01-29 15:40:00'),
('Vũ Đức Anh', 5, 'Đã học thử nhiều nơi nhưng chỉ Hải Âu English phù hợp nhất. Chấm Writing rất chi tiết.', NULL, 1, '2026-01-30 10:10:00'),
('Lý Thị Thu', 5, 'Môi trường học tập thân thiện, các bạn học viên hỗ trợ nhau. Có thêm nhiều bạn mới!', NULL, 1, '2026-01-31 17:00:00'),
('Trịnh Văn Nam', 5, 'Đạt 8.0 IELTS sau 4 tháng học, đủ điều kiện du học Úc. Cảm ơn Hải Âu English!', '/frontend/assets/images/uploads/reviews/review6.jpg', 1, '2026-02-01 09:30:00'),
('Phan Thị Linh', 4, 'Lớp Speaking 1-1 rất hiệu quả, tự tin giao tiếp hơn nhiều. Giá cả hợp lý.', NULL, 1, '2026-02-01 14:15:00'),
('Nguyễn Thanh Tùng', 5, 'Mình học khóa IELTS Intensive, từ 5.5 lên 7.0 trong 2 tháng. Giáo viên dạy rất dễ hiểu!', '/frontend/assets/images/uploads/reviews/review7.jpg', 1, '2026-02-01 16:00:00'),
('Lê Thị Hồng Nhung', 5, 'Cảm ơn thầy Đức đã giúp em cải thiện kỹ năng Writing từ 5.5 lên 7.0. Phương pháp dạy rất hiệu quả.', NULL, 1, '2026-02-01 17:30:00'),
('Trần Quốc Bảo', 5, 'Đạt 7.5 IELTS ngay lần thi đầu tiên. Trung tâm có lộ trình học rất khoa học.', '/frontend/assets/images/uploads/reviews/review8.jpg', 1, '2026-02-01 18:45:00'),
('Võ Thị Kim Ngân', 4, 'Lớp học online rất tiện lợi, giáo viên tương tác tốt. Chỉ tiếc là đôi khi mạng hơi lag.', NULL, 1, '2026-02-01 19:20:00'),
('Đinh Văn Phong', 5, 'Đã giới thiệu cho 3 người bạn đến học. Ai cũng hài lòng với chất lượng giảng dạy.', '/frontend/assets/images/uploads/reviews/review9.jpg', 1, '2026-02-01 20:00:00'),
('Huỳnh Thị Mỹ Duyên', 5, 'Từ sợ Speaking đến tự tin nói tiếng Anh. Cảm ơn cô Hà đã kiên nhẫn chỉ dạy!', NULL, 1, '2026-02-01 21:15:00'),
('Đỗ Minh Quân', 5, 'Học phí đắt hơn một chút nhưng xứng đáng. Cam kết đầu ra rõ ràng, học lại miễn phí nếu không đạt.', '/frontend/assets/images/uploads/reviews/review10.jpg', 1, '2026-02-01 22:00:00'),
('Mai Thị Thanh Trúc', 5, 'Tài liệu học tập được biên soạn rất kỹ, bám sát đề thi thật. Đạt 8.0 IELTS nhờ Hải Âu English!', NULL, 1, '2026-02-02 08:30:00');

-- =============================================
-- PHẦN 9: DỮ LIỆU MẪU - STUDENT_ACHIEVEMENTS (20 thành tích)
-- =============================================
INSERT INTO `student_achievements` (`student_name`, `achievement_title`, `description`, `image_url`, `score`, `course_name`, `is_featured`, `display_order`) VALUES
('Nguyễn Minh Anh', 'Đạt IELTS 8.0', 'Xuất sắc chinh phục band 8.0', '/frontend/assets/images/uploads/achievements/z7493567766783_7b98d81b3f65357e62f001c76114f1e3.jpg', '8.0', 'IELTS Advanced', 1, 1),
('Trần Thu Hà', 'Đạt IELTS 7.5', 'Vượt mục tiêu band 7.5', '/frontend/assets/images/uploads/achievements/z7493567774238_9b00e58b2490bf576833e694f826e832.jpg', '7.5', 'IELTS Intermediate', 1, 2),
('Lê Hoàng Nam', 'Đạt IELTS 7.0', 'Từ 5.0 lên 7.0 sau 3 tháng', '/frontend/assets/images/uploads/achievements/z7493567781789_dbc17e90a1be236b8696e5ab3e1ef6a9.jpg', '7.0', 'IELTS Intensive', 1, 3),
('Phạm Thị Mai', 'Đạt IELTS 8.5', 'Top học viên xuất sắc', '/frontend/assets/images/uploads/achievements/z7493567786884_67274602c06c663241bba3f9b90d2538.jpg', '8.5', 'IELTS Advanced', 1, 4),
('Võ Thanh Tùng', 'Đạt IELTS 7.5', 'Học bổng du học Úc', '/frontend/assets/images/uploads/achievements/z7493567793805_c98503c9314ba60bc539b1f5c523a99b.jpg', '7.5', 'IELTS Advanced', 1, 5),
('Đặng Minh Châu', 'Đạt IELTS 8.0', 'Writing 7.5, Reading 8.5', '/frontend/assets/images/uploads/achievements/z7493567802610_5a542d970779a3076fc66f734118fe9f.jpg', '8.0', 'IELTS Advanced', 1, 6),
('Ngô Thị Lan', 'Đạt IELTS 7.0', 'Đủ điều kiện du học Nhật', '/frontend/assets/images/uploads/achievements/z7493567809583_1e14b4b20f80d941c2218a42864a508a.jpg', '7.0', 'IELTS Intermediate', 1, 7),
('Bùi Văn Đức', 'Đạt IELTS 7.5', 'Từ 4.5 lên 7.5 sau 6 tháng', '/frontend/assets/images/uploads/achievements/z7493567815868_02b48630927718db6c96c49f5da1384d.jpg', '7.5', 'IELTS Foundation', 1, 8),
('Hoàng Thị Yến', 'Đạt IELTS 8.0', 'Học viên trẻ nhất đạt 8.0', '/frontend/assets/images/uploads/achievements/z7493567824712_0c170b1359474b8e82efd19b90bfb687.jpg', '8.0', 'IELTS Advanced', 1, 9),
('Lý Minh Khoa', 'Đạt IELTS 7.0', 'Vừa học vừa làm vẫn đạt', '/frontend/assets/images/uploads/achievements/z7493567828682_8ca506dcad6dfe1c5ce43f88d423a541.jpg', '7.0', 'IELTS Online', 1, 10),
('Trịnh Ngọc Hân', 'Đạt IELTS 7.5', 'Speaking 8.0', '/frontend/assets/images/uploads/achievements/z7493567837102_dd515ee13466f826ee40c2600f83160c.jpg', '7.5', 'IELTS Intensive', 1, 11),
('Phan Văn Hùng', 'Đạt IELTS 8.0', 'Listening 9.0', '/frontend/assets/images/uploads/achievements/z7493567846110_31e6941e65399efdf0bae1fade6058e8.jpg', '8.0', 'IELTS Advanced', 1, 12),
('Đỗ Thị Nhung', 'Đạt IELTS 7.0', 'Đủ điều kiện du học Anh', '/frontend/assets/images/uploads/achievements/z7493567852370_0568007bf9eae009d5f3d4e67560ff94.jpg', '7.0', 'IELTS Intermediate', 1, 13),
('Vũ Quang Minh', 'Đạt IELTS 7.5', 'Cải thiện 2.0 band', '/frontend/assets/images/uploads/achievements/z7493567858634_61629547135e7d77752445c78d26f3f0.jpg', '7.5', 'IELTS Intensive', 1, 14),
('Nguyễn Thị Hương', 'Đạt IELTS 8.5', 'Top 1% toàn quốc', '/frontend/assets/images/uploads/achievements/z7493567865426_dc0eadf236ecc6a40d1cdeea955f0b2a.jpg', '8.5', 'IELTS Advanced', 1, 15),
('Lê Anh Tuấn', 'Đạt IELTS 7.0', 'Nỗ lực được đền đáp', '/frontend/assets/images/uploads/achievements/z7493567874825_4e3bcccf07dc06c0d3d0b28f16504210.jpg', '7.0', 'IELTS Foundation', 1, 16),
('Trần Văn Bình', 'Đạt IELTS 7.5', 'Reading 8.0', '/frontend/assets/images/uploads/achievements/z7493567879736_166a4c525f3ea82a5351fe767d149fab.jpg', '7.5', 'IELTS Intensive', 1, 17),
('Phạm Ngọc Linh', 'Đạt IELTS 8.0', 'Học bổng ĐH Melbourne', '/frontend/assets/images/uploads/achievements/z7493567887953_8e825436635dfa0ed8037cdf2c7c0d62.jpg', '8.0', 'IELTS Advanced', 1, 18),
('Hoàng Văn Sơn', 'Đạt IELTS 7.0', 'Chinh phục ở tuổi 35', '/frontend/assets/images/uploads/achievements/z7493567894533_d260adcc7e569fb5b2a2ff86b92b32cb.jpg', '7.0', 'IELTS 1-1', 1, 19),
('Đặng Thị Thu', 'Đạt IELTS 7.5', 'Mẹ bỉm sữa vẫn đạt 7.5', '/frontend/assets/images/uploads/achievements/z7493567901741_0e989321f3f6bdaa6ebe8ccce75732ff.jpg', '7.5', 'IELTS Online', 1, 20);

-- =============================================
-- PHẦN 10: DỮ LIỆU MẪU - TEACHER_REVIEWS
-- =============================================
INSERT INTO `teacher_reviews` (`reviewer_name`, `reviewer_avatar`, `reviewer_info`, `rating`, `comment`, `is_approved`) VALUES
('Nguyễn Hoàng', 'NH', 'Học viên lớp Speaking', 5, 'Cô Hà dạy Speaking rất chi tiết và nhiệt tình. Nhờ cô mà em tự tin hơn rất nhiều khi giao tiếp tiếng Anh. Em đã đạt 7.5 Speaking!', 1),
('Trần Linh', 'TL', 'Học viên lớp Advanced', 5, 'Thầy Đức giảng bài rất dễ hiểu, có nhiều ví dụ thực tế. Writing của mình từ 5.5 lên 7.0 chỉ sau 2 tháng học.', 1),
('Phạm Anh', 'PA', 'Học viên lớp Intermediate', 5, 'Cô Mai chấm Writing rất kỹ, giải thích rõ ràng từng lỗi sai. Sau khóa học, mình cảm thấy tự tin hơn rất nhiều khi viết essay.', 1),
('Lê Văn Minh', 'LM', 'Học viên IELTS Foundation', 5, 'Thầy Hoàng dạy Listening cực kỳ hay! Kỹ thuật note-taking giúp mình từ 5.0 lên 7.0 Listening.', 1),
('Hoàng Thị Nga', 'HN', 'Học viên lớp IELTS 1-1', 5, 'Học 1 kèm 1 với cô Hà thật sự hiệu quả. Lộ trình được thiết kế riêng cho mình, tiến bộ rõ rệt từng tuần.', 1);

-- =============================================
-- PHẦN 11: DỮ LIỆU MẪU - COURSE_FEE_ITEMS
-- =============================================
INSERT INTO `course_fee_items` (`category`, `level`, `curriculum`, `duration`, `fee`, `is_highlight`, `display_order`) VALUES
-- Tiểu học
('tieuhoc', 'Lớp 1', 'Family and Friends 1', '9 tháng', '600.000đ/tháng', 0, 1),
('tieuhoc', 'Lớp 2', 'Family and Friends 2', '9 tháng', '650.000đ/tháng', 0, 2),
('tieuhoc', 'Lớp 3', 'Family and Friends 3', '9 tháng', '700.000đ/tháng', 0, 3),
('tieuhoc', 'Lớp 4', 'Family and Friends 4', '9 tháng', '750.000đ/tháng', 0, 4),
('tieuhoc', 'Lớp 5', 'Family and Friends 5', '9 tháng', '800.000đ/tháng', 0, 5),
('tieuhoc', 'Tiểu học Nâng cao', 'Cambridge Starters', '9 tháng', '900.000đ/tháng', 1, 6),
('tieuhoc', 'Tiểu học 1 kèm 1', 'Theo yêu cầu', 'Linh hoạt', '250.000đ/giờ', 0, 7),
-- THCS
('thcs', 'Lớp 6', 'English 6', '9 tháng', '850.000đ/tháng', 0, 1),
('thcs', 'Lớp 7', 'English 7', '9 tháng', '900.000đ/tháng', 0, 2),
('thcs', 'Lớp 8', 'English 8', '9 tháng', '950.000đ/tháng', 0, 3),
('thcs', 'Lớp 9', 'English 9 + Luyện thi', '9 tháng', '1.000.000đ/tháng', 1, 4),
('thcs', 'THCS Nâng cao', 'Cambridge KET/PET', '9 tháng', '1.100.000đ/tháng', 1, 5),
('thcs', 'Luyện thi Chuyên Anh', 'Đề chuyên các tỉnh', '6 tháng', '1.500.000đ/tháng', 1, 6),
('thcs', 'THCS 1 kèm 1', 'Theo yêu cầu', 'Linh hoạt', '300.000đ/giờ', 0, 7),
-- IELTS
('ielts', 'IELTS Foundation', 'Cambridge IELTS', '3 tháng', '3.500.000đ/khóa', 0, 1),
('ielts', 'IELTS Intermediate', 'Cambridge + Giáo trình riêng', '3 tháng', '4.500.000đ/khóa', 0, 2),
('ielts', 'IELTS Advanced', 'Cambridge 15-19 + Strategies', '2 tháng', '5.500.000đ/khóa', 1, 3),
('ielts', 'IELTS Intensive', 'Luyện đề chuyên sâu', '1.5 tháng', '6.000.000đ/khóa', 1, 4),
('ielts', 'IELTS 1 kèm 1', 'Cá nhân hóa 100%', 'Linh hoạt', '500.000đ/giờ', 0, 5),
('ielts', 'IELTS Online', 'Tài liệu điện tử', '3 tháng', '3.000.000đ/khóa', 0, 6),
('ielts', 'IELTS Writing', '200+ Essays mẫu', '1.5 tháng', '4.000.000đ/khóa', 0, 7);

-- =============================================
-- PHẦN 12: DỮ LIỆU MẪU - SITE_SETTINGS
-- =============================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'Trung tâm Ngoại ngữ Giáo dục Anh văn Hải Âu', 'text', 'Tên trung tâm'),
('site_description', 'Trung tâm đào tạo IELTS uy tín với đội ngũ giảng viên 8.0+ và phương pháp giảng dạy hiệu quả được chứng minh.', 'text', 'Mô tả trung tâm'),
('site_slogan', 'Chinh phục IELTS cùng Hải Âu', 'text', 'Slogan'),
('contact_email', 'haiauenglish@gmail.com', 'text', 'Email liên hệ'),
('contact_phone', '0931 828 960', 'text', 'Số điện thoại hotline'),
('zalo_phone', '0931828960', 'text', 'Số Zalo'),
('contact_address', '14/2A Trương Phước Phan, Phường Bình Trị Đông, TP.HCM', 'text', 'Địa chỉ trung tâm'),
('facebook_url', 'https://www.facebook.com/AnhNguHaiAu', 'text', 'Facebook page'),
('working_hours', 'Thứ 2 - Chủ nhật: 8:00 - 21:00', 'text', 'Giờ làm việc'),
('academic_year', '2025-2026', 'text', 'Năm học hiện tại'),
('map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.6503!2d106.6034!3d10.7628!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDQ1JzQ2LjEiTiAxMDbCsDM2JzEyLjIiRQ!5e0!3m2!1svi!2s!4v1', 'text', 'Google Maps embed URL'),
('footer_copyright', 'Hải Âu English', 'text', 'Footer copyright text');

-- =============================================
-- PHẦN 13: DỮ LIỆU MẪU - SITE_CONTENT (Nội dung các trang)
-- =============================================

-- ========== TRANG CHỦ (home) ==========
INSERT INTO `site_content` (`page`, `section`, `content_key`, `content_value`, `content_type`) VALUES
-- Hero Section
('home', 'hero', 'title', 'Chinh phục IELTS', 'text'),
('home', 'hero', 'title_highlight', '8.0+', 'text'),
('home', 'hero', 'description', 'Phương pháp học tập hiệu quả với đội ngũ giảng viên chứng chỉ 8.0+, cam kết đầu ra và học lại miễn phí nếu không đạt mục tiêu.', 'text'),
('home', 'hero', 'cta_primary', 'Đăng ký học thử miễn phí', 'text'),
('home', 'hero', 'cta_secondary', 'Xem khóa học', 'text'),
('home', 'hero', 'stat_number', '1000+', 'text'),
('home', 'hero', 'stat_label', 'Học viên đạt 7.0+', 'text'),
('home', 'hero', 'image1', '/frontend/assets/images/places/z7459977810848_5e453152d0061eb2d753a253cbb33926.jpg', 'image'),
('home', 'hero', 'image2', '', 'image'),
('home', 'hero', 'image3', '', 'image'),
-- About Section
('home', 'about', 'title', 'Về Hải Âu English', 'text'),
('home', 'about', 'description', 'Trung tâm đào tạo IELTS hàng đầu với phương pháp giảng dạy độc quyền và đội ngũ giảng viên chất lượng cao', 'text'),
-- Stats Section
('home', 'stats', 'stat1_number', '5000+', 'text'),
('home', 'stats', 'stat1_label', 'Học viên đã tin tưởng', 'text'),
('home', 'stats', 'stat2_number', '98%', 'text'),
('home', 'stats', 'stat2_label', 'Tỷ lệ đạt mục tiêu', 'text'),
('home', 'stats', 'stat3_number', '50+', 'text'),
('home', 'stats', 'stat3_label', 'Giảng viên 8.0+', 'text'),
('home', 'stats', 'stat4_number', '10+', 'text'),
('home', 'stats', 'stat4_label', 'Năm kinh nghiệm', 'text'),
-- Why Choose Us Section
('home', 'why_choose', 'title', 'Vì sao chọn chúng tôi?', 'text'),
('home', 'why_choose', 'subtitle', 'Những lợi ích vượt trội khi học tại Hải Âu English', 'text'),
('home', 'why_choose', 'item1_title', 'Giáo trình độc quyền', 'text'),
('home', 'why_choose', 'item1_desc', 'Tài liệu học tập được biên soạn bởi đội ngũ giảng viên 8.5+ với kinh nghiệm lâu năm', 'text'),
('home', 'why_choose', 'item2_title', 'Lớp học nhỏ', 'text'),
('home', 'why_choose', 'item2_desc', 'Tối đa 8-10 học viên/lớp để đảm bảo chất lượng giảng dạy và chăm sóc cá nhân', 'text'),
('home', 'why_choose', 'item3_title', 'Cam kết đầu ra', 'text'),
('home', 'why_choose', 'item3_desc', 'Cam kết đầu ra rõ ràng, học lại miễn phí nếu không đạt mục tiêu', 'text'),
('home', 'why_choose', 'item4_title', 'Lộ trình cá nhân hóa', 'text'),
('home', 'why_choose', 'item4_desc', 'Xây dựng lộ trình học tập riêng phù hợp với trình độ và mục tiêu của từng học viên', 'text'),
('home', 'why_choose', 'item5_title', 'Học liệu đa dạng', 'text'),
('home', 'why_choose', 'item5_desc', 'Tài liệu phong phú từ sách giáo trình đến video bài giảng và bài tập online', 'text'),
('home', 'why_choose', 'item6_title', 'Hỗ trợ 24/7', 'text'),
('home', 'why_choose', 'item6_desc', 'Đội ngũ hỗ trợ học tập và giải đáp thắc mắc 24/7 qua nhiều kênh', 'text'),

-- ========== TRANG GIỚI THIỆU (about) ==========
('about', 'hero', 'title', 'Về Hải Âu English', 'text'),
('about', 'hero', 'subtitle', 'Trung tâm đào tạo IELTS hàng đầu với hơn 10 năm kinh nghiệm', 'text'),
('about', 'story', 'title', 'Câu chuyện của chúng tôi', 'text'),
('about', 'story', 'paragraph1', 'Hải Âu English được thành lập năm 2016 với sứ mệnh giúp học viên Việt Nam chinh phục chứng chỉ IELTS một cách hiệu quả và bền vững. Chúng tôi tin rằng mỗi học viên đều có tiềm năng đạt được mục tiêu của mình với phương pháp học tập phù hợp.', 'text'),
('about', 'story', 'paragraph2', 'Qua hơn 10 năm hoạt động, chúng tôi đã đào tạo hơn 5000+ học viên thành công với tỷ lệ đạt mục tiêu 98%. Đội ngũ giảng viên của chúng tôi đều có chứng chỉ IELTS 8.0+ và nhiều năm kinh nghiệm giảng dạy.', 'text'),
('about', 'story', 'paragraph3', 'Chúng tôi không ngừng cải tiến phương pháp giảng dạy, cập nhật tài liệu và áp dụng công nghệ hiện đại để mang đến trải nghiệm học tập tốt nhất cho học viên.', 'text'),
('about', 'mission', 'title', 'Sứ mệnh', 'text'),
('about', 'mission', 'description', 'Giúp mỗi học viên tự tin chinh phục IELTS và mở ra cơ hội học tập, làm việc quốc tế thông qua phương pháp giảng dạy hiệu quả, đội ngũ giảng viên chất lượng cao và môi trường học tập chuyên nghiệp.', 'text'),
('about', 'vision', 'title', 'Tầm nhìn', 'text'),
('about', 'vision', 'description', 'Trở thành trung tâm đào tạo IELTS số 1 Việt Nam, được công nhận quốc tế với chất lượng giảng dạy xuất sắc, đóng góp vào việc nâng cao trình độ tiếng Anh của người Việt và kết nối họ với thế giới.', 'text'),
('about', 'facilities', 'title', 'Cơ sở vật chất', 'text'),
('about', 'facilities', 'subtitle', 'Không gian học tập hiện đại và thoải mái', 'text'),

-- ========== TRANG LIÊN HỆ (contact) ==========
('contact', 'hero', 'title', 'Liên hệ với chúng tôi', 'text'),
('contact', 'hero', 'subtitle', 'Chúng tôi sẵn sàng tư vấn và hỗ trợ bạn 24/7', 'text'),
('contact', 'form', 'title', 'ĐĂNG KÝ HỌC/TƯ VẤN', 'text'),
('contact', 'form', 'subtitle', 'Điền thông tin và chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ', 'text'),
('contact', 'info', 'address', '14/2A Trương Phước Phan, Phường Bình Trị Đông, TP.HCM', 'text'),
('contact', 'info', 'phone', '0931 828 960', 'text'),
('contact', 'info', 'email', 'haiauenglish@gmail.com', 'text'),
('contact', 'info', 'working_hours', 'Thứ 2 - Chủ nhật: 8:00 - 21:00', 'text'),

-- ========== TRANG KHÓA HỌC (courses) ==========
('courses', 'hero', 'title', 'Chương trình đào tạo', 'text'),
('courses', 'hero', 'subtitle', 'Lựa chọn khóa học phù hợp với độ tuổi và trình độ của bạn', 'text'),
('courses', 'filter', 'all', 'Tất cả khóa học', 'text'),
('courses', 'filter', 'tieuhoc', 'Tiểu học', 'text'),
('courses', 'filter', 'thcs', 'THCS', 'text'),
('courses', 'filter', 'ielts', 'IELTS', 'text'),
('courses', 'sections', 'tieuhoc_title', '📚 CHƯƠNG TRÌNH TIẾNG ANH CẤP TIỂU HỌC', 'text'),
('courses', 'sections', 'thcs_title', '📖 CHƯƠNG TRÌNH TIẾNG ANH CẤP THCS', 'text'),
('courses', 'sections', 'ielts_title', '🎯 CHƯƠNG TRÌNH IELTS VÀ LT IELTS', 'text'),

-- ========== TRANG GIẢNG VIÊN (teachers) ==========
('teachers', 'hero', 'title', 'Đội ngũ giảng viên', 'text'),
('teachers', 'hero', 'subtitle', 'Giảng viên chứng chỉ 8.0+ với nhiều năm kinh nghiệm giảng dạy', 'text'),
('teachers', 'stats', 'stat1_number', '50+', 'text'),
('teachers', 'stats', 'stat1_label', 'Giảng viên', 'text'),
('teachers', 'stats', 'stat2_number', '8.5+', 'text'),
('teachers', 'stats', 'stat2_label', 'Điểm TB IELTS', 'text'),
('teachers', 'stats', 'stat3_number', '10+', 'text'),
('teachers', 'stats', 'stat3_label', 'Năm kinh nghiệm', 'text'),
('teachers', 'stats', 'stat4_number', '100%', 'text'),
('teachers', 'stats', 'stat4_label', 'Được đào tạo', 'text'),
('teachers', 'featured', 'title', 'Giảng viên nổi bật', 'text'),
('teachers', 'featured', 'subtitle', 'Những giảng viên xuất sắc của Hải Âu English', 'text'),
('teachers', 'qualifications', 'title', 'Tiêu chuẩn giảng viên', 'text'),
('teachers', 'qualifications', 'subtitle', 'Chúng tôi đặt ra những tiêu chuẩn cao cho đội ngũ giảng viên', 'text'),
('teachers', 'qualifications', 'qual1_title', 'Chứng chỉ IELTS 8.0+', 'text'),
('teachers', 'qualifications', 'qual1_desc', 'Tất cả giảng viên đều có chứng chỉ IELTS 8.0 trở lên, đảm bảo trình độ tiếng Anh xuất sắc', 'text'),
('teachers', 'qualifications', 'qual2_title', 'Kinh nghiệm giảng dạy', 'text'),
('teachers', 'qualifications', 'qual2_desc', 'Tối thiểu 3 năm kinh nghiệm giảng dạy IELTS với hồ sơ học viên thành công rõ ràng', 'text'),
('teachers', 'qualifications', 'qual3_title', 'Đào tạo chuyên sâu', 'text'),
('teachers', 'qualifications', 'qual3_desc', 'Được đào tạo về phương pháp giảng dạy hiện đại và kỹ năng sư phạm chuyên nghiệp', 'text'),
('teachers', 'qualifications', 'qual4_title', 'Kỹ năng giao tiếp', 'text'),
('teachers', 'qualifications', 'qual4_desc', 'Khả năng truyền đạt kiến thức hiệu quả, tạo động lực và kết nối với học viên', 'text'),
('teachers', 'qualifications', 'qual5_title', 'Cập nhật liên tục', 'text'),
('teachers', 'qualifications', 'qual5_desc', 'Thường xuyên cập nhật xu hướng thi, đề thi mới và phương pháp giảng dạy hiện đại', 'text'),
('teachers', 'qualifications', 'qual6_title', 'Tâm huyết với nghề', 'text'),
('teachers', 'qualifications', 'qual6_desc', 'Yêu thích giảng dạy, luôn đặt sự thành công của học viên lên hàng đầu', 'text'),
('teachers', 'testimonials', 'title', 'Học viên nói gì về giảng viên', 'text'),
('teachers', 'testimonials', 'subtitle', 'Đánh giá chân thực từ học viên về chất lượng giảng dạy', 'text'),
('teachers', 'cta', 'title', 'Học với đội ngũ giảng viên xuất sắc', 'text'),
('teachers', 'cta', 'subtitle', 'Đăng ký ngay để được tư vấn và sắp xếp lớp học phù hợp', 'text');

-- =============================================
-- KẾT THÚC - Kiểm tra kết quả
-- =============================================
SELECT 'TỔNG KẾT DỮ LIỆU ĐÃ TẠO:' AS '';
SELECT 'users' AS 'Bảng', COUNT(*) AS 'Số dòng' FROM users
UNION ALL SELECT 'courses', COUNT(*) FROM courses
UNION ALL SELECT 'teachers', COUNT(*) FROM teachers
UNION ALL SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL SELECT 'student_achievements', COUNT(*) FROM student_achievements
UNION ALL SELECT 'teacher_reviews', COUNT(*) FROM teacher_reviews
UNION ALL SELECT 'course_fee_items', COUNT(*) FROM course_fee_items
UNION ALL SELECT 'site_content', COUNT(*) FROM site_content
UNION ALL SELECT 'site_settings', COUNT(*) FROM site_settings;

SELECT '✅ Database đã được tạo thành công!' AS 'Trạng thái';
