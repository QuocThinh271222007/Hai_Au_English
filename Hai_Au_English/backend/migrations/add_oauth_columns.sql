-- =============================================
-- Migration: Add OAuth columns to users table
-- Date: 2024
-- Description: Thêm các cột hỗ trợ OAuth login (Google/Facebook)
-- =============================================

-- Thêm cột oauth_provider để lưu nhà cung cấp OAuth (google, facebook)
ALTER TABLE `users` 
ADD COLUMN `oauth_provider` VARCHAR(50) DEFAULT NULL AFTER `is_active`,
ADD COLUMN `oauth_id` VARCHAR(255) DEFAULT NULL AFTER `oauth_provider`;

-- Cập nhật password để cho phép NULL (OAuth users không cần password)
ALTER TABLE `users` 
MODIFY COLUMN `password` VARCHAR(255) DEFAULT NULL;

-- Thêm index cho oauth_provider và oauth_id
ALTER TABLE `users`
ADD INDEX `idx_oauth` (`oauth_provider`, `oauth_id`);

-- =============================================
-- Verify the changes
-- =============================================
-- SHOW COLUMNS FROM users;
