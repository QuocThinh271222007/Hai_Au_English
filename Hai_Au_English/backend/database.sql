-- =============================================
-- HẢI ÂU ENGLISH - DATABASE HOÀN CHỈNH
-- File SQL duy nhất - Chạy trong phpMyAdmin
-- Bao gồm: Tạo bảng + Dữ liệu mẫu
-- =============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- LƯU Ý HOSTINGER: 
-- Không cần tạo database, database đã được tạo sẵn qua Control Panel
-- Chỉ cần chọn đúng database trong phpMyAdmin rồi import file này

-- =============================================
-- 1. BẢNG CONTACTS (liên hệ)
-- =============================================
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `course` VARCHAR(100) NOT NULL,
  `level` VARCHAR(50) DEFAULT NULL,
  `message` TEXT,
  `agreement` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`email`),
  INDEX (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. BẢNG USERS
-- =============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `avatar_url` VARCHAR(500) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`email`),
  INDEX (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. BẢNG COURSES (khóa học)
-- =============================================
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `level` VARCHAR(100) DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `curriculum` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(12,0) DEFAULT 0,
  `price_unit` VARCHAR(50) DEFAULT '/tháng',
  `category` ENUM('tieuhoc', 'thcs', 'ielts') DEFAULT 'tieuhoc',
  `badge` VARCHAR(50) DEFAULT NULL,
  `badge_type` VARCHAR(50) DEFAULT NULL,
  `features` TEXT DEFAULT NULL,
  `target` VARCHAR(255) DEFAULT NULL,
  `total_sessions` INT DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. BẢNG ENROLLMENTS (đăng ký khóa học)
-- =============================================
DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `academic_year` VARCHAR(20) DEFAULT NULL,
  `semester` VARCHAR(50) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `status` ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
  `progress` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_enrollment` (`user_id`, `course_id`, `academic_year`),
  INDEX (`user_id`),
  INDEX (`course_id`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. BẢNG SCORES (điểm số)
-- =============================================
DROP TABLE IF EXISTS `scores`;
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
  `test_type` ENUM('placement', 'midterm', 'final', 'mock') DEFAULT 'mock',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`enrollment_id`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 6. BẢNG FEEDBACK (nhận xét từ giảng viên)
-- =============================================
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `content` TEXT NOT NULL,
  `rating` INT DEFAULT NULL,
  `feedback_date` DATE DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`enrollment_id`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 7. BẢNG TEACHERS (giảng viên)
-- =============================================
DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `ielts_score` DECIMAL(2,1) DEFAULT NULL,
  `experience_years` INT DEFAULT 0,
  `students_count` INT DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 0,
  `specialties` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 8. BẢNG SCHEDULES (thời khóa biểu)
-- =============================================
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room` VARCHAR(100) DEFAULT NULL,
  `is_online` TINYINT(1) DEFAULT 0,
  `meeting_link` VARCHAR(500) DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT '#1e40af',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`enrollment_id`),
  INDEX (`teacher_id`),
  INDEX (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 9. BẢNG REVIEWS (đánh giá từ học viên)
-- =============================================
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `user_name` VARCHAR(255) NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_rating` (`rating`),
  INDEX `idx_approved` (`is_approved`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 10. BẢNG STUDENT ACHIEVEMENTS (thành tích học viên)
-- =============================================
DROP TABLE IF EXISTS `student_achievements`;
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
DROP TABLE IF EXISTS `trash`;
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
-- 12. BẢNG SITE_CONTENT (nội dung trang web - Admin quản lý)
-- =============================================
DROP TABLE IF EXISTS `site_content`;
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
-- 13. BẢNG SITE_SETTINGS (cài đặt chung - Admin quản lý)
-- =============================================
DROP TABLE IF EXISTS `site_settings`;
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
-- DỮ LIỆU MẪU - USERS
-- Mật khẩu: admin123 (admin), password (user)
-- =============================================
INSERT INTO `users` (`fullname`, `email`, `password`, `phone`, `role`, `date_of_birth`, `gender`) VALUES
('Admin Hải Âu', 'admin@haiau.edu.vn', '$2y$10$5gxbzcC7TGNDH6PAeJc9BuklbXTy40nLB2p.sy2R01Ctn6bXFBQt.', '0901234567', 'admin', '1990-01-15', 'male'),
('Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user', '2000-05-20', 'male'),
('Trần Thị B', 'tranthib@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'user', '2001-08-10', 'female');

-- =============================================
-- DỮ LIỆU MẪU - COURSES
-- =============================================
INSERT INTO `courses` (`name`, `description`, `image_url`, `level`, `duration`, `price`, `price_unit`, `category`, `badge`, `features`, `target`, `total_sessions`) VALUES
('IELTS Foundation', 'Khóa học nền tảng dành cho người mới bắt đầu, mục tiêu đạt 5.0-6.0 IELTS', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=250&fit=crop', 'beginner', '3 tháng', 12000000, '/khóa', 'group', 'Phổ biến', '36 buổi học (72 giờ) Lớp 8-10 học viên Giáo trình độc quyền Cam kết đầu ra 5.0-6.0', '5.0-6.0', 36),
('IELTS Intermediate', 'Nâng cao kỹ năng 4 kỹ năng, mục tiêu đạt 6.5-7.0 IELTS', 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&h=250&fit=crop', 'intermediate', '3 tháng', 14500000, '/khóa', 'group', NULL, '40 buổi học (80 giờ) Lớp 8-10 học viên Luyện đề Cambridge Cam kết đầu ra 6.5-7.0', '6.5-7.0', 40),
('IELTS Advanced', 'Hoàn thiện kỹ năng và chiến thuật thi, mục tiêu đạt 7.5-8.5 IELTS', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=400&h=250&fit=crop', 'advanced', '2 tháng', 18000000, '/khóa', 'group', 'Premium', '32 buổi học (64 giờ) Lớp 6-8 học viên Chấm chữa Speaking/Writing Cam kết đầu ra 7.5-8.5', '7.5-8.5', 32),
('IELTS 1-1 Cá nhân', 'Học 1 kèm 1 với giảng viên 8.5+, lộ trình 100% cá nhân hóa', 'https://images.unsplash.com/photo-1491841651911-c44c30c34548?w=400&h=250&fit=crop', 'all', 'Linh hoạt', 800000, '/giờ', 'private', 'VIP', 'Lịch học linh hoạt Học 1 kèm 1 Lộ trình cá nhân hóa 100% Giảng viên 8.5+', 'Mọi trình độ', 20),
('IELTS Online', 'Học trực tuyến với giảng viên qua Zoom, tiết kiệm thời gian di chuyển', 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=400&h=250&fit=crop', 'all', '3 tháng', 9500000, '/khóa', 'online', NULL, '36 buổi học trực tuyến Lớp 8-12 học viên Tài liệu điện tử miễn phí Học mọi lúc mọi nơi', 'Mọi trình độ', 36),
('IELTS Writing Intensive', 'Khóa học chuyên sâu về kỹ năng Writing, luyện Task 1 & Task 2', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=400&h=250&fit=crop', 'intermediate', '1.5 tháng', 8000000, '/khóa', 'group', 'Mới', '24 buổi học (48 giờ) Lớp 6-8 học viên Chấm bài chi tiết 200+ mẫu essays', '6.5+ Writing', 24);

-- =============================================
-- DỮ LIỆU MẪU - TEACHERS
-- =============================================
INSERT INTO `teachers` (`name`, `title`, `description`, `image_url`, `ielts_score`, `experience_years`, `students_count`, `rating`, `specialties`, `is_featured`) VALUES
('Ms. Nguyễn Thu Hà', 'Trưởng bộ môn Speaking', 'Thạc sĩ Ngôn ngữ Anh - ĐH Ngoại ngữ Hà Nội. 10 năm kinh nghiệm giảng dạy IELTS, đặc biệt chuyên sâu Speaking.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&h=400&fit=crop', 8.5, 10, 500, 4.9, 'Speaking, Pronunciation', 1),
('Mr. Trần Minh Đức', 'Giám đốc học thuật', 'Thạc sĩ TESOL - ĐH Cambridge. 12 năm kinh nghiệm, chuyên gia Writing và Reading với phương pháp độc quyền.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop', 9.0, 12, 800, 5.0, 'Writing, Reading', 1),
('Ms. Lê Thị Mai', 'Chuyên gia Writing', 'Cử nhân Ngôn ngữ Anh - ĐH KHXH&NV. 8 năm kinh nghiệm chuyên sâu IELTS Academic Writing.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400&h=400&fit=crop', 8.5, 8, 600, 4.8, 'Writing, Grammar', 1),
('Mr. Phạm Văn Hoàng', 'Chuyên gia Listening', 'Thạc sĩ Giáo dục - ĐH Sư phạm TP.HCM. Chuyên gia luyện nghe với kỹ thuật note-taking hiệu quả.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop', 8.0, 6, 400, 4.7, 'Listening, Vocabulary', 1);

-- =============================================
-- DỮ LIỆU MẪU - ENROLLMENTS
-- =============================================
INSERT INTO `enrollments` (`user_id`, `course_id`, `academic_year`, `semester`, `start_date`, `end_date`, `status`, `progress`) VALUES
(2, 1, '2025-2026', 'Học kỳ 1', '2025-09-01', '2025-12-01', 'completed', 100),
(2, 2, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-04-10', 'active', 35),
(3, 3, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-03-10', 'active', 20);

-- =============================================
-- DỮ LIỆU MẪU - SCORES
-- =============================================
INSERT INTO `scores` (`enrollment_id`, `user_id`, `listening`, `reading`, `writing`, `speaking`, `overall`, `test_date`, `test_type`) VALUES
(1, 2, 6.0, 5.5, 5.5, 6.0, 5.5, '2025-12-01', 'final'),
(2, 2, 6.0, 5.5, 5.5, 6.0, 5.5, '2026-01-15', 'placement');

-- =============================================
-- DỮ LIỆU MẪU - SCHEDULES (THỜI KHÓA BIỂU)
-- =============================================
INSERT INTO `schedules` (`enrollment_id`, `teacher_id`, `title`, `day_of_week`, `start_time`, `end_time`, `room`, `is_online`, `color`, `is_active`) VALUES
-- Enrollment 2: Nguyễn Văn A - IELTS Intermediate (active)
(2, 1, 'IELTS Intermediate - Speaking', 'monday', '09:00:00', '11:00:00', 'P.101', 0, '#1e40af', 1),
(2, 2, 'IELTS Intermediate - Writing', 'wednesday', '09:00:00', '11:00:00', 'P.102', 0, '#059669', 1),
(2, 3, 'IELTS Intermediate - Reading', 'friday', '14:00:00', '16:00:00', 'P.101', 0, '#dc2626', 1),
-- Enrollment 3: Trần Thị B - IELTS Advanced (active)
(3, 2, 'IELTS Advanced - Writing', 'tuesday', '09:00:00', '11:00:00', 'P.201', 0, '#059669', 1),
(3, 4, 'IELTS Advanced - Listening', 'thursday', '14:00:00', '16:00:00', 'P.202', 0, '#7c3aed', 1),
(3, 1, 'IELTS Advanced - Speaking', 'saturday', '09:00:00', '11:00:00', 'P.201', 0, '#1e40af', 1);

-- =============================================
-- DỮ LIỆU MẪU - REVIEWS (20 đánh giá)
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
-- DỮ LIỆU MẪU - STUDENT ACHIEVEMENTS (20 thành tích)
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
-- KẾT THÚC - Kiểm tra kết quả
-- =============================================

-- =============================================
-- DỮ LIỆU MẪU - SITE_SETTINGS (Cài đặt hệ thống)
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
-- DỮ LIỆU MẪU - SITE_CONTENT (Nội dung các trang)
-- Đồng bộ với frontend pages (index.php, about.php, contact.php, etc.)
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
-- About Section (Về chúng tôi)
('home', 'about', 'title', 'Về Hải Âu English', 'text'),
('home', 'about', 'description', 'Trung tâm đào tạo IELTS hàng đầu với phương pháp giảng dạy độc quyền và đội ngũ giảng viên chất lượng cao', 'text'),
-- Stats Section (Thống kê)
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
-- Hero Section
('about', 'hero', 'title', 'Về Hải Âu English', 'text'),
('about', 'hero', 'subtitle', 'Trung tâm đào tạo IELTS hàng đầu với hơn 10 năm kinh nghiệm', 'text'),
-- Story Section
('about', 'story', 'title', 'Câu chuyện của chúng tôi', 'text'),
('about', 'story', 'paragraph1', 'Hải Âu English được thành lập năm 2016 với sứ mệnh giúp học viên Việt Nam chinh phục chứng chỉ IELTS một cách hiệu quả và bền vững. Chúng tôi tin rằng mỗi học viên đều có tiềm năng đạt được mục tiêu của mình với phương pháp học tập phù hợp.', 'text'),
('about', 'story', 'paragraph2', 'Qua hơn 10 năm hoạt động, chúng tôi đã đào tạo hơn 5000+ học viên thành công với tỷ lệ đạt mục tiêu 98%. Đội ngũ giảng viên của chúng tôi đều có chứng chỉ IELTS 8.0+ và nhiều năm kinh nghiệm giảng dạy.', 'text'),
('about', 'story', 'paragraph3', 'Chúng tôi không ngừng cải tiến phương pháp giảng dạy, cập nhật tài liệu và áp dụng công nghệ hiện đại để mang đến trải nghiệm học tập tốt nhất cho học viên.', 'text'),
-- Mission & Vision
('about', 'mission', 'title', 'Sứ mệnh', 'text'),
('about', 'mission', 'description', 'Giúp mỗi học viên tự tin chinh phục IELTS và mở ra cơ hội học tập, làm việc quốc tế thông qua phương pháp giảng dạy hiệu quả, đội ngũ giảng viên chất lượng cao và môi trường học tập chuyên nghiệp.', 'text'),
('about', 'vision', 'title', 'Tầm nhìn', 'text'),
('about', 'vision', 'description', 'Trở thành trung tâm đào tạo IELTS số 1 Việt Nam, được công nhận quốc tế với chất lượng giảng dạy xuất sắc, đóng góp vào việc nâng cao trình độ tiếng Anh của người Việt và kết nối họ với thế giới.', 'text'),
-- Facilities Section
('about', 'facilities', 'title', 'Cơ sở vật chất', 'text'),
('about', 'facilities', 'subtitle', 'Không gian học tập hiện đại và thoải mái', 'text'),

-- ========== TRANG LIÊN HỆ (contact) ==========
-- Hero Section
('contact', 'hero', 'title', 'Liên hệ với chúng tôi', 'text'),
('contact', 'hero', 'subtitle', 'Chúng tôi sẵn sàng tư vấn và hỗ trợ bạn 24/7', 'text'),
-- Form Section
('contact', 'form', 'title', 'ĐĂNG KÝ HỌC/TƯ VẤN', 'text'),
('contact', 'form', 'subtitle', 'Điền thông tin và chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ', 'text'),
-- Contact Info
('contact', 'info', 'address', '14/2A Trương Phước Phan, Phường Bình Trị Đông, TP.HCM', 'text'),
('contact', 'info', 'phone', '0931 828 960', 'text'),
('contact', 'info', 'email', 'haiauenglish@gmail.com', 'text'),
('contact', 'info', 'working_hours', 'Thứ 2 - Chủ nhật: 8:00 - 21:00', 'text'),

-- ========== TRANG KHÓA HỌC (courses) ==========
-- Hero Section
('courses', 'hero', 'title', 'Chương trình đào tạo', 'text'),
('courses', 'hero', 'subtitle', 'Lựa chọn khóa học phù hợp với độ tuổi và trình độ của bạn', 'text'),
-- Filter Buttons
('courses', 'filter', 'all', 'Tất cả khóa học', 'text'),
('courses', 'filter', 'tieuhoc', 'Tiểu học', 'text'),
('courses', 'filter', 'thcs', 'THCS', 'text'),
('courses', 'filter', 'ielts', 'IELTS', 'text'),
-- Section Titles
('courses', 'sections', 'tieuhoc_title', '📚 CHƯƠNG TRÌNH TIẾNG ANH CẤP TIỂU HỌC', 'text'),
('courses', 'sections', 'thcs_title', '📖 CHƯƠNG TRÌNH TIẾNG ANH CẤP THCS', 'text'),
('courses', 'sections', 'ielts_title', '🎯 CHƯƠNG TRÌNH IELTS VÀ LT IELTS', 'text'),

-- ========== TRANG GIẢNG VIÊN (teachers) ==========
-- Hero Section
('teachers', 'hero', 'title', 'Đội ngũ giảng viên', 'text'),
('teachers', 'hero', 'subtitle', 'Giảng viên chứng chỉ 8.0+ với nhiều năm kinh nghiệm giảng dạy', 'text'),
-- Stats Section
('teachers', 'stats', 'stat1_number', '50+', 'text'),
('teachers', 'stats', 'stat1_label', 'Giảng viên', 'text'),
('teachers', 'stats', 'stat2_number', '8.5+', 'text'),
('teachers', 'stats', 'stat2_label', 'Điểm TB IELTS', 'text'),
('teachers', 'stats', 'stat3_number', '10+', 'text'),
('teachers', 'stats', 'stat3_label', 'Năm kinh nghiệm', 'text'),
('teachers', 'stats', 'stat4_number', '100%', 'text'),
('teachers', 'stats', 'stat4_label', 'Được đào tạo', 'text'),
-- Featured Section
('teachers', 'featured', 'title', 'Giảng viên nổi bật', 'text'),
('teachers', 'featured', 'subtitle', 'Những giảng viên xuất sắc của Hải Âu English', 'text'),
-- Qualifications Section
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
-- Testimonials Section
('teachers', 'testimonials', 'title', 'Học viên nói gì về giảng viên', 'text'),
('teachers', 'testimonials', 'subtitle', 'Đánh giá chân thực từ học viên về chất lượng giảng dạy', 'text'),
('teachers', 'testimonials', 'review1_text', 'Cô Hà dạy Speaking rất chi tiết và nhiệt tình. Nhờ cô mà em tự tin hơn rất nhiều khi giao tiếp tiếng Anh. Em đã đạt 7.5 Speaking!', 'text'),
('teachers', 'testimonials', 'review1_avatar', 'NH', 'text'),
('teachers', 'testimonials', 'review1_name', 'Nguyễn Hoàng', 'text'),
('teachers', 'testimonials', 'review1_info', 'Học viên lớp Speaking', 'text'),
('teachers', 'testimonials', 'review2_text', 'Thầy Đức giảng bài rất dễ hiểu, có nhiều ví dụ thực tế. Writing của mình từ 5.5 lên 7.0 chỉ sau 2 tháng học.', 'text'),
('teachers', 'testimonials', 'review2_avatar', 'TL', 'text'),
('teachers', 'testimonials', 'review2_name', 'Trần Linh', 'text'),
('teachers', 'testimonials', 'review2_info', 'Học viên lớp Advanced', 'text'),
('teachers', 'testimonials', 'review3_text', 'Cô Mai chấm Writing rất kỹ, giải thích rõ ràng từng lỗi sai. Sau khóa học, mình cảm thấy tự tin hơn rất nhiều khi viết essay.', 'text'),
('teachers', 'testimonials', 'review3_avatar', 'PA', 'text'),
('teachers', 'testimonials', 'review3_name', 'Phạm Anh', 'text'),
('teachers', 'testimonials', 'review3_info', 'Học viên lớp Intermediate', 'text'),
-- CTA Section
('teachers', 'cta', 'title', 'Học với đội ngũ giảng viên xuất sắc', 'text'),
('teachers', 'cta', 'subtitle', 'Đăng ký ngay để được tư vấn và sắp xếp lớp học phù hợp', 'text');

SELECT 'users' AS 'Bảng', COUNT(*) AS 'Số dòng' FROM users
UNION ALL SELECT 'courses', COUNT(*) FROM courses
UNION ALL SELECT 'teachers', COUNT(*) FROM teachers
UNION ALL SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL SELECT 'student_achievements', COUNT(*) FROM student_achievements
UNION ALL SELECT 'site_content', COUNT(*) FROM site_content
UNION ALL SELECT 'site_settings', COUNT(*) FROM site_settings;
