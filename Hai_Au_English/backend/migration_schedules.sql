-- =============================================
-- MIGRATION: Nâng cấp hệ thống Thời khóa biểu
-- Liên kết toàn diện với Classes, Courses, Teachers
-- =============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- =============================================
-- 1. TẠO BẢNG CLASS_SCHEDULES (Thời khóa biểu theo lớp)
-- Mỗi lớp có nhiều buổi học trong tuần
-- =============================================
DROP TABLE IF EXISTS `class_schedules`;
CREATE TABLE `class_schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id` INT UNSIGNED NOT NULL COMMENT 'Lớp học',
  `course_id` INT UNSIGNED NOT NULL COMMENT 'Khóa học (denormalized for faster queries)',
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
-- 2. TẠO BẢNG SCHEDULE_EXCEPTIONS (Lịch nghỉ/thay đổi)
-- Để quản lý ngày nghỉ lễ, thay đổi lịch đặc biệt
-- =============================================
DROP TABLE IF EXISTS `schedule_exceptions`;
CREATE TABLE `schedule_exceptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_schedule_id` INT UNSIGNED DEFAULT NULL COMMENT 'Liên kết với buổi học cụ thể (NULL = áp dụng tất cả)',
  `class_id` INT UNSIGNED DEFAULT NULL COMMENT 'Áp dụng cho cả lớp',
  `exception_date` DATE NOT NULL COMMENT 'Ngày áp dụng ngoại lệ',
  `exception_type` ENUM('cancel', 'reschedule', 'makeup', 'holiday') NOT NULL,
  `new_date` DATE DEFAULT NULL COMMENT 'Ngày mới nếu reschedule',
  `new_start_time` TIME DEFAULT NULL,
  `new_end_time` TIME DEFAULT NULL,
  `new_room` VARCHAR(50) DEFAULT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_date` (`exception_date`),
  INDEX `idx_class` (`class_id`),
  CONSTRAINT `fk_exception_schedule` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exception_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. TẠO BẢNG ATTENDANCE (Điểm danh)
-- Theo dõi học viên tham gia từng buổi học
-- =============================================
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_schedule_id` INT UNSIGNED NOT NULL,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
  `notes` TEXT DEFAULT NULL,
  `marked_by` INT UNSIGNED DEFAULT NULL COMMENT 'ID của người điểm danh (teacher/admin)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`class_schedule_id`, `enrollment_id`, `attendance_date`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_date` (`attendance_date`),
  CONSTRAINT `fk_attendance_schedule` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. CẬP NHẬT BẢNG TEACHERS - Thêm availability
-- =============================================
ALTER TABLE `teachers` 
ADD COLUMN IF NOT EXISTS `availability` JSON DEFAULT NULL COMMENT 'Lịch trống của giảng viên';

-- =============================================
-- 5. DỮ LIỆU MẪU - CLASS_SCHEDULES
-- =============================================

-- Lấy class_id và course_id từ classes hiện có
-- Giả sử có class_id=1 (IELTS 6.5 - Lớp A), course_id=15

-- Thời khóa biểu mẫu cho Lớp A - IELTS 6.5
INSERT INTO `class_schedules` (`class_id`, `course_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room`, `subject`, `color`) VALUES
-- Lớp 1: IELTS 6.5 - Lớp A (class_id = 1, nếu tồn tại)
(1, 1, 1, 'monday', '18:00:00', '20:00:00', 'P.101', 'Speaking', '#1e40af'),
(1, 1, 2, 'wednesday', '18:00:00', '20:00:00', 'P.101', 'Writing', '#059669'),
(1, 1, 3, 'friday', '18:00:00', '20:00:00', 'P.101', 'Reading & Listening', '#dc2626'),

