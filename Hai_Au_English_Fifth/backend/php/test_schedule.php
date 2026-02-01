<?php
// Test file for schedule API
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

session_start();

// Fake login for testing
$_SESSION['user_id'] = 2; // Nguyễn Văn A

$mysqli = require __DIR__ . '/db.php';

$userId = $_SESSION['user_id'];

$stmt = $mysqli->prepare('
    SELECT s.*, 
        c.name as course_name,
        t.name as teacher_name
    FROM schedules s
    JOIN enrollments e ON s.enrollment_id = e.id
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN teachers t ON s.teacher_id = t.id
    WHERE e.user_id = ? AND s.is_active = 1 AND e.status = "active"
    ORDER BY FIELD(s.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"), s.start_time
');

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

echo json_encode(['success' => true, 'schedules' => $schedules, 'user_id' => $userId], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
