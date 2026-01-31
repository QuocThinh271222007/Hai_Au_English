<?php
// auth.php - Đăng ký, đăng nhập, xác thực người dùng
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

session_start();
$mysqli = require __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

// Check session
if ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'fullname' => $_SESSION['fullname'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Chưa đăng nhập']);
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
    
    // Auto login after register
    $_SESSION['user_id'] = $userId;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    
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
        if (!$isActive) {
            http_response_code(403);
            echo json_encode(['error' => 'Tài khoản đã bị khóa']);
            $stmt->close();
            exit;
        }
        
        if (password_verify($password, $hash)) {
            // Set session
            $_SESSION['user_id'] = $id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            
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
            echo json_encode(['error' => 'Sai email hoặc mật khẩu']);
        }
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Sai email hoặc mật khẩu']);
    }
    $stmt->close();
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Thiếu action (register/login/check/logout)']);
