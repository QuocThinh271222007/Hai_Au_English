<?php

/**
 * Central Configuration File for Hai Au English Backend
 * 
 * HƯỚNG DẪN DEPLOY TRÊN HOSTINGER:
 * 1. Thay đổi các thông tin database bên dưới
 * 2. Thay đổi FRONTEND_URL thành domain của bạn
 * 3. Upload toàn bộ project lên public_html
 */

// Prevent multiple includes
if (defined('HAI_AU_CONFIG_LOADED')) {
    return;
}
define('HAI_AU_CONFIG_LOADED', true);

// ============================================
// TỰ ĐỘNG DETECT MÔI TRƯỜNG (XAMPP vs HOSTINGER)
// ============================================

function isLocalhost()
{
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    return (strpos($host, 'localhost') !== false ||
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, 'haiauenglish_test.edu.vn') !== false ||
        strpos($host, 'haiauenglish-test.edu.vn') !== false);
}

// ============================================
// CẤU HÌNH DATABASE
// ============================================

if (isLocalhost()) {
    // ===== XAMPP LOCAL =====
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');  // XAMPP mặc định không có password
    define('DB_NAME', 'hai_au_english');
} else {
    // ===== HOSTINGER PRODUCTION =====
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'u636600488_Quang153');  // Username MySQL từ Hostinger
    define('DB_PASS', getenv('DB_PASS') ?: 'THAY_PASSWORD_CUA_BAN');   // ⚠️ THAY BẰNG PASSWORD MYSQL CỦA BẠN
    define('DB_NAME', getenv('DB_NAME') ?: 'u636600488_haiau'); // Tên database từ Hostinger
}

// ============================================
// CẤU HÌNH CORS/DOMAIN
// ============================================

// Tự động detect domain từ request
function getOrigin()
{
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        return $_SERVER['HTTP_ORIGIN'];
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $protocol . '://' . $host;
}

// Allowed origins (thêm domain của bạn vào đây)
define('ALLOWED_ORIGINS', [
    'https://haiauenglish.edu.vn',
    'https://www.haiauenglish.edu.vn',
    'http://haiauenglish_test.edu.vn',
    'http://localhost',
    'http://localhost:5500',      // Live Server
    'http://localhost:3000',      // React dev
    'http://127.0.0.1:5500',
]);

// Lấy frontend URL cho CORS
function getFrontendUrl()
{
    $origin = getOrigin();

    // Nếu origin trong whitelist, cho phép
    if (in_array($origin, ALLOWED_ORIGINS)) {
        return $origin;
    }

    // Kiểm tra xem có phải cùng domain không
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($origin, $host) !== false) {
        return $origin;
    }

    // Default: production domain
    return 'https://haiauenglish.edu.vn';
}

define('FRONTEND_URL', getFrontendUrl());

// ============================================
// CẤU HÌNH SESSION
// ============================================

// Cookie domain - để trống để tự động detect
function getCookieDomain()
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Localhost không cần domain
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return '';
    }

    // Loại bỏ port nếu có
    $host = preg_replace('/:\d+$/', '', $host);

    // Thêm dấu chấm phía trước để áp dụng cho subdomain
    return '.' . $host;
}

define('COOKIE_DOMAIN', getCookieDomain());

// ============================================
// CẤU HÌNH EMAIL (cho contact form)
// ============================================

define('ADMIN_EMAIL', 'haiauenglish@gmail.com');  // Email nhận thông báo
define('ADMIN_NAME', 'Hải Âu English');

// SMTP (send email)
define('SMTP_USERNAME', 'haiauenglish@gmail.com');
define('SMTP_SECRET', 'pzglabqcxoqiapmo');
define('SMTP_HOST', 'smtp.gmail.com');

// quan trọng (người nhận mail)
define('SHOP_OWNER', 'haiauenglish@gmail.com');

<<<<<<< HEAD
// Token expiration time (for password reset)
define('PASSWORD_RESET_EXPIRY', 15 * 60); // 15 phút

=======
>>>>>>> f4b95be7fe27c8af6a8f6a6cbb258ea29d4a6733
// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Set CORS headers - gọi ở đầu mỗi API endpoint
 */
function setCorsHeaders()
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * JSON response helper
 * Wrapped in function_exists to avoid conflicts with API-specific implementations
 */
if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Error response helper
 */
if (!function_exists('errorResponse')) {
    function errorResponse($message, $statusCode = 400)
    {
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Success response helper
 */
if (!function_exists('successResponse')) {
    function successResponse($data = [], $message = 'Success')
    {
        $response = ['success' => true, 'message' => $message];
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        http_response_code(200);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
