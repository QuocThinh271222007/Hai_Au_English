-- =============================================
-- BẢNG SCHEDULES (thời khóa biểu)
-- Lưu lịch học cho từng enrollment
-- =============================================
USE `hai_au_english`;

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
  `room` VARCHAR(100) DEFAULT NULL,
  `is_online` TINYINT(1) DEFAULT 0,
  `meeting_link` VARCHAR(500) DEFAULT NULL,
  `color` VARCHAR(20) DEFAULT '#3b82f6',
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
INSERT INTO `schedules` (`enrollment_id`, `teacher_id`, `title`, `day_of_week`, `start_time`, `end_time`, `room`, `is_online`, `color`) VALUES
-- Lịch cho Nguyễn Văn A - IELTS Intermediate (enrollment_id = 2)
(2, 2, 'IELTS Intermediate - Writing', 'monday', '18:00:00', '20:00:00', 'Phòng A1', 0, '#3b82f6'),
(2, 2, 'IELTS Intermediate - Reading', 'wednesday', '18:00:00', '20:00:00', 'Phòng A1', 0, '#10b981'),
(2, 1, 'IELTS Intermediate - Speaking', 'friday', '18:00:00', '20:00:00', 'Phòng A2', 0, '#f59e0b'),

-- Lịch cho Trần Thị B - IELTS Advanced (enrollment_id = 4)
(4, 3, 'IELTS Advanced - Writing Task 1&2', 'tuesday', '19:00:00', '21:00:00', 'Phòng B1', 0, '#8b5cf6'),
(4, 2, 'IELTS Advanced - Reading Strategies', 'thursday', '19:00:00', '21:00:00', 'Phòng B1', 0, '#ec4899'),
(4, 1, 'IELTS Advanced - Speaking Practice', 'saturday', '09:00:00', '11:00:00', 'Phòng B2', 0, '#06b6d4'),

-- Lịch cho Lê Văn C - IELTS Online (enrollment_id = 5)
(5, 4, 'IELTS Online - Listening Skills', 'monday', '20:00:00', '22:00:00', NULL, 1, '#f43f5e'),
(5, 5, 'IELTS Online - Reading Practice', 'wednesday', '20:00:00', '22:00:00', NULL, 1, '#14b8a6'),
(5, 6, 'IELTS Online - Foundation Review', 'saturday', '14:00:00', '16:00:00', NULL, 1, '#a855f7');
