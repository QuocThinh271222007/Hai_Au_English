<?php
// Database connection file - DO NOT output anything here

// Prevent multiple includes
if (defined('HAI_AU_DB_LOADED')) {
    return $mysqli ?? null;
}
define('HAI_AU_DB_LOADED', true);

// Include config file
require_once __DIR__ . '/config.php';

// Use constants from config
$DB_HOST = DB_HOST;
$DB_USER = DB_USER;
$DB_PASS = DB_PASS;
$DB_NAME = DB_NAME;

// MySQLi connection (for auth.php, contact.php, etc.)
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$mysqli->set_charset('utf8mb4');

// PDO connection (for admin.php, reviews.php, achievements.php)
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // PDO is optional, only needed for some files
    $pdo = null;
}

/**
 * Get PDO database connection
 * @return PDO
 */
function getDBConnection() {
    global $pdo;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }
    
    return $pdo;
}

/**
 * Get MySQLi database connection
 * @return mysqli
 */
function getMySQLiConnection() {
    global $mysqli;
    return $mysqli;
}

return $mysqli;
