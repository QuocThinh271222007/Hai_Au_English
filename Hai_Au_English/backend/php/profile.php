<?php
// profile.php - API cho thông tin profile user
error_reporting(0); // Suppress errors in production
ini_set('display_errors', 0);

// Include db.php first (which includes config.php)
require_once __DIR__ . '/db.php';

// Set CORS headers using config
setCorsHeaders();

require_once __DIR__ . '/session_config.php';
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
            $stmt = $mysqli->prepare('SELECT id, fullname, email, phone, avatar, date_of_birth, gender, address, role, created_at FROM users WHERE id = ?');
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
                // Tính tiến độ tự động theo thời gian
                if ($row['status'] === 'completed') {
                    $row['progress'] = 100;
                } elseif ($row['start_date'] && $row['end_date']) {
                    $now = time();
                    $start = strtotime($row['start_date']);
                    $end = strtotime($row['end_date']);
                    
                    if ($now < $start) {
                        $row['progress'] = 0;
                    } elseif ($now >= $end) {
                        $row['progress'] = 100;
                    } else {
                        $total = $end - $start;
                        $elapsed = $now - $start;
                        $row['progress'] = $total > 0 ? min(100, max(0, round(($elapsed / $total) * 100))) : 0;
                    }
                }
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
                SELECT f.*, c.name as course_name, t.name as teacher_name, t.image_url as teacher_avatar, e.academic_year, e.semester
                FROM feedback f
                JOIN enrollments e ON f.enrollment_id = e.id
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
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
            // Tiến độ học tập - trả về danh sách enrollments với progress tính tự động theo thời gian
            $stmt = $mysqli->prepare('
                SELECT e.*, c.name as course_name, c.level, c.duration, c.total_sessions
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.user_id = ? AND e.status IN ("active", "completed")
                ORDER BY e.status ASC, e.start_date DESC
            ');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $enrollments = [];
            while ($row = $result->fetch_assoc()) {
                // Tính tiến độ tự động theo thời gian nếu chưa có hoặc đang active
                if ($row['status'] === 'completed') {
                    $row['progress'] = 100;
                } elseif ($row['start_date'] && $row['end_date']) {
                    $now = time();
                    $start = strtotime($row['start_date']);
                    $end = strtotime($row['end_date']);
                    
                    if ($now < $start) {
                        $row['progress'] = 0;
                    } elseif ($now >= $end) {
                        $row['progress'] = 100;
                    } else {
                        $total = $end - $start;
                        $elapsed = $now - $start;
                        $row['progress'] = $total > 0 ? min(100, max(0, round(($elapsed / $total) * 100))) : 0;
                    }
                }
                $enrollments[] = $row;
            }
            echo json_encode(['success' => true, 'enrollments' => $enrollments]);
            break;
            
        case 'dashboard':
            // Tổng hợp tất cả cho dashboard
            // User info
            $stmt = $mysqli->prepare('SELECT id, fullname, email, phone, avatar, date_of_birth, gender, role, created_at FROM users WHERE id = ?');
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
                ORDER BY f.created_at DESC
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
            // Thời khóa biểu - lấy từ bảng class_schedules
            // Check if class_schedules table exists
            $tableCheck = $mysqli->query("SHOW TABLES LIKE 'class_schedules'");
            if ($tableCheck->num_rows === 0) {
                echo json_encode(['success' => true, 'schedules' => [], 'message' => 'Bảng class_schedules chưa được tạo']);
                break;
            }
            
            $stmt = $mysqli->prepare('
                SELECT DISTINCT
                    cs.id,
                    cs.day_of_week,
                    cs.start_time,
                    cs.end_time,
                    cs.room,
                    cs.is_online,
                    cs.meeting_link,
                    cs.notes,
                    cl.name as class_name,
                    cl.id as class_id,
                    c.id as course_id,
                    c.name as course_name,
                    c.image_url,
                    t.name as teacher_name,
                    t.image_url as teacher_avatar,
                    CASE 
                        WHEN cs.start_time < "12:00:00" THEN "morning"
                        WHEN cs.start_time < "17:00:00" THEN "afternoon"
                        ELSE "evening"
                    END as session,
                    c.name as title
                FROM class_schedules cs
                JOIN classes cl ON cs.class_id = cl.id
                JOIN courses c ON cl.course_id = c.id
                JOIN enrollments e ON e.class_id = cl.id
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                WHERE e.user_id = ? 
                    AND cs.is_active = 1 
                    AND e.status = "active"
                    AND cl.status = "active"
                ORDER BY FIELD(cs.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"), cs.start_time
            ');
            
            if (!$stmt) {
                echo json_encode(['success' => true, 'schedules' => []]);
                break;
            }
            
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedules = [];
            while ($row = $result->fetch_assoc()) {
                // Calculate period from start_time
                $startHour = intval(substr($row['start_time'], 0, 2));
                $startMin = intval(substr($row['start_time'], 3, 2));
                $endHour = intval(substr($row['end_time'], 0, 2));
                $endMin = intval(substr($row['end_time'], 3, 2));
                
                // Approximate period (45 min per period)
                if ($startHour < 12) {
                    $row['period'] = max(1, min(6, $startHour - 6));
                } elseif ($startHour < 17) {
                    $row['period'] = max(7, min(12, $startHour - 6));
                } else {
                    $row['period'] = max(13, min(15, $startHour - 5));
                }
                
                // Calculate period count (duration / 45 min)
                $duration = ($endHour * 60 + $endMin) - ($startHour * 60 + $startMin);
                $row['period_count'] = max(1, round($duration / 45));
                
                // Add color based on course
                $colors = ['#1e40af', '#059669', '#dc2626', '#7c3aed', '#ea580c', '#0891b2'];
                $row['color'] = $colors[$row['course_id'] % count($colors)];
                
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

// PUT - Cập nhật thông tin profile hoặc đổi mật khẩu
if ($method === 'PUT') {
    $userId = checkAuth();
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Kiểm tra nếu là action đổi mật khẩu
    if (isset($data['action']) && $data['action'] === 'change_password') {
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            http_response_code(400);
            echo json_encode(['error' => 'Vui lòng nhập đầy đủ thông tin']);
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
            exit;
        }
        
        // Lấy mật khẩu hiện tại từ database
        $stmt = $mysqli->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Mật khẩu hiện tại không đúng']);
            exit;
        }
        
        // Cập nhật mật khẩu mới
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra']);
        }
        exit;
    }
    
    $allowedFields = ['fullname', 'phone', 'date_of_birth', 'gender', 'address', 'avatar'];
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

// POST - Upload avatar
if ($method === 'POST') {
    $userId = checkAuth();
    
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    if ($action === 'upload-avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Không có file được upload hoặc có lỗi']);
            exit;
        }
        
        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)']);
            exit;
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            http_response_code(400);
            echo json_encode(['error' => 'File quá lớn. Tối đa 5MB']);
            exit;
        }
        
        // Tạo tên file unique
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        
        // Đường dẫn upload
        $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        $avatarUrl = '/frontend/assets/images/uploads/avatars/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Xóa avatar cũ nếu có
            $stmt = $mysqli->prepare('SELECT avatar FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $oldAvatar = $result->fetch_assoc()['avatar'] ?? null;
            
            if ($oldAvatar && strpos($oldAvatar, '/uploads/avatars/') !== false) {
                $oldPath = __DIR__ . '/../../' . ltrim($oldAvatar, '/');
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Cập nhật database
            $stmt = $mysqli->prepare('UPDATE users SET avatar = ? WHERE id = ?');
            $stmt->bind_param('si', $avatarUrl, $userId);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Upload avatar thành công',
                    'avatar' => $avatarUrl
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Lỗi cập nhật database']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Lỗi upload file']);
        }
        exit;
    }
    
    // Student self-enrollment
    if ($action === 'enroll-course') {
        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = intval($input['course_id'] ?? 0);
        
        if (!$courseId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vui lòng chọn khóa học']);
            exit;
        }
        
        // Check if course exists and is active
        $stmt = $mysqli->prepare('SELECT id, name, is_active FROM courses WHERE id = ?');
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        
        if (!$course) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Khóa học không tồn tại']);
            exit;
        }
        
        if (!$course['is_active']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Khóa học này đã đóng đăng ký']);
            exit;
        }
        
        // Check if already enrolled
        $stmt = $mysqli->prepare('SELECT id, status FROM enrollments WHERE user_id = ? AND course_id = ?');
        $stmt->bind_param('ii', $userId, $courseId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            $statusText = [
                'pending' => 'đang chờ duyệt',
                'active' => 'đang học',
                'completed' => 'đã hoàn thành',
                'cancelled' => 'đã hủy'
            ];
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'Bạn đã đăng ký khóa học này (' . ($statusText[$existing['status']] ?? $existing['status']) . ')'
            ]);
            exit;
        }
        
        // Create enrollment
        $academicYear = date('Y') . '-' . (date('Y') + 1);
        $semester = (date('n') >= 8) ? 'Học kỳ 1' : 'Học kỳ 2';
        $enrolledDate = date('Y-m-d');
        $status = 'pending'; // Need admin approval
        
        $stmt = $mysqli->prepare('
            INSERT INTO enrollments (user_id, course_id, academic_year, semester, enrolled_date, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->bind_param('iissss', $userId, $courseId, $academicYear, $semester, $enrolledDate, $status);
        
        if ($stmt->execute()) {
            // Create notification for admin
            require_once __DIR__ . '/notifications.php';
            
            $userStmt = $mysqli->prepare('SELECT fullname FROM users WHERE id = ?');
            $userStmt->bind_param('i', $userId);
            $userStmt->execute();
            $userName = $userStmt->get_result()->fetch_assoc()['fullname'] ?? 'Học viên';
            
            createAdminNotification(
                'enrollment', 
                'Đăng ký khóa học mới', 
                "Học viên {$userName} đăng ký khóa học \"{$course['name']}\"",
                $mysqli->insert_id,
                'enrollments'
            );
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đăng ký khóa học thành công! Vui lòng chờ admin xác nhận.',
                'enrollment_id' => $mysqli->insert_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Lỗi khi đăng ký: ' . $mysqli->error]);
        }
        exit;
    }
    
    // Cancel pending enrollment
    if ($action === 'cancel-enrollment') {
        $input = json_decode(file_get_contents('php://input'), true);
        $enrollmentId = intval($input['enrollment_id'] ?? 0);
        
        if (!$enrollmentId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID đăng ký không hợp lệ']);
            exit;
        }
        
        // Check if enrollment exists and belongs to current user
        $stmt = $mysqli->prepare('SELECT id, status, course_id FROM enrollments WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $enrollmentId, $userId);
        $stmt->execute();
        $enrollment = $stmt->get_result()->fetch_assoc();
        
        if (!$enrollment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy đăng ký']);
            exit;
        }
        
        // Only allow cancelling pending enrollments
        if ($enrollment['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chỉ có thể hủy đăng ký đang chờ duyệt']);
            exit;
        }
        
        // Delete the enrollment
        $stmt = $mysqli->prepare('DELETE FROM enrollments WHERE id = ?');
        $stmt->bind_param('i', $enrollmentId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Đã hủy đăng ký']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Lỗi khi hủy đăng ký']);
        }
        exit;
    }
    
    http_response_code(400);
    echo json_encode(['error' => 'Action không hợp lệ']);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);