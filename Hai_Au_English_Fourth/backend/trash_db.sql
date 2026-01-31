-- Bảng TRASH - Lưu trữ dữ liệu đã xóa (giữ trong 3 tháng)
-- Chạy file này sau khi đã chạy update_db.sql
USE `hai_au_english`;

-- =============================================
-- BẢNG TRASH (Thùng rác - xóa sau 3 tháng)
-- =============================================
DROP TABLE IF EXISTS `trash`;
CREATE TABLE IF NOT EXISTS `trash` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_table` VARCHAR(50) NOT NULL COMMENT 'Tên bảng gốc: users, courses, enrollments, scores, feedback, teachers',
  `original_id` INT UNSIGNED NOT NULL COMMENT 'ID gốc của bản ghi',
  `data` JSON NOT NULL COMMENT 'Dữ liệu JSON của bản ghi đã xóa',
  `deleted_by` INT UNSIGNED DEFAULT NULL COMMENT 'ID của admin đã xóa',
  `deleted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian xóa',
  `expires_at` DATETIME NOT NULL COMMENT 'Thời gian hết hạn (deleted_at + 3 tháng)',
  `restored_at` DATETIME DEFAULT NULL COMMENT 'Thời gian khôi phục (nếu có)',
  `is_restored` TINYINT(1) DEFAULT 0 COMMENT '0: Chưa khôi phục, 1: Đã khôi phục',
  PRIMARY KEY (`id`),
  INDEX (`original_table`),
  INDEX (`original_id`),
  INDEX (`deleted_at`),
  INDEX (`expires_at`),
  INDEX (`is_restored`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TRIGGER: Tự động tính expires_at = deleted_at + 3 tháng
-- =============================================
DELIMITER //
CREATE TRIGGER `set_trash_expires_at` BEFORE INSERT ON `trash`
FOR EACH ROW
BEGIN
    IF NEW.expires_at IS NULL THEN
        SET NEW.expires_at = DATE_ADD(NEW.deleted_at, INTERVAL 3 MONTH);
    END IF;
END//
DELIMITER ;

-- =============================================
-- EVENT: Tự động xóa vĩnh viễn sau 3 tháng
-- (Chạy mỗi ngày lúc 00:00)
-- =============================================
-- Bật Event Scheduler (nếu chưa bật)
SET GLOBAL event_scheduler = ON;

DELIMITER //
CREATE EVENT IF NOT EXISTS `auto_delete_expired_trash`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO
BEGIN
    DELETE FROM `trash` 
    WHERE `expires_at` < NOW() 
    AND `is_restored` = 0;
END//
DELIMITER ;

-- =============================================
-- STORED PROCEDURE: Di chuyển dữ liệu vào thùng rác
-- =============================================
DELIMITER //
CREATE PROCEDURE `move_to_trash`(
    IN p_table_name VARCHAR(50),
    IN p_record_id INT UNSIGNED,
    IN p_deleted_by INT UNSIGNED
)
BEGIN
    DECLARE v_data JSON;
    
    -- Lấy dữ liệu từ bảng gốc
    CASE p_table_name
        WHEN 'users' THEN
            SELECT JSON_OBJECT(
                'id', id, 'fullname', fullname, 'email', email, 'password', password,
                'phone', phone, 'avatar_url', avatar_url, 'date_of_birth', date_of_birth,
                'gender', gender, 'address', address, 'role', role, 'is_active', is_active,
                'created_at', created_at, 'updated_at', updated_at
            ) INTO v_data FROM users WHERE id = p_record_id;
            
        WHEN 'courses' THEN
            SELECT JSON_OBJECT(
                'id', id, 'name', name, 'description', description, 'image_url', image_url,
                'level', level, 'duration', duration, 'price', price, 'price_unit', price_unit,
                'category', category, 'badge', badge, 'badge_type', badge_type, 'features', features,
                'target', target, 'total_sessions', total_sessions, 'is_active', is_active, 'created_at', created_at
            ) INTO v_data FROM courses WHERE id = p_record_id;
            
        WHEN 'enrollments' THEN
            SELECT JSON_OBJECT(
                'id', id, 'user_id', user_id, 'course_id', course_id, 'academic_year', academic_year,
                'semester', semester, 'start_date', start_date, 'end_date', end_date,
                'status', status, 'progress', progress, 'created_at', created_at
            ) INTO v_data FROM enrollments WHERE id = p_record_id;
            
        WHEN 'scores' THEN
            SELECT JSON_OBJECT(
                'id', id, 'enrollment_id', enrollment_id, 'user_id', user_id,
                'listening', listening, 'reading', reading, 'writing', writing, 'speaking', speaking,
                'overall', overall, 'test_date', test_date, 'test_type', test_type, 'notes', notes, 'created_at', created_at
            ) INTO v_data FROM scores WHERE id = p_record_id;
            
        WHEN 'feedback' THEN
            SELECT JSON_OBJECT(
                'id', id, 'enrollment_id', enrollment_id, 'user_id', user_id, 'teacher_id', teacher_id,
                'content', content, 'rating', rating, 'feedback_date', feedback_date, 'created_at', created_at
            ) INTO v_data FROM feedback WHERE id = p_record_id;
            
        WHEN 'teachers' THEN
            SELECT JSON_OBJECT(
                'id', id, 'name', name, 'title', title, 'description', description, 'image_url', image_url,
                'ielts_score', ielts_score, 'experience_years', experience_years, 'students_count', students_count,
                'rating', rating, 'specialties', specialties, 'is_featured', is_featured, 'is_active', is_active, 'created_at', created_at
            ) INTO v_data FROM teachers WHERE id = p_record_id;
    END CASE;
    
    -- Chèn vào thùng rác
    IF v_data IS NOT NULL THEN
        INSERT INTO trash (original_table, original_id, data, deleted_by, deleted_at, expires_at)
        VALUES (p_table_name, p_record_id, v_data, p_deleted_by, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH));
        
        -- Xóa khỏi bảng gốc
        CASE p_table_name
            WHEN 'users' THEN DELETE FROM users WHERE id = p_record_id;
            WHEN 'courses' THEN DELETE FROM courses WHERE id = p_record_id;
            WHEN 'enrollments' THEN DELETE FROM enrollments WHERE id = p_record_id;
            WHEN 'scores' THEN DELETE FROM scores WHERE id = p_record_id;
            WHEN 'feedback' THEN DELETE FROM feedback WHERE id = p_record_id;
            WHEN 'teachers' THEN DELETE FROM teachers WHERE id = p_record_id;
        END CASE;
    END IF;
