<?php
// contact.php - Gửi email từ form liên hệ (không lưu database)
header('Content-Type: application/json; charset=utf-8');

// Allow CORS for local development (adjust in production)
if (
    isset($_SERVER['HTTP_ORIGIN']) &&
    ($_SERVER['HTTP_ORIGIN'] === 'http://localhost' || strpos($_SERVER['HTTP_ORIGIN'], 'http://localhost') === 0)
) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

// ============================================
// CẤU HÌNH EMAIL - THAY ĐỔI THÔNG TIN NÀY
// ============================================
$config = [
    'to_email' => 'your-email@example.com',      // Email nhận thông báo
    'to_name'  => 'Hải Âu English',              // Tên người nhận
    'subject'  => '[Hải Âu English] Đăng ký tư vấn mới', // Tiêu đề email
];

// Read input (JSON or form-encoded)
$input = file_get_contents('php://input');
if ($input) {
    $data = json_decode($input, true);
} else {
    $data = $_POST;
}

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Expected fields from frontend form
$fullname = trim($data['fullname'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$course = trim($data['course'] ?? '');
$level = trim($data['level'] ?? '');
$message = trim($data['message'] ?? '');
$agreement = isset($data['agreement']) && ($data['agreement'] === true || $data['agreement'] === 'on' || $data['agreement'] === '1') ? 1 : 0;

if ($fullname === '' || $email === '' || $phone === '' || $course === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Vui lòng điền đầy đủ thông tin bắt buộc (fullname, email, phone, course).']);
    exit;
}

// Simple email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email không hợp lệ.']);
    exit;
}

// ============================================
// GỬI EMAIL
// ============================================
$emailBody = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9fafb; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #374151; }
        .value { color: #1f2937; }
        .footer { padding: 15px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🎓 Đăng ký tư vấn mới</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <span class='label'>Họ tên:</span>
                <span class='value'>" . htmlspecialchars($fullname) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Email:</span>
                <span class='value'>" . htmlspecialchars($email) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Số điện thoại:</span>
                <span class='value'>" . htmlspecialchars($phone) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Khóa học quan tâm:</span>
                <span class='value'>" . htmlspecialchars($course) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Trình độ:</span>
                <span class='value'>" . htmlspecialchars($level ?: 'Chưa xác định') . "</span>
            </div>
            <div class='field'>
                <span class='label'>Tin nhắn:</span>
                <span class='value'>" . nl2br(htmlspecialchars($message ?: 'Không có')) . "</span>
            </div>
        </div>
        <div class='footer'>
            <p>Email được gửi tự động từ website Hải Âu English</p>
            <p>Thời gian: " . date('d/m/Y H:i:s') . "</p>
        </div>
    </div>
</body>
</html>
";

// Headers cho email HTML
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: ' . $fullname . ' <' . $email . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion()
];

// Gửi email
$mailSent = mail(
    $config['to_email'],
    $config['subject'] . ' - ' . $fullname,
    $emailBody,
    implode("\r\n", $headers)
);

if ($mailSent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ sớm nhất.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => 'Không thể gửi email. Vui lòng thử lại sau hoặc liên hệ trực tiếp.'
    ]);
}
exit;
