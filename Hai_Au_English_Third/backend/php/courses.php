<?php
// courses.php - Quản lý khóa học
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}
$mysqli = require __DIR__ . '/db.php';
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $result = $mysqli->query('SELECT * FROM courses');
    $courses = [];
    while ($row = $result->fetch_assoc()) $courses[] = $row;
    echo json_encode(['courses' => $courses]);
    exit;
}
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = $input ? json_decode($input, true) : $_POST;
    $name = trim($data['name'] ?? '');
    $desc = trim($data['description'] ?? '');
    if ($name === '') { http_response_code(400); echo json_encode(['error' => 'Thiếu tên khóa học']); exit; }
    $stmt = $mysqli->prepare('INSERT INTO courses (name, description) VALUES (?, ?)');
    $stmt->bind_param('ss', $name, $desc);
    if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error' => $stmt->error]); exit; }
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'Thiếu id']); exit; }
    $stmt = $mysqli->prepare('DELETE FROM courses WHERE id=?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error' => $stmt->error]); exit; }
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
