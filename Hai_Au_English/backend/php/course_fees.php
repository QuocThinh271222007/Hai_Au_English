<?php
/**
 * Course Fee Items API
 * Quản lý nội dung bảng học phí
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
                getCourseFeeItems();
            } elseif ($action === 'get' && isset($_GET['id'])) {
                getCourseFeeItemById($_GET['id']);
            } elseif ($action === 'by_category' && isset($_GET['category'])) {
                getCourseFeeItemsByCategory($_GET['category']);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                createCourseFeeItem();
            } elseif ($action === 'update') {
                updateCourseFeeItem();
            } elseif ($action === 'delete') {
                deleteCourseFeeItem();
            } elseif ($action === 'reorder') {
                reorderCourseFeeItems();
            } elseif ($action === 'bulk_update') {
                bulkUpdateCourseFeeItems();
            }
            break;
            
        default:
            jsonResponse(false, 'Method not allowed', null, 405);
    }
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage(), null, 500);
}

function getCourseFeeItems() {
    global $pdo;
    
    try {
        $activeOnly = isset($_GET['active']) && $_GET['active'] !== '0';
        $category = $_GET['category'] ?? null;
        
        $sql = "SELECT * FROM course_fee_items WHERE 1=1";
        $params = [];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        if ($category) {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }
        
        $sql .= " ORDER BY category ASC, display_order ASC, id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        // Group by category
        $grouped = [
            'tieuhoc' => [],
            'thcs' => [],
            'ielts' => []
        ];
        
        foreach ($items as $item) {
            $grouped[$item['category']][] = $item;
        }
        
        jsonResponse(true, 'Lấy danh sách học phí thành công', [
            'items' => $items,
            'grouped' => $grouped
        ]);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            jsonResponse(false, 'Bảng course_fee_items chưa được tạo. Vui lòng chạy file SQL.', null, 500);
        } else {
            jsonResponse(false, 'Lỗi database: ' . $e->getMessage(), null, 500);
        }
    }
}

function getCourseFeeItemById($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM course_fee_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        jsonResponse(false, 'Không tìm thấy mục học phí', null, 404);
    }
    
    jsonResponse(true, 'Lấy thông tin thành công', $item);
}

function getCourseFeeItemsByCategory($category) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM course_fee_items 
        WHERE category = ? AND is_active = 1 
        ORDER BY display_order ASC, id ASC
    ");
    $stmt->execute([$category]);
    $items = $stmt->fetchAll();
    
    jsonResponse(true, 'Lấy danh sách thành công', $items);
}

function createCourseFeeItem() {
    global $pdo;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['category']) || empty($data['level'])) {
            jsonResponse(false, 'Vui lòng nhập đầy đủ thông tin', null, 400);
        }
        
        // Get max display order for category
        $stmt = $pdo->prepare("SELECT MAX(display_order) as max_order FROM course_fee_items WHERE category = ?");
        $stmt->execute([$data['category']]);
        $result = $stmt->fetch();
        $maxOrder = $result['max_order'] ?? 0;
        
        $stmt = $pdo->prepare("
            INSERT INTO course_fee_items (category, level, curriculum, duration, fee, is_highlight, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['category'],
            $data['level'],
            $data['curriculum'] ?? null,
            $data['duration'] ?? null,
            $data['fee'] ?? null,
            $data['is_highlight'] ?? 0,
            $maxOrder + 1,
            $data['is_active'] ?? 1
        ]);
        
        $id = $pdo->lastInsertId();
        
        jsonResponse(true, 'Thêm mục học phí thành công', ['id' => $id]);
    } catch (PDOException $e) {
        // Check if table doesn't exist
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            jsonResponse(false, 'Bảng course_fee_items chưa được tạo. Vui lòng chạy file SQL trong phpMyAdmin.', null, 500);
        } else {
            jsonResponse(false, 'Lỗi database: ' . $e->getMessage(), null, 500);
        }
    }
}

function updateCourseFeeItem() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        jsonResponse(false, 'Thiếu ID', null, 400);
    }
    
    $stmt = $pdo->prepare("
        UPDATE course_fee_items SET
            category = ?,
            level = ?,
            curriculum = ?,
            duration = ?,
            fee = ?,
            is_highlight = ?,
            is_active = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['category'],
        $data['level'],
        $data['curriculum'] ?? null,
        $data['duration'] ?? null,
        $data['fee'] ?? null,
        $data['is_highlight'] ?? 0,
        $data['is_active'] ?? 1,
        $data['id']
    ]);
    
    jsonResponse(true, 'Cập nhật thành công');
}

function deleteCourseFeeItem() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        jsonResponse(false, 'Thiếu ID', null, 400);
    }
    
    $stmt = $pdo->prepare("DELETE FROM course_fee_items WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    jsonResponse(true, 'Xóa thành công');
}

function reorderCourseFeeItems() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['orders']) || !is_array($data['orders'])) {
        jsonResponse(false, 'Dữ liệu không hợp lệ', null, 400);
    }
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("UPDATE course_fee_items SET display_order = ? WHERE id = ?");
        
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

function bulkUpdateCourseFeeItems() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['items']) || !is_array($data['items'])) {
        jsonResponse(false, 'Dữ liệu không hợp lệ', null, 400);
    }
    
    $pdo->beginTransaction();
    
    try {
        $updateStmt = $pdo->prepare("
            UPDATE course_fee_items SET
                level = ?, curriculum = ?, duration = ?, fee = ?, is_highlight = ?, display_order = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $insertStmt = $pdo->prepare("
            INSERT INTO course_fee_items (category, level, curriculum, duration, fee, is_highlight, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($data['items'] as $index => $item) {
            if (!empty($item['id'])) {
                $updateStmt->execute([
                    $item['level'],
                    $item['curriculum'] ?? null,
                    $item['duration'] ?? null,
                    $item['fee'] ?? null,
                    $item['is_highlight'] ?? 0,
                    $index + 1,
                    $item['id']
                ]);
            } else {
                $insertStmt->execute([
                    $item['category'],
                    $item['level'],
                    $item['curriculum'] ?? null,
                    $item['duration'] ?? null,
                    $item['fee'] ?? null,
                    $item['is_highlight'] ?? 0,
                    $index + 1
                ]);
            }
        }
        
        $pdo->commit();
        jsonResponse(true, 'Cập nhật thành công');
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
