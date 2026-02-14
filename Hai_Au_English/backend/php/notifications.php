<?php
/**
 * Admin Notifications API - Thông báo cho Admin
 * 
 * Endpoints:
 * GET /notifications.php - Lấy danh sách thông báo
 * POST /notifications.php - Tạo thông báo mới (internal use)
 * PUT /notifications.php?id=X - Đánh dấu đã đọc
 * PUT /notifications.php?action=read_all - Đánh dấu tất cả đã đọc
 * DELETE /notifications.php?id=X - Xóa thông báo
 */

require_once 'config.php';
require_once 'db.php';
require_once 'session_config.php';

/**
 * Kiểm tra người dùng có phải admin không
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

// Chỉ xử lý request khi file được gọi trực tiếp (không phải include từ file khác)
if (basename($_SERVER['SCRIPT_FILENAME']) === 'notifications.php') {
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
            getNotifications();
            break;
        case 'POST':
            createNotification();
            break;
        case 'PUT':
            if (isset($_GET['action']) && $_GET['action'] === 'read_all') {
                markAllAsRead();
            } else {
                markAsRead();
            }
            break;
        case 'DELETE':
            deleteNotification();
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

/**
 * Lấy danh sách thông báo
 */
function getNotifications() {
    // Kiểm tra admin
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }
    
    $pdo = getDBConnection();
    
    // Kiểm tra bảng tồn tại
    try {
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'admin_notifications'");
        if ($tableCheck->rowCount() == 0) {
            // Bảng chưa tồn tại - tự động tạo
            createNotificationsTable($pdo);
        }
    } catch (PDOException $e) {
        jsonResponse(['success' => true, 'data' => [], 'unread_count' => 0, 'pagination' => ['page' => 1, 'limit' => 50, 'total' => 0, 'total_pages' => 1]]);
        return;
    }
    
    $type = $_GET['type'] ?? '';
    $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === '1';
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 50;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;
    
    try {
        // Build query
        $sql = "SELECT * FROM admin_notifications WHERE 1=1";
        $params = [];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        
        // Count total
        $countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Count unread
        $unreadStmt = $pdo->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0");
        $unreadCount = $unreadStmt->fetchColumn();
        
        // Get notifications
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse([
            'success' => true,
            'data' => $notifications,
            'unread_count' => intval($unreadCount),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total),
                'total_pages' => max(1, ceil($total / $limit))
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Get Notifications Error: " . $e->getMessage());
        jsonResponse(['success' => true, 'data' => [], 'unread_count' => 0, 'pagination' => ['page' => 1, 'limit' => 50, 'total' => 0, 'total_pages' => 1]]);
    }
}

/**
 * Tạo thông báo mới (dùng nội bộ)
 */
function createNotification() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $type = $input['type'] ?? 'system';
    $title = trim($input['title'] ?? '');
    $message = trim($input['message'] ?? '');
    $referenceId = $input['reference_id'] ?? null;
    $referenceTable = $input['reference_table'] ?? null;
    
    if (empty($title) || empty($message)) {
        jsonResponse(['success' => false, 'message' => 'Title và message là bắt buộc'], 400);
        return;
    }
    
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_notifications (type, title, message, reference_id, reference_table)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$type, $title, $message, $referenceId, $referenceTable]);
        $id = $pdo->lastInsertId();
        
        jsonResponse([
            'success' => true,
            'message' => 'Đã tạo thông báo',
            'data' => ['id' => $id]
        ], 201);
        
    } catch (PDOException $e) {
        error_log("Create Notification Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Đánh dấu thông báo đã đọc
 */
function markAsRead() {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID là bắt buộc'], 400);
        return;
    }
    
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE admin_notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Đã đánh dấu đã đọc'
        ]);
        
    } catch (PDOException $e) {
        error_log("Mark Read Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Đánh dấu tất cả đã đọc
 */
function markAllAsRead() {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }
    
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
        $stmt->execute();
        $count = $stmt->rowCount();
        
        jsonResponse([
            'success' => true,
            'message' => "Đã đánh dấu {$count} thông báo đã đọc"
        ]);
        
    } catch (PDOException $e) {
        error_log("Mark All Read Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Xóa thông báo
 */
function deleteNotification() {
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 403);
        return;
    }
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID là bắt buộc'], 400);
        return;
    }
    
    $pdo = getDBConnection();
    
    try {
        $stmt = $pdo->prepare("DELETE FROM admin_notifications WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Đã xóa thông báo'
        ]);
        
    } catch (PDOException $e) {
        error_log("Delete Notification Error: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Database error'], 500);
    }
}

/**
 * Helper: Tạo thông báo từ các module khác
 * Gọi function này từ reviews.php, scores.php, etc.
 */
