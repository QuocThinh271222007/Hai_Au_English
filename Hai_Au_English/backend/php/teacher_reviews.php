<?php
/**
 * Teacher Reviews API
 * Quản lý đánh giá về giảng viên từ học viên
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Define jsonResponse BEFORE including config to avoid redeclaration
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

require_once 'config.php';
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list' || empty($action)) {
                getTeacherReviews();
            } elseif ($action === 'get' && isset($_GET['id'])) {
                getTeacherReviewById($_GET['id']);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                createTeacherReview();
            } elseif ($action === 'update') {
                updateTeacherReview();
            } elseif ($action === 'delete') {
                deleteTeacherReview();
            } elseif ($action === 'reorder') {
                reorderTeacherReviews();
            }
            break;
            
        default:
            jsonResponse(false, 'Method not allowed', null, 405);
    }
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage(), null, 500);
}

function getTeacherReviews() {
    global $pdo;
    
    $approvedOnly = !isset($_GET['active']) || $_GET['active'] !== '0';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
    
    $sql = "SELECT * FROM teacher_reviews";
    $params = [];
    
    if ($approvedOnly) {
        $sql .= " WHERE is_approved = 1";
    }
    
    $sql .= " ORDER BY created_at DESC, id DESC";
    
    if ($limit > 0) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $pdo->prepare($sql);
    if ($limit > 0) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    
    jsonResponse(true, 'Lấy danh sách đánh giá thành công', $reviews);
}

function getTeacherReviewById($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM teacher_reviews WHERE id = ?");
    $stmt->execute([$id]);
    $review = $stmt->fetch();
    
    if (!$review) {
        jsonResponse(false, 'Không tìm thấy đánh giá', null, 404);
    }
    
    jsonResponse(true, 'Lấy thông tin đánh giá thành công', $review);
}

function createTeacherReview() {
    global $pdo;
    
    // Check if request is from logged-in user or admin
    require_once 'session_config.php';
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // If user is logged in, use their info
    $isLoggedInUser = isset($_SESSION['user_id']);
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    
    if ($isLoggedInUser && !$isAdmin) {
        // Regular logged-in user creating a review
        if (empty($data['comment']) || empty($data['rating'])) {
            jsonResponse(false, 'Vui lòng nhập đầy đủ thông tin', null, 400);
        }
        
        // Get user info
        $stmt = $pdo->prepare("SELECT fullname, avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        $reviewerName = $user['fullname'] ?? 'Học viên';
        $reviewerAvatar = $user['avatar'] ? substr($user['fullname'], 0, 2) : 'HV';
        $reviewerInfo = 'Học viên Hải Âu English';
        
        // Get max display order
        $stmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) as max_order FROM teacher_reviews");
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO teacher_reviews (reviewer_name, reviewer_avatar, reviewer_info, rating, comment, user_id, display_order, is_approved)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)
        ");
        
        $stmt->execute([
            $reviewerName,
            $reviewerAvatar,
            $reviewerInfo,
            $data['rating'],
            $data['comment'],
            $_SESSION['user_id'],
            $maxOrder + 1
        ]);
        
        $id = $pdo->lastInsertId();
        jsonResponse(true, 'Đánh giá của bạn đã được gửi thành công!', ['id' => $id]);
        
    } else if ($isAdmin) {
        // Admin creating a review
        if (empty($data['reviewer_name']) || empty($data['comment'])) {
            jsonResponse(false, 'Vui lòng nhập đầy đủ thông tin', null, 400);
        }
        
        // Get max display order
        $stmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) as max_order FROM teacher_reviews");
        $maxOrder = $stmt->fetch()['max_order'] ?? 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO teacher_reviews (reviewer_name, reviewer_avatar, reviewer_info, rating, comment, teacher_id, display_order, is_approved)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['reviewer_name'],
            $data['reviewer_avatar'] ?? null,
            $data['reviewer_info'] ?? null,
            $data['rating'] ?? 5,
            $data['comment'],
            $data['teacher_id'] ?? null,
            $maxOrder + 1,
            $data['is_approved'] ?? 1
        ]);
        
        $id = $pdo->lastInsertId();
        jsonResponse(true, 'Thêm đánh giá thành công', ['id' => $id]);
        
    } else {
        jsonResponse(false, 'Bạn cần đăng nhập để đánh giá', null, 401);
    }
}

function updateTeacherReview() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        jsonResponse(false, 'Thiếu ID đánh giá', null, 400);
    }
    
    $stmt = $pdo->prepare("
        UPDATE teacher_reviews SET
            reviewer_name = ?,
            reviewer_avatar = ?,
            reviewer_info = ?,
            rating = ?,
            comment = ?,
            teacher_id = ?,
            is_approved = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['reviewer_name'],
        $data['reviewer_avatar'] ?? null,
        $data['reviewer_info'] ?? null,
        $data['rating'] ?? 5,
        $data['comment'],
        $data['teacher_id'] ?? null,
        $data['is_approved'] ?? 1,
        $data['id']
    ]);
    
    jsonResponse(true, 'Cập nhật đánh giá thành công');
}

function deleteTeacherReview() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        jsonResponse(false, 'Thiếu ID đánh giá', null, 400);
    }
    
    $stmt = $pdo->prepare("DELETE FROM teacher_reviews WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    jsonResponse(true, 'Xóa đánh giá thành công');
}

function reorderTeacherReviews() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['orders']) || !is_array($data['orders'])) {
        jsonResponse(false, 'Dữ liệu không hợp lệ', null, 400);
    }
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("UPDATE teacher_reviews SET display_order = ? WHERE id = ?");
        
        foreach ($data['orders'] as $item) {
            $stmt->execute([$item['order'], $item['id']]);
        }
        
        $pdo->commit();
        jsonResponse(true, 'Sắp xếp lại thành công');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
