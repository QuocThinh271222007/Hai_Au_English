<?php
/**
 * Test Email SMTP Configuration
 * Chạy file này để kiểm tra cấu hình SMTP có hoạt động không
 * 
 * Cách test:
 * 1. Mở XAMPP và bật Apache
 * 2. Truy cập: http://localhost/Hai_Au_English/backend/test_email.php
 */

// Load config trước
require_once __DIR__ . '/php/config.php';

// Load EmailService
require_once __DIR__ . '/service/EmailService.php';

// Hiển thị cấu hình hiện tại (ẩn password)
echo "<h2>📧 Cấu hình SMTP hiện tại:</h2>";
echo "<pre>";
echo "SMTP_HOST: " . SMTP_HOST . "\n";
echo "SMTP_USERNAME: " . SMTP_USERNAME . "\n";
echo "SMTP_SECRET: " . str_repeat('*', strlen(SMTP_SECRET) - 4) . substr(SMTP_SECRET, -4) . "\n";
echo "SHOP_OWNER (người nhận): " . SHOP_OWNER . "\n";
echo "</pre>";

// Test gửi email
echo "<h2>🚀 Gửi email test...</h2>";

$emailService = new EmailService();
$result = $emailService->send(
    SHOP_OWNER, // Gửi đến chính email chủ shop
    'Test Email từ Hải Âu English - ' . date('Y-m-d H:i:s'),
    '
    <h1>Xin chào!</h1>
    <p>Đây là email test từ hệ thống Hải Âu English.</p>
    <p>Nếu bạn nhận được email này, nghĩa là cấu hình SMTP đã hoạt động!</p>
    <p><strong>Thời gian gửi:</strong> ' . date('Y-m-d H:i:s') . '</p>
    <hr>
    <small>Email tự động từ hệ thống</small>
    '
);

if ($result['success']) {
    echo "<div style='color: green; font-size: 18px; padding: 20px; background: #e8f5e9; border-radius: 8px;'>";
    echo "✅ <strong>THÀNH CÔNG!</strong> Email đã được gửi thành công.";
    echo "<br><br>Kiểm tra hộp thư của: <strong>" . SHOP_OWNER . "</strong>";
    echo "<br><small>(Nếu không thấy, hãy kiểm tra thư mục Spam)</small>";
    echo "</div>";
} else {
    echo "<div style='color: red; font-size: 18px; padding: 20px; background: #ffebee; border-radius: 8px;'>";
    echo "❌ <strong>LỖI:</strong> " . htmlspecialchars($result['error']);
    echo "<br><br><strong>Các nguyên nhân thường gặp:</strong>";
    echo "<ul>";
    echo "<li>App Password không đúng (hãy kiểm tra lại trong Google Account)</li>";
    echo "<li>Tài khoản Gmail chưa bật 2FA (cần bật 2FA để dùng App Password)</li>";
    echo "<li>App Password đã hết hạn hoặc bị thu hồi</li>";
    echo "</ul>";
    echo "</div>";
}
?>
