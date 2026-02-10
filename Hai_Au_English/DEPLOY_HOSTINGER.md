# 🚀 Hướng dẫn Deploy Hải Âu English lên Hostinger

> **Cập nhật:** Dự án đã được tối ưu để tự động detect môi trường (XAMPP local vs Hostinger production).  
> Chỉ cần thay đổi thông tin database Hostinger là có thể deploy!

---

## 📋 Tổng quan dự án

```
Hai_Au_English/
├── index.php                    # Redirect về trang chủ
├── .htaccess                    # Apache config (bảo mật, cache, redirect)
├── backend/
│   ├── .htaccess                # Bảo vệ files nhạy cảm
│   ├── database.sql             # SQL tạo database
│   ├── sample_data.sql          # Dữ liệu mẫu (optional)
│   └── php/
│       ├── config.php           # ⭐ CẤU HÌNH CHÍNH - SỬA FILE NÀY
│       ├── db.php               # Kết nối database
│       ├── auth.php             # API đăng nhập/đăng ký
│       ├── courses.php          # API khóa học
│       ├── teachers.php         # API giảng viên
│       ├── contact.php          # API liên hệ
│       ├── profile.php          # API profile học viên
│       ├── admin.php            # API admin
│       ├── reviews.php          # API đánh giá
│       └── achievements.php     # API thành tích
└── frontend/
    ├── .htaccess                # Cache và MIME types
    ├── assets/                  # Fonts, images
    ├── css/                     # Stylesheets
    ├── js/
    │   ├── config.js            # Auto-detect API path
    │   ├── services/            # API services
    │   └── controllers/         # UI controllers
    └── pages/                   # HTML pages
```

---

## 🔧 Bước 1: Chuẩn bị Database trên Hostinger

### 1.1. Tạo Database

1. Đăng nhập **Hostinger hPanel**: https://hpanel.hostinger.com
2. Vào **Databases** → **MySQL Databases**
3. Tạo database mới:
   - **Database name:** nhập tên (Hostinger sẽ thêm prefix, ví dụ: `u123456789_haiauenglish`)
   - **Username:** tạo user mới (ví dụ: `u123456789_admin`)
   - **Password:** tạo mật khẩu mạnh

4. **Ghi lại thông tin:**
   ```
   Database Host: localhost
   Database Name: u123456789_haiauenglish
   Username: u123456789_admin
   Password: YourSecurePass123!
   ```

### 1.2. Import Database

1. Vào **phpMyAdmin** từ hPanel
2. Chọn database vừa tạo
3. Click **Import** → Chọn file `backend/database.sql`
4. Click **Import**
5. *(Optional)* Import `backend/sample_data.sql` nếu muốn có dữ liệu mẫu

---

## ⚙️ Bước 2: Cấu hình Backend

Mở file `backend/php/config.php` và **CHỈ SỬA PHẦN HOSTINGER PRODUCTION**:

```php
// ===== HOSTINGER PRODUCTION =====
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'u123456789_admin');      // ← SỬA
define('DB_PASS', getenv('DB_PASS') ?: 'YourSecurePass123!');    // ← SỬA  
define('DB_NAME', getenv('DB_NAME') ?: 'u123456789_haiauenglish'); // ← SỬA
```

### Cấu hình thêm (tùy chọn):

```php
// CORS - Thêm domain của bạn
define('ALLOWED_ORIGINS', [
    'https://yourdomain.com',        // ← Thêm domain
    'https://www.yourdomain.com',    // ← Thêm www
    'http://localhost',
    // ... giữ nguyên các localhost khác
]);

// Email thông báo
define('ADMIN_EMAIL', 'admin@yourdomain.com');  // ← SỬA email
define('ADMIN_NAME', 'Hải Âu English');
```

---

## 📤 Bước 3: Upload Files lên Hostinger

### Cách 1: Qua File Manager (Khuyến nghị)

1. **Nén toàn bộ project** thành file ZIP
2. Đăng nhập hPanel → **File Manager**
3. Mở thư mục `public_html`
4. **Xóa** tất cả files mặc định (index.html, .htaccess cũ...)
5. Click **Upload** → chọn file ZIP
6. Sau khi upload xong, **Extract** file ZIP
7. **Kiểm tra** cấu trúc:
   ```
   public_html/
   ├── .htaccess
   ├── index.php
   ├── backend/
   └── frontend/
   ```

### Cách 2: Qua FTP (FileZilla)

1. Lấy thông tin FTP từ hPanel → **Files** → **FTP Accounts**
2. Mở FileZilla:
   - **Host:** ftp.yourdomain.com
   - **Username:** FTP username từ hPanel
   - **Password:** FTP password
   - **Port:** 21
3. Kết nối và upload toàn bộ vào `public_html/`

---

## 🔒 Bước 4: Bật HTTPS/SSL

1. Vào hPanel → **SSL**
2. Chọn **Free SSL** hoặc cài SSL có sẵn
3. Chờ kích hoạt (5-10 phút)
4. Bật **Force HTTPS**

**Hoặc** bỏ comment trong `.htaccess`:
```apache
# Force HTTPS - BỎ COMMENT 2 DÒNG NÀY
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## ✅ Bước 5: Kiểm tra sau Deploy

### Test các trang:

| Trang | URL | Kết quả mong đợi |
|-------|-----|------------------|
| Trang chủ | `https://yourdomain.com` | Redirect đến index.html |
| Khóa học | `https://yourdomain.com/frontend/pages/courses.html` | Hiển thị danh sách |
| Giảng viên | `https://yourdomain.com/frontend/pages/teachers.html` | Hiển thị danh sách |
| Đăng nhập | `https://yourdomain.com/frontend/pages/login.html` | Form đăng nhập |
| Admin | `https://yourdomain.com/frontend/pages/admin.html` | Dashboard (cần đăng nhập) |

