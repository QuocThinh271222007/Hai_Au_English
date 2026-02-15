<?php
/**
 * Hostinger Deployment Test
 * Kiểm tra tất cả chức năng quan trọng trên Hostinger
 * 
 * URL: https://yourdomain.com/backend/test_hostinger.php
 * 
 * ⚠️ XÓA FILE NÀY SAU KHI TEST XONG!
 */

// Start output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Hostinger Deployment Test - Hải Âu English</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 900px; 
            margin: 0 auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
        h2 { color: #374151; margin-top: 30px; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 12px 20px; 
            border-radius: 8px; 
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 12px 20px; 
            border-radius: 8px; 
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .warning { 
            background: #fff3cd; 
            color: #856404; 
            padding: 12px 20px; 
            border-radius: 8px; 
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info { 
            background: #e7f3ff; 
            color: #004085; 
            padding: 12px 20px; 
            border-radius: 8px; 
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
        pre { 
            background: #1e1e1e; 
            color: #d4d4d4; 
            padding: 15px; 
            border-radius: 8px; 
            overflow-x: auto;
            font-size: 13px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        .badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <h1>🔧 Hostinger Deployment Test</h1>
    <p>Kiểm tra cấu hình hệ thống Hải Âu English trên Hostinger</p>

    <?php
    // ============================================
    // 1. ENVIRONMENT INFO
    // ============================================
    ?>
    <div class="card">
        <h2>📋 1. Thông tin Server</h2>
        <table>
            <tr>
                <th>Thuộc tính</th>
                <th>Giá trị</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?= phpversion() ?></td>
            </tr>
            <tr>
                <td>Server Software</td>
                <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>Domain</td>
                <td><?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>Protocol</td>
                <td><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'HTTPS ✅' : 'HTTP ⚠️' ?></td>
            </tr>
            <tr>
                <td>Document Root</td>
                <td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></td>
            </tr>
        </table>
    </div>

    <?php
    // ============================================
    // 2. CONFIG FILES
    // ============================================
    ?>
    <div class="card">
        <h2>📁 2. Config Files</h2>
        <?php
        $configFile = __DIR__ . '/php/config.php';
        $oauthConfigFile = __DIR__ . '/php/oauth_config.php';
        $vendorAutoload = __DIR__ . '/vendor/autoload.php';
        
        if (file_exists($configFile)) {
            echo '<div class="success">✅ config.php tồn tại</div>';
            require_once $configFile;
        } else {
            echo '<div class="error">❌ config.php KHÔNG tồn tại!</div>';
        }
        
        if (file_exists($oauthConfigFile)) {
            echo '<div class="success">✅ oauth_config.php tồn tại</div>';
            require_once $oauthConfigFile;
        } else {
            echo '<div class="error">❌ oauth_config.php KHÔNG tồn tại!</div>';
        }
        
        if (file_exists($vendorAutoload)) {
            echo '<div class="success">✅ vendor/autoload.php tồn tại (PHPMailer đã cài)</div>';
        } else {
            echo '<div class="error">❌ vendor/autoload.php KHÔNG tồn tại! Cần upload thư mục vendor/</div>';
        }
        ?>
    </div>

    <?php
    // ============================================
    // 3. DATABASE CONNECTION
    // ============================================
    ?>
    <div class="card">
        <h2>🗄️ 3. Database Connection</h2>
        <?php
        if (defined('DB_HOST')) {
            echo '<div class="info">';
            echo "Host: " . DB_HOST . "<br>";
            echo "User: " . DB_USER . "<br>";
            echo "Database: " . DB_NAME . "<br>";
            echo '</div>';
            
            try {
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                if ($conn->connect_error) {
                    echo '<div class="error">❌ Kết nối thất bại: ' . htmlspecialchars($conn->connect_error) . '</div>';
                } else {
                    echo '<div class="success">✅ Kết nối database thành công!</div>';
                    
                    // Check users table
                    $result = $conn->query("SELECT COUNT(*) as count FROM users");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        echo '<div class="info">👥 Số users trong database: ' . $row['count'] . '</div>';
                    }
                    
                    $conn->close();
                }
            } catch (Exception $e) {
                echo '<div class="error">❌ Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            echo '<div class="error">❌ Config database chưa được load!</div>';
        }
        ?>
    </div>

    <?php
    // ============================================
    // 4. SMTP EMAIL CONFIG
    // ============================================
    ?>
    <div class="card">
        <h2>📧 4. SMTP Email Configuration</h2>
        <?php
        if (defined('SMTP_HOST')) {
            $smtpConfigured = !empty(SMTP_HOST) && !empty(SMTP_USERNAME) && !empty(SMTP_SECRET);
            
            echo '<table>';
            echo '<tr><td>SMTP Host</td><td>' . SMTP_HOST . '</td></tr>';
            echo '<tr><td>SMTP Username</td><td>' . SMTP_USERNAME . '</td></tr>';
            echo '<tr><td>SMTP Password</td><td>' . str_repeat('*', strlen(SMTP_SECRET) - 4) . substr(SMTP_SECRET, -4) . '</td></tr>';
            echo '<tr><td>Người nhận mặc định</td><td>' . (defined('SHOP_OWNER') ? SHOP_OWNER : 'N/A') . '</td></tr>';
            echo '</table>';
            
            if ($smtpConfigured) {
                echo '<div class="success">✅ SMTP đã được cấu hình</div>';
                
                // Test gửi email nếu có parameter
                if (isset($_GET['test_email'])) {
                    echo '<h3>🚀 Đang test gửi email...</h3>';
                    
                    require_once __DIR__ . '/service/EmailService.php';
                    $emailService = new EmailService();
                    $result = $emailService->send(
                        SHOP_OWNER,
                        '[TEST] Email từ Hostinger - ' . date('Y-m-d H:i:s'),
                        '<h2>Test Email thành công!</h2><p>Đây là email test từ Hostinger deployment.</p><p>Thời gian: ' . date('Y-m-d H:i:s') . '</p>'
                    );
                    
                    if ($result['success']) {
                        echo '<div class="success">✅ Email đã được gửi thành công đến ' . SHOP_OWNER . '!</div>';
                    } else {
                        echo '<div class="error">❌ Gửi email thất bại: ' . htmlspecialchars($result['error']) . '</div>';
                    }
                } else {
                    echo '<a href="?test_email=1" class="btn">📧 Test gửi Email</a>';
                }
            } else {
                echo '<div class="warning">⚠️ SMTP chưa được cấu hình đầy đủ</div>';
            }
        } else {
            echo '<div class="error">❌ Config SMTP chưa được load!</div>';
        }
        ?>
    </div>

    <?php
    // ============================================
    // 5. OAUTH CONFIGURATION
    // ============================================
    ?>
    <div class="card">
        <h2>🔐 5. OAuth Configuration</h2>
        <table>
            <tr>
                <th>Provider</th>
                <th>Trạng thái</th>
                <th>Redirect URI</th>
            </tr>
            <tr>
                <td>Google OAuth</td>
                <td>
                    <?php if (defined('GOOGLE_OAUTH_ENABLED') && GOOGLE_OAUTH_ENABLED): ?>
                        <span class="badge badge-success">BẬT</span>
                    <?php else: ?>
                        <span class="badge badge-warning">TẮT</span>
                    <?php endif; ?>
                </td>
                <td><small><?= defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : 'N/A' ?></small></td>
            </tr>
            <tr>
                <td>Facebook OAuth</td>
                <td>
                    <?php if (defined('FACEBOOK_OAUTH_ENABLED') && FACEBOOK_OAUTH_ENABLED): ?>
                        <span class="badge badge-success">BẬT</span>
                    <?php else: ?>
                        <span class="badge badge-warning">TẮT</span>
                    <?php endif; ?>
                </td>
                <td><small><?= defined('FACEBOOK_REDIRECT_URI') ? FACEBOOK_REDIRECT_URI : 'N/A' ?></small></td>
            </tr>
            <tr>
                <td>reCAPTCHA v3</td>
                <td>
                    <?php if (defined('RECAPTCHA_ENABLED') && RECAPTCHA_ENABLED): ?>
                        <span class="badge badge-success">BẬT</span>
                    <?php else: ?>
                        <span class="badge badge-warning">TẮT</span>
                    <?php endif; ?>
                </td>
                <td>-</td>
            </tr>
        </table>
        
        <?php if (defined('GOOGLE_OAUTH_ENABLED') && !GOOGLE_OAUTH_ENABLED): ?>
        <div class="warning">
            ⚠️ <strong>Google OAuth chưa bật.</strong><br>
            Để bật OAuth, cần:
            <ol>
                <li>Tạo OAuth credentials tại <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></li>
                <li>Thêm Redirect URI: <code><?= defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : '' ?></code></li>
                <li>Cập nhật GOOGLE_CLIENT_ID và GOOGLE_CLIENT_SECRET trong oauth_config.php</li>
                <li>Đặt GOOGLE_OAUTH_ENABLED = true</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>

    <?php
    // ============================================
    // 6. PHP EXTENSIONS
    // ============================================
    ?>
    <div class="card">
        <h2>🔌 6. PHP Extensions</h2>
        <?php
        $requiredExtensions = [
            'mysqli' => 'Database connection',
            'curl' => 'OAuth & API calls',
            'openssl' => 'SMTP encryption', 
            'json' => 'JSON handling',
            'mbstring' => 'UTF-8 support',
            'session' => 'User sessions'
        ];
        
        echo '<table>';
        echo '<tr><th>Extension</th><th>Trạng thái</th><th>Mục đích</th></tr>';
        foreach ($requiredExtensions as $ext => $purpose) {
            $loaded = extension_loaded($ext);
            echo '<tr>';
            echo '<td>' . $ext . '</td>';
            echo '<td>' . ($loaded ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-danger">MISSING</span>') . '</td>';
            echo '<td>' . $purpose . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        ?>
    </div>

    <?php
    // ============================================
    // 7. API ENDPOINTS TEST
    // ============================================
    ?>
    <div class="card">
        <h2>🔗 7. API Endpoints</h2>
        <?php
        $endpoints = [
            'auth.php' => 'Đăng nhập/Đăng ký',
            'courses.php' => 'Khóa học',
            'teachers.php' => 'Giảng viên',
            'contact.php' => 'Liên hệ',
            'profile.php' => 'Profile',
            'oauth_callback.php' => 'OAuth Callback'
        ];
        
        echo '<table>';
        echo '<tr><th>Endpoint</th><th>Trạng thái</th><th>Chức năng</th></tr>';
        foreach ($endpoints as $file => $desc) {
            $exists = file_exists(__DIR__ . '/php/' . $file);
            echo '<tr>';
            echo '<td>/backend/php/' . $file . '</td>';
            echo '<td>' . ($exists ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-danger">MISSING</span>') . '</td>';
            echo '<td>' . $desc . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        ?>
    </div>

    <?php
    // ============================================
    // 8. SUMMARY & ACTIONS
    // ============================================
    ?>
    <div class="card">
        <h2>📝 Tổng kết</h2>
        <?php
        $issues = [];
        
        // Check database
        if (!defined('DB_HOST') || !@(new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME))) {
            $issues[] = 'Database connection failed';
        }
        
        // Check vendor
        if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
            $issues[] = 'PHPMailer chưa được cài (thiếu vendor folder)';
        }
        
        // Check SMTP
        if (!defined('SMTP_HOST') || empty(SMTP_SECRET)) {
            $issues[] = 'SMTP chưa được cấu hình';
        }
        
        if (empty($issues)) {
            echo '<div class="success">✅ <strong>Tất cả đều OK!</strong> Hệ thống sẵn sàng hoạt động.</div>';
        } else {
            echo '<div class="error"><strong>❌ Có ' . count($issues) . ' vấn đề cần xử lý:</strong><ul>';
            foreach ($issues as $issue) {
                echo '<li>' . $issue . '</li>';
            }
            echo '</ul></div>';
        }
        ?>
        
        <div class="warning" style="margin-top: 20px;">
            ⚠️ <strong>QUAN TRỌNG:</strong> Xóa file này sau khi test xong để bảo mật!
            <br><br>
            <a href="?delete_self=1" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa file test này?')">🗑️ Xóa file test này</a>
        </div>
        
        <?php
        if (isset($_GET['delete_self'])) {
            if (unlink(__FILE__)) {
                echo '<div class="success">✅ File đã được xóa! Redirect về trang chủ...</div>';
                echo '<script>setTimeout(function(){ window.location.href = "/"; }, 2000);</script>';
            } else {
                echo '<div class="error">❌ Không thể xóa file. Hãy xóa thủ công.</div>';
            }
        }
        ?>
    </div>

    <p style="text-align: center; color: #888; margin-top: 30px;">
        Hải Âu English - Deployment Test Tool<br>
        <small>Generated at <?= date('Y-m-d H:i:s') ?></small>
    </p>
</body>
</html>
