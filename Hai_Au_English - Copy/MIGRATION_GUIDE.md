# MVC Migration Guide

## 🎯 Dự án đã được phân vùng theo mô hình MVC

Tất cả file đã được tổ chức lại theo cấu trúc MVC tiêu chuẩn mà không thay đổi nội dung file.

## 📁 Cấu trúc thư mục mới

```
Hai_Au_English/
├── views/                    # Tất cả file HTML
│   ├── index.html           # Trang chủ
│   ├── about.html           # Trang giới thiệu
│   ├── courses.html         # Trang khóa học
│   ├── teachers.html        # Trang giảng viên
│   ├── contact.html         # Trang liên hệ
│   ├── login.html           # Trang đăng nhập
│   ├── signup.html          # Trang đăng ký
│   └── test.html            # Trang test
│
├── css/                      # Tất cả file CSS
│   ├── styles.css           # CSS chung (import Tailwind)
│   └── pages/               # CSS riêng cho từng trang
│       ├── about.css
│       ├── contact.css
│       ├── courses.css
│       ├── index.css
│       ├── teachers.css
│       └── test.css
│
├── controllers/             # Tất cả file JavaScript logic
│   ├── main.js              # Controller chung
│   ├── auth.js              # Controller authentication
│   ├── contact.js           # Controller liên hệ
│   ├── courses.js           # Controller khóa học
│   ├── index.js             # Controller trang chủ
│   └── test.js              # Controller test
│
├── js/                      # Thư mục cho utilities & helpers
│   └── (sẽ thêm sau)
│
├── models/                  # Thư mục cho data files
│   └── (sẽ thêm sau)
│
├── public/                  # Thư mục public (nếu cần)
│   ├── index.html
│   └── assets/
│       ├── images/
│       └── fonts/
│
├── README.md
├── MIGRATION_GUIDE.md        # File này
└── (File cũ ở root - có thể xóa sau)
```

## 🔗 Cập nhật đường link

### 1. **CSS Links**

**Cũ:**
```html
<link rel="stylesheet" href="../styles.css">
<link rel="stylesheet" href="about.css">
```

**Mới (trong views/):**
```html
<link rel="stylesheet" href="../css/styles.css">
<link rel="stylesheet" href="../css/pages/about.css">
```

### 2. **JavaScript Links**

**Cũ:**
```html
<script src="main.js"></script>
<script src="../about/auth.js"></script>
```

**Mới (trong views/):**
```html
<script src="../controllers/main.js"></script>
<script src="../controllers/auth.js"></script>
```

### 3. **Navigation Links (trong views/)**

**Cũ:**
```html
<a href="../about/about.html">Giới thiệu</a>
<a href="../courses/courses.html">Khóa học</a>
```

**Mới:**
```html
<a href="about.html">Giới thiệu</a>
<a href="courses.html">Khóa học</a>
```

## ✅ Các file đã được di chuyển

### HTML Files (views/)
- ✅ about.html
- ✅ contact.html
- ✅ courses.html
- ✅ index.html
- ✅ login.html
- ✅ signup.html
- ✅ teachers.html
- ✅ test.html

### CSS Files
- ✅ styles.css → css/styles.css
- ✅ about.css → css/pages/about.css
- ✅ contact.css → css/pages/contact.css
- ✅ courses.css → css/pages/courses.css
- ✅ index.css → css/pages/index.css
- ✅ teachers.css → css/pages/teachers.css
- ✅ test.css → css/pages/test.css

### JavaScript Files (controllers/)
- ✅ main.js → controllers/main.js
- ✅ auth.js → controllers/auth.js
- ✅ contact.js → controllers/contact.js
- ✅ courses.js → controllers/courses.js
- ✅ index.js → controllers/index.js
- ✅ test.js → controllers/test.js

## 🚀 Hướng dẫn sử dụng

### 1. Phát triển (Development)

Mở file HTML từ thư mục `views/`:

```bash
# Sử dụng Live Server
# Right-click vào views/index.html → Open with Live Server
```

Hoặc sử dụng HTTP Server:

```bash
cd Hai_Au_English
python -m http.server 8000
# Mở http://localhost:8000/views/
```

### 2. Cải thiện cấu trúc tiếp theo

Các thư mục có thể được sử dụng như sau:

- **models/**: Thêm JSON files cho dữ liệu (courses.json, teachers.json, users.json)
- **js/**: Thêm utility functions, validation functions, API calls
- **public/**: Để lưu trữ static assets, landing page chính

### 3. Xóa file cũ (Optional)

Sau khi xác nhận tất cả link đều hoạt động, bạn có thể xóa các file cũ ở root:

```bash
# File có thể xóa
- about.html (đã có trong views/)
- about.css (đã có trong css/pages/)
- contact.html (đã có trong views/)
- ... (tất cả file đã được di chuyển)
```

## 📝 Ghi chú quan trọng

1. **Tất cả nội dung file đều không thay đổi** - Chỉ di chuyển vị trí và cập nhật đường link
2. **Logo và assets vẫn ở root** - `../logo.png` vẫn hoạt động từ views/
3. **Tailwind CSS CDN** - Vẫn sử dụng CDN link từ head
4. **Relative paths** - Tất cả paths đều dùng relative paths để dễ dàng deployment

## 🔧 Kiểm tra links

Sau migration, hãy kiểm tra:

- [ ] Tất cả navigation links hoạt động (trang chủ, giới thiệu, khóa học, etc.)
- [ ] CSS được load đúng (kiểm tra giao diện có bị lỗi không)
- [ ] JavaScript hoạt động (kiểm tra menu mobile, form submission)
- [ ] Logo và images hiển thị đúng

## 💡 Lợi ích của cấu trúc MVC

1. **Dễ bảo trì** - Các file được tổ chức logic theo từng phần
2. **Dễ mở rộng** - Thêm view, controller mới dễ dàng
3. **Dễ hợp tác** - Các developer khác nhau có thể làm việc trên các phần khác nhau
4. **Chuẩn mực** - Tuân theo chuẩn MVC được công nhận rộng rãi
5. **Sẵn sàng cho backend** - Cấu trúc này dễ chuyển sang backend framework sau

---

**Ngày migration:** 2026-01-28  
**Status:** ✅ Hoàn thành