### Test API:

```
https://yourdomain.com/backend/php/courses.php
→ Trả về JSON danh sách khóa học

https://yourdomain.com/backend/php/teachers.php
→ Trả về JSON danh sách giảng viên
```

### Đăng nhập Admin:

- **Email:** admin@haiau.edu.vn
- **Password:** admin123

⚠️ **QUAN TRỌNG:** Đổi mật khẩu admin ngay sau khi deploy!

---

## ❗ Xử lý lỗi thường gặp

### Lỗi 500 Internal Server Error

**Nguyên nhân:** Lỗi PHP hoặc .htaccess

**Cách sửa:**
1. Kiểm tra **Error Logs** trong hPanel
2. Đảm bảo PHP version >= 7.4 (hPanel → PHP Configuration)
3. Kiểm tra quyền file: 755 cho thư mục, 644 cho files
   ```
   chmod 755 public_html
   chmod 755 public_html/backend
   chmod 644 public_html/backend/php/config.php
   ```

### Lỗi Database Connection

**Nguyên nhân:** Sai thông tin database

**Cách sửa:**
1. Kiểm tra lại username/password trong `config.php`
2. Đảm bảo user có quyền trên database
3. Test kết nối qua phpMyAdmin

### Lỗi CORS (API bị chặn)

**Nguyên nhân:** Domain không trong whitelist

**Cách sửa:** Thêm domain vào `ALLOWED_ORIGINS` trong `config.php`:
```php
define('ALLOWED_ORIGINS', [
    'https://yourdomain.com',
    'https://www.yourdomain.com',
    // ...
]);
```

### Lỗi Session/Login không hoạt động

**Nguyên nhân:** Cookie domain hoặc session path

**Cách sửa:**
1. Xóa cookies trình duyệt
2. Kiểm tra session đã start chưa trong PHP
3. Đảm bảo `credentials: 'include'` trong fetch requests

### Lỗi 404 Not Found

**Nguyên nhân:** File không tồn tại hoặc .htaccess lỗi

**Cách sửa:**
1. Kiểm tra file đã upload đúng chưa
2. Kiểm tra `.htaccess` có bị lỗi syntax không
3. Đảm bảo mod_rewrite được bật (Hostinger thường bật sẵn)

### Ảnh không hiển thị

**Nguyên nhân:** Đường dẫn ảnh sai

**Cách sửa:**
1. Đảm bảo thư mục `frontend/assets/images/uploads/` tồn tại
2. Cấp quyền ghi cho thư mục uploads:
   ```
   chmod 775 public_html/frontend/assets/images/uploads
   chmod 775 public_html/frontend/assets/images/uploads/reviews
   chmod 775 public_html/frontend/assets/images/uploads/achievements
   ```

---

## 🔐 Bảo mật

### Checklist bảo mật:

- [x] File `.htaccess` bảo vệ files nhạy cảm (db.php, session_config.php)
- [x] File `.htaccess` chặn directory listing
- [x] File `.htaccess` chặn truy cập file .sql
- [ ] **Đổi mật khẩu admin** sau khi deploy
- [ ] **Bật HTTPS** 
- [ ] **Không commit password** lên Git

### Headers bảo mật (đã có trong .htaccess):

```apache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
```

---

## 📊 Tối ưu hiệu suất

### Đã tối ưu sẵn:

- ✅ GZIP compression cho HTML, CSS, JS
- ✅ Browser caching cho images (1 tháng), CSS/JS (1 tuần)
- ✅ Lazy loading cho images
- ✅ Minified Tailwind CSS

### Kiểm tra tốc độ:

- [PageSpeed Insights](https://pagespeed.web.dev/)
- [GTmetrix](https://gtmetrix.com/)

---

## 📧 Cấu hình Email (Contact Form)

Để gửi email từ contact form hoạt động:

1. Hostinger hỗ trợ `mail()` function mặc định
2. Cập nhật `ADMIN_EMAIL` trong `config.php`
3. Có thể cấu hình SMTP trong tương lai nếu cần

---

## 🔄 Cập nhật sau này

Khi cần update code:

1. Sửa code local (XAMPP)
2. Test đầy đủ trên localhost
3. Upload files đã sửa lên Hostinger (overwrite)
4. Clear browser cache để thấy thay đổi

**Lưu ý:** Không upload lại `config.php` nếu chỉ có thông tin database thay đổi!

---

## 📞 Hỗ trợ

Nếu gặp vấn đề:

1. Kiểm tra **Error Logs** trong hPanel → Advanced → Error Logs
2. Mở **Browser Console** (F12) để xem lỗi JavaScript
3. Kiểm tra **Network tab** để xem API requests có lỗi không
4. Liên hệ support Hostinger nếu lỗi server

---

## ✨ Tính năng đã tối ưu cho Hostinger

| Tính năng | Trạng thái |
|-----------|------------|
| Auto-detect XAMPP/Hostinger | ✅ |
| CORS headers tự động | ✅ |
| Session/Cookie tự động | ✅ |
| Base path cho assets | ✅ |
| GZIP compression | ✅ |
| Browser caching | ✅ |
| Security headers | ✅ |
| Protected sensitive files | ✅ |
| Carousel auto-slide (reviews/achievements) | ✅ |
| Lightbox xem ảnh thành tích | ✅ |
| Responsive design | ✅ |

---

## 📝 Thông tin tài khoản test

### Admin:
- **Email:** admin@haiau.edu.vn
- **Password:** admin123

### User:
- **Email:** nguyenvana@gmail.com
- **Password:** password

---

**🎉 Chúc bạn deploy thành công!**
