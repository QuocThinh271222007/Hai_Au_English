<?php
/**
 * Student Achievements API - Quản lý thành tích học viên tiêu biểu
 * 
 * Endpoints:
 * GET /achievements.php - Lấy danh sách thành tích
 * POST /achievements.php - Tạo thành tích mới (chỉ admin)
 * PUT /achievements.php?id=X - Cập nhật thành tích (chỉ admin)
 * DELETE /achievements.php?id=X - Xóa thành tích (chỉ admin)
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
        getAchievements();
        break;
    case 'POST':
        createAchievement();
        break;
    case 'PUT':
        updateAchievement();
        break;
    case 'DELETE':
        deleteAchievement();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Lấy danh sách thành tích
 */
function getAchievements() {
    $pdo = getDBConnection();
    
    $featured = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 20;
    
    try {
        $sql = "SELECT * FROM student_achievements";
        $params = [];
        
        if ($featured !== null) {
            $sql .= " WHERE is_featured = ?";
            $params[] = $featured ? 1 : 0;
        }
        
        $sql .= " ORDER BY display_order ASC, created_at DESC LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex, $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse([
            'success' => true,
            'data' => $achievements,
            'count' => count($achievements)
        ]);
        
    } catch (PDOException $e) {
        error_log("Get Achievements Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Tạo thành tích mới (chỉ admin)
 */
function createAchievement() {
    // Kiểm tra admin
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Chỉ admin mới có quyền này'], 403);
        return;
    }
    
    // Parse input - hỗ trợ cả JSON và form-data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $studentName = trim($input['student_name'] ?? '');
    $achievementTitle = trim($input['achievement_title'] ?? '');
    
    if (empty($studentName) || empty($achievementTitle)) {
        jsonResponse(['success' => false, 'message' => 'Tên học viên và tiêu đề thành tích là bắt buộc'], 400);
        return;
    }
    
    // Handle image upload
    $imageUrl = $input['image_url'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageUrl = handleImageUpload($_FILES['image'], 'achievements');
        if ($imageUrl === false) {
            jsonResponse(['success' => false, 'message' => 'Lỗi upload ảnh'], 400);
            return;
        }
    }
    
    $pdo = getDBConnection();
    
    try {
        // Kiểm tra giới hạn số thành tích được hiển thị
        $limitSettings = getLimitSettings();
        $featuredCount = countFeaturedAchievements();
        $isFeatured = isset($input['is_featured']) ? 1 : 0;
        
        // Nếu admin muốn hiển thị nhưng đã đạt giới hạn - trả về lỗi
        if ($isFeatured && $featuredCount >= $limitSettings['max_approved_achievements']) {
            jsonResponse([
                'success' => false, 
                'message' => 'Đã đạt giới hạn ' . $limitSettings['max_approved_achievements'] . ' thành tích được hiển thị. Vui lòng xóa hoặc ẩn một thành tích khác trước.',
                'limit_reached' => true,
                'current_count' => $featuredCount,
                'max_count' => $limitSettings['max_approved_achievements']
            ], 400);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO student_achievements 
            (student_name, achievement_title, description, image_url, score, course_name, achievement_date, is_featured, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $studentName,
            $achievementTitle,
            $input['description'] ?? '',
            $imageUrl,
            $input['score'] ?? '',
            $input['course_name'] ?? '',
            $input['achievement_date'] ?? null,
            $isFeatured,
            intval($input['display_order'] ?? 0)
        ]);
        
        $id = $pdo->lastInsertId();
        
        // Tạo thông báo
        $notifTitle = $isFeatured ? 'Thành tích mới được thêm' : 'Thành tích mới (Chưa hiển thị)';
        $notifMessage = "Thành tích \"{$achievementTitle}\" của {$studentName} đã được thêm.";
        createAdminNotification('achievement', $notifTitle, $notifMessage, $id, 'student_achievements');
        
        jsonResponse([
            'success' => true,
            'message' => 'Đã thêm thành tích mới',
            'data' => ['id' => $id, 'is_featured' => $isFeatured]
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Create Achievement Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Lỗi khi lưu thành tích'], 500);
    }
}

/**
 * Cập nhật thành tích (chỉ admin)
 */
function updateAchievement() {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID is required'], 400);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        jsonResponse(['success' => false, 'message' => 'Invalid input'], 400);
        return;
    }
    
    $pdo = getDBConnection();
    
    try {
        // Kiểm tra giới hạn nếu đang hiển thị (is_featured = 1)
        if (isset($input['is_featured']) && $input['is_featured'] == 1) {
            // Lấy trạng thái hiện tại
            $checkStmt = $pdo->prepare("SELECT is_featured FROM student_achievements WHERE id = ?");
            $checkStmt->execute([$id]);
            $current = $checkStmt->fetch();
            
            // Chỉ kiểm tra nếu đang chuyển từ ẩn sang hiển thị
            if ($current && $current['is_featured'] == 0) {
                $limitSettings = getLimitSettings();
                $featuredCount = countFeaturedAchievements();
                
                if ($featuredCount >= $limitSettings['max_approved_achievements']) {
                    jsonResponse([
                        'success' => false, 
                        'message' => 'Đã đạt giới hạn ' . $limitSettings['max_approved_achievements'] . ' thành tích được hiển thị. Vui lòng ẩn hoặc xóa một thành tích khác trước.',
                        'limit_reached' => true,
                        'current_count' => $featuredCount,
                        'max_count' => $limitSettings['max_approved_achievements']
                    ], 400);
                    return;
                }
            }
        }
        
        // Build dynamic update query
        $updates = [];
        $params = [];
        
        $allowedFields = ['student_name', 'achievement_title', 'description', 'image_url', 'score', 'course_name', 'achievement_date', 'is_featured', 'display_order'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
            return;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE student_achievements SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true, 'message' => 'Đã cập nhật thành tích']);
        
    } catch (PDOException $e) {
        error_log("Update Achievement Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Xóa thành tích (chỉ admin)
 */
function deleteAchievement() {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID is required'], 400);
        return;
    }
    
    $pdo = getDBConnection();
    
    try {
        // Lấy thông tin để xóa
        $checkStmt = $pdo->prepare("SELECT image_url, student_name, achievement_title FROM student_achievements WHERE id = ?");
        $checkStmt->execute([$id]);
        $achievement = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$achievement) {
            jsonResponse(['success' => false, 'message' => 'Không tìm thấy thành tích'], 404);
            return;
        }
        
        // Xóa ảnh nếu có và là ảnh local
        if ($achievement['image_url'] && strpos($achievement['image_url'], '/uploads/') !== false) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $achievement['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Xóa record
        $stmt = $pdo->prepare("DELETE FROM student_achievements WHERE id = ?");
        $stmt->execute([$id]);
        
        createAdminNotification('achievement', 'Xóa thành tích', 'Thành tích "' . ($achievement['achievement_title'] ?? '') . '" của ' . ($achievement['student_name'] ?? 'học viên') . ' đã bị xóa', $id, 'student_achievements');
        
        jsonResponse(['success' => true, 'message' => 'Đã xóa thành tích']);
        
    } catch (PDOException $e) {
        error_log("Delete Achievement Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Kiểm tra quyền admin
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

/**
 * Xử lý upload ảnh
 */
function handleImageUpload($file, $folder = 'achievements') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/frontend/assets/images/uploads/' . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return '/frontend/assets/images/uploads/' . $folder . '/' . $filename;
    }
    
    return false;
}
