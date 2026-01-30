<?php
// contact.php - accepts POST from contact form and inserts into MySQL
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

// Load DB connection (returns mysqli)
$mysqli = require __DIR__ . '/db.php';

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

// Insert using prepared statement
$stmt = $mysqli->prepare("INSERT INTO contacts (fullname, email, phone, course, level, message, agreement, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param('sssssis', $fullname, $email, $phone, $course, $level, $message, $agreement);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$insertId = $stmt->insert_id;
$stmt->close();

echo json_encode(['success' => true, 'id' => $insertId]);
exit;