END//
DELIMITER ;

-- =============================================
-- STORED PROCEDURE: Khôi phục dữ liệu từ thùng rác
-- =============================================
DELIMITER //
CREATE PROCEDURE `restore_from_trash`(
    IN p_trash_id INT UNSIGNED
)
BEGIN
    DECLARE v_table VARCHAR(50);
    DECLARE v_data JSON;
    DECLARE v_original_id INT UNSIGNED;
    
    -- Lấy thông tin từ thùng rác
    SELECT original_table, original_id, data INTO v_table, v_original_id, v_data
    FROM trash WHERE id = p_trash_id AND is_restored = 0;
    
    IF v_data IS NOT NULL THEN
        -- Khôi phục dữ liệu theo bảng
        CASE v_table
            WHEN 'users' THEN
                INSERT INTO users (id, fullname, email, password, phone, avatar_url, date_of_birth, gender, address, role, is_active, created_at)
                VALUES (
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.id')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.fullname')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.email')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.password')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.phone')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.avatar_url')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.date_of_birth')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.gender')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.address')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.role')),
                    JSON_EXTRACT(v_data, '$.is_active'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.created_at'))
                );
                
            WHEN 'courses' THEN
                INSERT INTO courses (id, name, description, image_url, level, duration, price, price_unit, category, badge, badge_type, features, target, total_sessions, is_active, created_at)
                VALUES (
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.id')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.name')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.description')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.image_url')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.level')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.duration')),
                    JSON_EXTRACT(v_data, '$.price'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.price_unit')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.category')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.badge')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.badge_type')),
                    JSON_EXTRACT(v_data, '$.features'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.target')),
                    JSON_EXTRACT(v_data, '$.total_sessions'),
                    JSON_EXTRACT(v_data, '$.is_active'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.created_at'))
                );
                
            WHEN 'enrollments' THEN
                INSERT INTO enrollments (id, user_id, course_id, academic_year, semester, start_date, end_date, status, progress, created_at)
                VALUES (
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.id')),
                    JSON_EXTRACT(v_data, '$.user_id'),
                    JSON_EXTRACT(v_data, '$.course_id'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.academic_year')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.semester')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.start_date')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.end_date')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.status')),
                    JSON_EXTRACT(v_data, '$.progress'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.created_at'))
                );
                
            WHEN 'teachers' THEN
                INSERT INTO teachers (id, name, title, description, image_url, ielts_score, experience_years, students_count, rating, specialties, is_featured, is_active, created_at)
                VALUES (
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.id')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.name')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.title')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.description')),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.image_url')),
                    JSON_EXTRACT(v_data, '$.ielts_score'),
                    JSON_EXTRACT(v_data, '$.experience_years'),
                    JSON_EXTRACT(v_data, '$.students_count'),
                    JSON_EXTRACT(v_data, '$.rating'),
                    JSON_EXTRACT(v_data, '$.specialties'),
                    JSON_EXTRACT(v_data, '$.is_featured'),
                    JSON_EXTRACT(v_data, '$.is_active'),
                    JSON_UNQUOTE(JSON_EXTRACT(v_data, '$.created_at'))
                );
        END CASE;
        
        -- Đánh dấu đã khôi phục
        UPDATE trash SET is_restored = 1, restored_at = NOW() WHERE id = p_trash_id;
    END IF;
END//
DELIMITER ;
