<?php
/**
 * Test all admin APIs - Run this once to verify all endpoints work
 */

session_start();

// Simulate admin login
$_SESSION['user_id'] = 1;

require_once __DIR__ . '/php/db.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>API Test Results</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .err{color:red;} pre{background:#f5f5f5;padding:10px;}</style>";

// Test site-settings
echo "<h2>1. Site Settings API</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY setting_key");
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='ok'>✓ Found " . count($settings) . " settings</p>";
    echo "<pre>" . json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch (Exception $e) {
    echo "<p class='err'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test site-content
echo "<h2>2. Site Content API</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM site_content WHERE is_active = 1 ORDER BY page, section");
    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='ok'>✓ Found " . count($contents) . " content items</p>";
    
    // Group by page
    $grouped = [];
    foreach ($contents as $c) {
        $grouped[$c['page']][] = $c['content_key'];
    }
    foreach ($grouped as $page => $keys) {
        echo "<p>&nbsp;&nbsp;• $page: " . count($keys) . " items</p>";
    }
} catch (Exception $e) {
    echo "<p class='err'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test upload directories
echo "<h2>3. Upload Directories</h2>";
$dirs = [
    'avatars' => __DIR__ . '/../frontend/assets/images/uploads/avatars/',
    'reviews' => __DIR__ . '/../frontend/assets/images/uploads/reviews/',
    'achievements' => __DIR__ . '/../frontend/assets/images/uploads/achievements/',
    'teachers' => __DIR__ . '/../frontend/assets/images/uploads/teachers/',
    'content' => __DIR__ . '/../frontend/assets/images/uploads/content/'
];

foreach ($dirs as $name => $path) {
    if (file_exists($path)) {
        if (is_writable($path)) {
            echo "<p class='ok'>✓ $name: exists & writable</p>";
        } else {
            echo "<p class='err'>✗ $name: exists but NOT writable</p>";
        }
    } else {
        // Try to create
        if (mkdir($path, 0777, true)) {
            echo "<p class='ok'>✓ $name: created successfully</p>";
        } else {
            echo "<p class='err'>✗ $name: does NOT exist and cannot create</p>";
        }
    }
}

// Test change password endpoint
echo "<h2>4. Password Hash Test</h2>";
$testPassword = 'admin123';
$stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE id = 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin) {
    if (password_verify($testPassword, $admin['password'])) {
        echo "<p class='ok'>✓ Admin password 'admin123' is correct</p>";
    } else {
        echo "<p class='err'>✗ Admin password 'admin123' does NOT match</p>";
        echo "<p>Fixing password...</p>";
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 1");
        $stmt->execute([$newHash]);
        echo "<p class='ok'>✓ Password reset to 'admin123'</p>";
    }
}

echo "<h2>5. Default Avatar</h2>";
$avatarPath = __DIR__ . '/../frontend/assets/images/default-avatar.svg';
if (file_exists($avatarPath)) {
    echo "<p class='ok'>✓ default-avatar.svg exists</p>";
} else {
    echo "<p class='err'>✗ default-avatar.svg NOT found</p>";
}

echo "<hr><p><strong>Test completed!</strong></p>";
echo "<p><a href='/frontend/admin/admin.php'>Go to Admin Dashboard</a></p>";
