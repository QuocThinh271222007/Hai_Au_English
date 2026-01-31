<?php
// courses.php - Quản lý khóa học (API đầy đủ)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$mysqli = require __DIR__ . '/db.php';
$method = $_SERVER['REQUEST_METHOD'];

// GET - Lấy danh sách khóa học
if ($method === 'GET') {
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
    
    // Lấy theo category nếu có
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    
    if ($category && $category !== 'all') {
        $stmt = $mysqli->prepare('SELECT * FROM courses WHERE category = ? AND is_active = 1 ORDER BY id ASC');
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query('SELECT * FROM courses WHERE is_active = 1 ORDER BY id ASC');
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
    $input = file_get_contents('php://input');
    $data = $input ? json_decode($input, true) : $_POST;
    
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $image_url = trim($data['image_url'] ?? '');
    $level = $data['level'] ?? 'beginner';
    $duration = trim($data['duration'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $price_unit = trim($data['price_unit'] ?? '/khóa');
    $category = $data['category'] ?? 'group';
    $badge = trim($data['badge'] ?? '') ?: null;
    $badge_type = trim($data['badge_type'] ?? '') ?: null;
    $features = isset($data['features']) ? json_encode($data['features']) : '[]';
    $target = trim($data['target'] ?? '');
    
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu tên khóa học']);
        exit;
    }
    
    $stmt = $mysqli->prepare('INSERT INTO courses (name, description, image_url, level, duration, price, price_unit, category, badge, badge_type, features, target) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssdssssss', $name, $description, $image_url, $level, $duration, $price, $price_unit, $category, $badge, $badge_type, $features, $target);
    
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
if ($method === 'PUT') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id = intval($data['id'] ?? $_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu id khóa học']);
        exit;
    }
    
    $updates = [];
    $params = [];
    $types = '';
    
    $allowedFields = ['name', 'description', 'image_url', 'level', 'duration', 'price', 'price_unit', 'category', 'badge', 'badge_type', 'features', 'target', 'is_active'];
    
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
