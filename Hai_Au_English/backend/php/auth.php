<?php
// auth.php - Đăng ký, đăng nhập, xác thực người dùng
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/oauth_config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifications.php';

// Set CORS headers using config
setCorsHeaders();

require_once __DIR__ . '/session_config.php';
$mysqli = getMySQLiConnection();

$action = $_GET['action'] ?? '';

/**
 * Verify reCAPTCHA token if enabled
 * Supports reCAPTCHA v3
 */
function checkRecaptcha($data) {
    // Check if reCAPTCHA is disabled in config
    if (!RECAPTCHA_ENABLED || empty(RECAPTCHA_SECRET_KEY)) {
        return true;
    }
    
    // Skip reCAPTCHA on localhost (XAMPP)
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return true;
    }
    
    $token = $data['recaptcha_token'] ?? '';
    
    // No token = cannot verify
    if (empty($token)) {
        error_log('reCAPTCHA: No token provided by frontend');
        return false;
    }
    
    $result = verifyRecaptcha($token);
    
    if (!$result['success']) {
        error_log('reCAPTCHA verification failed: ' . ($result['error'] ?? 'unknown'));
    }
    
    return $result['success'] === true;
}

// Get OAuth URLs for frontend
if ($action === 'oauth_config') {
    $config = [
        'google' => [
            'enabled' => GOOGLE_OAUTH_ENABLED,
            'url' => GOOGLE_OAUTH_ENABLED ? getGoogleAuthUrl() : null
        ],
        'facebook' => [
            'enabled' => FACEBOOK_OAUTH_ENABLED,
            'url' => FACEBOOK_OAUTH_ENABLED ? getFacebookAuthUrl() : null
        ],
        'recaptcha' => [
            'enabled' => RECAPTCHA_ENABLED,
            'site_key' => RECAPTCHA_ENABLED ? RECAPTCHA_SITE_KEY : null
        ]
    ];
    echo json_encode(['success' => true, 'config' => $config]);
    exit;
}

// Check session
if ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        // Lấy thêm avatar từ database
        $stmt = $mysqli->prepare('SELECT avatar FROM users WHERE id = ?');
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'fullname' => $_SESSION['fullname'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role'],
                'avatar' => $user['avatar'] ?? null
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    }
    exit;
}

// Logout
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

$input = file_get_contents('php://input');
$data = $input ? json_decode($input, true) : $_POST;
if (!is_array($data)) {
    $data = [];
}

// Register
if ($action === 'register') {
    $fullname = trim($data['fullname'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $phone = trim($data['phone'] ?? '');
    
    // Verify reCAPTCHA
    if (!checkRecaptcha($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.']);
        exit;
    }
    
    if ($fullname === '' || $email === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập đầy đủ thông tin']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email không hợp lệ']);
        exit;
    }
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        exit;
    }
    
    // Validate số điện thoại: bắt buộc, chỉ chứa số, đúng 10 chữ số
    if ($phone === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập số điện thoại']);
        exit;
    }
    // Loại bỏ khoảng trắng và dấu gạch ngang nếu có
    $phone = preg_replace('/[\s\-]/', '', $phone);
    
    // Kiểm tra chỉ chứa số
    if (!preg_match('/^[0-9]+$/', $phone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Số điện thoại chỉ được chứa chữ số']);
        exit;
    }
    
    // Kiểm tra độ dài 10 số (chuẩn Việt Nam)
    if (strlen($phone) !== 10) {
        http_response_code(400);
        echo json_encode(['error' => 'Số điện thoại phải có đúng 10 chữ số']);
        exit;
    }
    
    // Kiểm tra đầu số hợp lệ (Việt Nam: 03, 05, 07, 08, 09)
    if (!preg_match('/^(03|05|07|08|09)[0-9]{8}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Số điện thoại không hợp lệ (phải bắt đầu bằng 03, 05, 07, 08 hoặc 09)']);
        exit;
    }
    
    // Kiểm tra email đã tồn tại
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        http_response_code(409);
        echo json_encode(['error' => 'Email đã được đăng ký. Vui lòng sử dụng email khác hoặc đăng nhập.']);
        exit;
    }
    $stmt->close();
    
    // Kiểm tra số điện thoại đã tồn tại
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE phone=? AND phone != ""');
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        http_response_code(409);
        echo json_encode(['error' => 'Số điện thoại đã được đăng ký. Vui lòng sử dụng số khác.']);
        exit;
    }
    $stmt->close();
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';
    
    $stmt = $mysqli->prepare('INSERT INTO users (fullname, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('sssss', $fullname, $email, $hash, $phone, $role);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi đăng ký: ' . $stmt->error]);
        exit;
    }
    
    $userId = $mysqli->insert_id;
    $stmt->close();
    
    // Tạo thông báo cho admin
    createAdminNotification('user', 'Người dùng mới đăng ký', 'Người dùng "' . $fullname . '" đã đăng ký tài khoản. Email: ' . $email, $userId, 'users');
    
    // Auto login after register
    $_SESSION['user_id'] = $userId;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['last_activity'] = time();
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $userId,
            'fullname' => $fullname,
            'email' => $email,
            'role' => $role
        ]
    ]);
    exit;
}

