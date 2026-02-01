-- Cập nhật database cho Hải Âu English
-- Bao gồm: users (với role), courses, enrollments, scores
USE `hai_au_english`;

-- =============================================
-- BẢNG USERS (mở rộng với role và thông tin profile)
-- =============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
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
-- BẢNG COURSES (khóa học)
-- =============================================
DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `level` ENUM('beginner', 'intermediate', 'advanced', 'all') DEFAULT 'beginner',
  `duration` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(12,0) DEFAULT 0,
  `price_unit` VARCHAR(50) DEFAULT '/khóa',
  `category` ENUM('group', 'private', 'online') DEFAULT 'group',
  `badge` VARCHAR(50) DEFAULT NULL,
  `badge_type` VARCHAR(50) DEFAULT NULL,
  `features` JSON DEFAULT NULL,
  `target` VARCHAR(255) DEFAULT NULL,
  `total_sessions` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- BẢNG ENROLLMENTS (đăng ký khóa học)
-- =============================================
DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE IF NOT EXISTS `enrollments` (
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
-- BẢNG SCORES (điểm số)
-- =============================================
DROP TABLE IF EXISTS `scores`;
CREATE TABLE IF NOT EXISTS `scores` (
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
-- BẢNG FEEDBACK (nhận xét từ giảng viên)
-- =============================================
DROP TABLE IF EXISTS `feedback`;
CREATE TABLE IF NOT EXISTS `feedback` (
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
-- BẢNG TEACHERS (giữ nguyên)
-- =============================================
DROP TABLE IF EXISTS `teachers`;
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `ielts_score` DECIMAL(2,1) DEFAULT NULL,
  `experience_years` INT DEFAULT 0,
  `students_count` INT DEFAULT 0,
  `rating` DECIMAL(2,1) DEFAULT 0,
  `specialties` JSON DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DỮ LIỆU MẪU CHO USERS
-- =============================================
INSERT INTO `users` (`fullname`, `email`, `password`, `phone`, `role`, `date_of_birth`, `gender`) VALUES
('Admin Hải Âu', 'admin@haiau.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 'admin', '1990-01-15', 'male'),
('Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user', '2000-05-20', 'male'),
('Trần Thị B', 'tranthib@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'user', '2001-08-10', 'female'),
('Lê Văn C', 'levanc@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0934567890', 'user', '1999-12-25', 'male');
-- Mật khẩu mặc định: password

-- =============================================
-- DỮ LIỆU MẪU CHO COURSES
-- =============================================
INSERT INTO `courses` (`name`, `description`, `image_url`, `level`, `duration`, `price`, `price_unit`, `category`, `badge`, `badge_type`, `features`, `target`, `total_sessions`) VALUES
('IELTS Foundation', 'Khóa học nền tảng dành cho người mới bắt đầu, mục tiêu đạt 5.0-6.0 IELTS', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400&h=250&fit=crop', 'beginner', '3 tháng', 12000000, '/khóa', 'group', 'Phổ biến', NULL, '["36 buổi học (72 giờ)", "Lớp 8-10 học viên", "Giáo trình độc quyền", "Cam kết đầu ra 5.0-6.0"]', '5.0-6.0', 36),
('IELTS Intermediate', 'Nâng cao kỹ năng 4 kỹ năng, mục tiêu đạt 6.5-7.0 IELTS', 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&h=250&fit=crop', 'intermediate', '3 tháng', 14500000, '/khóa', 'group', NULL, NULL, '["40 buổi học (80 giờ)", "Lớp 8-10 học viên", "Luyện đề Cambridge", "Cam kết đầu ra 6.5-7.0"]', '6.5-7.0', 40),
('IELTS Advanced', 'Hoàn thiện kỹ năng và chiến thuật thi, mục tiêu đạt 7.5-8.5 IELTS', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=400&h=250&fit=crop', 'advanced', '2 tháng', 18000000, '/khóa', 'group', 'Premium', 'badge-premium', '["32 buổi học (64 giờ)", "Lớp 6-8 học viên", "Chấm chữa Speaking/Writing", "Cam kết đầu ra 7.5-8.5"]', '7.5-8.5', 32),
('IELTS 1-1 Cá nhân', 'Học 1 kèm 1 với giảng viên 8.5+, lộ trình 100% cá nhân hóa', 'https://images.unsplash.com/photo-1491841651911-c44c30c34548?w=400&h=250&fit=crop', 'all', 'Linh hoạt', 800000, '/giờ', 'private', 'VIP', 'badge-vip', '["Lịch học linh hoạt", "Học 1 kèm 1", "Lộ trình cá nhân hóa 100%", "Giảng viên 8.5+"]', 'Mọi trình độ', 20),
('IELTS Online', 'Học trực tuyến với giảng viên qua Zoom, tiết kiệm thời gian di chuyển', 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=400&h=250&fit=crop', 'all', '3 tháng', 9500000, '/khóa', 'online', NULL, NULL, '["36 buổi học trực tuyến", "Lớp 8-12 học viên", "Tài liệu điện tử miễn phí", "Học mọi lúc mọi nơi"]', 'Mọi trình độ', 36),
('IELTS Writing Intensive', 'Khóa học chuyên sâu về kỹ năng Writing, luyện Task 1 & Task 2', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=400&h=250&fit=crop', 'intermediate', '1.5 tháng', 8000000, '/khóa', 'group', 'Mới', NULL, '["24 buổi học (48 giờ)", "Lớp 6-8 học viên", "Chấm bài chi tiết", "200+ mẫu essays"]', '6.5+ Writing', 24);

-- =============================================
-- DỮ LIỆU MẪU CHO ENROLLMENTS
-- =============================================
INSERT INTO `enrollments` (`user_id`, `course_id`, `academic_year`, `semester`, `start_date`, `end_date`, `status`, `progress`) VALUES
(2, 1, '2025-2026', 'Học kỳ 1', '2025-09-01', '2025-12-01', 'completed', 100),
(2, 2, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-04-10', 'active', 35),
(3, 1, '2025-2026', 'Học kỳ 1', '2025-09-01', '2025-12-01', 'completed', 100),
(3, 3, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-03-10', 'active', 20),
(4, 5, '2025-2026', 'Học kỳ 1', '2025-10-01', '2026-01-01', 'active', 75),
-- Admin enrollment for testing
(1, 2, '2025-2026', 'Học kỳ 2', '2026-01-10', '2026-04-10', 'active', 50);

-- =============================================
-- DỮ LIỆU MẪU CHO SCORES
-- =============================================
INSERT INTO `scores` (`enrollment_id`, `user_id`, `listening`, `reading`, `writing`, `speaking`, `overall`, `test_date`, `test_type`, `notes`) VALUES
(1, 2, 5.5, 5.0, 5.0, 5.5, 5.5, '2025-10-15', 'midterm', 'Cần cải thiện Reading'),
(1, 2, 6.0, 5.5, 5.5, 6.0, 5.5, '2025-12-01', 'final', 'Đạt mục tiêu khóa học'),
(2, 2, 6.0, 5.5, 5.5, 6.0, 5.5, '2026-01-15', 'placement', 'Bài test đầu vào'),
(3, 3, 5.0, 5.5, 5.0, 5.0, 5.0, '2025-10-15', 'midterm', 'Cần luyện thêm Speaking'),
(3, 3, 5.5, 6.0, 5.5, 5.5, 5.5, '2025-12-01', 'final', 'Tiến bộ tốt'),
-- Admin scores for testing
(6, 1, 6.5, 6.0, 6.0, 6.5, 6.5, '2026-01-20', 'placement', 'Bài test đầu vào'),
(6, 1, 7.0, 6.5, 6.5, 7.0, 7.0, '2026-02-15', 'midterm', 'Tiến bộ tốt');

-- =============================================
-- DỮ LIỆU MẪU CHO FEEDBACK
-- =============================================
INSERT INTO `feedback` (`enrollment_id`, `user_id`, `teacher_id`, `content`, `rating`, `feedback_date`) VALUES
(1, 2, 1, 'Học viên chăm chỉ, tiến bộ nhanh ở kỹ năng Speaking. Cần luyện thêm Reading để cân bằng 4 kỹ năng.', 4, '2025-12-01'),
(2, 2, 2, 'Bước đầu làm quen với khóa Intermediate. Có nền tảng tốt từ khóa Foundation.', 4, '2026-01-20'),
(3, 3, 1, 'Học viên có tiềm năng, cần tự tin hơn khi Speaking. Grammar tốt.', 4, '2025-12-01'),
-- Admin feedback for testing
(6, 1, 2, 'Học viên có khả năng tiếp thu nhanh. Cần chú ý phần Writing Task 2 nhiều hơn.', 5, '2026-02-01');

-- =============================================
-- DỮ LIỆU MẪU CHO TEACHERS
-- =============================================
INSERT INTO `teachers` (`name`, `title`, `description`, `image_url`, `ielts_score`, `experience_years`, `students_count`, `rating`, `specialties`, `is_featured`) VALUES
('Ms. Nguyễn Thu Hà', 'Trưởng bộ môn Speaking', '10 năm kinh nghiệm giảng dạy IELTS, chuyên gia về kỹ năng Speaking. Từng làm việc tại British Council.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=300&fit=crop', 8.5, 10, 500, 4.9, '["Speaking", "Writing"]', 1),
('Mr. Trần Minh Đức', 'Giám đốc học thuật', '12 năm kinh nghiệm, thạc sĩ ngôn ngữ học tại University of Cambridge. Chuyên gia đào tạo giảng viên IELTS.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop', 9.0, 12, 800, 5.0, '["Writing", "Reading"]', 1),
('Ms. Lê Thị Mai', 'Chuyên gia Writing', '8 năm kinh nghiệm, tốt nghiệp xuất sắc ĐH Ngoại Ngữ Hà Nội. Chuyên sâu về IELTS Academic Writing.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=300&fit=crop', 8.5, 8, 600, 4.8, '["Writing", "Grammar"]', 1),
('Mr. Phạm Văn Long', 'Giảng viên Listening', '7 năm kinh nghiệm, từng học tập và làm việc tại Australia. Chuyên gia kỹ năng Listening và phát âm chuẩn.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop', 8.0, 7, 450, 4.9, '["Listening", "Pronunciation"]', 1),
('Ms. Võ Thị Hương', 'Giảng viên Reading', '9 năm kinh nghiệm, thạc sĩ giáo dục tại UK. Chuyên về các chiến lược đọc hiểu nâng cao.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=300&fit=crop', 8.5, 9, 550, 4.9, '["Reading", "Vocabulary"]', 1),
('Mr. Hoàng Anh Tuấn', 'Giảng viên Foundation', '6 năm kinh nghiệm, chuyên đào tạo học viên mới bắt đầu. Phương pháp giảng dạy sinh động, dễ hiểu.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop', 8.0, 6, 400, 4.8, '["Foundation", "Speaking"]', 1);

-- =============================================
-- BẢNG SCHEDULES (thời khóa biểu)
-- =============================================
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `teacher_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `period` INT DEFAULT NULL,
  `period_count` INT DEFAULT 1,
  `session` ENUM('morning', 'afternoon', 'evening') DEFAULT NULL,
  `course_code` VARCHAR(50) DEFAULT NULL,
  `group_name` VARCHAR(100) DEFAULT NULL,
  `class_name` VARCHAR(100) DEFAULT NULL,
  `room` VARCHAR(100) DEFAULT NULL,
  `is_online` TINYINT(1) DEFAULT 0,
  `meeting_link` VARCHAR(500) DEFAULT NULL,
  `teacher_email` VARCHAR(255) DEFAULT NULL,
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
-- DỮ LIỆU MẪU CHO SCHEDULES
-- =============================================
INSERT INTO `schedules` (`enrollment_id`, `teacher_id`, `title`, `day_of_week`, `start_time`, `end_time`, `period`, `period_count`, `session`, `course_code`, `group_name`, `room`, `is_online`, `color`) VALUES
-- Lịch cho Nguyễn Văn A - IELTS Intermediate (enrollment_id = 2)
(2, 2, 'IELTS Intermediate - Writing', 'monday', '18:00:00', '19:35:00', 13, 2, 'evening', 'IELTS-INT', 'Nhóm A1', 'Phòng 201', 0, '#1e40af'),
(2, 2, 'IELTS Intermediate - Reading', 'wednesday', '18:00:00', '19:35:00', 13, 2, 'evening', 'IELTS-INT', 'Nhóm A1', 'Phòng 201', 0, '#10b981'),
(2, 1, 'IELTS Intermediate - Speaking', 'friday', '18:00:00', '19:35:00', 13, 2, 'evening', 'IELTS-INT', 'Nhóm A1', 'Phòng 202', 0, '#f59e0b'),

-- Lịch cho Trần Thị B - IELTS Advanced (enrollment_id = 4)
(4, 3, 'IELTS Advanced - Writing', 'tuesday', '19:00:00', '20:30:00', 14, 2, 'evening', 'IELTS-ADV', 'Nhóm B1', 'Phòng 301', 0, '#8b5cf6'),
(4, 2, 'IELTS Advanced - Reading', 'thursday', '19:00:00', '20:30:00', 14, 2, 'evening', 'IELTS-ADV', 'Nhóm B1', 'Phòng 301', 0, '#ec4899'),
(4, 1, 'IELTS Advanced - Speaking', 'saturday', '09:00:00', '10:45:00', 3, 2, 'morning', 'IELTS-ADV', 'Nhóm B1', 'Phòng 302', 0, '#06b6d4'),

-- Lịch cho Lê Văn C - IELTS Online (enrollment_id = 5)
(5, 4, 'IELTS Online - Listening', 'monday', '20:00:00', '21:35:00', 14, 2, 'evening', 'IELTS-ONL', 'Online 1', NULL, 1, '#f43f5e'),
(5, 5, 'IELTS Online - Reading', 'wednesday', '20:00:00', '21:35:00', 14, 2, 'evening', 'IELTS-ONL', 'Online 1', NULL, 1, '#14b8a6'),
(5, 6, 'IELTS Online - Foundation', 'saturday', '14:00:00', '15:50:00', 7, 2, 'afternoon', 'IELTS-ONL', 'Online 1', NULL, 1, '#a855f7'),

-- Lịch cho Admin - IELTS Intermediate (enrollment_id = 6)
(6, 2, 'IELTS Intermediate - Writing', 'tuesday', '18:00:00', '19:35:00', 13, 2, 'evening', 'IELTS-INT', 'Nhóm Admin', 'Phòng 101', 0, '#1e40af'),
(6, 1, 'IELTS Intermediate - Reading', 'thursday', '18:00:00', '19:35:00', 13, 2, 'evening', 'IELTS-INT', 'Nhóm Admin', 'Phòng 101', 0, '#10b981'),
(6, 1, 'IELTS Intermediate - Speaking', 'saturday', '10:00:00', '11:35:00', 4, 2, 'morning', 'IELTS-INT', 'Nhóm Admin', 'Phòng 102', 0, '#f59e0b');
