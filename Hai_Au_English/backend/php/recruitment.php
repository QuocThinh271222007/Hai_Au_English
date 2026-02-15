<?php
/**
 * Recruitment API - Quản lý thông tin tuyển dụng
 * Hải Âu English
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

setCorsHeaders();

$mysqli = getMySQLiConnection();
$action = $_GET['action'] ?? 'list';

// ============================================
// PUBLIC ENDPOINTS (không cần đăng nhập)
// ============================================

// Lấy danh sách tuyển dụng (public)
if ($action === 'list') {
    $activeOnly = isset($_GET['active']) ? (bool)$_GET['active'] : true;
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    $where = [];
    $params = [];
    $types = '';
    
    if ($activeOnly) {
        $where[] = 'is_active = 1';
        $where[] = '(deadline IS NULL OR deadline >= CURDATE())';
    }
    
    if ($featured) {
        $where[] = 'is_featured = 1';
    }
    
    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Count total
    $countSql = "SELECT COUNT(*) as total FROM recruitments $whereClause";
    $countResult = $mysqli->query($countSql);
    $total = $countResult->fetch_assoc()['total'];
    
    // Get data
    $sql = "SELECT id, title, slug, department, location, employment_type, salary_range, 
                   experience, deadline, is_featured, view_count, created_at
            FROM recruitments 
            $whereClause 
            ORDER BY is_featured DESC, created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = $mysqli->query($sql);
    $jobs = [];
    
    while ($row = $result->fetch_assoc()) {
        $row['employment_type_label'] = getEmploymentTypeLabel($row['employment_type']);
        $row['deadline_formatted'] = $row['deadline'] ? date('d/m/Y', strtotime($row['deadline'])) : null;
        $jobs[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $jobs,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy chi tiết một tin tuyển dụng (public)
if ($action === 'detail') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $slug = $_GET['slug'] ?? '';
    
    if (!$id && !$slug) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu ID hoặc slug']);
        exit;
    }
    
    $where = $id ? 'id = ?' : 'slug = ?';
    $param = $id ? $id : $slug;
    $type = $id ? 'i' : 's';
    
    $stmt = $mysqli->prepare("SELECT * FROM recruitments WHERE $where");
    $stmt->bind_param($type, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();
    $stmt->close();
    
    if (!$job) {
        http_response_code(404);
        echo json_encode(['error' => 'Không tìm thấy tin tuyển dụng']);
        exit;
    }
    
    // Tăng view count
    $mysqli->query("UPDATE recruitments SET view_count = view_count + 1 WHERE id = {$job['id']}");
    
    $job['employment_type_label'] = getEmploymentTypeLabel($job['employment_type']);
    $job['deadline_formatted'] = $job['deadline'] ? date('d/m/Y', strtotime($job['deadline'])) : null;
    $job['created_at_formatted'] = date('d/m/Y', strtotime($job['created_at']));
    
    echo json_encode(['success' => true, 'data' => $job], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// ADMIN ENDPOINTS (cần đăng nhập admin)
// ============================================

require_once __DIR__ . '/session_config.php';

function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Không có quyền truy cập']);
        exit;
    }
}

// Lấy tất cả (admin - bao gồm inactive)
if ($action === 'admin_list') {
    requireAdmin();
    
    $sql = "SELECT * FROM recruitments ORDER BY created_at DESC";
    $result = $mysqli->query($sql);
    $jobs = [];
    
    while ($row = $result->fetch_assoc()) {
        $row['employment_type_label'] = getEmploymentTypeLabel($row['employment_type']);
        $jobs[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $jobs], JSON_UNESCAPED_UNICODE);
    exit;
}

// Tạo tin tuyển dụng mới
if ($action === 'create') {
    requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $title = trim($input['title'] ?? '');
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập tiêu đề']);
        exit;
    }
    
    $slug = createSlug($title);
    
    // Check slug unique
    $checkStmt = $mysqli->prepare('SELECT id FROM recruitments WHERE slug = ?');
    $checkStmt->bind_param('s', $slug);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $slug .= '-' . time();
    }
    $checkStmt->close();
    
    $department = trim($input['department'] ?? '');
    $location = trim($input['location'] ?? 'TP. Hồ Chí Minh');
    $employment_type = $input['employment_type'] ?? 'full-time';
    $salary_range = trim($input['salary_range'] ?? '');
    $experience = trim($input['experience'] ?? '');
    $description = $input['description'] ?? '';
    $requirements = $input['requirements'] ?? '';
    $benefits = $input['benefits'] ?? '';
    $contact_email = trim($input['contact_email'] ?? ADMIN_EMAIL);
    $contact_phone = trim($input['contact_phone'] ?? '0931 828 960');
    $deadline = !empty($input['deadline']) ? $input['deadline'] : null;
    $is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;
    $is_featured = isset($input['is_featured']) ? (int)$input['is_featured'] : 0;
    
    $stmt = $mysqli->prepare('INSERT INTO recruitments 
        (title, slug, department, location, employment_type, salary_range, experience, 
         description, requirements, benefits, contact_email, contact_phone, deadline, is_active, is_featured) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    
    $stmt->bind_param('sssssssssssssii',
        $title, $slug, $department, $location, $employment_type, $salary_range, $experience,
        $description, $requirements, $benefits, $contact_email, $contact_phone, $deadline, $is_active, $is_featured
    );
    
    if ($stmt->execute()) {
        $newId = $mysqli->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Tạo tin tuyển dụng thành công',
            'id' => $newId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi tạo tin: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Cập nhật tin tuyển dụng
if ($action === 'update') {
    requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu ID']);
        exit;
    }
    
    $title = trim($input['title'] ?? '');
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['error' => 'Vui lòng nhập tiêu đề']);
        exit;
    }
    
    $department = trim($input['department'] ?? '');
    $location = trim($input['location'] ?? 'TP. Hồ Chí Minh');
    $employment_type = $input['employment_type'] ?? 'full-time';
    $salary_range = trim($input['salary_range'] ?? '');
    $experience = trim($input['experience'] ?? '');
    $description = $input['description'] ?? '';
    $requirements = $input['requirements'] ?? '';
    $benefits = $input['benefits'] ?? '';
    $contact_email = trim($input['contact_email'] ?? ADMIN_EMAIL);
    $contact_phone = trim($input['contact_phone'] ?? '0931 828 960');
    $deadline = !empty($input['deadline']) ? $input['deadline'] : null;
    $is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;
    $is_featured = isset($input['is_featured']) ? (int)$input['is_featured'] : 0;
    
    $stmt = $mysqli->prepare('UPDATE recruitments SET 
        title = ?, department = ?, location = ?, employment_type = ?, salary_range = ?, 
        experience = ?, description = ?, requirements = ?, benefits = ?, 
        contact_email = ?, contact_phone = ?, deadline = ?, is_active = ?, is_featured = ?
        WHERE id = ?');
    
    $stmt->bind_param('ssssssssssssiii',
        $title, $department, $location, $employment_type, $salary_range,
        $experience, $description, $requirements, $benefits,
        $contact_email, $contact_phone, $deadline, $is_active, $is_featured, $id
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi cập nhật: ' . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// Xóa tin tuyển dụng
if ($action === 'delete') {
    requireAdmin();
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Thiếu ID']);
        exit;
    }
    
    $stmt = $mysqli->prepare('DELETE FROM recruitments WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa thành công']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Không tìm thấy tin để xóa']);
    }
    $stmt->close();
    exit;
}

// Toggle status
if ($action === 'toggle_status') {
    requireAdmin();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $field = $input['field'] ?? 'is_active';
    
    if (!$id || !in_array($field, ['is_active', 'is_featured'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Tham số không hợp lệ']);
        exit;
    }
    
    $stmt = $mysqli->prepare("UPDATE recruitments SET $field = NOT $field WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        // Get new value
        $result = $mysqli->query("SELECT $field FROM recruitments WHERE id = $id");
        $newValue = $result->fetch_assoc()[$field];
        
        echo json_encode([
            'success' => true,
            'new_value' => (bool)$newValue
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi cập nhật']);
    }
    $stmt->close();
    exit;
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function getEmploymentTypeLabel($type) {
    $labels = [
        'full-time' => 'Toàn thời gian',
        'part-time' => 'Bán thời gian',
        'contract' => 'Hợp đồng',
        'intern' => 'Thực tập'
    ];
    return $labels[$type] ?? $type;
}

function createSlug($str) {
    $str = mb_strtolower($str, 'UTF-8');
    
    // Vietnamese to ASCII
    $vietnamese = [
        'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
        'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
        'ì','í','ị','ỉ','ĩ',
        'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
        'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
        'ỳ','ý','ỵ','ỷ','ỹ',
        'đ'
    ];
    $ascii = [
        'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
        'e','e','e','e','e','e','e','e','e','e','e',
        'i','i','i','i','i',
        'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
        'u','u','u','u','u','u','u','u','u','u','u',
        'y','y','y','y','y',
        'd'
    ];
    
    $str = str_replace($vietnamese, $ascii, $str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    $str = trim($str, '-');
    
    return $str;
}

// Invalid action
http_response_code(400);
echo json_encode(['error' => 'Action không hợp lệ']);
