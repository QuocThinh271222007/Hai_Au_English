<?php
// Debug session
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

require_once 'php/session_config.php';

echo json_encode([
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'role_exists' => isset($_SESSION['role']),
    'role_value' => $_SESSION['role'] ?? 'NOT SET',
    'is_admin' => isset($_SESSION['role']) && $_SESSION['role'] === 'admin'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
