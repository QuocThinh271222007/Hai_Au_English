<?php
/**
 * OAuth Callback Handler
 * Xử lý callback từ Google/Facebook OAuth
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/oauth_config.php';
require_once __DIR__ . '/session_config.php';

$mysqli = require __DIR__ . '/db.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$error = $_GET['error'] ?? '';

// Lấy frontend URL để redirect
$frontendBase = isLocalhost() ? '/hai_au_english' : '';
$loginUrl = $frontendBase . '/DangNhap';
$profileUrl = $frontendBase . '/TrangCaNhan';

// Handle errors from provider
if ($error) {
    $_SESSION['oauth_error'] = 'Đăng nhập bị hủy hoặc có lỗi xảy ra';
    header('Location: ' . $loginUrl . '?error=oauth_cancelled');
    exit;
}

// Verify state (CSRF protection)
if (!verifyOAuthState($state)) {
    $_SESSION['oauth_error'] = 'Invalid state token. Vui lòng thử lại.';
    header('Location: ' . $loginUrl . '?error=invalid_state');
    exit;
}

// Handle based on provider
try {
    switch ($provider) {
        case 'google':
            if (!GOOGLE_OAUTH_ENABLED) {
                throw new Exception('Google OAuth is not enabled');
            }
            $userData = handleGoogleCallback($code);
            break;
            
        case 'facebook':
            if (!FACEBOOK_OAUTH_ENABLED) {
                throw new Exception('Facebook OAuth is not enabled');
            }
            $userData = handleFacebookCallback($code);
            break;
            
        default:
            throw new Exception('Unknown OAuth provider');
    }
    
    // Create or update user
    $user = createOrUpdateOAuthUser($mysqli, $userData, $provider);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['oauth_provider'] = $provider;
    
    // Redirect to profile
    header('Location: ' . $profileUrl . '?oauth=success');
    exit;
    
} catch (Exception $e) {
    error_log('OAuth error: ' . $e->getMessage());
    $_SESSION['oauth_error'] = $e->getMessage();
    header('Location: ' . $loginUrl . '?error=oauth_failed');
    exit;
}

/**
 * Handle Google OAuth callback
 */
function handleGoogleCallback($code) {
    // Exchange code for access token
    $tokenData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($tokenData),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get Google access token');
    }
    
    $tokens = json_decode($response, true);
    $accessToken = $tokens['access_token'] ?? '';
    
    if (!$accessToken) {
        throw new Exception('Invalid Google access token');
    }
    
    // Get user info
    $ch = curl_init(GOOGLE_USERINFO_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get Google user info');
    }
    
    $userInfo = json_decode($response, true);
    
    return [
        'oauth_id' => $userInfo['id'],
        'email' => $userInfo['email'] ?? '',
        'fullname' => $userInfo['name'] ?? '',
        'avatar' => $userInfo['picture'] ?? '',
        'verified' => $userInfo['verified_email'] ?? false
    ];
}

/**
 * Handle Facebook OAuth callback
 */
function handleFacebookCallback($code) {
    // Exchange code for access token
    $tokenUrl = FACEBOOK_TOKEN_URL . '?' . http_build_query([
        'client_id' => FACEBOOK_APP_ID,
        'client_secret' => FACEBOOK_APP_SECRET,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'code' => $code
    ]);
    
    $ch = curl_init($tokenUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get Facebook access token');
    }
    
    $tokens = json_decode($response, true);
    $accessToken = $tokens['access_token'] ?? '';
    
    if (!$accessToken) {
        throw new Exception('Invalid Facebook access token');
    }
    
    // Get user info
    $userInfoUrl = FACEBOOK_USERINFO_URL . '?' . http_build_query([
        'fields' => 'id,name,email,picture.type(large)',
        'access_token' => $accessToken
    ]);
    
    $ch = curl_init($userInfoUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get Facebook user info');
    }
    
    $userInfo = json_decode($response, true);
    
    return [
        'oauth_id' => $userInfo['id'],
        'email' => $userInfo['email'] ?? '',
        'fullname' => $userInfo['name'] ?? '',
        'avatar' => $userInfo['picture']['data']['url'] ?? '',
        'verified' => true // Facebook requires verified email
    ];
}

/**
 * Create or update user from OAuth data
 */
function createOrUpdateOAuthUser($mysqli, $userData, $provider) {
    $email = $userData['email'];
    $oauthId = $userData['oauth_id'];
    $fullname = $userData['fullname'];
    $avatar = $userData['avatar'];
    
    if (empty($email)) {
        throw new Exception('Email không được cung cấp từ ' . $provider);
    }
    
    // Check if user exists by email
    $stmt = $mysqli->prepare('SELECT id, fullname, role, oauth_provider, oauth_id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingUser = $result->fetch_assoc();
    $stmt->close();
    
    if ($existingUser) {
        // User exists - update OAuth info if needed
        $userId = $existingUser['id'];
        
        // Update OAuth provider info and avatar
        $stmt = $mysqli->prepare('
            UPDATE users SET 
                oauth_provider = ?, 
                oauth_id = ?,
                avatar_url = COALESCE(avatar_url, ?),
                updated_at = NOW()
            WHERE id = ?
        ');
        $stmt->bind_param('sssi', $provider, $oauthId, $avatar, $userId);
        $stmt->execute();
        $stmt->close();
        
        return [
            'id' => $userId,
            'fullname' => $existingUser['fullname'],
            'email' => $email,
            'role' => $existingUser['role']
        ];
    } else {
        // Create new user
        $role = 'user';
        $isActive = 1;
        
        $stmt = $mysqli->prepare('
            INSERT INTO users (fullname, email, oauth_provider, oauth_id, avatar_url, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ');
        $stmt->bind_param('ssssssi', $fullname, $email, $provider, $oauthId, $avatar, $role, $isActive);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create user: ' . $stmt->error);
        }
        
        $userId = $mysqli->insert_id;
        $stmt->close();
        
        return [
            'id' => $userId,
            'fullname' => $fullname,
            'email' => $email,
            'role' => $role
        ];
    }
}
