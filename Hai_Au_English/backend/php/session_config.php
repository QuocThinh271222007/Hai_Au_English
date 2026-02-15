<?php
// session_config.php - Central session configuration for production
// Include this file before any output to configure and start PHP session

// Include config if not already included
if (!defined('COOKIE_DOMAIN')) {
    require_once __DIR__ . '/config.php';
}

// Session inactivity timeout (15 minutes)
define('SESSION_INACTIVITY_TIMEOUT', 15 * 60);

// Get cookie domain from config (auto-detected)
$cookieDomain = COOKIE_DOMAIN;

// Detect HTTPS
$secureFlag = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
              (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

// Session cookie configuration
$sessionParams = [
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureFlag,
    'httponly' => true,
    'samesite' => 'Lax'
];

// Only set domain if not localhost
if (!empty($cookieDomain)) {
    $sessionParams['domain'] = $cookieDomain;
}

session_set_cookie_params($sessionParams);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session inactivity timeout
function checkSessionInactivity() {
    if (isset($_SESSION['user_id'])) {
        $currentTime = time();
        
        // Check if last activity exists
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = $currentTime - $_SESSION['last_activity'];
            
            // If inactive for more than timeout, destroy session
            if ($inactiveTime > SESSION_INACTIVITY_TIMEOUT) {
                session_unset();
                session_destroy();
                return false; // Session expired
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = $currentTime;
        return true; // Session active
    }
    return false; // No user logged in
}

// Check and update session activity (call on every authenticated request)
checkSessionInactivity();

// Don't output anything
return;
