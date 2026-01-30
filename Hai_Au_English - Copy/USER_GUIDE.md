# Hướng dẫn sử dụng Website Hải Âu English

## 👤 Dành cho User (Người dùng thông thường)

### 1. Truy cập Website
- Mở trình duyệt (Chrome, Firefox, Edge, Safari)
- Truy cập: `http://localhost/hai_au_frontend/index.html`

### 2. Các trang có thể truy cập

| Trang | Đường dẫn | Mô tả |
|-------|-----------|-------|
| Trang chủ | `/index.html` | Giới thiệu tổng quan về trung tâm |
| Giới thiệu | `/pages/about.html` | Thông tin chi tiết về Hải Âu English |
| Khóa học | `/pages/courses.html` | Danh sách các khóa học IELTS |
| Giảng viên | `/pages/teachers.html` | Đội ngũ giảng viên |
| Liên hệ | `/pages/contact.html` | Form đăng ký tư vấn |
| Đăng nhập | `/pages/login.html` | Đăng nhập tài khoản |
| Đăng ký | `/pages/signup.html` | Tạo tài khoản mới |

### 3. Đăng ký tài khoản
1. Vào trang **Đăng ký** (`/pages/signup.html`)
2. Điền đầy đủ thông tin:
   - Họ và tên
   - Email (phải hợp lệ)
   - Mật khẩu (tối thiểu 8 ký tự)
3. Nhấn **Đăng ký**
4. Nếu thành công, bạn sẽ nhận được thông báo

### 4. Đăng nhập
1. Vào trang **Đăng nhập** (`/pages/login.html`)
2. Nhập email và mật khẩu đã đăng ký
3. Nhấn **Đăng nhập**

### 5. Gửi form liên hệ/đăng ký tư vấn
1. Vào trang **Liên hệ** (`/pages/contact.html`)
2. Điền thông tin:
   - Họ và tên (*)
   - Email (*)
   - Số điện thoại (*)
   - Khóa học quan tâm (*)
   - Trình độ hiện tại
   - Lời nhắn
3. Đồng ý với chính sách bảo mật
4. Nhấn **Gửi thông tin**
5. Trung tâm sẽ liên hệ trong vòng 24 giờ

---

## 🔐 Dành cho Admin (Quản trị viên)

### 1. Quản lý dữ liệu qua phpMyAdmin
- Truy cập: `http://localhost/phpmyadmin`
- Chọn database: `hai_au_english`

### 2. Quản lý Contacts (Liên hệ)
```sql
-- Xem tất cả liên hệ
SELECT * FROM contacts ORDER BY created_at DESC;

-- Xem liên hệ mới nhất
SELECT * FROM contacts WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Xóa liên hệ cũ
DELETE FROM contacts WHERE id = [id_cần_xóa];
```

### 3. Quản lý Users (Người dùng)
```sql
-- Xem tất cả users
SELECT id, fullname, email, created_at FROM users;

-- Xóa user
DELETE FROM users WHERE id = [id_cần_xóa];
```

### 4. Quản lý Courses (Khóa học)
```sql
-- Xem tất cả khóa học
SELECT * FROM courses;

-- Thêm khóa học mới
INSERT INTO courses (name, description) VALUES ('Tên khóa học', 'Mô tả');

-- Xóa khóa học
DELETE FROM courses WHERE id = [id_cần_xóa];
```

