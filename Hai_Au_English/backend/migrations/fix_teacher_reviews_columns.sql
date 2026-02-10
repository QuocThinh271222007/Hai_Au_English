-- Migration: Fix teacher_reviews table columns
-- Run this on production database if needed

-- Add teacher_id column if not exists
ALTER TABLE `teacher_reviews` ADD COLUMN IF NOT EXISTS `teacher_id` INT UNSIGNED DEFAULT NULL AFTER `user_id`;

-- Add display_order column if not exists
ALTER TABLE `teacher_reviews` ADD COLUMN IF NOT EXISTS `display_order` INT DEFAULT 0 AFTER `teacher_id`;

-- Add index for teacher_id if not exists
-- Check if index exists first
SET @indexExists = (
    SELECT COUNT(*) FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
    AND table_name = 'teacher_reviews' 
    AND index_name = 'idx_teacher_id'
);

SET @sql = IF(@indexExists = 0, 
    'ALTER TABLE `teacher_reviews` ADD INDEX `idx_teacher_id` (`teacher_id`)',
    'SELECT "Index idx_teacher_id already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for teacher_id if not exists (optional - may fail if teachers table has mismatched data)
-- You may need to run this separately:
-- ALTER TABLE `teacher_reviews` ADD CONSTRAINT `fk_teacher_reviews_teacher` 
--     FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Verify changes
DESCRIBE `teacher_reviews`;
