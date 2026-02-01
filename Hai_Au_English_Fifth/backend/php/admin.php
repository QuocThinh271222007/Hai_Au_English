<?php
/**
 * Admin API - Quản lý admin dashboard
 * Bao gồm CRUD cho users, courses, enrollments, scores, feedback, teachers
 * Hỗ trợ soft delete (thùng rác) và khôi phục
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

session_start();

// Kiểm tra quyền admin
function checkAdmin() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền admin']);
        exit;
    }
    
    return $_SESSION['user_id'];
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // ==================== DASHBOARD ====================
        case 'stats':
            checkAdmin();
            
            $stats = [];
            
            // Tổng học viên
            $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
            $stats['users'] = $stmt->fetchColumn();
            
            // Đăng ký đang học
            $stmt = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'");
            $stats['enrollments'] = $stmt->fetchColumn();
            
            // Tổng khóa học
            $stmt = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1");
            $stats['courses'] = $stmt->fetchColumn();
            
            // Tổng giảng viên
            $stmt = $pdo->query("SELECT COUNT(*) FROM teachers WHERE is_active = 1");
            $stats['teachers'] = $stmt->fetchColumn();
            
            // Thùng rác
            $stmt = $pdo->query("SELECT COUNT(*) FROM trash WHERE is_restored = 0");
            $stats['trash'] = $stmt->fetchColumn();
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'recent-enrollments':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT e.*, u.fullname, u.email, c.name as course_name
                FROM enrollments e
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
                ORDER BY e.created_at DESC
                LIMIT 10
            ");
            $enrollments = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $enrollments]);
            break;
            
        // ==================== USERS ====================
        case 'users':
            checkAdmin();
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $pdo->query("
                    SELECT id, fullname, email, phone, role, is_active, created_at, updated_at
                    FROM users
                    ORDER BY created_at DESC
                ");
                $users = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $users]);
            }
            break;
        
        case 'user-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Kiểm tra dữ liệu bắt buộc
            if (empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
                break;
            }
            
            // Kiểm tra email đã tồn tại
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email này đã được đăng ký']);
                break;
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (fullname, email, phone, password, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'student', ?, NOW())
            ");
            $stmt->execute([
                $data['fullname'],
                $data['email'],
                $data['phone'] ?? '',
                $hashedPassword,
                $data['is_active'] ?? 1
            ]);
            
            $newId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Thêm học viên thành công', 'id' => $newId]);
            break;
            
        case 'user-update':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['id'] ?? 0;
            
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    fullname = ?, email = ?, phone = ?, 
                    is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['fullname'],
                $data['email'],
                $data['phone'],
                $data['is_active'] ?? 1,
                $userId
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
            break;
            
        case 'user-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['id'] ?? 0;
            
            // Không cho xóa admin
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user && $user['role'] === 'admin') {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản admin']);
                break;
            }
            
            // Di chuyển vào thùng rác
            moveToTrash('users', $userId, $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== COURSES ====================
        case 'courses':
            checkAdmin();
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
                $courses = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $courses]);
            }
            break;
            
        case 'course-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO courses (name, description, image_url, level, duration, price, price_unit, category, badge, target, total_sessions, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['image_url'] ?? '',
                $data['level'] ?? 'beginner',
                $data['duration'] ?? '',
                $data['price'] ?? 0,
                $data['price_unit'] ?? '/khóa',
                $data['category'] ?? 'group',
                $data['badge'] ?? null,
                $data['target'] ?? '',
                $data['total_sessions'] ?? 0,
                $data['is_active'] ?? 1
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm khóa học thành công', 'id' => $pdo->lastInsertId()]);
            break;
            
        case 'course-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE courses SET 
                    name = ?, description = ?, image_url = ?, level = ?, 
                    duration = ?, price = ?, price_unit = ?, category = ?,
                    badge = ?, target = ?, total_sessions = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['image_url'] ?? '',
                $data['level'] ?? 'beginner',
                $data['duration'] ?? '',
                $data['price'] ?? 0,
                $data['price_unit'] ?? '/khóa',
                $data['category'] ?? 'group',
                $data['badge'] ?? null,
                $data['target'] ?? '',
                $data['total_sessions'] ?? 0,
                $data['is_active'] ?? 1,
                $data['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật khóa học thành công']);
            break;
            
        case 'course-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('courses', $data['id'], $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== ENROLLMENTS ====================
        case 'enrollments':
            checkAdmin();
            
            $status = $_GET['status'] ?? '';
            
            $sql = "
                SELECT e.*, u.fullname, u.email, c.name as course_name
                FROM enrollments e
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
            ";
            
            if ($status) {
                $sql .= " WHERE e.status = ?";
                $stmt = $pdo->prepare($sql . " ORDER BY e.created_at DESC");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->query($sql . " ORDER BY e.created_at DESC");
            }
            
            $enrollments = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $enrollments]);
            break;
            
        case 'enrollment-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO enrollments (user_id, course_id, academic_year, semester, start_date, end_date, status, progress)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['user_id'],
                $data['course_id'],
                $data['academic_year'] ?? '',
                $data['semester'] ?? '',
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['status'] ?? 'pending',
                $data['progress'] ?? 0
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm đăng ký thành công']);
            break;
            
        case 'enrollment-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE enrollments SET 
                    academic_year = ?, semester = ?, start_date = ?, 
                    end_date = ?, status = ?, progress = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['academic_year'],
                $data['semester'],
                $data['start_date'],
                $data['end_date'],
                $data['status'],
                $data['progress'],
                $data['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
            break;
            
        case 'enrollment-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('enrollments', $data['id'], $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== TEACHERS ====================
        case 'teachers':
            checkAdmin();
            
            $stmt = $pdo->query("SELECT * FROM teachers ORDER BY created_at DESC");
            $teachers = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $teachers]);
            break;
            
        case 'teacher-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO teachers (name, title, description, image_url, ielts_score, experience_years, students_count, rating, specialties, is_featured, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['image_url'] ?? '',
                $data['ielts_score'] ?? null,
                $data['experience_years'] ?? 0,
                $data['students_count'] ?? 0,
                $data['rating'] ?? 0,
                json_encode($data['specialties'] ?? []),
                $data['is_featured'] ?? 0,
                $data['is_active'] ?? 1
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm giảng viên thành công']);
            break;
            
        case 'teacher-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE teachers SET 
                    name = ?, title = ?, description = ?, image_url = ?,
                    ielts_score = ?, experience_years = ?, students_count = ?,
                    rating = ?, specialties = ?, is_featured = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['title'] ?? '',
                $data['description'] ?? '',
                $data['image_url'] ?? '',
                $data['ielts_score'] ?? null,
                $data['experience_years'] ?? 0,
                $data['students_count'] ?? 0,
                $data['rating'] ?? 0,
                json_encode($data['specialties'] ?? []),
                $data['is_featured'] ?? 0,
                $data['is_active'] ?? 1,
                $data['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật giảng viên thành công']);
            break;
            
        case 'teacher-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('teachers', $data['id'], $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== SCORES ====================
        case 'scores':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT s.*, u.fullname, c.name as course_name
                FROM scores s
                JOIN users u ON s.user_id = u.id
                LEFT JOIN enrollments e ON s.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                ORDER BY s.created_at DESC
            ");
            $scores = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $scores]);
            break;
            
        case 'score-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Tính overall
            $overall = ($data['listening'] + $data['reading'] + $data['writing'] + $data['speaking']) / 4;
            $overall = round($overall * 2) / 2; // Làm tròn 0.5
            
            $stmt = $pdo->prepare("
                INSERT INTO scores (enrollment_id, user_id, listening, reading, writing, speaking, overall, test_date, test_type, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['enrollment_id'],
                $data['user_id'],
                $data['listening'],
                $data['reading'],
                $data['writing'],
                $data['speaking'],
                $overall,
                $data['test_date'],
                $data['test_type'] ?? 'mock',
                $data['notes'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm điểm thành công']);
            break;
            
        case 'score-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $overall = ($data['listening'] + $data['reading'] + $data['writing'] + $data['speaking']) / 4;
            $overall = round($overall * 2) / 2;
            
            $stmt = $pdo->prepare("
                UPDATE scores SET 
                    listening = ?, reading = ?, writing = ?, speaking = ?, 
                    overall = ?, test_date = ?, test_type = ?, notes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['listening'],
                $data['reading'],
                $data['writing'],
                $data['speaking'],
                $overall,
                $data['test_date'],
                $data['test_type'],
                $data['notes'] ?? '',
                $data['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật điểm thành công']);
            break;
            
        case 'score-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('scores', $data['id'], $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== FEEDBACK ====================
        case 'feedback':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT f.*, u.fullname as student_name, t.name as teacher_name, c.name as course_name
                FROM feedback f
                JOIN users u ON f.user_id = u.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                LEFT JOIN enrollments e ON f.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                ORDER BY f.created_at DESC
            ");
            $feedback = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $feedback]);
            break;
            
        case 'feedback-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO feedback (enrollment_id, user_id, teacher_id, content, rating, feedback_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['enrollment_id'],
                $data['user_id'],
                $data['teacher_id'] ?? null,
                $data['content'],
                $data['rating'] ?? null,
                $data['feedback_date'] ?? date('Y-m-d')
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm nhận xét thành công']);
            break;
            
        case 'feedback-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('feedback', $data['id'], $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== SCHEDULES (THỜI KHÓA BIỂU) ====================
        case 'schedules':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT s.*, 
                    u.fullname as student_name,
                    c.name as course_name,
                    t.name as teacher_name
                FROM schedules s
                JOIN enrollments e ON s.enrollment_id = e.id
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN teachers t ON s.teacher_id = t.id
                WHERE s.is_active = 1
                ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
            ");
            $schedules = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $schedules]);
            break;
            
        case 'schedule-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO schedules (enrollment_id, teacher_id, title, description, day_of_week, start_time, end_time, room, is_online, meeting_link, color)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['enrollment_id'],
                $data['teacher_id'] ?: null,
                $data['title'],
                $data['description'] ?? '',
                $data['day_of_week'],
                $data['start_time'],
                $data['end_time'],
                $data['room'] ?? '',
                $data['is_online'] ?? 0,
                $data['meeting_link'] ?? '',
                $data['color'] ?? '#3b82f6'
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Thêm lịch học thành công']);
            break;
            
        case 'schedule-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE schedules SET 
                    teacher_id = ?, title = ?, description = ?, day_of_week = ?,
                    start_time = ?, end_time = ?, room = ?, is_online = ?, meeting_link = ?, color = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['teacher_id'] ?: null,
                $data['title'],
                $data['description'] ?? '',
                $data['day_of_week'],
                $data['start_time'],
                $data['end_time'],
                $data['room'] ?? '',
                $data['is_online'] ?? 0,
                $data['meeting_link'] ?? '',
                $data['color'] ?? '#3b82f6',
                $data['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật lịch học thành công']);
            break;
            
        case 'schedule-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('schedules', $data['id'], $adminId);
            
            echo json_encode(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== TRASH (THÙNG RÁC) ====================
        case 'trash':
            checkAdmin();
            
            $table = $_GET['table'] ?? '';
            
            $sql = "SELECT * FROM trash WHERE is_restored = 0";
            if ($table) {
                $sql .= " AND original_table = ?";
                $stmt = $pdo->prepare($sql . " ORDER BY deleted_at DESC");
                $stmt->execute([$table]);
            } else {
                $stmt = $pdo->query($sql . " ORDER BY deleted_at DESC");
            }
            
            $trash = $stmt->fetchAll();
            
            // Parse JSON data
            foreach ($trash as &$item) {
                $item['data'] = json_decode($item['data'], true);
            }
            
            echo json_encode(['success' => true, 'data' => $trash]);
            break;
            
        case 'trash-restore':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $trashId = $data['id'] ?? 0;
            
            // Lấy thông tin từ thùng rác
            $stmt = $pdo->prepare("SELECT * FROM trash WHERE id = ? AND is_restored = 0");
            $stmt->execute([$trashId]);
            $trashItem = $stmt->fetch();
            
            if (!$trashItem) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy dữ liệu']);
                break;
            }
            
            $originalData = json_decode($trashItem['data'], true);
            $tableName = $trashItem['original_table'];
            
            // Khôi phục dữ liệu
            restoreFromTrash($tableName, $originalData);
            
            // Đánh dấu đã khôi phục
            $stmt = $pdo->prepare("UPDATE trash SET is_restored = 1, restored_at = NOW() WHERE id = ?");
            $stmt->execute([$trashId]);
            
            echo json_encode(['success' => true, 'message' => 'Khôi phục thành công']);
            break;
            
        case 'trash-delete-permanent':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("DELETE FROM trash WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Đã xóa vĩnh viễn']);
            break;
            
        case 'trash-empty':
            checkAdmin();
            
            $table = $_GET['table'] ?? '';
            
            if ($table) {
                $stmt = $pdo->prepare("DELETE FROM trash WHERE original_table = ? AND is_restored = 0");
                $stmt->execute([$table]);
            } else {
                $pdo->exec("DELETE FROM trash WHERE is_restored = 0");
            }
            
            echo json_encode(['success' => true, 'message' => 'Đã dọn sạch thùng rác']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Di chuyển dữ liệu vào thùng rác
 */
