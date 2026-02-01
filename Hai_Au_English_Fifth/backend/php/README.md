# Backend PHP - Hải Âu English API

## 📋 Tổng quan

Backend API được xây dựng bằng PHP thuần với MySQL, chạy trên XAMPP (Apache).

## 🗂️ Cấu trúc files

```
backend/php/
├── db.php          # Kết nối database (mysqli + PDO)
├── auth.php        # API đăng ký, đăng nhập, logout
├── admin.php       # API Admin Dashboard (CRUD tất cả tables)
├── profile.php     # API User Profile
├── courses.php     # API khóa học (public)
├── contact.php     # API form liên hệ
├── users.php       # API quản lý user (legacy)
└── README.md       # File này
```

## 🚀 Cài đặt

### 1. Copy files
```bash
# Copy thư mục vào XAMPP
C:/xampp/htdocs/hai_au_english/backend/php/
```

### 2. Import Database
```bash
# Mở phpMyAdmin: http://localhost/phpmyadmin
# Import theo thứ tự:
1. backend/create_db.sql
2. backend/update_db.sql
```

### 3. Cấu hình kết nối
Sửa file `db.php` nếu cần:
```php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hai_au_english';
```

## 📚 API Reference

### Authentication (`auth.php`)

#### Đăng ký
```http
POST /backend/php/auth.php?action=register
Content-Type: application/json

{
    "fullname": "Nguyễn Văn A",
    "email": "email@example.com",
    "phone": "0901234567",
    "password": "123456"
}
```

#### Đăng nhập
```http
POST /backend/php/auth.php?action=login
Content-Type: application/json

{
    "email": "email@example.com",
    "password": "123456"
}
```

#### Kiểm tra session
```http
GET /backend/php/auth.php?action=check
```

#### Đăng xuất
```http
GET /backend/php/auth.php?action=logout
```

---

### Admin Dashboard (`admin.php`)

> ⚠️ Yêu cầu đăng nhập với role = 'admin'

#### Dashboard thống kê
```http
GET /backend/php/admin.php?action=dashboard
```

Response:
```json
{
    "success": true,
    "data": {
        "stats": {
            "users": 50,
            "courses": 10,
            "enrollments": 100,
            "scores": 200
        }
    }
}
```

#### CRUD Users
```http
# Lấy danh sách
GET ?action=users

# Tạo mới
POST ?action=user-create
{ "fullname": "...", "email": "...", "password": "...", "phone": "..." }

# Cập nhật
POST ?action=user-update
{ "id": 1, "fullname": "...", "email": "...", "phone": "...", "is_active": 1 }

# Xóa (soft delete)
POST ?action=user-delete
{ "id": 1 }
```

#### CRUD Courses
```http
GET ?action=courses
POST ?action=course-create
POST ?action=course-update
POST ?action=course-delete
```

#### CRUD Enrollments
```http
GET ?action=enrollments
POST ?action=enrollment-update
POST ?action=enrollment-delete
```

#### CRUD Scores
```http
GET ?action=scores
POST ?action=score-create
POST ?action=score-update
POST ?action=score-delete
```

#### CRUD Feedback
```http
GET ?action=feedback
POST ?action=feedback-create
POST ?action=feedback-update
POST ?action=feedback-delete
```

#### CRUD Schedules
```http
GET ?action=schedules
POST ?action=schedule-create
POST ?action=schedule-update
POST ?action=schedule-delete
```

#### Trash Management
```http
# Lấy danh sách thùng rác
GET ?action=trash&table=users  # Lọc theo bảng (optional)

# Khôi phục
POST ?action=restore
{ "trash_id": 1 }

# Xóa vĩnh viễn
POST ?action=permanent-delete
{ "trash_id": 1 }

# Dọn sạch thùng rác
POST ?action=empty-trash
{ "table": "users" }  # Lọc theo bảng (optional)
```

---

### User Profile (`profile.php`)

> ⚠️ Yêu cầu đăng nhập

#### Lấy thông tin cá nhân
```http
GET /backend/php/profile.php?action=info
```

#### Cập nhật thông tin
```http
POST /backend/php/profile.php?action=update
{
    "fullname": "Nguyễn Văn B",
    "phone": "0909876543",
    "current_password": "123456",    // Bắt buộc nếu đổi mật khẩu
    "new_password": "654321"         // Optional
}
```

#### Lấy điểm số
```http
GET /backend/php/profile.php?action=scores
```

Response:
```json
{
    "success": true,
    "data": {
        "timeline": [
            {
                "test_date": "2026-01-15",
                "listening": 7.0,
                "reading": 7.5,
                "writing": 6.5,
                "speaking": 7.0,
                "overall": 7.0
            }
        ],
        "averages": {
            "listening": 7.0,
            "reading": 7.5,
            "writing": 6.5,
            "speaking": 7.0
        }
    }
}
```

#### Lấy lịch học
```http
GET /backend/php/profile.php?action=schedule&year=2025-2026&semester=1
```

---

### Courses Public (`courses.php`)

```http
# Lấy tất cả khóa học
GET /backend/php/courses.php

# Lấy 1 khóa học
GET /backend/php/courses.php?id=1
```

---

### Contact Form (`contact.php`)

```http
POST /backend/php/contact.php
{
    "fullname": "Nguyễn Văn A",
    "email": "email@example.com",
    "phone": "0901234567",
    "course": "IELTS Foundation",
    "level": "beginner",
    "message": "Tôi muốn đăng ký khóa học",
    "agreement": true
}
```

---

## 🔐 Authentication Flow

1. User gửi request login với email/password
2. Server kiểm tra credentials
3. Nếu hợp lệ, tạo PHP session và lưu user_id
4. Client gửi requests kèm `credentials: 'include'`
5. Server kiểm tra session cho các API protected

```php
// Kiểm tra đăng nhập
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Kiểm tra admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
```

## 🗑️ Soft Delete (Trash)

Khi xóa dữ liệu, thay vì DELETE trực tiếp:
1. Copy data vào bảng `trash` dạng JSON
2. DELETE từ bảng gốc
3. Có thể khôi phục (restore) hoặc xóa vĩnh viễn

```php
function moveToTrash($table, $id, $deletedBy) {
    global $pdo;
    
    // Lấy data hiện tại
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Lưu vào trash
    $stmt = $pdo->prepare("INSERT INTO trash (original_table, original_id, data, deleted_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$table, $id, json_encode($data), $deletedBy]);
    
    // Xóa từ bảng gốc
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
}
```

## 🐛 Debug

### Xem PHP errors
```
C:/xampp/apache/logs/error.log
```

### Enable error display (development only)
```php
// Thêm vào đầu file PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Test API với cURL
```bash
# GET request
curl http://localhost/hai_au_english/backend/php/courses.php

# POST request
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@haiau.edu.vn","password":"password"}' \
  http://localhost/hai_au_english/backend/php/auth.php?action=login
```

---

**Cập nhật:** 2026-02-01
