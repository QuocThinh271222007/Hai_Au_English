# Hướng Dẫn Cài Đặt OAuth & reCAPTCHA

## Mục Lục
1. [Tổng Quan](#1-tổng-quan)
2. [Google reCAPTCHA v3](#2-google-recaptcha-v3)
3. [Google OAuth 2.0](#3-google-oauth-20)
4. [Facebook OAuth](#4-facebook-oauth)
5. [Cập Nhật Database](#5-cập-nhật-database)
6. [Cấu Hình File](#6-cấu-hình-file)
7. [Testing](#7-testing)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Tổng Quan

Hệ thống đã được tích hợp:
- **reCAPTCHA v3**: Chống bot tự động đăng ký/đăng nhập
- **Google OAuth**: Đăng nhập bằng tài khoản Google
- **Facebook OAuth**: Đăng nhập bằng tài khoản Facebook

### Các File Đã Thay Đổi/Thêm Mới:
```
backend/
├── php/
│   ├── oauth_config.php      (MỚI) - Cấu hình OAuth & reCAPTCHA
│   ├── oauth_callback.php    (MỚI) - Xử lý callback từ Google/Facebook
│   └── auth.php              (CẬP NHẬT) - Tích hợp reCAPTCHA
├── migrations/
│   └── add_oauth_columns.sql (MỚI) - Migration thêm cột OAuth

frontend/
├── pages/
│   ├── login.php             (CẬP NHẬT) - Thêm OAuth buttons & reCAPTCHA
│   └── signup.php            (CẬP NHẬT) - Thêm OAuth buttons & reCAPTCHA
└── js/
    └── controllers/
        └── auth.js           (CẬP NHẬT) - Hỗ trợ reCAPTCHA token
```

---

## 2. Google reCAPTCHA v3

### Bước 1: Đăng Ký reCAPTCHA
1. Truy cập [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Đăng nhập bằng Google Account
3. Click **"+"** để tạo site mới
4. Điền thông tin:
   - **Label**: Hai Au English (hoặc tên dự án)
   - **reCAPTCHA type**: chọn **reCAPTCHA v3**
   - **Domains**: 
     - `localhost` (cho development)
     - `yourdomain.com` (cho production - VD: haiauu.site)
5. Accept Terms → Submit

### Bước 2: Lấy Keys
Sau khi tạo xong, bạn sẽ nhận được:
- **Site Key** (public): `6Lc...ABC`
- **Secret Key** (private): `6Lc...XYZ`

### Bước 3: Cấu Hình
Mở file `backend/php/oauth_config.php` và cập nhật:

```php
// ==================== RECAPTCHA V3 CONFIG ====================
define('RECAPTCHA_ENABLED', true);  // Đổi thành true để bật
define('RECAPTCHA_SITE_KEY', '6Lc...ABC');  // Site Key của bạn
define('RECAPTCHA_SECRET_KEY', '6Lc...XYZ');  // Secret Key của bạn
define('RECAPTCHA_MIN_SCORE', 0.5);  // Điểm tối thiểu (0.0 - 1.0)
```

**Lưu ý về RECAPTCHA_MIN_SCORE:**
- `0.0`: Cho phép tất cả (kể cả bot)
- `0.5`: Cân bằng (khuyến nghị)
- `0.9`: Rất nghiêm ngặt (có thể block người thật)

---

## 3. Google OAuth 2.0

### Bước 1: Tạo Google Cloud Project
1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project có sẵn
3. Vào **APIs & Services** → **Credentials**

### Bước 2: Cấu Hình OAuth Consent Screen
1. Vào **OAuth consent screen**
2. Chọn **External** (cho tất cả người dùng Google)
3. Điền thông tin:
   - **App name**: Hải Âu English
   - **User support email**: your@email.com
   - **Developer contact**: your@email.com
4. **Scopes**: Thêm `email` và `profile`
5. **Test users**: Thêm email để test (nếu app ở testing mode)

### Bước 3: Tạo OAuth Client ID
1. Vào **Credentials** → **Create Credentials** → **OAuth client ID**
2. Chọn **Web application**
3. Điền:
   - **Name**: Hai Au English Web Client
   - **Authorized JavaScript origins**:
     ```
     http://localhost
     https://yourdomain.com
     ```
   - **Authorized redirect URIs**:
     ```
     http://localhost/hai_au_english/backend/php/oauth_callback.php?provider=google
     https://yourdomain.com/backend/php/oauth_callback.php?provider=google
     ```
4. Click **Create**

### Bước 4: Lấy Credentials
Bạn sẽ nhận được:
- **Client ID**: `123456789-abc...apps.googleusercontent.com`
- **Client Secret**: `GOCSPX-...`

### Bước 5: Cấu Hình
Mở file `backend/php/oauth_config.php`:

```php
// ==================== GOOGLE OAUTH CONFIG ====================
define('GOOGLE_OAUTH_ENABLED', true);  // Đổi thành true
define('GOOGLE_CLIENT_ID', '123456789-abc...apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'GOCSPX-...');
```

Đảm bảo Redirect URI khớp với cấu hình:
```php
define('GOOGLE_REDIRECT_URI', $baseUrl . '/backend/php/oauth_callback.php?provider=google');
```

---

## 4. Facebook OAuth

### Bước 1: Tạo Facebook App
1. Truy cập [Facebook Developers](https://developers.facebook.com/)
2. Click **My Apps** → **Create App**
3. Chọn **Consumer** hoặc **Business**
4. Điền tên app: **Hai Au English**

### Bước 2: Cấu Hình Facebook Login
1. Vào app dashboard
2. Click **Add Product** → Tìm **Facebook Login** → **Set Up**
3. Chọn **Web**
4. Điền **Site URL**: `https://yourdomain.com` hoặc `http://localhost`

### Bước 3: Cấu Hình OAuth Settings
1. Vào **Facebook Login** → **Settings**
2. Điền **Valid OAuth Redirect URIs**:
   ```
   http://localhost/hai_au_english/backend/php/oauth_callback.php?provider=facebook
   https://yourdomain.com/backend/php/oauth_callback.php?provider=facebook
   ```
3. Bật các options:
   - ✅ Client OAuth Login
   - ✅ Web OAuth Login
   - ✅ Enforce HTTPS (cho production)

### Bước 4: Lấy App Credentials
1. Vào **Settings** → **Basic**
2. Lấy:
   - **App ID**: `1234567890123456`
   - **App Secret**: Click "Show" để xem

### Bước 5: Cấu Hình
Mở file `backend/php/oauth_config.php`:

```php
// ==================== FACEBOOK OAUTH CONFIG ====================
define('FACEBOOK_OAUTH_ENABLED', true);  // Đổi thành true
define('FACEBOOK_APP_ID', '1234567890123456');
define('FACEBOOK_APP_SECRET', 'abc123...');
```

### Bước 6: App Review (Production)
Để app hoạt động với tất cả người dùng (không chỉ test users):
1. Vào **App Review** → **Requests**
2. Request quyền **email** và **public_profile**
3. Đợi Facebook approve (1-5 ngày làm việc)

---

## 5. Cập Nhật Database

### Chạy Migration Script
Mở phpMyAdmin hoặc MySQL CLI và chạy:

```sql
-- File: backend/migrations/add_oauth_columns.sql

ALTER TABLE `users` 
ADD COLUMN `oauth_provider` VARCHAR(50) DEFAULT NULL,
ADD COLUMN `oauth_id` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `users` 
MODIFY COLUMN `password` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `users`
ADD INDEX `idx_oauth` (`oauth_provider`, `oauth_id`);
```

### Kiểm Tra
```sql
DESCRIBE users;
```

Phải thấy các cột mới:
- `oauth_provider` - VARCHAR(50)
- `oauth_id` - VARCHAR(255)
- `password` - NULL được phép

---

## 6. Cấu Hình File

### File Chính: `backend/php/oauth_config.php`

```php
<?php
// ===== BASE URL DETECTION =====
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Development (localhost)
    if (strpos($host, 'localhost') !== false) {
        return $protocol . '://' . $host . '/hai_au_english';
    }
    
    // Production
    return $protocol . '://' . $host;
}

$baseUrl = getBaseUrl();

// ===== RECAPTCHA V3 =====
define('RECAPTCHA_ENABLED', true);                    // ← Bật/tắt
define('RECAPTCHA_SITE_KEY', 'YOUR_SITE_KEY');        // ← Thay thế
define('RECAPTCHA_SECRET_KEY', 'YOUR_SECRET_KEY');    // ← Thay thế
define('RECAPTCHA_MIN_SCORE', 0.5);

// ===== GOOGLE OAUTH =====
define('GOOGLE_OAUTH_ENABLED', true);                               // ← Bật/tắt
define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID.apps.googleusercontent.com');  // ← Thay thế
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');               // ← Thay thế
define('GOOGLE_REDIRECT_URI', $baseUrl . '/backend/php/oauth_callback.php?provider=google');

// ===== FACEBOOK OAUTH =====
define('FACEBOOK_OAUTH_ENABLED', true);               // ← Bật/tắt
define('FACEBOOK_APP_ID', 'YOUR_APP_ID');             // ← Thay thế
define('FACEBOOK_APP_SECRET', 'YOUR_APP_SECRET');     // ← Thay thế
define('FACEBOOK_REDIRECT_URI', $baseUrl . '/backend/php/oauth_callback.php?provider=facebook');
```

---

## 7. Testing

### Test reCAPTCHA
1. Bật `RECAPTCHA_ENABLED = true`
2. Thử đăng nhập - Không thấy CAPTCHA (vì v3 invisible)
3. Kiểm tra Network tab → Có request tới `google.com/recaptcha`
4. Nếu điểm thấp, sẽ bị từ chối đăng nhập

### Test Google OAuth
1. Bật `GOOGLE_OAUTH_ENABLED = true`
2. Click nút "Google" trên trang đăng nhập
3. Redirect đến Google → Chọn tài khoản
4. Redirect về `/TrangCaNhan?oauth=success`

### Test Facebook OAuth
1. Bật `FACEBOOK_OAUTH_ENABLED = true`
2. Click nút "Facebook" trên trang đăng nhập
3. Redirect đến Facebook → Đăng nhập/Cho phép
4. Redirect về `/TrangCaNhan?oauth=success`

### Kiểm Tra Database
```sql
SELECT id, fullname, email, oauth_provider, oauth_id 
FROM users 
WHERE oauth_provider IS NOT NULL;
```

---

## 8. Troubleshooting

### reCAPTCHA Issues

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-------------|-----------|
| "timeout-or-duplicate" | Token đã hết hạn | Token chỉ valid 2 phút, cần request mới |
| "missing-input-secret" | Thiếu Secret Key | Kiểm tra `RECAPTCHA_SECRET_KEY` |
| "invalid-input-secret" | Secret Key sai | Kiểm tra lại key từ Google Console |
| Score luôn thấp | Bot detection | Thử từ browser khác/incognito |

### Google OAuth Issues

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-------------|-----------|
| "redirect_uri_mismatch" | URI không khớp | Kiểm tra Authorized Redirect URIs trong Console |
| "access_denied" | User từ chối | Bình thường, user nhấn Cancel |
| "invalid_client" | Client ID sai | Kiểm tra lại Client ID |
| CORS error | Origin không được phép | Thêm vào Authorized JS origins |

### Facebook OAuth Issues

| Lỗi | Nguyên Nhân | Giải Pháp |
|-----|-------------|-----------|
| "URL Blocked" | Redirect URI không hợp lệ | Thêm vào Valid OAuth Redirect URIs |
| "App Not Setup" | App ở Development mode | Thêm user vào Test Users |
| No email returned | User ẩn email | Không thể tạo account (yêu cầu email) |

### Cách Debug

1. **Kiểm tra PHP Error Log:**
   ```
   C:\xampp\php\logs\php_error_log
   ```

2. **Kiểm tra Console Browser:**
   - F12 → Console tab
   - Xem lỗi JavaScript

3. **Kiểm tra Network:**
   - F12 → Network tab
   - Filter by XHR
   - Xem request/response

4. **Test OAuth Config:**
   ```
   http://localhost/hai_au_english/backend/php/auth.php?action=oauth_config
   ```
   Phải trả về JSON với URLs hợp lệ.

---

## Checklist Triển Khai

### Development (localhost)
- [ ] Tạo reCAPTCHA với domain `localhost`
- [ ] Tạo Google OAuth với localhost origins/redirects
- [ ] Tạo Facebook App với localhost
- [ ] Chạy migration SQL
- [ ] Test cả 3 tính năng

### Production
- [ ] Thêm production domain vào reCAPTCHA
- [ ] Thêm production URLs vào Google OAuth
- [ ] Thêm production URLs vào Facebook App
- [ ] Update `oauth_config.php` với production keys (nếu khác)
- [ ] Facebook: Request App Review
- [ ] Test trên production

---

## Thông Tin Hỗ Trợ

- **Google reCAPTCHA Docs**: https://developers.google.com/recaptcha/docs/v3
- **Google OAuth Docs**: https://developers.google.com/identity/protocols/oauth2
- **Facebook Login Docs**: https://developers.facebook.com/docs/facebook-login/web

---

*Cập nhật lần cuối: 2024*
