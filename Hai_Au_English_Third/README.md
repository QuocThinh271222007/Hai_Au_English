# Hải Âu English - Full Stack Web Application

Website giới thiệu và quản lý trung tâm dạy học tiếng Anh IELTS được xây dựng với mô hình **Backend (PHP/MySQL) + Frontend (HTML/CSS/JS)**.

> **Lưu ý:** Dự án đã chuyển sang sử dụng **PHP + MySQL** cho backend thay vì Node.js. Các file Node.js cũ vẫn còn trong `backend/src/` nhưng không còn sử dụng.

## 🏗️ Cấu trúc dự án

```
Hai_Au_English/
│
├── backend/
│   ├── php/                    # 🆕 API Backend PHP (đang sử dụng)
│   │   ├── db.php              # Kết nối MySQL
│   │   ├── auth.php            # Đăng ký, đăng nhập
│   │   ├── users.php           # Quản lý user (admin)
│   │   ├── courses.php         # Quản lý khóa học
│   │   ├── contact.php         # Xử lý form liên hệ
│   │   └── README.md           # Hướng dẫn backend PHP
│   │
│   ├── src/                    # ⚠️ Backend Node.js cũ (không còn sử dụng)
│   │   ├── controllers/
│   │   ├── routes/
│   │   ├── config/
│   │   └── server.js
│   │
│   ├── create_db.sql           # SQL tạo database và bảng contacts
│   ├── update_db.sql           # SQL tạo bảng users, courses
│   ├── package.json            # Dependencies Node.js (không còn sử dụng)
│   └── .env.example            # Environment variables (không còn sử dụng)
│
│
├── frontend/                   # Client-side (HTML/CSS/JS)
│   ├── index.html              # Trang chủ
│   ├── pages/                  # Các trang HTML
│   │   ├── about.html          # Giới thiệu
│   │   ├── courses.html        # Khóa học
│   │   ├── teachers.html       # Giảng viên
│   │   ├── contact.html        # Liên hệ
│   │   ├── login.html          # Đăng nhập
│   │   └── signup.html         # Đăng ký
│   │
│   ├── css/
│   │   ├── styles.css          # CSS chung
│   │   └── pages/              # CSS riêng từng trang
│   │
│   ├── js/
│   │   ├── services/           # Gọi API PHP
│   │   │   ├── authService.js
│   │   │   ├── courseService.js
│   │   │   └── contactService.js
│   │   ├── controllers/        # Xử lý form, UI
│   │   │   ├── auth.js
│   │   │   ├── contact.js
│   │   │   ├── courses.js
│   │   │   └── main.js
│   │   ├── animations/         # Animation UI
│   │   │   └── uiAnimations.js
│   │   └── ui/
│   │       └── toast.js
│   │
│   └── assets/                 # Ảnh, fonts
│
├── README.md
└── MIGRATION_GUIDE.md
```

## 🔄 Kiến trúc Backend-Frontend

### Backend (PHP/MySQL) - Đang sử dụng
- **Server**: Apache (XAMPP)
- **API Base**: `http://localhost/hai_au_backend/`
- **Database**: MySQL
- **Authentication**: Session-based (password_hash/password_verify)

### Frontend (Vanilla HTML/CSS/JS)
- **Server**: Apache hoặc Live Server
- **API Client**: Fetch API
- **Storage**: localStorage (sessions)

## 📚 API Endpoints (PHP)

### Authentication (`auth.php`)
```
POST   backend/php/auth.php?action=register   - Đăng ký tài khoản mới
POST   backend/php/auth.php?action=login      - Đăng nhập
```

### Courses (`courses.php`)
```
GET    backend/php/courses.php                - Lấy danh sách khóa học
POST   backend/php/courses.php                - Thêm khóa học
DELETE backend/php/courses.php?id=...         - Xóa khóa học
```

### Contacts (`contact.php`)
```
POST   backend/php/contact.php                - Gửi form liên hệ
```

### Users (`users.php`)
```
GET    backend/php/users.php                  - Lấy danh sách user (admin)
DELETE backend/php/users.php?id=...           - Xóa user (admin)
```

## 🚀 Cài đặt và Chạy (XAMPP)

