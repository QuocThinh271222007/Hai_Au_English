<?php
/**
 * Reviews API - Quản lý đánh giá từ học viên
 * 
 * Endpoints:
 * GET /reviews.php - Lấy danh sách đánh giá
 * POST /reviews.php - Tạo đánh giá mới (cần đăng nhập)
 * DELETE /reviews.php?id=X - Xóa đánh giá (chỉ chủ sở hữu hoặc admin)
 */

require_once 'config.php';
require_once 'db.php';
require_once 'session_config.php';
require_once 'notifications.php';

// Set CORS headers
setCorsHeaders();

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getReviews();
        break;
    case 'POST':
        createReview();
        break;
    case 'DELETE':
        deleteReview();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Lấy danh sách đánh giá
 */
function getReviews() {
    $pdo = getDBConnection();
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;
    
    try {
        // Đếm tổng số reviews
        $countStmt = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 1");
        $total = $countStmt->fetchColumn();
        
        // Lấy reviews kèm avatar từ bảng users
        $stmt = $pdo->prepare("
            SELECT r.id, r.user_name, r.rating, r.comment, r.image_url, r.created_at,
                   u.avatar as user_avatar
            FROM reviews r
            LEFT JOIN users u ON r.user_name = u.fullname
            WHERE r.is_approved = 1 
            ORDER BY r.created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tính average rating
        $avgStmt = $pdo->query("SELECT AVG(rating) as avg_rating FROM reviews WHERE is_approved = 1");
        $avgRating = round($avgStmt->fetchColumn(), 1);
        
        jsonResponse([
            'success' => true,
            'data' => $reviews,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total),
                'total_pages' => ceil($total / $limit)
            ],
            'average_rating' => $avgRating
        ]);
        
    } catch (PDOException $e) {
        error_log("Get Reviews Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Tạo đánh giá mới
 */
function createReview() {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá'], 401);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Lấy thông tin user từ database
    $pdo = getDBConnection();
    $userStmt = $pdo->prepare("SELECT fullname, avatar FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    $userName = $userInfo['fullname'] ?? $_SESSION['user_name'] ?? 'Học viên';
    
    // Parse input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Kiểm tra form data (cho upload ảnh)
        $input = $_POST;
    }
    
    // Validate
    $rating = isset($input['rating']) ? intval($input['rating']) : 0;
    $comment = trim($input['comment'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        jsonResponse(['success' => false, 'message' => 'Đánh giá sao phải từ 1 đến 5'], 400);
        return;
    }
    
    if (empty($comment) || strlen($comment) < 10) {
        jsonResponse(['success' => false, 'message' => 'Nhận xét phải có ít nhất 10 ký tự'], 400);
        return;
    }
    
    if (strlen($comment) > 500) {
        jsonResponse(['success' => false, 'message' => 'Nhận xét không được quá 500 ký tự'], 400);
        return;
    }
    
    // Handle image upload
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageUrl = handleImageUpload($_FILES['image'], 'reviews');
        if ($imageUrl === false) {
            jsonResponse(['success' => false, 'message' => 'Lỗi upload ảnh. Chỉ chấp nhận JPG, PNG, GIF dưới 5MB'], 400);
            return;
        }
    }
    
    $pdo = getDBConnection();
    
    try {
        // Kiểm tra giới hạn số review được duyệt
        $limitSettings = getLimitSettings();
        $approvedCount = countApprovedReviews();
        $isApproved = 1; // Mặc định được duyệt
        $pendingMessage = '';
        
        if ($limitSettings['auto_pending_reviews'] && $approvedCount >= $limitSettings['max_approved_reviews']) {
            $isApproved = 0; // Tự động đặt chờ duyệt
            $pendingMessage = ' Đánh giá đang chờ duyệt vì số lượng đã đạt giới hạn.';
        }
        
        // Kiểm tra xem user đã review chưa (giới hạn 1 review/user nếu cần)
        // $checkStmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ?");
        // $checkStmt->execute([$userId]);
        // if ($checkStmt->fetch()) {
        //     jsonResponse(['success' => false, 'message' => 'Bạn đã đánh giá rồi'], 400);
        //     return;
        // }
        
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, user_name, rating, comment, image_url, is_approved)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$userId, $userName, $rating, $comment, $imageUrl, $isApproved]);
        $reviewId = $pdo->lastInsertId();
        
        // Tạo thông báo cho admin
        $notifTitle = $isApproved ? 'Đánh giá mới' : 'Đánh giá mới (Chờ duyệt)';
        $notifMessage = "{$userName} đã gửi đánh giá {$rating} sao: \"{$comment}\"";
        if (!$isApproved) {
            $notifMessage .= " [Đang chờ duyệt vì đã đạt giới hạn {$limitSettings['max_approved_reviews']} đánh giá]";
        }
        createAdminNotification('review', $notifTitle, $notifMessage, $reviewId, 'reviews');
        
        jsonResponse([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi!' . $pendingMessage,
            'data' => [
                'id' => $reviewId,
                'user_name' => $userName,
                'rating' => $rating,
                'comment' => $comment,
                'image_url' => $imageUrl,
                'is_approved' => $isApproved,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Create Review Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Lỗi khi lưu đánh giá'], 500);
    }
}

/**
 * Xóa đánh giá
 */
function deleteReview() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        return;
    }
    
    $reviewId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$reviewId) {
        jsonResponse(['success' => false, 'message' => 'Review ID is required'], 400);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    
    $pdo = getDBConnection();
    
    try {
        // Kiểm tra quyền sở hữu
        $checkStmt = $pdo->prepare("SELECT user_id, user_name, comment, image_url FROM reviews WHERE id = ?");
        $checkStmt->execute([$reviewId]);
        $review = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$review) {
            jsonResponse(['success' => false, 'message' => 'Không tìm thấy đánh giá'], 404);
            return;
        }
        
        if ($review['user_id'] != $userId && !$isAdmin) {
            jsonResponse(['success' => false, 'message' => 'Bạn không có quyền xóa đánh giá này'], 403);
            return;
        }
        
        // Xóa ảnh nếu có
        if ($review['image_url']) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $review['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Xóa review
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        
        $deletedBy = $isAdmin ? 'Admin' : ($review['user_name'] ?? 'Người dùng');
        createAdminNotification('review', 'Xóa đánh giá', 'Đánh giá của ' . ($review['user_name'] ?? 'người dùng') . ' đã bị xóa bởi ' . $deletedBy . ': "' . substr($review['comment'] ?? '', 0, 50) . '..."', $reviewId, 'reviews');
        
        jsonResponse(['success' => true, 'message' => 'Đã xóa đánh giá']);
        
    } catch (PDOException $e) {
        error_log("Delete Review Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Xử lý upload ảnh
 */
function handleImageUpload($file, $folder = 'reviews') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Validate file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Create upload directory
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/images/uploads/' . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return '/frontend/assets/images/uploads/' . $folder . '/' . $filename;
    }
    
    return false;
}
