<?php
/**
 * Base Configuration for Hai Au English Frontend
 * Tự động detect đường dẫn gốc của project
 * Hoạt động cho cả XAMPP local và Hostinger production
 */

// Detect môi trường
function isLocalEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    return (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
}

// Detect base path từ URL
function getBasePath() {
    // Nếu là Hostinger production - không có base path
    if (!isLocalEnvironment()) {
        return '';
    }
    
    // XAMPP local - kiểm tra thư mục con
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Danh sách các thư mục có thể là base path
    $knownBases = ['/hai_au_english', '/Hai_Au_English'];
    
    foreach ($knownBases as $base) {
        if (stripos($requestUri, $base) === 0 || stripos($scriptName, $base) === 0) {
            return strtolower($base);
        }
    }
    
    return '';
}

// Đường dẫn gốc của project
$basePath = getBasePath();

// Các URL paths - Clean URLs
$paths = [
    'home' => $basePath . '/TrangChu',
    'about' => $basePath . '/GioiThieu', 
    'courses' => $basePath . '/KhoaHoc',
    'teachers' => $basePath . '/GiangVien',
    'contact' => $basePath . '/LienHe',
    'login' => $basePath . '/DangNhap',
    'signup' => $basePath . '/DangKy',
    'profile' => $basePath . '/HocVien',
    'admin' => $basePath . '/QuanTri',
    'recruitment' => $basePath . '/TuyenDung',
];

// Đường dẫn assets (CSS, JS, images)
$assetsPath = $basePath . '/frontend';

// Đường dẫn API backend
$apiPath = $basePath . '/backend/php';
?>
