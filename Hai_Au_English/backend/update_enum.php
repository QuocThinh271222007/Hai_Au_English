<?php
/**
 * Script cập nhật ENUM và thêm settings
 */

require_once 'php/db.php';
$pdo = getDBConnection();

// 1. Cập nhật ENUM cho admin_notifications
try {
    $pdo->exec("ALTER TABLE admin_notifications MODIFY COLUMN type ENUM('review', 'achievement', 'score', 'contact', 'user', 'system', 'course', 'class', 'schedule', 'teacher', 'enrollment', 'teacher_review') DEFAULT 'system'");
    echo "ENUM updated successfully\n";
} catch (Exception $e) {
    echo "ENUM update: " . $e->getMessage() . "\n";
}

// 2. Thêm setting max_approved_teacher_reviews nếu chưa có
try {
    $check = $pdo->query("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'max_approved_teacher_reviews'");
    if ($check->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO site_settings (setting_key, setting_value, description) VALUES ('max_approved_teacher_reviews', '20', 'Số nhận xét giáo viên tối đa được phê duyệt')");
        echo "Added teacher_reviews setting\n";
    } else {
        echo "teacher_reviews setting already exists\n";
    }
} catch (Exception $e) {
    echo "Setting insert: " . $e->getMessage() . "\n";
}

echo "Done!\n";