### 5. API Endpoints cho Admin
| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/hai_au_backend/users.php` | Lấy danh sách users |
| DELETE | `/hai_au_backend/users.php?id=X` | Xóa user |
| GET | `/hai_au_backend/courses.php` | Lấy danh sách khóa học |
| POST | `/hai_au_backend/courses.php` | Thêm khóa học |
| DELETE | `/hai_au_backend/courses.php?id=X` | Xóa khóa học |

---

## 💻 Dành cho Developer (Lập trình viên)

### 1. Cài đặt môi trường phát triển

#### Yêu cầu
- XAMPP v8.0+ (Apache + MySQL + PHP)
- Text Editor (VS Code khuyến nghị)
- Browser (Chrome DevTools)

#### Cài đặt
```bash
# 1. Clone/copy dự án
# 2. Copy backend/php → C:/xampp/htdocs/hai_au_backend
# 3. Copy frontend → C:/xampp/htdocs/hai_au_frontend
# 4. Import database
#    - Mở phpMyAdmin
#    - Import: backend/create_db.sql
#    - Import: backend/update_db.sql
```

### 2. Cấu trúc dự án
```
Hai_Au_English/
├── backend/
│   ├── php/                # API PHP
│   │   ├── db.php          # Kết nối MySQL
│   │   ├── auth.php        # Đăng ký/đăng nhập
│   │   ├── users.php       # CRUD users
│   │   ├── courses.php     # CRUD courses
│   │   └── contact.php     # Nhận form liên hệ
│   ├── create_db.sql       # Tạo DB + bảng contacts
│   └── update_db.sql       # Tạo bảng users, courses
│
├── frontend/
│   ├── index.html
│   ├── pages/              # Các trang HTML
│   ├── css/                # Stylesheets
│   ├── js/
│   │   ├── services/       # Gọi API (authService, courseService, contactService)
│   │   ├── controllers/    # Xử lý form, UI logic
│   │   ├── animations/     # Animation UI
│   │   └── ui/             # Toast, helpers
│   └── assets/             # Ảnh, fonts
```

### 3. Thêm tính năng mới

#### Tạo API endpoint mới
```php
<?php
// backend/php/your_api.php
header('Content-Type: application/json; charset=utf-8');
$mysqli = require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Xử lý GET
    $result = $mysqli->query('SELECT * FROM your_table');
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode(['data' => $data]);
    exit;
}

if ($method === 'POST') {
    // Xử lý POST
    $input = json_decode(file_get_contents('php://input'), true);
    // Validate và insert
    echo json_encode(['success' => true]);
    exit;
}
```

#### Tạo service JS tương ứng
```javascript
// frontend/js/services/yourService.js
const API_BASE = 'backend/php/your_api.php';

export const yourService = {
    async getAll() {
        const res = await fetch(API_BASE);
        return res.json();
    },
    async create(data) {
        const res = await fetch(API_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    }
};
export default yourService;
```

### 4. Debug & Troubleshooting

#### Xem lỗi PHP
```
C:/xampp/apache/logs/error.log
```

#### Xem lỗi JavaScript
- Mở DevTools (F12) → Console

#### Test API trực tiếp
```bash
# Test GET
curl http://localhost/hai_au_backend/courses.php

# Test POST
curl -X POST -H "Content-Type: application/json" \
  -d '{"name":"Test","description":"Test course"}' \
  http://localhost/hai_au_backend/courses.php
```

### 5. Database Schema

#### Bảng contacts
| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Primary key |
| fullname | VARCHAR(255) | Họ tên |
| email | VARCHAR(255) | Email |
| phone | VARCHAR(50) | SĐT |
| course | VARCHAR(100) | Khóa học quan tâm |
| level | VARCHAR(50) | Trình độ |
| message | TEXT | Lời nhắn |
| agreement | TINYINT | Đồng ý điều khoản |
| created_at | DATETIME | Thời gian tạo |

#### Bảng users
| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Primary key |
| fullname | VARCHAR(255) | Họ tên |
| email | VARCHAR(255) | Email (unique) |
| password | VARCHAR(255) | Mật khẩu (hashed) |
| created_at | DATETIME | Thời gian tạo |

#### Bảng courses
| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | INT | Primary key |
| name | VARCHAR(255) | Tên khóa học |
| description | TEXT | Mô tả |

---

**Cập nhật:** 2026-01-30  
**Phiên bản:** PHP/MySQL
