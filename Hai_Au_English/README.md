# 🎓 Hải Âu English - Website Trung tâm IELTS

Website quản lý trung tâm dạy tiếng Anh IELTS với đầy đủ tính năng cho học viên và admin.

## 🚀 Cài đặt nhanh (XAMPP)

1. Copy thư mục vào `C:\xampp\htdocs\Hai_Au_English\`
2. Import `backend/database.sql` vào phpMyAdmin
3. Truy cập: http://localhost/Hai_Au_English

## 📁 Cấu trúc dự án

```
Hai_Au_English/
├── index.php                 # Redirect về trang chủ
├── .htaccess                 # Apache config
├── backend/
│   ├── database.sql          # Database + dữ liệu mẫu
│   └── php/                  # API endpoints
│       ├── config.php        # ⭐ Cấu hình chính
│       ├── auth.php          # Đăng nhập/Đăng ký
│       ├── courses.php       # Khóa học
│       ├── teachers.php      # Giảng viên
│       ├── reviews.php       # Đánh giá
│       ├── achievements.php  # Thành tích
│       ├── admin.php         # Admin API
│       └── profile.php       # Profile học viên
└── frontend/
    ├── pages/                # HTML pages
    ├── css/                  # Stylesheets
    ├── js/                   # JavaScript
    └── assets/               # Images, fonts
```

## 🔑 Tài khoản test

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@haiau.edu.vn | admin123 |
| User | nguyenvana@gmail.com | password |

## ✨ Tính năng

- ✅ Trang chủ với carousel thành tích & đánh giá
- ✅ Danh sách khóa học & giảng viên
- ✅ Đăng ký/Đăng nhập với session
- ✅ Profile học viên với điểm số & tiến độ
- ✅ Admin dashboard quản lý tất cả
- ✅ Responsive design (mobile-friendly)
- ✅ Auto-detect XAMPP/Hostinger

## 🌐 Deploy lên Hostinger

Xem hướng dẫn chi tiết tại: [DEPLOY_HOSTINGER.md](DEPLOY_HOSTINGER.md)

## 🛠️ Tech Stack

- **Frontend:** HTML5, Tailwind CSS, JavaScript ES6
- **Backend:** PHP 7.4+, MySQL
- **Server:** Apache (XAMPP / Hostinger)

---

**© 2026 Hải Âu English**
Copy-Item -Path "c:\Users\Thinh\Downloads\Hai_Au_English (5) (1) - Copy\Hai_Au_English\*" -Destination "C:\xampp\htdocs\hai_au_english\" -Recurse -Force