function moveToTrash($tableName, $recordId, $deletedBy) {
    global $pdo;
    
    // Lấy dữ liệu từ bảng gốc
    $stmt = $pdo->prepare("SELECT * FROM `$tableName` WHERE id = ?");
    $stmt->execute([$recordId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        throw new Exception('Không tìm thấy dữ liệu');
    }
    
    // Chèn vào thùng rác
    $stmt = $pdo->prepare("
        INSERT INTO trash (original_table, original_id, data, deleted_by, deleted_at, expires_at)
        VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH))
    ");
    $stmt->execute([$tableName, $recordId, json_encode($data), $deletedBy]);
    
    // Xóa khỏi bảng gốc
    $stmt = $pdo->prepare("DELETE FROM `$tableName` WHERE id = ?");
    $stmt->execute([$recordId]);
}

/**
 * Khôi phục dữ liệu từ thùng rác
 */
function restoreFromTrash($tableName, $data) {
    global $pdo;
    
    $originalId = $data['id'];
    
    // Kiểm tra xem ID đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM `$tableName` WHERE id = ?");
    $stmt->execute([$originalId]);
    
    if ($stmt->fetch()) {
        throw new Exception('Dữ liệu với ID này đã tồn tại');
    }
    
    // Loại bỏ updated_at nếu có (sẽ tự động set)
    unset($data['updated_at']);
    
    // Tạo câu INSERT
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    $columnsStr = implode(', ', array_map(function($col) { return "`$col`"; }, $columns));
    $placeholdersStr = implode(', ', $placeholders);
    
    $sql = "INSERT INTO `$tableName` ($columnsStr) VALUES ($placeholdersStr)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
}
