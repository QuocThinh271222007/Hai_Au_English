-- =============================================
-- CẬP NHẬT BẢNG SCHEDULES 
-- Thêm các trường cho thời khóa biểu chi tiết
-- =============================================
USE `hai_au_english`;

-- Thêm cột mới cho schedules
ALTER TABLE `schedules`
ADD COLUMN IF NOT EXISTS `period` INT DEFAULT 1 COMMENT 'Tiết học (1-12)',
ADD COLUMN IF NOT EXISTS `period_count` INT DEFAULT 1 COMMENT 'Số tiết liên tiếp',
ADD COLUMN IF NOT EXISTS `session` ENUM('morning', 'afternoon', 'evening') DEFAULT 'morning' COMMENT 'Buổi học',
ADD COLUMN IF NOT EXISTS `group_name` VARCHAR(50) DEFAULT NULL COMMENT 'Tên nhóm',
ADD COLUMN IF NOT EXISTS `class_name` VARCHAR(100) DEFAULT NULL COMMENT 'Tên lớp',
ADD COLUMN IF NOT EXISTS `course_code` VARCHAR(50) DEFAULT NULL COMMENT 'Mã khóa học',
ADD COLUMN IF NOT EXISTS `teacher_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email giảng viên',
ADD COLUMN IF NOT EXISTS `academic_year` VARCHAR(20) DEFAULT '2025-2026' COMMENT 'Năm học',
ADD COLUMN IF NOT EXISTS `semester` INT DEFAULT 2 COMMENT 'Học kỳ (1, 2, 3)',
ADD COLUMN IF NOT EXISTS `start_date` DATE DEFAULT NULL COMMENT 'Ngày bắt đầu hiệu lực',
ADD COLUMN IF NOT EXISTS `end_date` DATE DEFAULT NULL COMMENT 'Ngày kết thúc hiệu lực';

-- Cập nhật dữ liệu mẫu
UPDATE `schedules` SET 
    `period` = 7, 
    `period_count` = 3,
    `session` = 'evening',
    `group_name` = '01',
    `class_name` = 'IELTS.INT.A',
    `course_code` = 'IELTS1001',
    `academic_year` = '2025-2026',
    `semester` = 2,
    `start_date` = '2026-01-06',
    `end_date` = '2026-04-30'
WHERE `enrollment_id` = 2;

UPDATE `schedules` SET 
    `period` = 8, 
    `period_count` = 3,
    `session` = 'evening',
    `group_name` = '01',
    `class_name` = 'IELTS.ADV.B',
    `course_code` = 'IELTS2001',
    `academic_year` = '2025-2026',
    `semester` = 2,
    `start_date` = '2026-01-06',
    `end_date` = '2026-04-30'
WHERE `enrollment_id` = 4;

UPDATE `schedules` SET 
    `period` = CASE 
        WHEN `start_time` < '12:00:00' THEN 1
        WHEN `start_time` < '17:00:00' THEN 5
        ELSE 8
    END,
    `period_count` = 2,
    `session` = CASE 
        WHEN `start_time` < '12:00:00' THEN 'morning'
        WHEN `start_time` < '17:00:00' THEN 'afternoon'
        ELSE 'evening'
    END,
    `group_name` = '01',
    `class_name` = 'IELTS.ONL.C',
    `course_code` = 'IELTS3001',
    `academic_year` = '2025-2026',
    `semester` = 2,
    `start_date` = '2026-01-06',
    `end_date` = '2026-04-30'
WHERE `enrollment_id` = 5;

-- Thêm dữ liệu mẫu mới với đầy đủ thông tin
DELETE FROM `schedules` WHERE `id` > 9;

INSERT INTO `schedules` (
    `enrollment_id`, `teacher_id`, `title`, `day_of_week`, `start_time`, `end_time`, 
    `room`, `is_online`, `meeting_link`, `color`, `period`, `period_count`, `session`,
    `group_name`, `class_name`, `course_code`, `teacher_email`,
    `academic_year`, `semester`, `start_date`, `end_date`
) VALUES
-- Học viên ID 2 - Nguyễn Văn A
(2, 2, 'IELTS Writing Task 1&2', 'monday', '18:00:00', '21:00:00', 'Phòng A1', 0, NULL, '#1e40af', 7, 3, 'evening', '01', 'IELTS.INT.A', 'IELTS1001', 'teacher1@haiau.edu.vn', '2025-2026', 2, '2026-01-06', '2026-04-30'),
(2, 1, 'IELTS Speaking Practice', 'wednesday', '18:00:00', '21:00:00', 'Phòng A2', 0, NULL, '#1e40af', 7, 3, 'evening', '01', 'IELTS.INT.A', 'IELTS1001', 'teacher2@haiau.edu.vn', '2025-2026', 2, '2026-01-06', '2026-04-30'),
(2, 3, 'IELTS Reading Strategies', 'friday', '18:00:00', '20:00:00', 'Phòng A1', 0, NULL, '#1e40af', 7, 2, 'evening', '01', 'IELTS.INT.A', 'IELTS1001', 'teacher3@haiau.edu.vn', '2025-2026', 2, '2026-01-06', '2026-04-30'),
(2, 4, 'IELTS Listening Skills', 'saturday', '08:00:00', '10:30:00', 'Phòng A3', 0, NULL, '#059669', 1, 3, 'morning', '01', 'IELTS.INT.A', 'IELTS1001', 'teacher4@haiau.edu.vn', '2025-2026', 2, '2026-01-06', '2026-04-30');