// Login
if ($action === 'login') {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    // Verify reCAPTCHA
    if (!checkRecaptcha($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.']);
        exit;
    }
    
    if ($email === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập email và mật khẩu']);
        exit;
    }
    
    $stmt = $mysqli->prepare('SELECT id, fullname, password, role, is_active FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($id, $fullname, $hash, $role, $isActive);
    
    if ($stmt->fetch()) {
        $stmt->close();
        
        if (!$isActive) {
            http_response_code(403);
            echo json_encode(['error' => 'Tài khoản đã bị khóa. Vui lòng liên hệ admin.']);
            exit;
        }
        
        if (password_verify($password, $hash)) {
            // Set session
            $_SESSION['user_id'] = $id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['last_activity'] = time();
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $id,
                    'fullname' => $fullname,
                    'email' => $email,
                    'role' => $role
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Mật khẩu không chính xác']);
        }
    } else {
        $stmt->close();
        http_response_code(404);
        echo json_encode(['error' => 'Tài khoản không tồn tại. Vui lòng kiểm tra lại email hoặc đăng ký mới.']);
    }
    exit;
}

// ============================================
// FORGOT PASSWORD - Gửi email reset
// ============================================
if ($action === 'forgot_password') {
    $email = trim($data['email'] ?? '');
    
    if ($email === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập email']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email không hợp lệ']);
        exit;
    }
    
    // Kiểm tra email có tồn tại không
    $stmt = $mysqli->prepare('SELECT id, fullname FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Email không tồn tại trong hệ thống']);
        exit;
    }
    
    // Kiểm tra xem đã gửi request gần đây chưa (chống spam - 2 phút)
    $stmt = $mysqli->prepare('SELECT id FROM password_resets WHERE email = ? AND expires_at > NOW() AND used = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        http_response_code(429);
        echo json_encode(['error' => 'Vui lòng đợi 2 phút trước khi gửi yêu cầu mới']);
        exit;
    }
    $stmt->close();
    
    // Tạo token ngẫu nhiên
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
    
    // Xóa token cũ của email này
    $stmt = $mysqli->prepare('DELETE FROM password_resets WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();
    
    // Lưu token mới
    $stmt = $mysqli->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $email, $token, $expiresAt);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi hệ thống. Vui lòng thử lại.']);
        exit;
    }
    $stmt->close();
    
    // Tạo link reset password
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = isLocalhost() ? '/hai_au_english' : '';
    $resetLink = "{$protocol}://{$host}{$basePath}/QuenMatKhau?token={$token}";
    
    // Gửi email
    require_once __DIR__ . '/../service/EmailService.php';
    $emailService = new EmailService();
    
    $expiryMinutes = PASSWORD_RESET_EXPIRY / 60;
    $emailContent = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 30px; background: #f9fafb; border-radius: 0 0 8px 8px; }
            .btn { display: inline-block; padding: 14px 28px; background: #2563eb; color: white !important; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
            .btn:hover { background: #1d4ed8; }
            .warning { color: #dc2626; font-size: 14px; margin-top: 20px; }
            .footer { text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🔐 Đặt lại mật khẩu</h2>
            </div>
            <div class='content'>
                <p>Xin chào <strong>{$user['fullname']}</strong>,</p>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại Hải Âu English.</p>
                <p>Nhấn nút bên dưới để đặt lại mật khẩu:</p>
                <p style='text-align: center;'>
                    <a href='{$resetLink}' class='btn'>Đặt lại mật khẩu</a>
                </p>
                <p>Hoặc copy link sau vào trình duyệt:</p>
                <p style='background: #e5e7eb; padding: 10px; border-radius: 4px; word-break: break-all; font-size: 13px;'>{$resetLink}</p>
                <p class='warning'>⚠️ Link này sẽ hết hạn sau <strong>{$expiryMinutes} phút</strong>.</p>
                <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
            </div>
            <div class='footer'>
                <p>Email tự động từ Hải Âu English - Vui lòng không trả lời email này.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $result = $emailService->send($email, '[Hải Âu English] Đặt lại mật khẩu', $emailContent);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã gửi email hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra hộp thư (và cả thư mục Spam).'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Không thể gửi email. Vui lòng thử lại sau.']);
    }
    exit;
}

