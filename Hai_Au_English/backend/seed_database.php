<?php
/**
 * Seed database content with proper UTF-8 encoding
 * Run this file once to populate site_content and site_settings
 */

require_once __DIR__ . '/php/db.php';

// Clear existing data
$pdo->exec("DELETE FROM site_content");
$pdo->exec("DELETE FROM site_settings");

echo "Cleared existing data.\n";

// Insert site_content
$contents = [
    // Home Page - Hero
    ['home', 'hero', 'title', 'Chinh phục IELTS cùng Hải Âu English', 'text'],
    ['home', 'hero', 'subtitle', 'Cam kết đầu ra - Học phí hợp lý', 'text'],
    ['home', 'hero', 'description', 'Trung tâm luyện thi IELTS uy tín tại TP.HCM với đội ngũ giảng viên 8.0+ IELTS', 'text'],
    ['home', 'hero', 'cta_text', 'Đăng ký tư vấn miễn phí', 'text'],
    
    // Home Page - Stats
    ['home', 'stats', 'students_count', '1500+', 'text'],
    ['home', 'stats', 'teachers_count', '20+', 'text'],
    ['home', 'stats', 'success_rate', '95%', 'text'],
    ['home', 'stats', 'experience_years', '10+', 'text'],
    
    // Home Page - Why Choose
    ['home', 'why_choose', 'title', 'Tại sao chọn Hải Âu English?', 'text'],
    ['home', 'why_choose', 'item1_title', 'Giảng viên chất lượng', 'text'],
    ['home', 'why_choose', 'item1_desc', 'Đội ngũ giảng viên 8.0+ IELTS với nhiều năm kinh nghiệm', 'text'],
    ['home', 'why_choose', 'item2_title', 'Cam kết đầu ra', 'text'],
    ['home', 'why_choose', 'item2_desc', 'Học lại miễn phí nếu không đạt band điểm cam kết', 'text'],
    ['home', 'why_choose', 'item3_title', 'Lớp học nhỏ', 'text'],
    ['home', 'why_choose', 'item3_desc', 'Tối đa 15 học viên/lớp để đảm bảo chất lượng', 'text'],
    ['home', 'why_choose', 'item4_title', 'Học phí hợp lý', 'text'],
    ['home', 'why_choose', 'item4_desc', 'Học phí cạnh tranh với nhiều ưu đãi hấp dẫn', 'text'],
    
    // About Page
    ['about', 'intro', 'title', 'Về Hải Âu English', 'text'],
    ['about', 'intro', 'description', 'Hải Âu English là trung tâm luyện thi IELTS hàng đầu tại TP.HCM, với sứ mệnh mang đến chất lượng giảng dạy tiếng Anh chuẩn quốc tế.', 'text'],
    ['about', 'mission', 'title', 'Sứ mệnh', 'text'],
    ['about', 'mission', 'content', 'Mang đến chất lượng giảng dạy tiếng Anh chuẩn quốc tế, giúp học viên chinh phục mục tiêu IELTS một cách hiệu quả nhất.', 'text'],
    ['about', 'vision', 'title', 'Tầm nhìn', 'text'],
    ['about', 'vision', 'content', 'Trở thành trung tâm luyện thi IELTS hàng đầu Việt Nam, được tin tưởng bởi hàng nghìn học viên.', 'text'],
    
    // Contact Page
    ['contact', 'info', 'phone', '0123 456 789', 'text'],
    ['contact', 'info', 'email', 'contact@haiauenglish.edu.vn', 'text'],
    ['contact', 'info', 'address', '123 Nguyễn Văn A, Quận 1, TP.HCM', 'text'],
    ['contact', 'info', 'working_hours', 'Thứ 2 - Chủ nhật: 8:00 - 21:00', 'text'],
];

$stmt = $pdo->prepare("INSERT INTO site_content (page, section, content_key, content_value, content_type) VALUES (?, ?, ?, ?, ?)");

foreach ($contents as $content) {
    $stmt->execute($content);
}

echo "Inserted " . count($contents) . " site_content records.\n";

// Insert site_settings
$settings = [
    ['contact_email', 'contact@haiauenglish.edu.vn', 'Email liên hệ'],
    ['hotline', '0123 456 789', 'Số điện thoại hotline'],
    ['address', '123 Nguyễn Văn A, Quận 1, TP.HCM', 'Địa chỉ trung tâm'],
    ['academic_year', '2024-2025', 'Năm học hiện tại'],
];

$stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");

foreach ($settings as $setting) {
    $stmt->execute($setting);
}

echo "Inserted " . count($settings) . " site_settings records.\n";

echo "\n✅ Database seeded successfully!\n";