### 1️⃣ Cài đặt XAMPP
```
1. Tải XAMPP: https://www.apachefriends.org/index.html
2. Cài đặt và mở XAMPP Control Panel
3. Bật Apache và MySQL
```

### 2️⃣ Import Database
```
1. Mở http://localhost/phpmyadmin
2. Import file: backend/create_db.sql (tạo DB và bảng contacts)
3. Import file: backend/update_db.sql (tạo bảng users, courses)
```

### 3️⃣ Copy mã nguồn vào XAMPP
```
1. Copy thư mục backend/php vào C:/xampp/htdocs/hai_au_backend
2. Copy thư mục frontend vào C:/xampp/htdocs/hai_au_frontend
```

### 4️⃣ Cấu hình kết nối MySQL
Sửa file `hai_au_backend/db.php` nếu cần:
```php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';      // Mặc định XAMPP
$DB_PASS = '';          // Mặc định XAMPP (rỗng)
$DB_NAME = 'hai_au_english';
```

### 5️⃣ Truy cập website
```
http://localhost/hai_au_frontend/index.html
```

### 6️⃣ Cấu hình API URL trong frontend
Sửa các file trong `frontend/js/services/` nếu đường dẫn PHP khác:
```javascript
const API_BASE = 'backend/php/auth.php';  // hoặc đường dẫn tuyệt đối
```

## 💾 Yêu cầu Hệ thống

- **XAMPP**: v8.0+ (Apache + MySQL + PHP)
- **PHP**: v7.4+ (có sẵn trong XAMPP)
- **MySQL**: v5.7+ (có sẵn trong XAMPP)
- **Browser**: Chrome, Firefox, Safari, Edge (mới nhất)

## 📝 Hướng dẫn Phát triển

### Thêm tính năng mới trong Backend PHP

1. **Tạo file PHP mới** (`backend/php/`)
     ```php
     <?php
     header('Content-Type: application/json; charset=utf-8');
     $mysqli = require __DIR__ . '/db.php';
     
     // Xử lý request
     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         $data = json_decode(file_get_contents('php://input'), true);
         // Business logic
         echo json_encode(['success' => true]);
     }
     ```

2. **Tạo service JS tương ứng** (`frontend/js/services/`)
     ```javascript
     const API_BASE = 'backend/php/your_api.php';
     export const yourService = {
         async doSomething(data) {
             const res = await fetch(API_BASE, {
                 method: 'POST',
                 headers: { 'Content-Type': 'application/json' },
                 body: JSON.stringify(data)
             });
             return res.json();
         }
     };
     ```

### Gọi API từ Frontend

**Sử dụng Authentication Service:**
```javascript
import authService from './js/services/authService.js';

// Đăng nhập
try {
    const response = await authService.login('email@example.com', 'password');
    console.log('Logged in:', response);
} catch (error) {
    console.error('Login failed:', error.message);
}
```

**Sử dụng Course Service:**
```javascript
import courseService from './js/services/courseService.js';

// Lấy danh sách khóa học
const courses = await courseService.getAllCourses();
```

**Sử dụng Contact Service:**
```javascript
import contactService from './js/services/contactService.js';

// Gửi form liên hệ
const result = await contactService.submitContact({
    fullName: 'John Doe',
    email: 'john@example.com',
    phone: '0123456789',
    course: 'IELTS Starter',
    message: 'I want to enroll'
});
```

## 🔐 Authentication Flow

```
1. User fills signup/login form
2. Frontend calls authService.login() or authService.register()
3. Backend validates credentials
4. Backend generates JWT token
5. Token stored in localStorage
6. All API calls include token in header: "Authorization: Bearer token"
7. Backend validates token for protected routes
```

## 🗄️ Database Models (TODO)

### User Model
```javascript
{
    _id: ObjectId,
    fullName: String,
    email: String (unique),
    password: String (hashed),
    phone: String,
    address: String,
    enrolledCourses: [CourseId],
    role: String ('user' | 'admin'),
    createdAt: Date,
    updatedAt: Date
}
```

### Course Model
```javascript
{
    _id: ObjectId,
    name: String,
    level: String,
    duration: String,
    price: Number,
    description: String,
    image: String,
    createdAt: Date,
    updatedAt: Date
}
```

