<?php
/**
 * OAuth & Security Configuration
 * Hải Âu English - Cấu hình đăng nhập mạng xã hội và bảo mật
 * 
 * ⚠️ HƯỚNG DẪN THIẾT LẬP - ĐỌC FILE OAUTH_SETUP.md
 */

// Prevent direct access
if (!defined('HAI_AU_CONFIG_LOADED')) {
    die('Direct access not allowed');
}

// ============================================
// GOOGLE RECAPTCHA v3 (Chống Bot)
// ============================================
// Lấy keys tại: https://www.google.com/recaptcha/admin
// Chọn reCAPTCHA v3

define('RECAPTCHA_ENABLED', true);
define('RECAPTCHA_SITE_KEY', '6LceJmwsAAAAAIfrfs2SL-x4D8s1dSRVahQ3Aw8X');
define('RECAPTCHA_SECRET_KEY', '6LceJmwsAAAAAJBGxJDcnR-7qNWDOz07zpiNTP9s');
define('RECAPTCHA_MIN_SCORE', 0.5);
// ============================================
// GOOGLE OAUTH 2.0
// ============================================
// Thiết lập tại: https://console.cloud.google.com/apis/credentials
// 1. Tạo project mới (hoặc chọn project có sẵn)
// 2. APIs & Services > Credentials > Create Credentials > OAuth client ID
// 3. Application type: Web application
// 4. Authorized redirect URIs: 
//    - http://localhost/hai_au_english/backend/php/oauth_callback.php?provider=google (dev)
//    - https://yourdomain.com/backend/php/oauth_callback.php?provider=google (production)

define('GOOGLE_OAUTH_ENABLED', false);  // Đặt true khi đã có credentials
define('GOOGLE_CLIENT_ID', '');        // ⚠️ THAY BẰNG CLIENT ID CỦA BẠN
define('GOOGLE_CLIENT_SECRET', '');    // ⚠️ THAY BẰNG CLIENT SECRET CỦA BẠN

// ============================================
// FACEBOOK OAUTH
// ============================================
// Thiết lập tại: https://developers.facebook.com/apps/
// 1. Create App > Consumer
// 2. Settings > Basic: lấy App ID và App Secret
// 3. Facebook Login > Settings:
//    - Valid OAuth Redirect URIs:
//      - http://localhost/hai_au_english/backend/php/oauth_callback.php?provider=facebook (dev)
//      - https://yourdomain.com/backend/php/oauth_callback.php?provider=facebook (production)
// 4. App Review: Submit for review để public app (production)

define('FACEBOOK_OAUTH_ENABLED', false);  // Đặt true khi đã có credentials
define('FACEBOOK_APP_ID', '');           // ⚠️ THAY BẰNG APP ID CỦA BẠN
define('FACEBOOK_APP_SECRET', '');       // ⚠️ THAY BẰNG APP SECRET CỦA BẠN

// ============================================
// OAUTH REDIRECT URLS (Tự động detect)
// ============================================

function getOAuthCallbackUrl($provider) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Detect base path
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = '';
    if (stripos($scriptPath, '/hai_au_english/') !== false) {
        $basePath = '/hai_au_english';
    }
    
    return "{$protocol}://{$host}{$basePath}/backend/php/oauth_callback.php?provider={$provider}";
}

define('GOOGLE_REDIRECT_URI', getOAuthCallbackUrl('google'));
define('FACEBOOK_REDIRECT_URI', getOAuthCallbackUrl('facebook'));

// ============================================
// OAUTH ENDPOINTS
// ============================================

// Google OAuth URLs
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Facebook OAuth URLs
define('FACEBOOK_AUTH_URL', 'https://www.facebook.com/v18.0/dialog/oauth');
define('FACEBOOK_TOKEN_URL', 'https://graph.facebook.com/v18.0/oauth/access_token');
define('FACEBOOK_USERINFO_URL', 'https://graph.facebook.com/v18.0/me');

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Verify reCAPTCHA v3 token
 * @param string $token Token từ frontend
 * @param string $action Expected action name
 * @return array ['success' => bool, 'score' => float, 'error' => string]
 */
function verifyRecaptcha($token, $action = 'login') {
    // Disable reCAPTCHA for localhost testing
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return ['success' => true, 'score' => 1.0, 'message' => 'reCAPTCHA disabled for localhost'];
    }
    
    if (!RECAPTCHA_ENABLED || empty(RECAPTCHA_SECRET_KEY)) {
        return ['success' => true, 'score' => 1.0, 'message' => 'reCAPTCHA disabled'];
    }
    
    if (empty($token)) {
        return ['success' => false, 'score' => 0, 'error' => 'Missing reCAPTCHA token'];
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        // If verification fails, allow login but log it
        error_log('reCAPTCHA verification failed - network error');
        return ['success' => true, 'score' => 0.5, 'message' => 'Verification skipped'];
    }
    
    $response = json_decode($result, true);
    
    if (!$response['success']) {
        return [
            'success' => false, 
            'score' => 0, 
            'error' => 'reCAPTCHA verification failed: ' . implode(', ', $response['error-codes'] ?? ['unknown'])
        ];
    }
    
    // Check action matches
    if (isset($response['action']) && $response['action'] !== $action) {
        return ['success' => false, 'score' => 0, 'error' => 'Invalid reCAPTCHA action'];
    }
    
    // Check score
    $score = $response['score'] ?? 0;
    if ($score < RECAPTCHA_MIN_SCORE) {
        return [
            'success' => false, 
            'score' => $score, 
            'error' => 'reCAPTCHA score too low (possible bot)'
        ];
    }
    
    return ['success' => true, 'score' => $score];
}

/**
 * Generate OAuth state token for CSRF protection
 */
function generateOAuthState() {
    $state = bin2hex(random_bytes(32));
    $_SESSION['oauth_state'] = $state;
    return $state;
}

/**
 * Verify OAuth state token
 */
function verifyOAuthState($state) {
    if (empty($state) || empty($_SESSION['oauth_state'])) {
        return false;
    }
    $valid = hash_equals($_SESSION['oauth_state'], $state);
    unset($_SESSION['oauth_state']); // Clear after use
    return $valid;
}

/**
 * Get Google OAuth Authorization URL
 */
function getGoogleAuthUrl() {
    if (!GOOGLE_OAUTH_ENABLED || empty(GOOGLE_CLIENT_ID)) {
        return null;
    }
    
    $state = generateOAuthState();
    
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'offline',
        'state' => $state,
        'prompt' => 'select_account'
    ];
    
    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}

/**
 * Get Facebook OAuth Authorization URL
 */
function getFacebookAuthUrl() {
    if (!FACEBOOK_OAUTH_ENABLED || empty(FACEBOOK_APP_ID)) {
        return null;
    }
    
    $state = generateOAuthState();
    
    $params = [
        'client_id' => FACEBOOK_APP_ID,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email,public_profile',
        'state' => $state
    ];
    
    return FACEBOOK_AUTH_URL . '?' . http_build_query($params);
}
