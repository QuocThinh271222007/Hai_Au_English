<?php
// courses.php - Quản lý khóa học (API đầy đủ)
require_once __DIR__ . '/config.php';

// Set CORS headers using config
setCorsHeaders();

$mysqli = require __DIR__ . '/db.php';
$method = $_SERVER['REQUEST_METHOD'];

// GET - Lấy danh sách khóa học
if ($method === 'GET') {
    // Lấy danh sách lớp học và thời khóa biểu của khóa học
    if (isset($_GET['action']) && $_GET['action'] === 'classes') {
        $courseId = intval($_GET['course_id'] ?? 0);
        if (!$courseId) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu course_id']);
            exit;
        }
        
        // Lấy danh sách lớp học
        $stmt = $mysqli->prepare('
            SELECT cl.*, 
                t.name as teacher_name,
                t.image_url as teacher_avatar,
                (SELECT COUNT(*) FROM enrollments WHERE class_id = cl.id AND status IN ("active", "completed")) as student_count
            FROM classes cl
            LEFT JOIN teachers t ON cl.teacher_id = t.id
            WHERE cl.course_id = ? AND cl.status = "active"
            ORDER BY cl.start_date ASC
        ');
        $stmt->bind_param('i', $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            // Lấy thời khóa biểu của lớp
            $stmt2 = $mysqli->prepare('
                SELECT cs.*, t.name as teacher_name
                FROM class_schedules cs
                LEFT JOIN teachers t ON cs.teacher_id = t.id
                WHERE cs.class_id = ? AND cs.is_active = 1
                ORDER BY FIELD(cs.day_of_week, "monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"), cs.start_time
            ');
            $stmt2->bind_param('i', $row['id']);
            $stmt2->execute();
            $schedResult = $stmt2->get_result();
            
            $schedules = [];
            while ($sched = $schedResult->fetch_assoc()) {
                $schedules[] = $sched;
            }
            $row['schedules'] = $schedules;
            
            // Format schedule summary
            $scheduleSummary = [];
            $dayMap = [
                'monday' => 'T2', 'tuesday' => 'T3', 'wednesday' => 'T4',
                'thursday' => 'T5', 'friday' => 'T6', 'saturday' => 'T7', 'sunday' => 'CN'
            ];
            foreach ($schedules as $s) {
                $day = $dayMap[$s['day_of_week']] ?? $s['day_of_week'];
                $time = substr($s['start_time'], 0, 5) . '-' . substr($s['end_time'], 0, 5);
                $scheduleSummary[] = "$day ($time)";
            }
            $row['schedule_formatted'] = implode(', ', $scheduleSummary);
            
            $classes[] = $row;
        }
        
        echo json_encode(['success' => true, 'classes' => $classes]);
        exit;
    }
    
    // Lấy theo ID nếu có
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $mysqli->prepare('SELECT * FROM courses WHERE id = ? AND is_active = 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        if ($course) {
            // Parse JSON features
            $course['features'] = json_decode($course['features'], true) ?? [];
            $course['price'] = floatval($course['price']);
            echo json_encode(['success' => true, 'course' => $course]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy khóa học']);
        }
        exit;
    }
    
    // Lấy theo category nếu có (dùng cột age_group)
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    if ($category && $category !== 'all') {
        $stmt = $mysqli->prepare('SELECT * FROM courses WHERE age_group = ? AND is_active = 1 ORDER BY display_order ASC, id ASC');
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query('SELECT * FROM courses WHERE is_active = 1 ORDER BY age_group ASC, display_order ASC, id ASC');
    }
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Parse JSON features
        $row['features'] = json_decode($row['features'], true) ?? [];
        $row['price'] = floatval($row['price']);
        $courses[] = $row;
    }
    echo json_encode(['success' => true, 'courses' => $courses]);
    exit;
}

// POST - Thêm khóa học mới
if ($method === 'POST') {
    // Handle both FormData (for file upload) and JSON
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
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'File không hợp lệ. Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP tối đa 5MB']);
                exit;
            }
        }
        // Parse features if string
        if (isset($data['features']) && is_string($data['features'])) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $data['features'])));
        }
    } else {
        $input = file_get_contents('php://input');
        $data = $input ? json_decode($input, true) : $_POST;
    }
    
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $image_url = trim($data['image_url'] ?? '');
    $level = trim($data['level'] ?? '');
    $duration = trim($data['duration'] ?? '');
    $curriculum = trim($data['curriculum'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $price_unit = trim($data['price_unit'] ?? '/tháng');
    $age_group = $data['age_group'] ?? $data['category'] ?? 'tieuhoc';
    $badge = trim($data['badge'] ?? '') ?: null;
    $badge_type = trim($data['badge_type'] ?? '') ?: null;
    $features = isset($data['features']) ? json_encode($data['features']) : '[]';
    $target = trim($data['target'] ?? '');
    
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu tên khóa học']);
        exit;
    }
    
    $stmt = $mysqli->prepare('INSERT INTO courses (name, description, image_url, level, duration, curriculum, price, price_unit, age_group, badge, features, target) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssdsssss', $name, $description, $image_url, $level, $duration, $curriculum, $price, $price_unit, $age_group, $badge, $features, $target);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    
    $newId = $mysqli->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Thêm khóa học thành công']);
    exit;
}

// PUT - Cập nhật khóa học
if ($method === 'PUT' || ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT')) {
    // Handle both FormData (for file upload) and JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'multipart/form-data') !== false) {
        $data = $_POST;
        $id = intval($data['id'] ?? $_GET['id'] ?? 0);
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
                // Delete old image if exists
                if ($id) {
                    $oldStmt = $mysqli->prepare("SELECT image_url FROM courses WHERE id = ?");
                    $oldStmt->bind_param('i', $id);
                    $oldStmt->execute();
                    $oldResult = $oldStmt->get_result();
                    $oldImage = $oldResult->fetch_column();
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
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
    }
    
    $id = intval($data['id'] ?? $_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu id khóa học']);
        exit;
    }
    
    $updates = [];
    $params = [];
    $types = '';
    
    $allowedFields = ['name', 'description', 'image_url', 'level', 'duration', 'curriculum', 'price', 'price_unit', 'age_group', 'badge', 'features', 'target', 'is_active'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "`$field` = ?";
            if ($field === 'features') {
                $params[] = json_encode($data[$field]);
                $types .= 's';
            } elseif ($field === 'price') {
                $params[] = floatval($data[$field]);
                $types .= 'd';
            } elseif ($field === 'is_active') {
                $params[] = intval($data[$field]);
                $types .= 'i';
            } else {
                $params[] = $data[$field];
                $types .= 's';
            }
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'Không có dữ liệu để cập nhật']);
        exit;
    }
    
    $params[] = $id;
    $types .= 'i';
    
    $sql = 'UPDATE courses SET ' . implode(', ', $updates) . ' WHERE id = ?';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    exit;
}

// DELETE - Xóa khóa học
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu id']);
        exit;
    }
    
    $stmt = $mysqli->prepare('DELETE FROM courses WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Xóa thành công']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;
