<?php
// users.php - Quản lý user (admin)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Set CORS headers using config
setCorsHeaders();

$mysqli = getMySQLiConnection();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $result = $mysqli->query('SELECT id, fullname, email, created_at FROM users');
    $users = [];
    while ($row = $result->fetch_assoc()) $users[] = $row;
    echo json_encode(['users' => $users]);
    exit;
}
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'Thiếu id']); exit; }
    $stmt = $mysqli->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) { http_response_code(500); echo json_encode(['error' => $stmt->error]); exit; }
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
