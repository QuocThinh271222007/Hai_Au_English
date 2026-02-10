<?php
/**
 * Comprehensive seed for all site content
 * Run once to populate site_content with all page content
 */

require_once __DIR__ . '/php/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Seeding Site Content</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;}</style>";

// Clear existing content
$pdo->exec("DELETE FROM site_content");
echo "<p class='ok'>✓ Cleared existing content</p>";

$contents = [
    // ==================== HOME PAGE ====================
    // Hero Section
    ['home', 'hero', 'title', 'Chinh phục IELTS', 'text'],
    ['home', 'hero', 'title_highlight', '8.0+', 'text'],
    ['home', 'hero', 'description', 'Phương pháp học tập hiệu quả với đội ngũ giảng viên chứng chỉ 8.0+, cam kết đầu ra và học lại miễn phí nếu không đạt mục tiêu.', 'text'],
    ['home', 'hero', 'cta_primary', 'Đăng ký học thử miễn phí', 'text'],
    ['home', 'hero', 'cta_secondary', 'Xem khóa học', 'text'],
    ['home', 'hero', 'stat_number', '1000+', 'text'],
    ['home', 'hero', 'stat_label', 'Học viên đạt 7.0+', 'text'],
    
    // About Section (on homepage)
    ['home', 'about', 'title', 'Về Hải Âu English', 'text'],
    ['home', 'about', 'description', 'Trung tâm đào tạo IELTS hàng đầu với phương pháp giảng dạy độc quyền và đội ngũ giảng viên chất lượng cao', 'text'],
    
    // Stats
    ['home', 'stats', 'stat1_number', '5000+', 'text'],
    ['home', 'stats', 'stat1_label', 'Học viên đã tin tưởng', 'text'],
    ['home', 'stats', 'stat2_number', '98%', 'text'],
    ['home', 'stats', 'stat2_label', 'Tỷ lệ đạt mục tiêu', 'text'],
    ['home', 'stats', 'stat3_number', '50+', 'text'],
    ['home', 'stats', 'stat3_label', 'Giảng viên 8.0+', 'text'],
    ['home', 'stats', 'stat4_number', '10+', 'text'],
    ['home', 'stats', 'stat4_label', 'Năm kinh nghiệm', 'text'],
    
    // Why Choose Us
    ['home', 'why_choose', 'title', 'Vì sao chọn chúng tôi?', 'text'],
    ['home', 'why_choose', 'subtitle', 'Những lợi ích vượt trội khi học tại Hải Âu English', 'text'],
    ['home', 'why_choose', 'item1_title', 'Giáo trình độc quyền', 'text'],
    ['home', 'why_choose', 'item1_desc', 'Tài liệu học tập được biên soạn bởi đội ngũ giảng viên 8.5+ với kinh nghiệm lâu năm', 'text'],
    ['home', 'why_choose', 'item2_title', 'Lớp học nhỏ', 'text'],
    ['home', 'why_choose', 'item2_desc', 'Tối đa 8-10 học viên/lớp để đảm bảo chất lượng giảng dạy và chăm sóc cá nhân', 'text'],
    ['home', 'why_choose', 'item3_title', 'Cam kết đầu ra', 'text'],
    ['home', 'why_choose', 'item3_desc', 'Cam kết đầu ra rõ ràng, học lại miễn phí nếu không đạt mục tiêu', 'text'],
    ['home', 'why_choose', 'item4_title', 'Lộ trình cá nhân hóa', 'text'],
    ['home', 'why_choose', 'item4_desc', 'Xây dựng lộ trình học tập riêng phù hợp với trình độ và mục tiêu của từng học viên', 'text'],
    ['home', 'why_choose', 'item5_title', 'Học liệu đa dạng', 'text'],
    ['home', 'why_choose', 'item5_desc', 'Tài liệu phong phú từ sách giáo trình đến video bài giảng và bài tập online', 'text'],
    ['home', 'why_choose', 'item6_title', 'Hỗ trợ 24/7', 'text'],
    ['home', 'why_choose', 'item6_desc', 'Đội ngũ hỗ trợ học tập và giải đáp thắc mắc 24/7 qua nhiều kênh', 'text'],
    
    // ==================== ABOUT PAGE ====================
    // Hero
    ['about', 'hero', 'title', 'Về Hải Âu English', 'text'],
    ['about', 'hero', 'subtitle', 'Trung tâm đào tạo IELTS hàng đầu với hơn 10 năm kinh nghiệm', 'text'],
    
    // Story
    ['about', 'story', 'title', 'Câu chuyện của chúng tôi', 'text'],
    ['about', 'story', 'paragraph1', 'Hải Âu English được thành lập năm 2016 với sứ mệnh giúp học viên Việt Nam chinh phục chứng chỉ IELTS một cách hiệu quả và bền vững. Chúng tôi tin rằng mỗi học viên đều có tiềm năng đạt được mục tiêu của mình với phương pháp học tập phù hợp.', 'text'],
    ['about', 'story', 'paragraph2', 'Qua hơn 10 năm hoạt động, chúng tôi đã đào tạo hơn 5000+ học viên thành công với tỷ lệ đạt mục tiêu 98%. Đội ngũ giảng viên của chúng tôi đều có chứng chỉ IELTS 8.0+ và nhiều năm kinh nghiệm giảng dạy.', 'text'],
    ['about', 'story', 'paragraph3', 'Chúng tôi không ngừng cải tiến phương pháp giảng dạy, cập nhật tài liệu và áp dụng công nghệ hiện đại để mang đến trải nghiệm học tập tốt nhất cho học viên.', 'text'],
    
    // Mission & Vision
    ['about', 'mission', 'title', 'Sứ mệnh', 'text'],
    ['about', 'mission', 'description', 'Giúp mỗi học viên tự tin chinh phục IELTS và mở ra cơ hội học tập, làm việc quốc tế thông qua phương pháp giảng dạy hiệu quả, đội ngũ giảng viên chất lượng cao và môi trường học tập chuyên nghiệp.', 'text'],
    ['about', 'vision', 'title', 'Tầm nhìn', 'text'],
    ['about', 'vision', 'description', 'Trở thành trung tâm đào tạo IELTS số 1 Việt Nam, được công nhận quốc tế với chất lượng giảng dạy xuất sắc, đóng góp vào việc nâng cao trình độ tiếng Anh của người Việt và kết nối họ với thế giới.', 'text'],
    
    // Facilities
    ['about', 'facilities', 'title', 'Cơ sở vật chất', 'text'],
    ['about', 'facilities', 'subtitle', 'Không gian học tập hiện đại và thoải mái', 'text'],
    
    // ==================== COURSES PAGE ====================
    // Hero
    ['courses', 'hero', 'title', 'Chương trình đào tạo', 'text'],
    ['courses', 'hero', 'subtitle', 'Lựa chọn khóa học phù hợp với độ tuổi và trình độ của bạn', 'text'],
    
    // Filters
    ['courses', 'filter', 'all', 'Tất cả khóa học', 'text'],
    ['courses', 'filter', 'tieuhoc', 'Tiểu học', 'text'],
    ['courses', 'filter', 'thcs', 'THCS', 'text'],
    ['courses', 'filter', 'ielts', 'IELTS', 'text'],
    
    // Dynamic Section
    ['courses', 'dynamic', 'title', '🎓 Khóa học mới nhất', 'text'],
    ['courses', 'dynamic', 'subtitle', 'Các khóa học được cập nhật thường xuyên', 'text'],
    
    // Section Titles
    ['courses', 'sections', 'tieuhoc_title', '📚 CHƯƠNG TRÌNH TIẾNG ANH CẤP TIỂU HỌC', 'text'],
    ['courses', 'sections', 'thcs_title', '📖 CHƯƠNG TRÌNH TIẾNG ANH CẤP THCS', 'text'],
    ['courses', 'sections', 'ielts_title', '🎯 CHƯƠNG TRÌNH IELTS VÀ LT IELTS', 'text'],
    
    // ==================== TEACHERS PAGE ====================
    ['teachers', 'hero', 'title', 'Đội ngũ giảng viên', 'text'],
    ['teachers', 'hero', 'subtitle', 'Giảng viên chứng chỉ 8.0+ với nhiều năm kinh nghiệm giảng dạy', 'text'],
    
    // ==================== CONTACT PAGE ====================
    ['contact', 'hero', 'title', 'Liên hệ với chúng tôi', 'text'],
    ['contact', 'hero', 'subtitle', 'Chúng tôi sẵn sàng tư vấn và hỗ trợ bạn 24/7', 'text'],
    
    ['contact', 'info', 'address', '123 Nguyễn Văn A, Quận 1, TP.HCM', 'text'],
    ['contact', 'info', 'phone', '0123 456 789', 'text'],
    ['contact', 'info', 'email', 'contact@haiauenglish.edu.vn', 'text'],
    ['contact', 'info', 'working_hours', 'Thứ 2 - Chủ nhật: 8:00 - 21:00', 'text'],
    
    ['contact', 'form', 'title', 'ĐĂNG KÝ HỌC/TƯ VẤN', 'text'],
    ['contact', 'form', 'subtitle', 'Điền thông tin và chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ', 'text'],
];

$stmt = $pdo->prepare("INSERT INTO site_content (page, section, content_key, content_value, content_type) VALUES (?, ?, ?, ?, ?)");

$count = 0;
foreach ($contents as $content) {
    $stmt->execute($content);
    $count++;
}

echo "<p class='ok'>✓ Inserted $count content items</p>";

// Count by page
$stmt = $pdo->query("SELECT page, COUNT(*) as cnt FROM site_content GROUP BY page");
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Content by page:</h2>";
echo "<ul>";
foreach ($pages as $p) {
    echo "<li><strong>{$p['page']}</strong>: {$p['cnt']} items</li>";
}
echo "</ul>";

echo "<hr><p><strong>✅ Seeding completed!</strong></p>";
echo "<p><a href='/frontend/pages/index.php'>Go to Homepage</a> | <a href='/frontend/admin/admin.php'>Go to Admin</a></p>";
