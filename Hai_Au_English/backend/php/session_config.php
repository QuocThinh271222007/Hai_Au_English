<?php
// session_config.php - Central session configuration for production
// Include this file before any output to configure and start PHP session

// Include config if not already included
if (!defined('COOKIE_DOMAIN')) {
    require_once __DIR__ . '/config.php';
}

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

// Don't output anything
return;