function createAdminNotification($type, $title, $message, $referenceId = null, $referenceTable = null) {
    try {
        $pdo = getDBConnection();
        
        // Kiểm tra và tạo bảng nếu chưa tồn tại
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'admin_notifications'");
        if ($tableCheck->rowCount() == 0) {
            createNotificationsTable($pdo);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_notifications (type, title, message, reference_id, reference_table)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$type, $title, $message, $referenceId, $referenceTable]);
        return true;
    } catch (PDOException $e) {
        error_log("Create Admin Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper: Lấy cài đặt giới hạn
 */
function getLimitSettings() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("
            SELECT setting_key, setting_value 
            FROM site_settings 
            WHERE setting_key IN ('max_approved_reviews', 'max_approved_achievements', 'auto_pending_reviews', 'auto_pending_achievements')
        ");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return [
            'max_approved_reviews' => intval($settings['max_approved_reviews'] ?? 20),
            'max_approved_achievements' => intval($settings['max_approved_achievements'] ?? 20),
            'auto_pending_reviews' => ($settings['auto_pending_reviews'] ?? '1') === '1',
            'auto_pending_achievements' => ($settings['auto_pending_achievements'] ?? '1') === '1'
        ];
    } catch (PDOException $e) {
        error_log("Get Limit Settings Error: " . $e->getMessage());
        return [
            'max_approved_reviews' => 20,
            'max_approved_achievements' => 20,
            'auto_pending_reviews' => true,
            'auto_pending_achievements' => true
        ];
    }
}

/**
 * Helper: Đếm số review đã duyệt
 */
function countApprovedReviews() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 1");
        return intval($stmt->fetchColumn());
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Helper: Đếm số thành tích hiển thị
 */
function countFeaturedAchievements() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM student_achievements WHERE is_featured = 1");
        return intval($stmt->fetchColumn());
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Helper: Tự động tạo bảng notifications nếu chưa tồn tại
 */
function createNotificationsTable($pdo) {
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_notifications` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `type` ENUM('review', 'achievement', 'score', 'contact', 'user', 'system', 'course', 'class', 'schedule', 'teacher', 'enrollment', 'teacher_review') NOT NULL DEFAULT 'system',
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `reference_id` INT UNSIGNED DEFAULT NULL,
                `reference_table` VARCHAR(50) DEFAULT NULL,
                `is_read` TINYINT(1) DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_type` (`type`),
                INDEX `idx_is_read` (`is_read`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Thêm thông báo chào mừng
        $pdo->exec("
            INSERT INTO `admin_notifications` (`type`, `title`, `message`) VALUES
            ('system', 'Hệ thống khởi tạo thành công', 'Hệ thống thông báo admin đã được khởi tạo. Bạn sẽ nhận thông báo khi có review mới, điểm số mới, hoặc các thay đổi quan trọng.')
        ");
        
        // Thêm settings giới hạn nếu chưa có
        $pdo->exec("
            INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
            ('max_approved_reviews', '20', 'number', 'Số lượng đánh giá tối đa được duyệt hiển thị'),
            ('max_approved_achievements', '20', 'number', 'Số lượng thành tích tối đa được hiển thị'),
            ('max_approved_teacher_reviews', '20', 'number', 'Số lượng đánh giá giảng viên tối đa được duyệt'),
            ('auto_pending_reviews', '1', 'boolean', 'Tự động đặt review mới ở trạng thái chờ duyệt khi vượt giới hạn'),
            ('auto_pending_achievements', '1', 'boolean', 'Tự động đặt thành tích mới ở trạng thái chờ hiển thị khi vượt giới hạn')
        ");
        
        return true;
    } catch (PDOException $e) {
        error_log("Create Notifications Table Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper: Đếm số đánh giá giảng viên đã duyệt
 */
function countApprovedTeacherReviews() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM teacher_reviews WHERE is_approved = 1");
        return intval($stmt->fetchColumn());
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Helper: Lấy cài đặt giới hạn (bao gồm teacher_reviews)  
 */
function getLimitSettingsExtended() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("
            SELECT setting_key, setting_value 
            FROM site_settings 
            WHERE setting_key IN ('max_approved_reviews', 'max_approved_achievements', 'max_approved_teacher_reviews', 'auto_pending_reviews', 'auto_pending_achievements')
        ");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return [
            'max_approved_reviews' => intval($settings['max_approved_reviews'] ?? 20),
            'max_approved_achievements' => intval($settings['max_approved_achievements'] ?? 20),
            'max_approved_teacher_reviews' => intval($settings['max_approved_teacher_reviews'] ?? 20),
            'auto_pending_reviews' => ($settings['auto_pending_reviews'] ?? '1') === '1',
            'auto_pending_achievements' => ($settings['auto_pending_achievements'] ?? '1') === '1'
        ];
    } catch (PDOException $e) {
        return [
            'max_approved_reviews' => 20,
            'max_approved_achievements' => 20,
            'max_approved_teacher_reviews' => 20,
            'auto_pending_reviews' => true,
            'auto_pending_achievements' => true
        ];
    }
}
