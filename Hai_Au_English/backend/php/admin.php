<?php
/**
 * Admin API - Quản lý admin dashboard
 * Bao gồm CRUD cho users, courses, enrollments, scores, feedback, teachers
 * Hỗ trợ soft delete (thùng rác) và khôi phục
 */

require_once __DIR__ . '/config.php';

// Set CORS headers using config
setCorsHeaders();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_config.php';
require_once __DIR__ . '/notifications.php';

// Helper function for JSON encoding with UTF-8 support
function json_response($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Kiểm tra quyền admin
function checkAdmin() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        json_response(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        json_response(['success' => false, 'message' => 'Không có quyền admin']);
        exit;
    }
    
    return $_SESSION['user_id'];
}

// Helper function to update class schedule summary text
function updateClassScheduleSummary($pdo, $classId) {
    $dayNames = [
        'monday' => 'T2',
        'tuesday' => 'T3',
        'wednesday' => 'T4',
        'thursday' => 'T5',
        'friday' => 'T6',
        'saturday' => 'T7',
        'sunday' => 'CN'
    ];
    
    $stmt = $pdo->prepare("
        SELECT day_of_week, TIME_FORMAT(start_time, '%H:%i') as start_time, TIME_FORMAT(end_time, '%H:%i') as end_time
        FROM class_schedules 
        WHERE class_id = ? AND is_active = 1
        ORDER BY FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), start_time
    ");
    $stmt->execute([$classId]);
    $schedules = $stmt->fetchAll();
    
    if (empty($schedules)) {
        $summary = null;
    } else {
        $parts = [];
        foreach ($schedules as $s) {
            $dayVN = $dayNames[$s['day_of_week']] ?? $s['day_of_week'];
            $parts[] = "{$dayVN} ({$s['start_time']}-{$s['end_time']})";
        }
        $summary = implode(', ', $parts);
    }
    
    $stmt = $pdo->prepare("UPDATE classes SET schedule = ? WHERE id = ?");
    $stmt->execute([$summary, $classId]);
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // ==================== ADMIN PROFILE ====================
        case 'upload-avatar':
            $adminId = checkAdmin();
            
            if (!isset($_FILES['avatar'])) {
                json_response(['success' => false, 'message' => 'Không có file được upload']);
                break;
            }
            
            $file = $_FILES['avatar'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                json_response(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
                break;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'File quá lớn. Tối đa 5MB']);
                break;
            }
            
            // Create upload directory if not exists
            $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/avatars/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . $adminId . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;
            
            // Delete old avatar if exists
            $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt->execute([$adminId]);
            $oldAvatar = $stmt->fetchColumn();
            
            if ($oldAvatar && file_exists(__DIR__ . '/../../' . $oldAvatar)) {
                unlink(__DIR__ . '/../../' . $oldAvatar);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $avatarUrl = '/frontend/assets/images/uploads/avatars/' . $filename;
                
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$avatarUrl, $adminId]);
                
                json_response(['success' => true, 'avatar' => $avatarUrl]);
            } else {
                json_response(['success' => false, 'message' => 'Không thể lưu file']);
            }
            break;
            
        case 'change-password':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                json_response(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
                break;
            }
            
            if (strlen($newPassword) < 6) {
                json_response(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
                break;
            }
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$adminId]);
            $user = $stmt->fetch();
            
            if (!password_verify($currentPassword, $user['password'])) {
                json_response(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
                break;
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $adminId]);
            
            json_response(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            break;
            
        case 'get-profile':
            $adminId = checkAdmin();
            
            $stmt = $pdo->prepare("SELECT id, fullname, email, phone, avatar FROM users WHERE id = ?");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch();
            
            json_response(['success' => true, 'data' => $admin]);
            break;

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
            
            json_response(['success' => true, 'data' => $stats]);
            break;
            
        case 'recent-enrollments':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT e.*, u.fullname, u.email, c.name as course_name
                FROM enrollments e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN courses c ON e.course_id = c.id
                ORDER BY e.created_at DESC
                LIMIT 10
            ");
            $enrollments = $stmt->fetchAll();
            
            json_response(['success' => true, 'data' => $enrollments]);
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
                json_response(['success' => true, 'data' => $users]);
            }
            break;
        
        case 'user-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Kiểm tra dữ liệu bắt buộc
            if (empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
                json_response(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
                break;
            }
            
            // Kiểm tra email đã tồn tại
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                json_response(['success' => false, 'message' => 'Email này đã được đăng ký']);
                break;
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (fullname, email, phone, password, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'user', ?, NOW())
            ");
            $stmt->execute([
                $data['fullname'],
                $data['email'],
                $data['phone'] ?? '',
                $hashedPassword,
                $data['is_active'] ?? 1
            ]);
            
            $newId = $pdo->lastInsertId();
            createAdminNotification('user', 'Học viên mới', 'Đã thêm học viên "' . ($data['fullname'] ?? '') . '" vào hệ thống', $newId, 'users');
            
            json_response(['success' => true, 'message' => 'Thêm học viên thành công', 'id' => $newId]);
            break;
            
        case 'user-update':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['id'] ?? 0;
            
            if (!$userId) {
                json_response(['success' => false, 'message' => 'Thiếu ID người dùng']);
                break;
            }
            
            // Get current user data first
            $stmt = $pdo->prepare("SELECT fullname, email, phone, is_active FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentUser = $stmt->fetch();
            
            if (!$currentUser) {
                json_response(['success' => false, 'message' => 'Không tìm thấy người dùng']);
                break;
            }
            
            // Merge with existing data for partial updates
            $fullname = $data['fullname'] ?? $currentUser['fullname'];
            $email = $data['email'] ?? $currentUser['email'];
            $phone = $data['phone'] ?? $currentUser['phone'];
            $isActive = isset($data['is_active']) ? $data['is_active'] : $currentUser['is_active'];
            
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    fullname = ?, email = ?, phone = ?, 
                    is_active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$fullname, $email, $phone, $isActive, $userId]);
            createAdminNotification('user', 'Cập nhật học viên', 'Thông tin học viên "' . $fullname . '" đã được cập nhật', $userId, 'users');
            
            json_response(['success' => true, 'message' => 'Cập nhật thành công']);
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
                json_response(['success' => false, 'message' => 'Không thể xóa tài khoản admin']);
                break;
            }
            
            // Di chuyển vào thùng rác
            moveToTrash('users', $userId, $adminId);
            createAdminNotification('user', 'Xóa học viên', 'Học viên đã được chuyển vào thùng rác', $userId, 'users');
            
            json_response(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== COURSES ====================
        case 'courses':
            checkAdmin();
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
                $courses = $stmt->fetchAll();
                json_response(['success' => true, 'data' => $courses]);
            }
            break;
            
        case 'course-create':
            checkAdmin();
            
            // Handle both FormData and JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $data = $_POST;
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                        $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/courses/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'course_' . uniqid() . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                            $data['image_url'] = '/frontend/assets/images/uploads/courses/' . $filename;
                        }
                    }
                }
                // Parse features if string
                if (isset($data['features']) && is_string($data['features'])) {
                    $data['features'] = array_filter(array_map('trim', explode("\n", $data['features'])));
                }
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
            }
            
            // Handle features array
            $features = isset($data['features']) && is_array($data['features']) 
                ? json_encode($data['features']) 
                : '[]';
            
            $stmt = $pdo->prepare("
                INSERT INTO courses (name, description, image_url, level, duration, curriculum, price, price_unit, category, badge, target, total_sessions, is_active, features)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['image_url'] ?? '',
                $data['level'] ?? '',
                $data['duration'] ?? '',
                $data['curriculum'] ?? '',
                $data['price'] ?? 0,
                $data['price_unit'] ?? '/tháng',
                $data['category'] ?? 'group',
                $data['badge'] ?? null,
                $data['target'] ?? '',
                $data['total_sessions'] ?? 0,
                $data['is_active'] ?? 1,
                $features
            ]);
            
            $courseId = $pdo->lastInsertId();
            createAdminNotification('course', 'Khóa học mới', 'Đã thêm khóa học "' . ($data['name'] ?? '') . '"', $courseId, 'courses');
            
            json_response(['success' => true, 'message' => 'Thêm khóa học thành công', 'id' => $courseId]);
            break;
            
        case 'course-update':
            checkAdmin();
            
            // Handle both FormData and JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $data = $_POST;
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                        // Delete old image if exists
                        $courseId = $data['id'] ?? 0;
                        if ($courseId) {
                            $oldStmt = $pdo->prepare("SELECT image_url FROM courses WHERE id = ?");
                            $oldStmt->execute([$courseId]);
                            $oldImage = $oldStmt->fetchColumn();
                            if ($oldImage && strpos($oldImage, '/uploads/courses/') !== false) {
                                $oldPath = __DIR__ . '/../../' . ltrim($oldImage, '/');
                                if (file_exists($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                        }
                        
                        $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/courses/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'course_' . uniqid() . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                            $data['image_url'] = '/frontend/assets/images/uploads/courses/' . $filename;
                        }
                    }
                }
                // Parse features if string
                if (isset($data['features']) && is_string($data['features'])) {
                    $data['features'] = array_filter(array_map('trim', explode("\n", $data['features'])));
                }
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
            }
            
            // Handle features array
            $features = isset($data['features']) && is_array($data['features']) 
                ? json_encode($data['features']) 
                : '[]';
            
            $stmt = $pdo->prepare("
                UPDATE courses SET 
                    name = ?, description = ?, image_url = ?, level = ?, 
                    duration = ?, curriculum = ?, price = ?, price_unit = ?, category = ?,
                    badge = ?, target = ?, total_sessions = ?, is_active = ?, features = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['image_url'] ?? '',
                $data['level'] ?? '',
                $data['duration'] ?? '',
                $data['curriculum'] ?? '',
                $data['price'] ?? 0,
                $data['price_unit'] ?? '/tháng',
                $data['category'] ?? 'group',
                $data['badge'] ?? null,
                $data['target'] ?? '',
                $data['total_sessions'] ?? 0,
                $data['is_active'] ?? 1,
                $features,
                $data['id']
            ]);
            createAdminNotification('course', 'Cập nhật khóa học', 'Khóa học "' . ($data['name'] ?? '') . '" đã được cập nhật', $data['id'], 'courses');
            
            json_response(['success' => true, 'message' => 'Cập nhật khóa học thành công']);
            break;
            
        case 'course-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Lấy tên khóa học trước khi xóa
            $courseStmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
            $courseStmt->execute([$data['id']]);
            $courseName = $courseStmt->fetchColumn() ?: 'Khóa học';
            
            moveToTrash('courses', $data['id'], $adminId);
            createAdminNotification('course', 'Xóa khóa học', 'Khóa học "' . $courseName . '" đã được chuyển vào thùng rác', $data['id'], 'courses');
            
            json_response(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== ENROLLMENTS ====================
        case 'enrollments':
            checkAdmin();
            
            $status = $_GET['status'] ?? '';
            
            $sql = "
                SELECT e.*, u.fullname, u.email, c.name as course_name, cl.name as class_name
                FROM enrollments e
                LEFT JOIN users u ON e.user_id = u.id
                LEFT JOIN courses c ON e.course_id = c.id
                LEFT JOIN classes cl ON e.class_id = cl.id
            ";
            
            if ($status) {
                $sql .= " WHERE e.status = ?";
                $stmt = $pdo->prepare($sql . " ORDER BY e.created_at DESC");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->query($sql . " ORDER BY e.created_at DESC");
            }
            
            $enrollments = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $enrollments]);
            break;
            
        case 'enrollment-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO enrollments (user_id, course_id, class_id, academic_year, semester, start_date, end_date, status, progress)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['user_id'],
                $data['course_id'],
                $data['class_id'] ?? null,
                $data['academic_year'] ?? '',
                $data['semester'] ?? '',
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['status'] ?? 'pending',
                $data['progress'] ?? 0
            ]);
            
            $enrollmentId = $pdo->lastInsertId();
            createAdminNotification('enrollment', 'Đăng ký mới', 'Có đăng ký khóa học mới', $enrollmentId, 'enrollments');
            
            json_response(['success' => true, 'message' => 'Thêm đăng ký thành công', 'id' => $enrollmentId]);
            break;
            
        case 'enrollment-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $enrollmentId = $data['id'] ?? 0;
            
            if (!$enrollmentId) {
                json_response(['success' => false, 'message' => 'Thiếu ID đăng ký']);
                break;
            }
            
            // Get current enrollment data for partial updates
            $stmt = $pdo->prepare("SELECT academic_year, semester, start_date, end_date, status, progress, class_id FROM enrollments WHERE id = ?");
            $stmt->execute([$enrollmentId]);
            $current = $stmt->fetch();
            
            if (!$current) {
                json_response(['success' => false, 'message' => 'Không tìm thấy đăng ký']);
                break;
            }
            
            $stmt = $pdo->prepare("
                UPDATE enrollments SET 
                    academic_year = ?, semester = ?, start_date = ?, 
                    end_date = ?, status = ?, progress = ?, class_id = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['academic_year'] ?? $current['academic_year'],
                $data['semester'] ?? $current['semester'],
                $data['start_date'] ?? $current['start_date'],
                $data['end_date'] ?? $current['end_date'],
                $data['status'] ?? $current['status'],
                $data['progress'] ?? $current['progress'],
                isset($data['class_id']) ? ($data['class_id'] ?: null) : $current['class_id'],
                $enrollmentId
            ]);
            createAdminNotification('enrollment', 'Cập nhật đăng ký', 'Đăng ký khóa học #' . $enrollmentId . ' đã được cập nhật', $enrollmentId, 'enrollments');
            
            json_response(['success' => true, 'message' => 'Cập nhật thành công']);
            break;
            
        case 'enrollment-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('enrollments', $data['id'], $adminId);
            createAdminNotification('enrollment', 'Xóa đăng ký', 'Đăng ký khóa học #' . $data['id'] . ' đã được chuyển vào thùng rác', $data['id'], 'enrollments');
            
            json_response(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== CLASSES (LỚP HỌC) ====================
        case 'classes':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT c.*, 
                       co.name as course_name,
                       t.name as teacher_name,
                       (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as student_count
                FROM classes c 
                LEFT JOIN courses co ON c.course_id = co.id
                LEFT JOIN teachers t ON c.teacher_id = t.id
                ORDER BY c.created_at DESC
            ");
            $classes = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $classes]);
            break;
            
        case 'class-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $name = $data['name'] ?? '';
            $courseId = $data['course_id'] ?? null;
            $teacherId = $data['teacher_id'] ?: null;
            $maxStudents = $data['max_students'] ?? 20;
            $schedule = $data['schedule'] ?? '';
            $room = $data['room'] ?? '';
            $startDate = $data['start_date'] ?: null;
            $endDate = $data['end_date'] ?: null;
            $academicYear = $data['academic_year'] ?? '';
            $semester = $data['semester'] ?? '';
            $status = $data['status'] ?? 'upcoming';
            $description = $data['description'] ?? '';
            
            if (!$name || !$courseId) {
                json_response(['success' => false, 'message' => 'Tên lớp và khóa học là bắt buộc']);
                break;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO classes (name, course_id, teacher_id, max_students, schedule, room, 
                                    start_date, end_date, academic_year, semester, status, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $courseId, $teacherId, $maxStudents, $schedule, $room,
                           $startDate, $endDate, $academicYear, $semester, $status, $description]);
            
            $classId = $pdo->lastInsertId();
            createAdminNotification('class', 'Lớp học mới', 'Đã tạo lớp học "' . $name . '"', $classId, 'classes');
            
            json_response(['success' => true, 'message' => 'Tạo lớp học thành công', 'id' => $classId]);
            break;
            
        case 'class-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            
            if (!$id) {
                json_response(['success' => false, 'message' => 'Thiếu ID lớp học']);
                break;
            }
            
            // Get current data for partial updates
            $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch();
            
            if (!$current) {
                json_response(['success' => false, 'message' => 'Không tìm thấy lớp học']);
                break;
            }
            
            $name = $data['name'] ?? $current['name'];
            $courseId = isset($data['course_id']) ? $data['course_id'] : $current['course_id'];
            $teacherId = isset($data['teacher_id']) ? ($data['teacher_id'] ?: null) : $current['teacher_id'];
            $maxStudents = $data['max_students'] ?? $current['max_students'];
            // Schedule is auto-updated from class_schedules, don't overwrite unless explicitly set
            $schedule = isset($data['schedule']) && $data['schedule'] !== '' ? $data['schedule'] : $current['schedule'];
            $room = $data['room'] ?? $current['room'];
            $startDate = isset($data['start_date']) ? ($data['start_date'] ?: null) : $current['start_date'];
            $endDate = isset($data['end_date']) ? ($data['end_date'] ?: null) : $current['end_date'];
            $academicYear = $data['academic_year'] ?? $current['academic_year'];
            $semester = $data['semester'] ?? $current['semester'];
            $status = $data['status'] ?? $current['status'];
            $description = $data['description'] ?? $current['description'];
            
            $stmt = $pdo->prepare("
                UPDATE classes SET 
                    name = ?, course_id = ?, teacher_id = ?, max_students = ?, 
                    schedule = ?, room = ?, start_date = ?, end_date = ?,
                    academic_year = ?, semester = ?, status = ?, description = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $courseId, $teacherId, $maxStudents, $schedule, $room,
                           $startDate, $endDate, $academicYear, $semester, $status, $description, $id]);
            createAdminNotification('class', 'Cập nhật lớp học', 'Lớp học "' . $name . '" đã được cập nhật', $id, 'classes');
            
            json_response(['success' => true, 'message' => 'Cập nhật lớp học thành công']);
            break;
            
        case 'class-delete':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            
            if (!$id) {
                json_response(['success' => false, 'message' => 'Thiếu ID lớp học']);
                break;
            }
            
            // Check if class has students
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id = ?");
            $stmt->execute([$id]);
            $studentCount = $stmt->fetchColumn();
            
            if ($studentCount > 0) {
                json_response(['success' => false, 'message' => 'Không thể xóa lớp còn học viên. Vui lòng chuyển học viên sang lớp khác trước.']);
                break;
            }
            
            // Lấy tên lớp trước khi xóa
            $classStmt = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
            $classStmt->execute([$id]);
            $className = $classStmt->fetchColumn() ?: 'Lớp học';
            
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([$id]);
            createAdminNotification('class', 'Xóa lớp học', 'Lớp học "' . $className . '" đã được xóa', $id, 'classes');
            
            json_response(['success' => true, 'message' => 'Xóa lớp học thành công']);
            break;
            
        case 'class-students':
            checkAdmin();
            
            $classId = $_GET['class_id'] ?? 0;
            
            if (!$classId) {
                json_response(['success' => false, 'message' => 'Thiếu ID lớp học']);
                break;
            }
            
            // Get class info
            $stmt = $pdo->prepare("
                SELECT c.*, co.name as course_name, t.name as teacher_name
                FROM classes c
                LEFT JOIN courses co ON c.course_id = co.id
                LEFT JOIN teachers t ON c.teacher_id = t.id
                WHERE c.id = ?
            ");
            $stmt->execute([$classId]);
            $classInfo = $stmt->fetch();
            
            // Get students in this class
            $stmt = $pdo->prepare("
                SELECT e.*, u.fullname, u.email, u.phone, u.avatar
                FROM enrollments e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.class_id = ?
                ORDER BY u.fullname ASC
            ");
            $stmt->execute([$classId]);
            $students = $stmt->fetchAll();
            
            json_response(['success' => true, 'class' => $classInfo, 'students' => $students]);
            break;
            
        case 'class-assign-student':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $enrollmentId = $data['enrollment_id'] ?? 0;
            $classId = $data['class_id'] ?? null;
            
            if (!$enrollmentId) {
                json_response(['success' => false, 'message' => 'Thiếu thông tin đăng ký']);
                break;
            }
            
            // Check if class has space
            if ($classId) {
                $stmt = $pdo->prepare("SELECT max_students, (SELECT COUNT(*) FROM enrollments WHERE class_id = ?) as current_count FROM classes WHERE id = ?");
                $stmt->execute([$classId, $classId]);
                $classData = $stmt->fetch();
                
                if ($classData && $classData['current_count'] >= $classData['max_students']) {
                    json_response(['success' => false, 'message' => 'Lớp đã đủ sĩ số']);
                    break;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE enrollments SET class_id = ? WHERE id = ?");
            $stmt->execute([$classId, $enrollmentId]);
            
            json_response(['success' => true, 'message' => $classId ? 'Phân lớp thành công' : 'Đã xóa khỏi lớp']);
            break;
            
        case 'class-available-students':
            // Get students that can be assigned to a class (same course, not in any class)
            checkAdmin();
            
            $classId = $_GET['class_id'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT course_id FROM classes WHERE id = ?");
            $stmt->execute([$classId]);
            $courseId = $stmt->fetchColumn();
            
            if (!$courseId) {
                json_response(['success' => false, 'message' => 'Không tìm thấy lớp học']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT e.*, u.fullname, u.email
                FROM enrollments e
                LEFT JOIN users u ON e.user_id = u.id
                WHERE e.course_id = ? AND (e.class_id IS NULL OR e.class_id = 0)
                AND e.status IN ('pending', 'active')
                ORDER BY u.fullname ASC
            ");
            $stmt->execute([$courseId]);
            $students = $stmt->fetchAll();
            
            json_response(['success' => true, 'students' => $students]);
            break;
        
        case 'class-all-users':
            // Get ALL users (not just enrolled) for adding to class
            checkAdmin();
            
            $classId = $_GET['class_id'] ?? 0;
            $search = $_GET['search'] ?? '';
            
            // Get class info
            $stmt = $pdo->prepare("SELECT course_id FROM classes WHERE id = ?");
            $stmt->execute([$classId]);
            $courseId = $stmt->fetchColumn();
            
            // Get all users with role = 'user' (students)
            // Exclude users already in this class
            $query = "
                SELECT u.id, u.fullname, u.email, u.phone, u.avatar, u.created_at,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.user_id = u.id AND e.class_id = ?) as in_this_class,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.user_id = u.id) as total_enrollments,
                    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM enrollments e 
                     LEFT JOIN courses c ON e.course_id = c.id WHERE e.user_id = u.id) as enrolled_courses
                FROM users u
                WHERE u.role = 'user' AND u.is_active = 1
            ";
            
            $params = [$classId];
            
            if ($search) {
                $query .= " AND (u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query .= " ORDER BY u.fullname ASC LIMIT 100";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            json_response(['success' => true, 'users' => $users, 'course_id' => $courseId]);
            break;
        
        case 'class-add-user':
            // Add user to class (auto-create enrollment if needed)
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = $data['user_id'] ?? 0;
            $classId = $data['class_id'] ?? 0;
            
            if (!$userId || !$classId) {
                json_response(['success' => false, 'message' => 'Thiếu thông tin user_id hoặc class_id']);
                break;
            }
            
            // Get class info
            $stmt = $pdo->prepare("SELECT c.*, co.name as course_name, co.price 
                                   FROM classes c 
                                   LEFT JOIN courses co ON c.course_id = co.id 
                                   WHERE c.id = ?");
            $stmt->execute([$classId]);
            $classInfo = $stmt->fetch();
            
            if (!$classInfo) {
                json_response(['success' => false, 'message' => 'Không tìm thấy lớp học']);
                break;
            }
            
            // Check class capacity
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id = ?");
            $stmt->execute([$classId]);
            $currentCount = $stmt->fetchColumn();
            
            if ($currentCount >= $classInfo['max_students']) {
                json_response(['success' => false, 'message' => 'Lớp đã đủ sĩ số ('. $classInfo['max_students'] .' học viên)']);
                break;
            }
            
            // Check if user already in this class
            $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND class_id = ?");
            $stmt->execute([$userId, $classId]);
            if ($stmt->fetch()) {
                json_response(['success' => false, 'message' => 'Học viên đã có trong lớp này']);
                break;
            }
            
            // Check if user has enrollment for this course
            $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ? AND (class_id IS NULL OR class_id = 0)");
            $stmt->execute([$userId, $classInfo['course_id']]);
            $existingEnrollment = $stmt->fetch();
            
            if ($existingEnrollment) {
                // Update existing enrollment with class_id
                $stmt = $pdo->prepare("UPDATE enrollments SET class_id = ?, status = 'active' WHERE id = ?");
                $stmt->execute([$classId, $existingEnrollment['id']]);
            } else {
                // Create new enrollment with class_id
                $stmt = $pdo->prepare("
                    INSERT INTO enrollments (user_id, course_id, class_id, status, payment_status, created_at)
                    VALUES (?, ?, ?, 'active', 'pending', NOW())
                ");
                $stmt->execute([$userId, $classInfo['course_id'], $classId]);
            }
            
            // Get user info for response
            $stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            json_response([
                'success' => true, 
                'message' => 'Đã thêm học viên "'. $user['fullname'] .'" vào lớp "'. $classInfo['name'] .'"'
            ]);
            break;
        
        case 'class-remove-student':
            // Remove student from class (keep enrollment but remove class_id)
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $enrollmentId = $data['enrollment_id'] ?? 0;
            
            if (!$enrollmentId) {
                json_response(['success' => false, 'message' => 'Thiếu enrollment_id']);
                break;
            }
            
            $stmt = $pdo->prepare("UPDATE enrollments SET class_id = NULL WHERE id = ?");
            $stmt->execute([$enrollmentId]);
            
            json_response(['success' => true, 'message' => 'Đã xóa học viên khỏi lớp']);
            break;
        
        case 'student-details':
            // Get detailed info about a student
            checkAdmin();
            
            $userId = $_GET['user_id'] ?? 0;
            
            if (!$userId) {
                json_response(['success' => false, 'message' => 'Thiếu user_id']);
                break;
            }
            
            // Get user info
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                json_response(['success' => false, 'message' => 'Không tìm thấy học viên']);
                break;
            }
            
            // Get all enrollments with class and course info
            $stmt = $pdo->prepare("
                SELECT e.*, 
                    c.name as course_name, c.level, c.duration, c.image_url as course_image,
                    cl.name as class_name, cl.schedule, cl.start_date, cl.end_date,
                    t.name as teacher_name
                FROM enrollments e
                LEFT JOIN courses c ON e.course_id = c.id
                LEFT JOIN classes cl ON e.class_id = cl.id
                LEFT JOIN teachers t ON cl.teacher_id = t.id
                WHERE e.user_id = ?
                ORDER BY e.created_at DESC
            ");
            $stmt->execute([$userId]);
            $enrollments = $stmt->fetchAll();
            
            // Get scores
            $stmt = $pdo->prepare("
                SELECT s.*, c.name as course_name, cl.name as class_name
                FROM scores s
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN classes cl ON s.class_id = cl.id
                WHERE s.user_id = ?
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$userId]);
            $scores = $stmt->fetchAll();
            
            // Get feedback
            $stmt = $pdo->prepare("
                SELECT f.*, c.name as course_name, t.name as teacher_name
                FROM feedback f
                LEFT JOIN courses c ON f.course_id = c.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$userId]);
            $feedback = $stmt->fetchAll();
            
            json_response([
                'success' => true, 
                'user' => $user,
                'enrollments' => $enrollments,
                'scores' => $scores,
                'feedback' => $feedback
            ]);
            break;
        
        case 'class-statistics':
            // Get statistics for a class
            checkAdmin();
            
            $classId = $_GET['class_id'] ?? 0;
            
            if (!$classId) {
                json_response(['success' => false, 'message' => 'Thiếu class_id']);
                break;
            }
            
            // Get class info with student count
            $stmt = $pdo->prepare("
                SELECT c.*, co.name as course_name, t.name as teacher_name,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as student_count,
                    (SELECT AVG(s.score) FROM scores s WHERE s.class_id = c.id) as avg_score,
                    (SELECT COUNT(DISTINCT s.user_id) FROM scores s WHERE s.class_id = c.id) as graded_students
                FROM classes c
                LEFT JOIN courses co ON c.course_id = co.id
                LEFT JOIN teachers t ON c.teacher_id = t.id
                WHERE c.id = ?
            ");
            $stmt->execute([$classId]);
            $classInfo = $stmt->fetch();
            
            // Get score distribution
            $stmt = $pdo->prepare("
                SELECT 
                    SUM(CASE WHEN score >= 9 THEN 1 ELSE 0 END) as excellent,
                    SUM(CASE WHEN score >= 7 AND score < 9 THEN 1 ELSE 0 END) as good,
                    SUM(CASE WHEN score >= 5 AND score < 7 THEN 1 ELSE 0 END) as average,
                    SUM(CASE WHEN score < 5 THEN 1 ELSE 0 END) as below_average
                FROM scores WHERE class_id = ?
            ");
            $stmt->execute([$classId]);
            $scoreDistribution = $stmt->fetch();
            
            json_response([
                'success' => true, 
                'class' => $classInfo,
                'score_distribution' => $scoreDistribution
            ]);
            break;
        
        // ==================== CLASS SCHEDULES (Thời khóa biểu) ====================
        case 'class-schedules':
            // Get schedules for a specific class
            checkAdmin();
            
            $classId = $_GET['class_id'] ?? 0;
            
            if (!$classId) {
                json_response(['success' => false, 'message' => 'Thiếu class_id']);
                break;
            }
            
            try {
                // Check if class_schedules table exists
                $tableCheck = $pdo->query("SHOW TABLES LIKE 'class_schedules'");
                if ($tableCheck->rowCount() === 0) {
                    // Get class info only
                    $stmt = $pdo->prepare("
                        SELECT cl.*, c.name as course_name, t.name as teacher_name
                        FROM classes cl
                        LEFT JOIN courses c ON cl.course_id = c.id
                        LEFT JOIN teachers t ON cl.teacher_id = t.id
                        WHERE cl.id = ?
                    ");
                    $stmt->execute([$classId]);
                    $classInfo = $stmt->fetch();
                    json_response(['success' => true, 'schedules' => [], 'class' => $classInfo, 'message' => 'Bảng class_schedules chưa được tạo']);
                    break;
                }
                
                $stmt = $pdo->prepare("
                    SELECT cs.*, t.name as teacher_name, c.name as course_name, cl.name as class_name
                    FROM class_schedules cs
                    LEFT JOIN teachers t ON cs.teacher_id = t.id
                    LEFT JOIN courses c ON cs.course_id = c.id
                    LEFT JOIN classes cl ON cs.class_id = cl.id
                    WHERE cs.class_id = ?
                    ORDER BY FIELD(cs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cs.start_time
                ");
                $stmt->execute([$classId]);
                $schedules = $stmt->fetchAll();
                
                // Get class info
                $stmt = $pdo->prepare("
                    SELECT cl.*, c.name as course_name, t.name as teacher_name
                    FROM classes cl
                    LEFT JOIN courses c ON cl.course_id = c.id
                    LEFT JOIN teachers t ON cl.teacher_id = t.id
                    WHERE cl.id = ?
                ");
                $stmt->execute([$classId]);
                $classInfo = $stmt->fetch();
                
                json_response(['success' => true, 'schedules' => $schedules, 'class' => $classInfo]);
            } catch (PDOException $e) {
                json_response(['success' => true, 'schedules' => [], 'class' => null, 'debug' => $e->getMessage()]);
            }
            break;
        
        case 'all-schedules':
            // Get all schedules for timetable view
            checkAdmin();
            
            try {
                // Check if class_schedules table exists
                $tableCheck = $pdo->query("SHOW TABLES LIKE 'class_schedules'");
                if ($tableCheck->rowCount() === 0) {
                    json_response(['success' => true, 'schedules' => []]);
                    break;
                }
                
                $courseId = $_GET['course_id'] ?? null;
                $teacherId = $_GET['teacher_id'] ?? null;
                $classId = $_GET['class_id'] ?? null;
                
                $query = "
                    SELECT cs.*, 
                        t.name as teacher_name, 
                        c.name as course_name, c.category,
                        cl.name as class_name, cl.status as class_status, cl.start_date, cl.end_date,
                        (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as student_count
                    FROM class_schedules cs
                    LEFT JOIN teachers t ON cs.teacher_id = t.id
                    LEFT JOIN courses c ON cs.course_id = c.id
                    LEFT JOIN classes cl ON cs.class_id = cl.id
                    WHERE cs.is_active = 1 AND cl.is_active = 1
                ";
                $params = [];
                
                if ($courseId) {
                    $query .= " AND cs.course_id = ?";
                    $params[] = $courseId;
                }
                if ($teacherId) {
                    $query .= " AND cs.teacher_id = ?";
                    $params[] = $teacherId;
                }
                if ($classId) {
                    $query .= " AND cs.class_id = ?";
                    $params[] = $classId;
                }
                
                $query .= " ORDER BY FIELD(cs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cs.start_time";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $schedules = $stmt->fetchAll();
                
                json_response(['success' => true, 'schedules' => $schedules]);
            } catch (PDOException $e) {
                json_response(['success' => true, 'schedules' => [], 'debug' => $e->getMessage()]);
            }
            break;
        
        case 'schedule-create':
            // Create a new schedule entry for a class
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $classId = $data['class_id'] ?? 0;
            $dayOfWeek = $data['day_of_week'] ?? '';
            $startTime = $data['start_time'] ?? '';
            $endTime = $data['end_time'] ?? '';
            
            if (!$classId || !$dayOfWeek || !$startTime || !$endTime) {
                json_response(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                break;
            }
            
            // Get course_id from class
            $stmt = $pdo->prepare("SELECT course_id FROM classes WHERE id = ?");
            $stmt->execute([$classId]);
            $courseId = $stmt->fetchColumn();
            
            if (!$courseId) {
                json_response(['success' => false, 'message' => 'Không tìm thấy lớp học']);
                break;
            }
            
            // Check for conflicts
            $stmt = $pdo->prepare("
                SELECT id FROM class_schedules 
                WHERE class_id = ? AND day_of_week = ? AND is_active = 1
                AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))
            ");
            $stmt->execute([$classId, $dayOfWeek, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime]);
            if ($stmt->fetch()) {
                json_response(['success' => false, 'message' => 'Trùng lịch học! Lớp đã có buổi học khác trong khung giờ này']);
                break;
            }
            
            // Check teacher conflicts if teacher is assigned
            if (!empty($data['teacher_id'])) {
                $stmt = $pdo->prepare("
                    SELECT cs.id, cl.name as class_name FROM class_schedules cs
                    JOIN classes cl ON cs.class_id = cl.id
                    WHERE cs.teacher_id = ? AND cs.day_of_week = ? AND cs.is_active = 1 AND cl.is_active = 1
                    AND ((cs.start_time <= ? AND cs.end_time > ?) OR (cs.start_time < ? AND cs.end_time >= ?) OR (cs.start_time >= ? AND cs.end_time <= ?))
                ");
                $stmt->execute([$data['teacher_id'], $dayOfWeek, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime]);
                $conflict = $stmt->fetch();
                if ($conflict) {
                    json_response(['success' => false, 'message' => 'Giảng viên đã có lịch dạy lớp "' . $conflict['class_name'] . '" trong khung giờ này']);
                    break;
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO class_schedules (class_id, course_id, teacher_id, day_of_week, start_time, end_time, room, is_online, meeting_link, subject, notes, color)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $classId,
                $courseId,
                $data['teacher_id'] ?? null,
                $dayOfWeek,
                $startTime,
                $endTime,
                $data['room'] ?? null,
                $data['is_online'] ?? 0,
                $data['meeting_link'] ?? null,
                $data['subject'] ?? null,
                $data['notes'] ?? null,
                $data['color'] ?? '#1e40af'
            ]);
            
            // Update classes.schedule field (summary text)
            updateClassScheduleSummary($pdo, $classId);
            
            $scheduleId = $pdo->lastInsertId();
            createAdminNotification('schedule', 'Lịch học mới', 'Đã thêm lịch học cho lớp', $scheduleId, 'class_schedules');
            
            json_response(['success' => true, 'message' => 'Đã thêm lịch học thành công', 'id' => $scheduleId]);
            break;
        
        case 'schedule-update':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $scheduleId = $data['id'] ?? 0;
            
            if (!$scheduleId) {
                json_response(['success' => false, 'message' => 'Thiếu ID lịch học']);
                break;
            }
            
            // Get current schedule info
            $stmt = $pdo->prepare("SELECT class_id, teacher_id FROM class_schedules WHERE id = ?");
            $stmt->execute([$scheduleId]);
            $current = $stmt->fetch();
            
            if (!$current) {
                json_response(['success' => false, 'message' => 'Không tìm thấy lịch học']);
                break;
            }
            
            $dayOfWeek = $data['day_of_week'] ?? '';
            $startTime = $data['start_time'] ?? '';
            $endTime = $data['end_time'] ?? '';
            
            // Check for class conflicts (excluding current schedule)
            if ($dayOfWeek && $startTime && $endTime) {
                $stmt = $pdo->prepare("
                    SELECT id FROM class_schedules 
                    WHERE class_id = ? AND day_of_week = ? AND is_active = 1 AND id != ?
                    AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))
                ");
                $stmt->execute([$current['class_id'], $dayOfWeek, $scheduleId, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime]);
                if ($stmt->fetch()) {
                    json_response(['success' => false, 'message' => 'Trùng lịch học trong lớp']);
                    break;
                }
            }
            
            // Check teacher conflicts
            $teacherId = $data['teacher_id'] ?? $current['teacher_id'];
            if ($teacherId && $dayOfWeek && $startTime && $endTime) {
                $stmt = $pdo->prepare("
                    SELECT cs.id, cl.name as class_name FROM class_schedules cs
                    JOIN classes cl ON cs.class_id = cl.id
                    WHERE cs.teacher_id = ? AND cs.day_of_week = ? AND cs.is_active = 1 AND cl.is_active = 1 AND cs.id != ?
                    AND ((cs.start_time <= ? AND cs.end_time > ?) OR (cs.start_time < ? AND cs.end_time >= ?) OR (cs.start_time >= ? AND cs.end_time <= ?))
                ");
                $stmt->execute([$teacherId, $dayOfWeek, $scheduleId, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime]);
                $conflict = $stmt->fetch();
                if ($conflict) {
                    json_response(['success' => false, 'message' => 'Giảng viên đã có lịch dạy lớp "' . $conflict['class_name'] . '" trong khung giờ này']);
                    break;
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE class_schedules SET
                    teacher_id = ?,
                    day_of_week = ?,
                    start_time = ?,
                    end_time = ?,
                    room = ?,
                    is_online = ?,
                    meeting_link = ?,
                    subject = ?,
                    notes = ?,
                    color = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['teacher_id'] ?? null,
                $dayOfWeek,
                $startTime,
                $endTime,
                $data['room'] ?? null,
                $data['is_online'] ?? 0,
                $data['meeting_link'] ?? null,
                $data['subject'] ?? null,
                $data['notes'] ?? null,
                $data['color'] ?? '#1e40af',
                $scheduleId
            ]);
            
            // Update classes.schedule field
            updateClassScheduleSummary($pdo, $current['class_id']);
            createAdminNotification('schedule', 'Cập nhật lịch học', 'Lịch học đã được cập nhật', $scheduleId, 'class_schedules');
            
            json_response(['success' => true, 'message' => 'Cập nhật lịch học thành công']);
            break;
        
        case 'schedule-delete':
            checkAdmin();
            
            $scheduleId = $_GET['id'] ?? 0;
            
            if (!$scheduleId) {
                json_response(['success' => false, 'message' => 'Thiếu ID lịch học']);
                break;
            }
            
            // Get class_id before delete
            $stmt = $pdo->prepare("SELECT class_id FROM class_schedules WHERE id = ?");
            $stmt->execute([$scheduleId]);
            $classId = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM class_schedules WHERE id = ?");
            $stmt->execute([$scheduleId]);
            
            // Update classes.schedule field
            if ($classId) {
                updateClassScheduleSummary($pdo, $classId);
            }
            createAdminNotification('schedule', 'Xóa lịch học', 'Lịch học đã được xóa', $scheduleId, 'class_schedules');
            
            json_response(['success' => true, 'message' => 'Đã xóa lịch học']);
            break;
        
        case 'student-schedule':
            // Get schedule for a specific student (by user_id)
            checkAdmin();
            
            $userId = $_GET['user_id'] ?? 0;
            
            if (!$userId) {
                json_response(['success' => false, 'message' => 'Thiếu user_id']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    cs.*,
                    c.name as course_name, c.category,
                    cl.name as class_name, cl.start_date, cl.end_date,
                    t.name as teacher_name,
                    e.id as enrollment_id, e.status as enrollment_status
                FROM enrollments e
                JOIN classes cl ON e.class_id = cl.id
                JOIN class_schedules cs ON cl.id = cs.class_id
                JOIN courses c ON cs.course_id = c.id
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                WHERE e.user_id = ? AND e.status IN ('active', 'pending') AND cs.is_active = 1 AND cl.is_active = 1
                ORDER BY FIELD(cs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cs.start_time
            ");
            $stmt->execute([$userId]);
            $schedules = $stmt->fetchAll();
            
            json_response(['success' => true, 'schedules' => $schedules]);
            break;
        
        case 'teacher-schedule':
            // Get schedule for a specific teacher
            checkAdmin();
            
            $teacherId = $_GET['teacher_id'] ?? 0;
            
            if (!$teacherId) {
                json_response(['success' => false, 'message' => 'Thiếu teacher_id']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    cs.*,
                    c.name as course_name, c.category,
                    cl.name as class_name, cl.start_date, cl.end_date, cl.status as class_status,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as student_count
                FROM class_schedules cs
                JOIN classes cl ON cs.class_id = cl.id
                JOIN courses c ON cs.course_id = c.id
                WHERE cs.teacher_id = ? AND cs.is_active = 1 AND cl.is_active = 1
                ORDER BY FIELD(cs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cs.start_time
            ");
            $stmt->execute([$teacherId]);
            $schedules = $stmt->fetchAll();
            
            json_response(['success' => true, 'schedules' => $schedules]);
            break;
        
        case 'course-schedule':
            // Get all schedules for a specific course (all classes)
            checkAdmin();
            
            $courseId = $_GET['course_id'] ?? 0;
            
            if (!$courseId) {
                json_response(['success' => false, 'message' => 'Thiếu course_id']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT 
                    cs.*,
                    c.name as course_name, c.category,
                    cl.name as class_name, cl.start_date, cl.end_date, cl.status as class_status, cl.max_students,
                    t.name as teacher_name,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as student_count
                FROM class_schedules cs
                JOIN classes cl ON cs.class_id = cl.id
                JOIN courses c ON cs.course_id = c.id
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                WHERE cs.course_id = ? AND cs.is_active = 1 AND cl.is_active = 1
                ORDER BY cl.name, FIELD(cs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cs.start_time
            ");
            $stmt->execute([$courseId]);
            $schedules = $stmt->fetchAll();
            
            // Get course info
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            $course = $stmt->fetch();
            
            // Get all classes for this course
            $stmt = $pdo->prepare("
                SELECT cl.*, t.name as teacher_name,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as student_count
                FROM classes cl
                LEFT JOIN teachers t ON cl.teacher_id = t.id
                WHERE cl.course_id = ? AND cl.is_active = 1
                ORDER BY cl.name
            ");
            $stmt->execute([$courseId]);
            $classes = $stmt->fetchAll();
            
            json_response(['success' => true, 'schedules' => $schedules, 'course' => $course, 'classes' => $classes]);
            break;
            
        // ==================== TEACHERS ====================
        case 'teachers':
            checkAdmin();
            
            $stmt = $pdo->query("SELECT * FROM teachers ORDER BY created_at DESC");
            $teachers = $stmt->fetchAll();
            // Decode specialties JSON for each teacher
            foreach ($teachers as &$teacher) {
                $teacher['specialties'] = json_decode($teacher['specialties'] ?? '[]', true);
            }
            json_response(['success' => true, 'data' => $teachers]);
            break;
        
        case 'teacher-upload-image':
            checkAdmin();
            
            if (!isset($_FILES['image'])) {
                json_response(['success' => false, 'message' => 'Không có file được upload']);
                break;
            }
            
            $file = $_FILES['image'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                json_response(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
                break;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'File quá lớn. Tối đa 5MB']);
                break;
            }
            
            // Create upload directory if not exists
            $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/teachers/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'teacher_' . uniqid() . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $imageUrl = '/frontend/assets/images/uploads/teachers/' . $filename;
                json_response(['success' => true, 'image_url' => $imageUrl]);
            } else {
                json_response(['success' => false, 'message' => 'Không thể lưu file']);
            }
            break;
            
        case 'teacher-create':
            checkAdmin();
            
            // Handle both FormData and JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $data = $_POST;
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                        $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/teachers/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'teacher_' . uniqid() . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                            $data['image_url'] = '/frontend/assets/images/uploads/teachers/' . $filename;
                        }
                    }
                }
                // Parse specialties if string
                if (isset($data['specialties']) && is_string($data['specialties'])) {
                    $data['specialties'] = json_decode($data['specialties'], true) ?? [];
                }
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO teachers (name, title, degree, description, image_url, ielts_score, experience_years, students_count, rating, specialties, is_featured, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['title'] ?? '',
                $data['degree'] ?? '',
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
            
            $newId = $pdo->lastInsertId();
            createAdminNotification('teacher', 'Giảng viên mới', 'Đã thêm giảng viên "' . ($data['name'] ?? '') . '"', $newId, 'teachers');
            
            json_response(['success' => true, 'message' => 'Thêm giảng viên thành công', 'id' => $newId]);
            break;
            
        case 'teacher-update':
            checkAdmin();
            
            // Handle both FormData and JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $data = $_POST;
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['image'];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                        // Delete old image if exists
                        $teacherId = $data['id'] ?? 0;
                        if ($teacherId) {
                            $oldStmt = $pdo->prepare("SELECT image_url FROM teachers WHERE id = ?");
                            $oldStmt->execute([$teacherId]);
                            $oldImage = $oldStmt->fetchColumn();
                            if ($oldImage && strpos($oldImage, '/uploads/teachers/') !== false) {
                                $oldPath = __DIR__ . '/../../' . ltrim($oldImage, '/');
                                if (file_exists($oldPath)) {
                                    unlink($oldPath);
                                }
                            }
                        }
                        
                        $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/teachers/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename = 'teacher_' . uniqid() . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                            $data['image_url'] = '/frontend/assets/images/uploads/teachers/' . $filename;
                        }
                    }
                }
                // Parse specialties if string
                if (isset($data['specialties']) && is_string($data['specialties'])) {
                    $data['specialties'] = json_decode($data['specialties'], true) ?? [];
                }
            } else {
                $data = json_decode(file_get_contents('php://input'), true);
            }
            
            $stmt = $pdo->prepare("
                UPDATE teachers SET 
                    name = ?, title = ?, degree = ?, description = ?, image_url = ?,
                    ielts_score = ?, experience_years = ?, students_count = ?,
                    rating = ?, specialties = ?, is_featured = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['title'] ?? '',
                $data['degree'] ?? '',
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
            createAdminNotification('teacher', 'Cập nhật giảng viên', 'Giảng viên "' . ($data['name'] ?? '') . '" đã được cập nhật', $data['id'], 'teachers');
            
            json_response(['success' => true, 'message' => 'Cập nhật giảng viên thành công']);
            break;
            
        case 'teacher-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Lấy tên giảng viên trước khi xóa
            $teacherStmt = $pdo->prepare("SELECT name FROM teachers WHERE id = ?");
            $teacherStmt->execute([$data['id']]);
            $teacherName = $teacherStmt->fetchColumn() ?: 'Giảng viên';
            
            moveToTrash('teachers', $data['id'], $adminId);
            createAdminNotification('teacher', 'Xóa giảng viên', 'Giảng viên "' . $teacherName . '" đã được chuyển vào thùng rác', $data['id'], 'teachers');
            
            json_response(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== SCORES ====================
        case 'scores':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT s.*, u.fullname, c.name as course_name
                FROM scores s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN enrollments e ON s.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                ORDER BY s.created_at DESC
            ");
            $scores = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $scores]);
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
            
            $scoreId = $pdo->lastInsertId();
            
            // Lấy tên học viên
            $userStmt = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
            $userStmt->execute([$data['user_id']]);
            $userName = $userStmt->fetchColumn() ?: 'Học viên';
            
            // Tạo thông báo
            $testTypes = ['placement' => 'Đầu vào', 'midterm' => 'Giữa kỳ', 'final' => 'Cuối kỳ', 'mock' => 'Thử'];
            $testTypeName = $testTypes[$data['test_type'] ?? 'mock'] ?? 'Thử';
            createAdminNotification(
                'score',
                'Điểm mới được cập nhật',
                "Điểm {$testTypeName} của {$userName}: L:{$data['listening']} R:{$data['reading']} W:{$data['writing']} S:{$data['speaking']} - Overall: {$overall}",
                $scoreId,
                'scores'
            );
            
            json_response(['success' => true, 'message' => 'Thêm điểm thành công']);
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
            
            // Lấy thông tin điểm số để tạo thông báo
            $scoreStmt = $pdo->prepare("
                SELECT s.*, u.fullname 
                FROM scores s 
                LEFT JOIN users u ON s.user_id = u.id 
                WHERE s.id = ?
            ");
            $scoreStmt->execute([$data['id']]);
            $scoreInfo = $scoreStmt->fetch(PDO::FETCH_ASSOC);
            $userName = $scoreInfo['fullname'] ?? 'Học viên';
            
            // Tạo thông báo
            $testTypes = ['placement' => 'Đầu vào', 'midterm' => 'Giữa kỳ', 'final' => 'Cuối kỳ', 'mock' => 'Thử'];
            $testTypeName = $testTypes[$data['test_type']] ?? 'Thử';
            createAdminNotification(
                'score',
                'Điểm được cập nhật',
                "Điểm {$testTypeName} của {$userName} đã được cập nhật: L:{$data['listening']} R:{$data['reading']} W:{$data['writing']} S:{$data['speaking']} - Overall: {$overall}",
                $data['id'],
                'scores'
            );
            
            json_response(['success' => true, 'message' => 'Cập nhật điểm thành công']);
            break;
            
        case 'score-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Lấy thông tin điểm trước khi xóa
            $scoreStmt = $pdo->prepare("SELECT s.*, u.fullname FROM scores s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
            $scoreStmt->execute([$data['id']]);
            $scoreInfo = $scoreStmt->fetch(PDO::FETCH_ASSOC);
            $userName = $scoreInfo['fullname'] ?? 'Học viên';
            
            moveToTrash('scores', $data['id'], $adminId);
            createAdminNotification('score', 'Xóa điểm', 'Điểm của "' . $userName . '" đã được chuyển vào thùng rác', $data['id'], 'scores');
            
            json_response(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== FEEDBACK ====================
        case 'feedback':
            checkAdmin();
            
            $stmt = $pdo->query("
                SELECT f.*, u.fullname as student_name, t.name as teacher_name, c.name as course_name
                FROM feedback f
                LEFT JOIN users u ON f.user_id = u.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                LEFT JOIN enrollments e ON f.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                ORDER BY f.created_at DESC
            ");
            $feedback = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $feedback]);
            break;
            
        case 'feedback-create':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO feedback (enrollment_id, user_id, teacher_id, content, rating)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['enrollment_id'],
                $data['user_id'],
                $data['teacher_id'] ?? null,
                $data['content'],
                $data['rating'] ?? null
            ]);
            
            json_response(['success' => true, 'message' => 'Thêm nhận xét thành công']);
            break;
            
        case 'feedback-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            moveToTrash('feedback', $data['id'], $adminId);
            
            json_response(['success' => true, 'message' => 'Đã chuyển vào thùng rác']);
            break;
            
        // ==================== SCHEDULES (THỜI KHÓA BIỂU TỔNG QUAN) ====================
        case 'schedules':
            // Get all class schedules for overview
            checkAdmin();
            
            try {
                // Check if class_schedules table exists
                $tableCheck = $pdo->query("SHOW TABLES LIKE 'class_schedules'");
                if ($tableCheck->rowCount() === 0) {
                    // Table doesn't exist - return empty array
                    json_response(['success' => true, 'data' => [], 'message' => 'Bảng class_schedules chưa được tạo']);
                    break;
                }
                
                $stmt = $pdo->query("
                    SELECT cs.*, 
                        cl.name as class_name,
                        c.name as course_name,
                        t.name as teacher_name,
                        (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status = 'active') as student_count
                    FROM class_schedules cs
                    JOIN classes cl ON cs.class_id = cl.id
                    JOIN courses c ON cs.course_id = c.id
                    LEFT JOIN teachers t ON cs.teacher_id = t.id
                    WHERE cs.is_active = 1 AND cl.is_active = 1
                    ORDER BY FIELD(cs.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), cs.start_time
                ");
                $schedules = $stmt->fetchAll();
                json_response(['success' => true, 'data' => $schedules]);
            } catch (PDOException $e) {
                json_response(['success' => true, 'data' => [], 'debug' => $e->getMessage()]);
            }
            break;
            
        // NOTE: schedule-create, schedule-update, schedule-delete are handled above 
        // in the CLASS SCHEDULES section (lines ~1112-1200) for class_schedules table
            
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
            
            json_response(['success' => true, 'data' => $trash]);
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
                json_response(['success' => false, 'message' => 'Không tìm thấy dữ liệu']);
                break;
            }
            
            $originalData = json_decode($trashItem['data'], true);
            $tableName = $trashItem['original_table'];
            
            // Khôi phục dữ liệu
            restoreFromTrash($tableName, $originalData);
            
            // Đánh dấu đã khôi phục
            $stmt = $pdo->prepare("UPDATE trash SET is_restored = 1, restored_at = NOW() WHERE id = ?");
            $stmt->execute([$trashId]);
            
            json_response(['success' => true, 'message' => 'Khôi phục thành công']);
            break;
            
        case 'trash-delete-permanent':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("DELETE FROM trash WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            json_response(['success' => true, 'message' => 'Đã xóa vĩnh viễn']);
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
            
            json_response(['success' => true, 'message' => 'Đã dọn sạch thùng rác']);
            break;
        
        // ==================== REVIEWS MANAGEMENT ====================
        case 'reviews':
            checkAdmin();
            
            $filter = $_GET['filter'] ?? '';
            
            $sql = "
                SELECT r.*, u.fullname, u.avatar as user_avatar
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
            ";
            
            if ($filter === 'approved') {
                $sql .= " WHERE r.is_approved = 1";
            } elseif ($filter === 'pending') {
                $sql .= " WHERE r.is_approved = 0";
            }
            
            $sql .= " ORDER BY r.created_at DESC";
            
            $stmt = $pdo->query($sql);
            $reviews = $stmt->fetchAll();
            
            json_response(['success' => true, 'data' => $reviews]);
            break;
            
        case 'review-approve':
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $reviewId = $data['id'] ?? 0;
            $isApproved = $data['is_approved'] ?? 1;
            
            // Kiểm tra giới hạn nếu đang duyệt
            if ($isApproved == 1) {
                $approvedCount = countApprovedReviews();
                $limits = getLimitSettingsExtended();
                
                if ($approvedCount >= $limits['max_approved_reviews']) {
                    json_response([
                        'success' => false, 
                        'message' => 'Đã đạt giới hạn ' . $limits['max_approved_reviews'] . ' đánh giá được duyệt. Vui lòng ẩn hoặc xóa một đánh giá khác trước.',
                        'limit_reached' => true,
                        'current_count' => $approvedCount,
                        'max_count' => $limits['max_approved_reviews']
                    ]);
                    break;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = ? WHERE id = ?");
            $stmt->execute([$isApproved, $reviewId]);
            
            // Tạo thông báo
            if ($isApproved) {
                $stmt2 = $pdo->prepare("SELECT user_name FROM reviews WHERE id = ?");
                $stmt2->execute([$reviewId]);
                $review = $stmt2->fetch();
                createAdminNotification('review', 'Đánh giá đã duyệt', 'Đánh giá từ "' . ($review['user_name'] ?? 'Ẩn danh') . '" đã được duyệt hiển thị', $reviewId, 'reviews');
            }
            
            json_response(['success' => true, 'message' => $isApproved ? 'Đã duyệt đánh giá' : 'Đã ẩn đánh giá']);
            break;
            
        case 'review-delete':
            checkAdmin();
            
            $reviewId = $_GET['id'] ?? 0;
            
            // Lấy thông tin review trước khi xóa
            $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
            $stmt->execute([$reviewId]);
            $review = $stmt->fetch();
            
            if (!$review) {
                json_response(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
                break;
            }
            
            // Xóa ảnh nếu có
            if ($review['image_url']) {
                $imagePath = __DIR__ . '/../../' . ltrim($review['image_url'], '/');
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Xóa review
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$reviewId]);
            createAdminNotification('review', 'Xóa đánh giá', 'Đánh giá từ "' . ($review['user_name'] ?? 'Ẩn danh') . '" đã bị xóa', $reviewId, 'reviews');
            
            json_response(['success' => true, 'message' => 'Đã xóa đánh giá']);
            break;
            
        // ==================== SEARCH FUNCTIONALITY ====================
        case 'search-users':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $stmt = $pdo->prepare("
                SELECT id, fullname, email, phone, role, is_active, created_at, updated_at
                FROM users
                WHERE (fullname LIKE ? OR email LIKE ? OR phone LIKE ? OR id = ?)
                ORDER BY created_at DESC
            ");
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $query]);
            $users = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $users]);
            break;
            
        case 'search-enrollments':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            $status = $_GET['status'] ?? '';
            
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $sql = "
                SELECT e.*, u.fullname, u.email, c.name as course_name
                FROM enrollments e
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
                WHERE (u.fullname LIKE ? OR u.email LIKE ? OR u.id = ? OR c.name LIKE ?)
            ";
            
            $params = [$searchTerm, $searchTerm, $query, $searchTerm];
            
            if ($status) {
                $sql .= " AND e.status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY e.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $enrollments = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $enrollments]);
            break;
            
        case 'search-teachers':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $stmt = $pdo->prepare("
                SELECT * FROM teachers
                WHERE (name LIKE ? OR title LIKE ? OR id = ?)
                ORDER BY created_at DESC
            ");
            $stmt->execute([$searchTerm, $searchTerm, $query]);
            $teachers = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $teachers]);
            break;
            
        case 'search-scores':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $stmt = $pdo->prepare("
                SELECT s.*, u.fullname, c.name as course_name
                FROM scores s
                JOIN users u ON s.user_id = u.id
                LEFT JOIN enrollments e ON s.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                WHERE (u.fullname LIKE ? OR u.email LIKE ? OR u.id = ?)
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$searchTerm, $searchTerm, $query]);
            $scores = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $scores]);
            break;
            
        case 'search-feedback':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $stmt = $pdo->prepare("
                SELECT f.*, u.fullname as student_name, t.name as teacher_name, c.name as course_name
                FROM feedback f
                JOIN users u ON f.user_id = u.id
                LEFT JOIN teachers t ON f.teacher_id = t.id
                LEFT JOIN enrollments e ON f.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                WHERE (u.fullname LIKE ? OR u.id = ? OR f.content LIKE ?)
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([$searchTerm, $query, $searchTerm]);
            $feedback = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $feedback]);
            break;
            
        case 'search-achievements':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $stmt = $pdo->prepare("
                SELECT * FROM student_achievements
                WHERE (student_name LIKE ? OR achievement_title LIKE ? OR id = ?)
                ORDER BY display_order, created_at DESC
            ");
            $stmt->execute([$searchTerm, $searchTerm, $query]);
            $achievements = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $achievements]);
            break;
            
        case 'search-reviews':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            $filter = $_GET['filter'] ?? '';
            
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $sql = "
                SELECT r.*, u.fullname, u.avatar as user_avatar
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE (r.user_name LIKE ? OR r.comment LIKE ? OR r.id = ?)
            ";
            
            $params = [$searchTerm, $searchTerm, $query];
            
            if ($filter === 'approved') {
                $sql .= " AND r.is_approved = 1";
            } elseif ($filter === 'pending') {
                $sql .= " AND r.is_approved = 0";
            }
            
            $sql .= " ORDER BY r.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $reviews]);
            break;
            
        case 'search-schedules':
            checkAdmin();
            
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                json_response(['success' => true, 'data' => []]);
                break;
            }
            
            $searchTerm = '%' . $query . '%';
            $stmt = $pdo->prepare("
                SELECT s.*, 
                    u.fullname as student_name,
                    c.name as course_name,
                    t.name as teacher_name
                FROM schedules s
                JOIN enrollments e ON s.enrollment_id = e.id
                JOIN users u ON e.user_id = u.id
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN teachers t ON s.teacher_id = t.id
                WHERE s.is_active = 1 AND (u.fullname LIKE ? OR u.id = ? OR c.name LIKE ? OR t.name LIKE ?)
                ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time
            ");
            $stmt->execute([$searchTerm, $query, $searchTerm, $searchTerm]);
            $schedules = $stmt->fetchAll();
            json_response(['success' => true, 'data' => $schedules]);
            break;
            
        // ==================== CONTENT IMAGE UPLOAD ====================
        case 'content-image-upload':
            $adminId = checkAdmin();
            
            if (!isset($_FILES['image'])) {
                json_response(['success' => false, 'message' => 'Không có file được upload']);
                break;
            }
            
            $file = $_FILES['image'];
            $page = $_POST['page'] ?? 'home';
            $section = $_POST['section'] ?? 'hero';
            $key = $_POST['key'] ?? 'image';
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                json_response(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
                break;
            }
            
            // Validate file size (max 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'File quá lớn. Tối đa 10MB']);
                break;
            }
            
            // Create upload directory if not exists
            $uploadDir = __DIR__ . '/../../frontend/assets/images/uploads/content/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $page . '_' . $section . '_' . $key . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $imageUrl = '/frontend/assets/images/uploads/content/' . $filename;
                
                // Update database
                $stmt = $pdo->prepare("SELECT id, content_value FROM site_content WHERE page = ? AND section = ? AND content_key = ?");
                $stmt->execute([$page, $section, $key]);
                $existing = $stmt->fetch();
                
                // Delete old image if exists
                if ($existing && $existing['content_value']) {
                    $oldImagePath = __DIR__ . '/../../' . ltrim($existing['content_value'], '/');
                    if (file_exists($oldImagePath) && strpos($existing['content_value'], '/uploads/content/') !== false) {
                        unlink($oldImagePath);
                    }
                }
                
                if ($existing) {
                    $stmt = $pdo->prepare("UPDATE site_content SET content_value = ?, content_type = 'image', updated_by = ? WHERE id = ?");
                    $stmt->execute([$imageUrl, $adminId, $existing['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO site_content (page, section, content_key, content_value, content_type, updated_by) VALUES (?, ?, ?, ?, 'image', ?)");
                    $stmt->execute([$page, $section, $key, $imageUrl, $adminId]);
                }
                
                json_response(['success' => true, 'image_url' => $imageUrl, 'message' => 'Upload thành công']);
            } else {
                json_response(['success' => false, 'message' => 'Không thể lưu file']);
            }
            break;
            
        // ==================== CONTENT IMAGE DELETE ====================
        case 'content-image-delete':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $page = $data['page'] ?? '';
            $section = $data['section'] ?? '';
            $key = $data['key'] ?? '';
            
            if (!$page || !$section || !$key) {
                json_response(['success' => false, 'message' => 'Thiếu thông tin']);
                break;
            }
            
            // Get current image
            $stmt = $pdo->prepare("SELECT id, content_value FROM site_content WHERE page = ? AND section = ? AND content_key = ?");
            $stmt->execute([$page, $section, $key]);
            $existing = $stmt->fetch();
            
            if ($existing && $existing['content_value']) {
                // Delete file if exists
                $oldImagePath = __DIR__ . '/../../' . ltrim($existing['content_value'], '/');
                if (file_exists($oldImagePath) && strpos($existing['content_value'], '/uploads/content/') !== false) {
                    unlink($oldImagePath);
                }
                
                // Clear database entry
                $stmt = $pdo->prepare("UPDATE site_content SET content_value = '', updated_by = ? WHERE id = ?");
                $stmt->execute([$adminId, $existing['id']]);
                
                json_response(['success' => true, 'message' => 'Xóa ảnh thành công']);
            } else {
                json_response(['success' => false, 'message' => 'Không tìm thấy ảnh']);
            }
            break;
            
        // ==================== SITE CONTENT MANAGEMENT ====================
        case 'site-content':
            checkAdmin();
            
            $page = $_GET['page'] ?? null;
            
            if ($page) {
                // Lấy content theo page
                $stmt = $pdo->prepare("SELECT * FROM site_content WHERE page = ? AND is_active = 1 ORDER BY section, content_key");
                $stmt->execute([$page]);
            } else {
                // Lấy tất cả content
                $stmt = $pdo->query("SELECT * FROM site_content WHERE is_active = 1 ORDER BY page, section, content_key");
            }
            
            $contents = $stmt->fetchAll();
            
            // Group by page and section
            $grouped = [];
            foreach ($contents as $content) {
                $grouped[$content['page']][$content['section']][$content['content_key']] = [
                    'id' => $content['id'],
                    'value' => $content['content_value'],
                    'type' => $content['content_type']
                ];
            }
            
            json_response(['success' => true, 'data' => $grouped, 'raw' => $contents]);
            break;
            
        case 'site-content-update':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['page']) || !isset($data['section']) || !isset($data['content_key']) || !isset($data['content_value'])) {
                json_response(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                break;
            }
            
            // Kiểm tra xem đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT id FROM site_content WHERE page = ? AND section = ? AND content_key = ?");
            $stmt->execute([$data['page'], $data['section'], $data['content_key']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update
                $stmt = $pdo->prepare("UPDATE site_content SET content_value = ?, content_type = ?, updated_by = ? WHERE id = ?");
                $stmt->execute([
                    $data['content_value'],
                    $data['content_type'] ?? 'text',
                    $adminId,
                    $existing['id']
                ]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO site_content (page, section, content_key, content_value, content_type, updated_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['page'],
                    $data['section'],
                    $data['content_key'],
                    $data['content_value'],
                    $data['content_type'] ?? 'text',
                    $adminId
                ]);
            }
            
            json_response(['success' => true, 'message' => 'Cập nhật nội dung thành công']);
            break;
            
        case 'site-content-bulk-update':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $items = $data['items'] ?? [];
            
            if (empty($items)) {
                json_response(['success' => false, 'message' => 'Không có dữ liệu để cập nhật']);
                break;
            }
            
            $pdo->beginTransaction();
            
            try {
                foreach ($items as $item) {
                    $stmt = $pdo->prepare("SELECT id FROM site_content WHERE page = ? AND section = ? AND content_key = ?");
                    $stmt->execute([$item['page'], $item['section'], $item['content_key']]);
                    $existing = $stmt->fetch();
                    
                    if ($existing) {
                        $stmt = $pdo->prepare("UPDATE site_content SET content_value = ?, content_type = ?, updated_by = ? WHERE id = ?");
                        $stmt->execute([$item['content_value'], $item['content_type'] ?? 'text', $adminId, $existing['id']]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO site_content (page, section, content_key, content_value, content_type, updated_by) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$item['page'], $item['section'], $item['content_key'], $item['content_value'], $item['content_type'] ?? 'text', $adminId]);
                    }
                }
                
                $pdo->commit();
                json_response(['success' => true, 'message' => 'Cập nhật thành công ' . count($items) . ' mục']);
            } catch (Exception $e) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
            }
            break;
            
        // ==================== SITE SETTINGS MANAGEMENT ====================
        case 'site-settings':
            checkAdmin();
            
            $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY setting_key");
            $settings = $stmt->fetchAll();
            
            // Convert to key-value object
            $settingsObj = [];
            foreach ($settings as $setting) {
                $settingsObj[$setting['setting_key']] = [
                    'id' => $setting['id'],
                    'value' => $setting['setting_value'],
                    'type' => $setting['setting_type'],
                    'description' => $setting['description']
                ];
            }
            
            json_response(['success' => true, 'data' => $settingsObj, 'raw' => $settings]);
            break;
            
        case 'site-settings-update':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['setting_key']) || !isset($data['setting_value'])) {
                json_response(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
                break;
            }
            
            // Kiểm tra xem đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT id FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$data['setting_key']]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ?, setting_type = ?, updated_by = ? WHERE id = ?");
                $stmt->execute([
                    $data['setting_value'],
                    $data['setting_type'] ?? 'text',
                    $adminId,
                    $existing['id']
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type, description, updated_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['setting_key'],
                    $data['setting_value'],
                    $data['setting_type'] ?? 'text',
                    $data['description'] ?? '',
                    $adminId
                ]);
            }
            
            json_response(['success' => true, 'message' => 'Cập nhật cài đặt thành công']);
            break;
            
        case 'site-settings-bulk-update':
            $adminId = checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $settings = $data['settings'] ?? [];
            
            if (empty($settings)) {
                json_response(['success' => false, 'message' => 'Không có dữ liệu để cập nhật']);
                break;
            }
            
            $pdo->beginTransaction();
            
            try {
                foreach ($settings as $setting) {
                    $key = $setting['setting_key'] ?? null;
                    $value = $setting['setting_value'] ?? '';
                    
                    if (!$key) continue;
                    
                    $stmt = $pdo->prepare("SELECT id FROM site_settings WHERE setting_key = ?");
                    $stmt->execute([$key]);
                    $existing = $stmt->fetch();
                    
                    if ($existing) {
                        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ?, updated_by = ? WHERE id = ?");
                        $stmt->execute([$value, $adminId, $existing['id']]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, updated_by) VALUES (?, ?, ?)");
                        $stmt->execute([$key, $value, $adminId]);
                    }
                }
                
                $pdo->commit();
                json_response(['success' => true, 'message' => 'Lưu cài đặt thành công']);
            } catch (Exception $e) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
            }
            break;
            
        // ==================== DASHBOARD STATISTICS ====================
        case 'dashboard-detailed-stats':
            // Thống kê chi tiết cho dashboard
            checkAdmin();
            
            $stats = [];
            
            // 1. Tổng số học viên theo trạng thái
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                FROM users WHERE role = 'user'
            ");
            $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 2. Thống kê đăng ký theo trạng thái
            $stmt = $pdo->query("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM enrollments 
                GROUP BY status
            ");
            $stats['enrollments_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // 3. Thống kê khóa học theo danh mục
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(age_group, 'other') as category,
                    COUNT(*) as count
                FROM courses 
                WHERE is_active = 1
                GROUP BY age_group
            ");
            $stats['courses_by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // 4. Thống kê lớp học theo trạng thái
            $stmt = $pdo->query("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM classes 
                GROUP BY status
            ");
            $stats['classes_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // 5. Thống kê điểm số trung bình
            $stmt = $pdo->query("
                SELECT 
                    AVG(overall) as avg_overall,
                    MAX(overall) as max_overall,
                    MIN(overall) as min_overall,
                    COUNT(*) as total_scores
                FROM scores
            ");
            $stats['scores'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 6. Top học viên điểm cao nhất
            $stmt = $pdo->query("
                SELECT s.*, u.fullname, u.email
                FROM scores s
                JOIN users u ON s.user_id = u.id
                ORDER BY s.overall DESC
                LIMIT 5
            ");
            $stats['top_students'] = $stmt->fetchAll();
            
            // 7. Thống kê doanh thu (giả định từ enrollments)
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(e.created_at, '%Y-%m') as month,
                    SUM(c.price) as revenue,
                    COUNT(*) as enrollments
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.status IN ('active', 'completed')
                GROUP BY DATE_FORMAT(e.created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ");
            $stats['monthly_revenue'] = $stmt->fetchAll();
            
            // 8. Lớp sắp đầy (còn <= 3 chỗ)
            $stmt = $pdo->query("
                SELECT c.*, co.name as course_name, t.name as teacher_name,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as student_count
                FROM classes c
                LEFT JOIN courses co ON c.course_id = co.id
                LEFT JOIN teachers t ON c.teacher_id = t.id
                WHERE c.status = 'active'
                HAVING (c.max_students - student_count) <= 3
                ORDER BY student_count DESC
            ");
            $stats['nearly_full_classes'] = $stmt->fetchAll();
            
            json_response(['success' => true, 'data' => $stats]);
            break;
            
        // ==================== USER ENROLLMENTS LOOKUP ====================
        case 'user-enrollments':
            // Lấy tất cả đăng ký của một học viên
            checkAdmin();
            
            $userId = $_GET['user_id'] ?? 0;
            if (!$userId) {
                json_response(['success' => false, 'message' => 'Thiếu user_id']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT e.*, c.name as course_name, c.price, cl.name as class_name, cl.schedule,
                       t.name as teacher_name
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                LEFT JOIN classes cl ON e.class_id = cl.id
                LEFT JOIN teachers t ON cl.teacher_id = t.id
                WHERE e.user_id = ?
                ORDER BY e.created_at DESC
            ");
            $stmt->execute([$userId]);
            $enrollments = $stmt->fetchAll();
            
            // Lấy thông tin user
            $stmt = $pdo->prepare("SELECT id, fullname, email, phone FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            // Lấy điểm số
            $stmt = $pdo->prepare("
                SELECT s.*, c.name as course_name
                FROM scores s
                LEFT JOIN enrollments e ON s.enrollment_id = e.id
                LEFT JOIN courses c ON e.course_id = c.id
                WHERE s.user_id = ?
                ORDER BY s.test_date DESC
            ");
            $stmt->execute([$userId]);
            $scores = $stmt->fetchAll();
            
            json_response([
                'success' => true, 
                'user' => $user,
                'enrollments' => $enrollments,
                'scores' => $scores
            ]);
            break;
            
        // ==================== TEACHER CLASSES LOOKUP ====================
        case 'teacher-classes':
            // Lấy tất cả lớp của một giảng viên
            checkAdmin();
            
            $teacherId = $_GET['teacher_id'] ?? 0;
            if (!$teacherId) {
                json_response(['success' => false, 'message' => 'Thiếu teacher_id']);
                break;
            }
            
            $stmt = $pdo->prepare("
                SELECT c.*, co.name as course_name,
                       (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as student_count
                FROM classes c
                LEFT JOIN courses co ON c.course_id = co.id
                WHERE c.teacher_id = ?
                ORDER BY c.status ASC, c.start_date DESC
            ");
            $stmt->execute([$teacherId]);
            $classes = $stmt->fetchAll();
            
            // Lấy thông tin teacher
            $stmt = $pdo->prepare("SELECT id, name, title, ielts_score FROM teachers WHERE id = ?");
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch();
            
            json_response([
                'success' => true, 
                'teacher' => $teacher,
                'classes' => $classes
            ]);
            break;
            
        // ==================== COURSE STATISTICS ====================
        case 'course-stats':
            // Thống kê chi tiết cho một khóa học
            checkAdmin();
            
            $courseId = $_GET['course_id'] ?? 0;
            if (!$courseId) {
                json_response(['success' => false, 'message' => 'Thiếu course_id']);
                break;
            }
            
            // Thông tin khóa học
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            $course = $stmt->fetch();
            
            // Số lớp học
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_classes,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_classes
                FROM classes WHERE course_id = ?
            ");
            $stmt->execute([$courseId]);
            $classStats = $stmt->fetch();
            
            // Số học viên đăng ký
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_enrollments,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_enrollments,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_enrollments,
                    AVG(progress) as avg_progress
                FROM enrollments WHERE course_id = ?
            ");
            $stmt->execute([$courseId]);
            $enrollStats = $stmt->fetch();
            
            // Điểm số trung bình
            $stmt = $pdo->prepare("
                SELECT AVG(s.overall) as avg_score
                FROM scores s
                JOIN enrollments e ON s.enrollment_id = e.id
                WHERE e.course_id = ?
            ");
            $stmt->execute([$courseId]);
            $scoreStats = $stmt->fetch();
            
            // Danh sách các lớp
            $stmt = $pdo->prepare("
                SELECT c.*, t.name as teacher_name,
                       (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as student_count
                FROM classes c
                LEFT JOIN teachers t ON c.teacher_id = t.id
                WHERE c.course_id = ?
                ORDER BY c.status ASC, c.created_at DESC
            ");
            $stmt->execute([$courseId]);
            $classes = $stmt->fetchAll();
            
            json_response([
                'success' => true,
                'course' => $course,
                'class_stats' => $classStats,
                'enrollment_stats' => $enrollStats,
                'score_stats' => $scoreStats,
                'classes' => $classes
            ]);
            break;
            
        // ==================== BULK ENROLLMENT UPDATE ====================
        case 'enrollment-bulk-update':
            // Cập nhật hàng loạt trạng thái đăng ký
            checkAdmin();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $ids = $data['ids'] ?? [];
            $status = $data['status'] ?? null;
            $classId = $data['class_id'] ?? null;
            
            if (empty($ids)) {
                json_response(['success' => false, 'message' => 'Chưa chọn đăng ký nào']);
                break;
            }
            
            $pdo->beginTransaction();
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                
                if ($status) {
                    $stmt = $pdo->prepare("UPDATE enrollments SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$status], $ids));
                }
                
                if ($classId !== null) {
                    $stmt = $pdo->prepare("UPDATE enrollments SET class_id = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$classId ?: null], $ids));
                }
                
                $pdo->commit();
                json_response(['success' => true, 'message' => 'Cập nhật ' . count($ids) . ' đăng ký thành công']);
            } catch (Exception $e) {
                $pdo->rollBack();
                json_response(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
            }
            break;
            
        default:
            json_response(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    json_response(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    json_response(['success' => false, 'message' => $e->getMessage()]);
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

