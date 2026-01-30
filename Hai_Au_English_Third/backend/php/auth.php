<?php
// auth.php - Đăng ký, đăng nhập, xác thực người dùng
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}
$mysqli = require __DIR__ . '/db.php';
$input = file_get_contents('php://input');
$data = $input ? json_decode($input, true) : $_POST;
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}
$action = $_GET['action'] ?? '';
if ($action === 'register') {
    $fullname = trim($data['fullname'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
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
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Email đã tồn tại']);
        exit;
    }
    $stmt->close();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare('INSERT INTO users (fullname, email, password, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('sss', $fullname, $email, $hash);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi đăng ký: ' . $stmt->error]);
        exit;
    }
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
} elseif ($action === 'login') {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    if ($email === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập email và mật khẩu']);
        exit;
    }
    $stmt = $mysqli->prepare('SELECT id, fullname, password FROM users WHERE email=?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($id, $fullname, $hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        echo json_encode(['success' => true, 'user' => ['id' => $id, 'fullname' => $fullname, 'email' => $email]]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Sai email hoặc mật khẩu']);
    }
    $stmt->close();
    exit;
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu action (register/login)']);
    exit;
}
