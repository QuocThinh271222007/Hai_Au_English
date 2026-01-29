# Hải Âu English Website - HTML/CSS/JS

Website giới thiệu trung tâm dạy học tiếng Anh IELTS được xây dựng bằng HTML, CSS thuần túy và JavaScript.

## Cấu trúc file

```
Hai_Au_English/
├── index.html          # Trang chủ
├── about.html          # Trang giới thiệu (cần tạo)
├── courses.html        # Trang khóa học (cần tạo)
├── teachers.html       # Trang giảng viên (cần tạo)
├── contact.html        # Trang liên hệ (cần tạo)
├── login.html          # Trang đăng nhập
├── signup.html         # Trang đăng ký
├── styles.css          # File CSS chung
├── about.css           # CSS cho trang about (cần tạo)
├── courses.css         # CSS cho trang courses (cần tạo)
├── teachers.css        # CSS cho trang teachers (cần tạo)
├── contact.css         # CSS cho trang contact (cần tạo)
├── main.js             # JavaScript cho chức năng chung
├── index.js            # JavaScript cho trang chủ
├── about.js            # JavaScript cho trang giới thiệu (cần tạo)
├── contact.js          # JavaScript cho trang liên hệ (cần tạo)
├── courses.js          # JavaScript cho trang khóa học (cần tạo)
├── teachers.js         # JavaScript cho trang giảng viên (cần tạo)
├── auth.js             # JavaScript cho authentication
└── README.md           # File hướng dẫn này
```

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
Chỉ cần mở file `index.html` trong trình duyệt web.

### 2. Development
Để phát triển, bạn có thể sử dụng Live Server:

```bash
# Nếu dùng VS Code
# Cài extension Live Server
# Right click vào index.html -> Open with Live Server
```

Hoặc dùng Python HTTP Server:

```bash
cd public
python -m http.server 8000
# Mở http://localhost:8000
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
Trong file `auth.js`, tìm function validation và thêm rules:

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
- [x] Mobile menu
- [x] Login/Signup forms
- [x] Form validation
- [x] Password toggle
- [x] Toast notifications
- [x] Smooth scroll
- [x] Active navigation

### Cần tạo thêm 📝
- [ ] Trang about.html
- [ ] Trang courses.html
- [ ] Trang teachers.html
- [ ] Trang contact.html
- [ ] Backend API integration
- [ ] Real authentication
- [ ] Database connection
- [ ] Email verification
- [ ] Password reset

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

## Contact

Nếu cần hỗ trợ, liên hệ:
- Email: HaiAuEnglish.vn
- Website: www.HaiAuEnglish.vn

## License

© 2026 Hải Âu English. All rights reserved.
