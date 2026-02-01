<?php
// teachers.php - Quản lý giảng viên (API)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$mysqli = require __DIR__ . '/db.php';
$method = $_SERVER['REQUEST_METHOD'];

// GET - Lấy danh sách giảng viên
if ($method === 'GET') {
    // Lấy theo ID nếu có
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $mysqli->prepare('SELECT * FROM teachers WHERE id = ? AND is_active = 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $teacher = $result->fetch_assoc();
        if ($teacher) {
            $teacher['specialties'] = json_decode($teacher['specialties'], true) ?? [];
            $teacher['ielts_score'] = floatval($teacher['ielts_score']);
            $teacher['rating'] = floatval($teacher['rating']);
            $teacher['students_count'] = intval($teacher['students_count']);
            $teacher['experience_years'] = intval($teacher['experience_years']);
            echo json_encode(['success' => true, 'teacher' => $teacher]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy giảng viên']);
        }
        exit;
    }
    
    // Lấy chỉ giảng viên nổi bật nếu có param
    $featured = isset($_GET['featured']) ? $_GET['featured'] : null;
    
    if ($featured === '1' || $featured === 'true') {
        $result = $mysqli->query('SELECT * FROM teachers WHERE is_featured = 1 AND is_active = 1 ORDER BY ielts_score DESC, rating DESC');
    } else {
        $result = $mysqli->query('SELECT * FROM teachers WHERE is_active = 1 ORDER BY is_featured DESC, ielts_score DESC');
    }
    
    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $row['specialties'] = json_decode($row['specialties'], true) ?? [];
        $row['ielts_score'] = floatval($row['ielts_score']);
        $row['rating'] = floatval($row['rating']);
        $row['students_count'] = intval($row['students_count']);
        $row['experience_years'] = intval($row['experience_years']);
        $teachers[] = $row;
    }
    echo json_encode(['success' => true, 'teachers' => $teachers]);
    exit;
}

// POST - Thêm giảng viên mới
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = $input ? json_decode($input, true) : $_POST;
    
    $name = trim($data['name'] ?? '');
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');
    $image_url = trim($data['image_url'] ?? '');
    $ielts_score = floatval($data['ielts_score'] ?? 0);
    $experience_years = intval($data['experience_years'] ?? 0);
    $students_count = intval($data['students_count'] ?? 0);
    $rating = floatval($data['rating'] ?? 0);
    $specialties = isset($data['specialties']) ? json_encode($data['specialties']) : '[]';
    $is_featured = intval($data['is_featured'] ?? 0);
    
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu tên giảng viên']);
        exit;
    }
    
    $stmt = $mysqli->prepare('INSERT INTO teachers (name, title, description, image_url, ielts_score, experience_years, students_count, rating, specialties, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssdiiisi', $name, $title, $description, $image_url, $ielts_score, $experience_years, $students_count, $rating, $specialties, $is_featured);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => $stmt->error]);
        exit;
    }
    
    $newId = $mysqli->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Thêm giảng viên thành công']);
    exit;
}

// PUT - Cập nhật giảng viên
if ($method === 'PUT') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $id = intval($data['id'] ?? $_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu id giảng viên']);
        exit;
    }
    
    $updates = [];
    $params = [];
    $types = '';
    
    $allowedFields = ['name', 'title', 'description', 'image_url', 'ielts_score', 'experience_years', 'students_count', 'rating', 'specialties', 'is_featured', 'is_active'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "`$field` = ?";
            if ($field === 'specialties') {
                $params[] = json_encode($data[$field]);
                $types .= 's';
            } elseif (in_array($field, ['ielts_score', 'rating'])) {
                $params[] = floatval($data[$field]);
                $types .= 'd';
            } elseif (in_array($field, ['experience_years', 'students_count', 'is_featured', 'is_active'])) {
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
    
    $sql = 'UPDATE teachers SET ' . implode(', ', $updates) . ' WHERE id = ?';
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

// DELETE - Xóa giảng viên
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu id']);
        exit;
    }
    
    $stmt = $mysqli->prepare('DELETE FROM teachers WHERE id = ?');
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
