<?php
// profile.php - API cho thông tin profile user
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

session_start();
$mysqli = require __DIR__ . '/db.php';
$method = $_SERVER['REQUEST_METHOD'];

// Kiểm tra đăng nhập
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Chưa đăng nhập']);
        exit;
    }
    return $_SESSION['user_id'];
}

// GET - Lấy thông tin profile
if ($method === 'GET') {
    $userId = checkAuth();
    
    // Lấy action
    $action = $_GET['action'] ?? 'profile';
    
    switch ($action) {
        case 'profile':
            // Thông tin cá nhân
            $stmt = $mysqli->prepare('SELECT id, fullname, email, phone, avatar_url, date_of_birth, gender, address, role, created_at FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Không tìm thấy user']);
            }
            break;
            
        case 'enrollments':
            // Danh sách khóa học đã đăng ký
            $stmt = $mysqli->prepare('
                SELECT e.*, c.name as course_name, c.level, c.duration, c.image_url, c.total_sessions
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.user_id = ?
                ORDER BY e.created_at DESC
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $enrollments = [];
            while ($row = $result->fetch_assoc()) {
                $enrollments[] = $row;
            }
            echo json_encode(['success' => true, 'enrollments' => $enrollments]);
            break;
            
        case 'scores':
            // Điểm số
            $stmt = $mysqli->prepare('
                SELECT s.*, c.name as course_name, e.academic_year, e.semester
                FROM scores s
                JOIN enrollments e ON s.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
                WHERE s.user_id = ?
                ORDER BY s.test_date DESC
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $scores = [];
            while ($row = $result->fetch_assoc()) {
                $scores[] = $row;
            }
            echo json_encode(['success' => true, 'scores' => $scores]);
            break;
            
        case 'feedback':
            // Nhận xét từ giảng viên
            $stmt = $mysqli->prepare('
                SELECT f.*, c.name as course_name, t.name as teacher_name, e.academic_year, e.semester
                FROM feedback f
                JOIN enrollments e ON f.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                WHERE f.user_id = ?
                ORDER BY f.feedback_date DESC
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                $feedback[] = $row;
            }
            echo json_encode(['success' => true, 'feedback' => $feedback]);
            break;
            
        case 'progress':
            // Tiến độ học tập tổng hợp
            $stmt = $mysqli->prepare('
                SELECT 
                    COUNT(CASE WHEN e.status = "completed" THEN 1 END) as completed_courses,
                    COUNT(CASE WHEN e.status = "active" THEN 1 END) as active_courses,
                    AVG(CASE WHEN e.status = "active" THEN e.progress END) as avg_progress,
                    (SELECT overall FROM scores WHERE user_id = ? ORDER BY test_date DESC LIMIT 1) as latest_score
                FROM enrollments e
                WHERE e.user_id = ?
            ');
            $stmt->bind_param('ii', $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $progress = $result->fetch_assoc();
            echo json_encode(['success' => true, 'progress' => $progress]);
            break;
            
        case 'dashboard':
            // Tổng hợp tất cả cho dashboard
            // User info
            $stmt = $mysqli->prepare('SELECT id, fullname, email, phone, avatar_url, date_of_birth, gender, role, created_at FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            // Enrollments
            $stmt = $mysqli->prepare('
                SELECT e.*, c.name as course_name, c.level, c.duration, c.total_sessions
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.user_id = ?
                ORDER BY e.created_at DESC
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Latest scores
            $stmt = $mysqli->prepare('
                SELECT s.*, c.name as course_name
                FROM scores s
                JOIN enrollments e ON s.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
                WHERE s.user_id = ?
                ORDER BY s.test_date DESC
                LIMIT 5
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $scores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            // Latest feedback
            $stmt = $mysqli->prepare('
                SELECT f.*, c.name as course_name, t.name as teacher_name
                FROM feedback f
                JOIN enrollments e ON f.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                WHERE f.user_id = ?
                ORDER BY f.feedback_date DESC
                LIMIT 3
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $feedback = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode([
                'success' => true,
                'user' => $user,
                'enrollments' => $enrollments,
                'scores' => $scores,
                'feedback' => $feedback
            ]);
            break;
            
        case 'schedule':
            // Thời khóa biểu
            $stmt = $mysqli->prepare('
                SELECT s.*, 
                    c.name as course_name,
                    t.name as teacher_name,
                    t.email as teacher_email_from_teacher
                FROM schedules s
                JOIN enrollments e ON s.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN teachers t ON s.teacher_id = t.id
                WHERE e.user_id = ? AND s.is_active = 1 AND e.status = "active"
                ORDER BY FIELD(s.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"), s.start_time
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedules = [];
            while ($row = $result->fetch_assoc()) {
                // Use teacher_email from schedules table if set, otherwise from teachers table
                if (empty($row['teacher_email']) && !empty($row['teacher_email_from_teacher'])) {
                    $row['teacher_email'] = $row['teacher_email_from_teacher'];
                }
                unset($row['teacher_email_from_teacher']);
                $schedules[] = $row;
            }
            echo json_encode(['success' => true, 'schedules' => $schedules]);
            break;
            
        case 'scores-chart':
            // Điểm cho biểu đồ
            $stmt = $mysqli->prepare('
                SELECT test_date, test_type, listening, reading, writing, speaking, overall
                FROM scores
                WHERE user_id = ?
                ORDER BY test_date ASC
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $timeline = [];
            while ($row = $result->fetch_assoc()) {
                $timeline[] = $row;
            }
            
            // Tính trung bình các kỹ năng
            $stmt = $mysqli->prepare('
                SELECT 
                    AVG(listening) as avg_listening,
                    AVG(reading) as avg_reading,
                    AVG(writing) as avg_writing,
                    AVG(speaking) as avg_speaking
                FROM scores
                WHERE user_id = ?
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $averages = $stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true, 
                'timeline' => $timeline,
                'averages' => $averages
            ]);
            break;
            
        case 'stats':
            // Thống kê cho dashboard
            $stats = [];
            
            // Khóa học đang học
            $stmt = $mysqli->prepare('SELECT COUNT(*) as cnt FROM enrollments WHERE user_id = ? AND status = "active"');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stats['active_courses'] = $stmt->get_result()->fetch_assoc()['cnt'];
            
            // Khóa học hoàn thành
            $stmt = $mysqli->prepare('SELECT COUNT(*) as cnt FROM enrollments WHERE user_id = ? AND status = "completed"');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stats['completed_courses'] = $stmt->get_result()->fetch_assoc()['cnt'];
            
            // Điểm IELTS cao nhất
            $stmt = $mysqli->prepare('SELECT MAX(overall) as max_score FROM scores WHERE user_id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stats['highest_score'] = $stmt->get_result()->fetch_assoc()['max_score'] ?? 0;
            
            // Tiến độ trung bình
            $stmt = $mysqli->prepare('SELECT AVG(progress) as avg_progress FROM enrollments WHERE user_id = ? AND status IN ("active", "completed")');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stats['avg_progress'] = round($stmt->get_result()->fetch_assoc()['avg_progress'] ?? 0);
            
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Action không hợp lệ']);
    }
    exit;
}

// PUT - Cập nhật thông tin profile
if ($method === 'PUT') {
    $userId = checkAuth();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $allowedFields = ['fullname', 'phone', 'date_of_birth', 'gender', 'address', 'avatar_url'];
    $updates = [];
    $params = [];
    $types = '';
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "`$field` = ?";
            $params[] = $data[$field];
            $types .= 's';
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'Không có dữ liệu để cập nhật']);
        exit;
    }
    
    $params[] = $userId;
    $types .= 'i';
    
    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $stmt->error]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
