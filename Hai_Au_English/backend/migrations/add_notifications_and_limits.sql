-- =============================================
-- MIGRATION: Thêm bảng notifications và cài đặt giới hạn
-- Chạy file này trong phpMyAdmin sau database.sql
-- =============================================

-- =============================================
-- 1. BẢNG ADMIN_NOTIFICATIONS (thông báo cho admin)
-- =============================================
DROP TABLE IF EXISTS `admin_notifications`;
CREATE TABLE `admin_notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('review', 'achievement', 'score', 'contact', 'user', 'system') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `reference_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID của bản ghi liên quan',
  `reference_table` VARCHAR(50) DEFAULT NULL COMMENT 'Tên bảng liên quan',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 2. THÊM CÀI ĐẶT GIỚI HẠN VÀO SITE_SETTINGS
-- =============================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('max_approved_reviews', '20', 'number', 'Số lượng đánh giá tối đa được duyệt hiển thị'),
('max_approved_achievements', '20', 'number', 'Số lượng thành tích tối đa được hiển thị'),
('auto_pending_reviews', '1', 'boolean', 'Tự động đặt review mới ở trạng thái chờ duyệt khi vượt giới hạn'),
('auto_pending_achievements', '1', 'boolean', 'Tự động đặt thành tích mới ở trạng thái chờ hiển thị khi vượt giới hạn')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- =============================================
-- 3. INSERT THÔNG BÁO MẪU
-- =============================================
INSERT INTO `admin_notifications` (`type`, `title`, `message`, `reference_id`, `reference_table`, `is_read`) VALUES
('system', 'Hệ thống khởi tạo thành công', 'Hệ thống thông báo admin đã được khởi tạo. Bạn sẽ nhận thông báo khi có review mới, điểm số mới, hoặc các thay đổi quan trọng.', NULL, NULL, 0);

-- =============================================
-- KẾT THÚC MIGRATION
-- =============================================
SELECT 'Migration notifications and limits completed!' AS 'Status';