-- Lớp 2: IELTS 6.5 - Lớp B (class_id = 2, nếu tồn tại)
(2, 1, 2, 'tuesday', '09:00:00', '11:00:00', 'P.102', 'Writing', '#059669'),
(2, 1, 1, 'thursday', '09:00:00', '11:00:00', 'P.102', 'Speaking', '#1e40af'),
(2, 1, 4, 'saturday', '09:00:00', '11:00:00', 'P.102', 'Listening', '#7c3aed');

-- =============================================
-- VIEW: Lịch học của học viên (qua enrollment)
-- =============================================
DROP VIEW IF EXISTS `v_student_schedules`;
CREATE VIEW `v_student_schedules` AS
SELECT 
    e.user_id,
    e.id as enrollment_id,
    u.fullname as student_name,
    u.email as student_email,
    c.name as course_name,
    c.id as course_id,
    cl.name as class_name,
    cl.id as class_id,
    cs.id as schedule_id,
    cs.day_of_week,
    cs.start_time,
    cs.end_time,
    cs.room,
    cs.is_online,
    cs.meeting_link,
    cs.subject,
    cs.color,
    t.id as teacher_id,
    t.name as teacher_name,
    cl.start_date as class_start_date,
    cl.end_date as class_end_date
FROM enrollments e
JOIN users u ON e.user_id = u.id
JOIN courses c ON e.course_id = c.id
JOIN classes cl ON e.class_id = cl.id
JOIN class_schedules cs ON cl.id = cs.class_id
LEFT JOIN teachers t ON cs.teacher_id = t.id
WHERE e.status IN ('active', 'pending')
  AND cs.is_active = 1
  AND cl.is_active = 1;

-- =============================================
-- VIEW: Lịch dạy của giảng viên
-- =============================================
DROP VIEW IF EXISTS `v_teacher_schedules`;
CREATE VIEW `v_teacher_schedules` AS
SELECT 
    t.id as teacher_id,
    t.name as teacher_name,
    c.name as course_name,
    c.id as course_id,
    cl.name as class_name,
    cl.id as class_id,
    cs.id as schedule_id,
    cs.day_of_week,
    cs.start_time,
    cs.end_time,
    cs.room,
    cs.is_online,
    cs.meeting_link,
    cs.subject,
    cs.color,
    cl.start_date,
    cl.end_date,
    (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as student_count
FROM teachers t
JOIN class_schedules cs ON t.id = cs.teacher_id
JOIN classes cl ON cs.class_id = cl.id
JOIN courses c ON cs.course_id = c.id
WHERE cs.is_active = 1
  AND cl.is_active = 1
  AND cl.status IN ('upcoming', 'active');

-- =============================================
-- VIEW: Thống kê lịch học theo khóa
-- =============================================
DROP VIEW IF EXISTS `v_course_schedules`;
CREATE VIEW `v_course_schedules` AS
SELECT 
    c.id as course_id,
    c.name as course_name,
    c.category,
    c.level,
    cl.id as class_id,
    cl.name as class_name,
    cl.max_students,
    cl.status as class_status,
    cl.start_date,
    cl.end_date,
    cl.room as default_room,
    GROUP_CONCAT(DISTINCT CONCAT(cs.day_of_week, ' ', TIME_FORMAT(cs.start_time, '%H:%i'), '-', TIME_FORMAT(cs.end_time, '%H:%i')) SEPARATOR ', ') as schedule_summary,
    (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as enrolled_count,
    (SELECT GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') FROM class_schedules cs2 JOIN teachers t ON cs2.teacher_id = t.id WHERE cs2.class_id = cl.id) as teachers
FROM courses c
JOIN classes cl ON c.id = cl.course_id
LEFT JOIN class_schedules cs ON cl.id = cs.class_id
WHERE c.is_active = 1
  AND cl.is_active = 1
GROUP BY c.id, c.name, c.category, c.level, cl.id, cl.name, cl.max_students, cl.status, cl.start_date, cl.end_date, cl.room;

-- =============================================
-- HOÀN TẤT
-- =============================================
SELECT 'Migration completed successfully!' as status;