### Contact Model
```javascript
{
    _id: ObjectId,
    fullName: String,
    email: String,
    phone: String,
    course: String,
    message: String,
    status: String ('pending' | 'contacted' | 'resolved'),
    createdAt: Date,
    updatedAt: Date
}
```

## 🛠️ Công cụ và Công nghệ

**Backend:**
- PHP 7.4+ - Server-side scripting
- MySQL - Database
- MySQLi - Database driver
- password_hash/password_verify - Password hashing
- Apache (XAMPP) - Web server

**Frontend:**
- HTML5 - Markup
- CSS3 + Tailwind - Styling
- Vanilla JavaScript (ES6+) - Interaction
- Fetch API - HTTP requests
- localStorage - Client storage

## 📋 Danh sách Tính năng

### ✅ Hoàn thành
- [x] Backend API structure
- [x] Frontend API Client
- [x] Authentication routes (backend)
- [x] Course management routes
- [x] Contact form routes
- [x] User profile routes
- [x] Database config
- [x] Frontend service layer
- [x] Responsive design

### 📝 Cần hoàn thành
- [x] MySQL Database (đã có)
- [x] Password hashing (password_hash)
- [ ] Session-based authentication
- [ ] Admin middleware
- [ ] Input validation middleware
- [ ] Error handling middleware
- [ ] Email notifications
- [ ] Admin dashboard
- [ ] Tests

## 🐛 Troubleshooting

### Backend PHP không chạy
```
1. Kiểm tra Apache và MySQL đã bật trong XAMPP Control Panel
2. Kiểm tra file db.php có đúng thông tin kết nối MySQL không
3. Kiểm tra database đã import chưa (vào phpMyAdmin kiểm tra)
4. Kiểm tra đường dẫn file PHP có đúng không (404 error)
```

### Frontend không kết nối được backend
```
1. Kiểm tra đường dẫn API trong frontend/js/services/*.js
2. Mở Console (F12) để xem lỗi chi tiết
3. Kiểm tra CORS - nếu lỗi, đảm bảo truy cập từ localhost
4. Kiểm tra file PHP có lỗi syntax không (xem Apache error log)
```

### Lỗi kết nối database
```php
// Kiểm tra thông tin trong backend/php/db.php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';  // Mặc định XAMPP là rỗng
$DB_NAME = 'hai_au_english';
```

## 📖 Tài liệu Thêm