// ============================================
// VERIFY RESET TOKEN - Kiểm tra token còn hợp lệ không
// ============================================
if ($action === 'verify_reset_token') {
    $token = trim($data['token'] ?? '');
    
    if ($token === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Token không hợp lệ']);
        exit;
    }
    
    $stmt = $mysqli->prepare('SELECT email, expires_at FROM password_resets WHERE token = ? AND used = 0');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();
    $stmt->close();
    
    if (!$reset) {
        http_response_code(404);
        echo json_encode(['error' => 'Link đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng']);
        exit;
    }
    
    if (strtotime($reset['expires_at']) < time()) {
        http_response_code(410);
        echo json_encode(['error' => 'Link đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu link mới.']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'email' => $reset['email']
    ]);
    exit;
}

// ============================================
// RESET PASSWORD - Đặt mật khẩu mới
// ============================================
if ($action === 'reset_password') {
    $token = trim($data['token'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    
    if ($token === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Token không hợp lệ']);
        exit;
    }
    
    if ($password === '' || strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        exit;
    }
    
    if ($password !== $confirmPassword) {
        http_response_code(400);
        echo json_encode(['error' => 'Mật khẩu xác nhận không khớp']);
        exit;
    }
    
    // Kiểm tra token
    $stmt = $mysqli->prepare('SELECT email, expires_at FROM password_resets WHERE token = ? AND used = 0');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $reset = $result->fetch_assoc();
    $stmt->close();
    
    if (!$reset) {
        http_response_code(404);
        echo json_encode(['error' => 'Link đặt lại mật khẩu không hợp lệ hoặc đã được sử dụng']);
        exit;
    }
    
    if (strtotime($reset['expires_at']) < time()) {
        http_response_code(410);
        echo json_encode(['error' => 'Link đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu link mới.']);
        exit;
    }
    
    // Cập nhật mật khẩu
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('UPDATE users SET password = ? WHERE email = ?');
    $stmt->bind_param('ss', $hash, $reset['email']);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi cập nhật mật khẩu. Vui lòng thử lại.']);
        exit;
    }
    $stmt->close();
    
    // Đánh dấu token đã sử dụng
    $stmt = $mysqli->prepare('UPDATE password_resets SET used = 1 WHERE token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập với mật khẩu mới.'
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Thiếu action (register/login/check/logout/forgot_password/reset_password)']);