- [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) - Hướng dẫn chi tiết migration
- [backend/php/README.md](backend/php/README.md) - Hướng dẫn backend PHP
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [XAMPP](https://www.apachefriends.org/)
- [Tailwind CSS](https://tailwindcss.com)

## 👥 Contributors

- Team Hải Âu English

## 📄 License

© 2026 Hải Âu English. All rights reserved.

---

## Tính năng

### Trang chủ (index.html)
- Hero section với call-to-action
- Thống kê về trung tâm
- Tính năng nổi bật
- Responsive design

### Trang đăng nhập (login.html)
- Form đăng nhập với validation
- Toggle hiển thị mật khẩu
- Remember me checkbox
- Social login (Google, Facebook)
- Link đến trang đăng ký

### Trang đăng ký (signup.html)
- Form đăng ký đầy đủ
- Validation real-time
- Password strength checker
- Confirm password
- Terms & conditions checkbox
- Social signup

### JavaScript Features
- Mobile menu toggle
- Smooth scroll
- Scroll to top button
- Form validation
- Toast notifications
- Active navigation highlighting
- Local storage for user session

## Cách sử dụng

### 1. Mở website
Mở file `views/index.html` trong trình duyệt web.

### 2. Development
Để phát triển, bạn có thể sử dụng Live Server:

```bash
# Nếu dùng VS Code
# Cài extension Live Server
# Right click vào views/index.html -> Open with Live Server
```

Hoặc dùng Python HTTP Server:

```bash
# Từ thư mục gốc
python -m http.server 8000
# Mở http://localhost:8000/views/
```

### 3. Chỉnh sửa

#### Thay đổi màu sắc
Tất cả màu sắc đều sử dụng Tailwind CSS. Các màu chủ đạo:
- Primary: `blue-600` (#2563eb)
- Success: `green-500` (#10b981)
- Error: `red-500` (#ef4444)

Để thay đổi, tìm và replace class `blue-600` thành màu khác (vd: `purple-600`, `indigo-600`)

#### Thay đổi nội dung
- Tìm text trong file HTML và chỉnh sửa trực tiếp
- Images: Thay URL trong thuộc tính `src`
- Links: Cập nhật thuộc tính `href`

#### Thêm validation cho form
Trong file `controllers/auth.js`, tìm function validation và thêm rules:

```javascript
function validateField(value) {
    // Thêm logic validation của bạn
    return true/false;
}
```

#### Thêm toast notification
```javascript
showToast('Thông báo của bạn', 'success'); // hoặc 'error', 'info'
```

#### Thay đổi CSS cho trang
Mỗi trang có file CSS riêng trong `css/pages/`:
- Trang chủ: `css/pages/index.css`
- Trang giới thiệu: `css/pages/about.css`
- Trang khóa học: `css/pages/courses.css`
- v.v...

## Tùy chỉnh Tailwind CSS

Website sử dụng Tailwind CSS qua CDN. Để tùy chỉnh:

### Option 1: Inline classes
Chỉnh sửa trực tiếp các class trong HTML:

```html
<!-- Thay đổi màu button -->
<button class="bg-purple-600 hover:bg-purple-700">Button</button>
```

### Option 2: Custom CSS
Thêm styles vào file `styles.css`:

```css
.custom-button {
    background: linear-gradient(to right, #667eea, #764ba2);
    /* custom styles */
}
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Features Roadmap

### Đã hoàn thành ✅
- [x] Responsive design
- [x] Mobile menu toggle
- [x] Login/Signup forms với validation
- [x] Real-time form validation
- [x] Password visibility toggle
- [x] Toast notifications
- [x] Smooth scroll navigation
- [x] Active navigation highlighting
- [x] Trang about.html hoàn chỉnh
- [x] Trang courses.html với danh sách khóa học
- [x] Trang teachers.html với danh sách giáo viên
- [x] Trang contact.html với form liên hệ
- [x] Local storage cho user session
- [x] Social login buttons
- [x] Password strength checker

### Cần tạo thêm 📝

- [x] Backend API integration (PHP)
- [x] Real authentication system (PHP)
- [x] Database connection (MySQL)
- [ ] Email verification
- [ ] Password reset functionality
- [ ] Admin dashboard
- [ ] Payment integration


## Tips

### 1. Testing Forms
- Email test: `test@example.com`
- Password test: Tối thiểu 8 ký tự, có chữ hoa, chữ thường, số

### 2. Debug
Mở Console trong Browser (F12) để xem logs và errors

### 3. Performance
- Optimize images (sử dụng WebP format)
- Minify CSS/JS khi production
- Sử dụng CDN cho assets

### 4. SEO
Thêm meta tags vào `<head>`:

```html
<meta name="description" content="Mô tả trang web">
<meta name="keywords" content="IELTS, học tiếng Anh">
<meta property="og:title" content="Hải Âu English">
<meta property="og:image" content="url-to-image">
```

## Deployment

### Hosting tĩnh (Static Hosting)
Upload tất cả files lên:
- Netlify
- Vercel
- GitHub Pages
- Firebase Hosting

### Cấu hình
Không cần cấu hình đặc biệt, chỉ cần upload files.

## Troubleshooting

### Lỗi: Tailwind CSS không load
- Kiểm tra kết nối internet
- CDN link có thể bị chặn bởi adblocker

### Lỗi: JavaScript không hoạt động
- Mở Console (F12) để xem error
- Kiểm tra file paths
- Đảm bảo scripts được load sau DOM

### Form không submit
- Kiểm tra validation rules
- Xem Console để debug
- Đảm bảo tất cả required fields được điền

---

## Cấu trúc MVC (Model-View-Controller)

Dự án sử dụng mô hình MVC để tổ chức code:

- **Models** (`models/`): Dữ liệu (JSON files)
- **Views** (`views/`): Giao diện người dùng (HTML files)
- **Controllers** (`controllers/`): Logic xử lý (JavaScript files)

Xem [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) để biết thêm chi tiết về cấu trúc MVC.
