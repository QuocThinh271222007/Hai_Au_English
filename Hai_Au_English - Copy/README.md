# Hải Âu English - Full Stack Web Application

Website giới thiệu và quản lý trung tâm dạy học tiếng Anh IELTS được xây dựng với mô hình **Backend + Frontend** thực tế.

## 🏗️ Cấu trúc dự án (MVC + API)

```
Hai_Au_English/
│
├── backend/                    # API Backend (Node.js + Express)
│   ├── src/
│   │   ├── controllers/        # Business logic handlers
│   │   │   ├── authController.js       # Xử lý đăng nhập, đăng ký
│   │   │   ├── courseController.js     # Xử lý khóa học
│   │   │   ├── contactController.js    # Xử lý form liên hệ
│   │   │   └── userController.js       # Xử lý thông tin người dùng
│   │   │
│   │   ├── models/             # Database schemas (MongoDB)
│   │   │   ├── User.js
│   │   │   ├── Course.js
│   │   │   └── Contact.js
│   │   │
│   │   ├── routes/             # API endpoints
│   │   │   ├── auth.js         # POST /api/auth/login, /register
│   │   │   ├── courses.js       # GET /api/courses, POST (admin)
│   │   │   ├── contacts.js      # POST /api/contacts, GET (admin)
│   │   │   └── users.js         # GET /api/users/profile, PUT
│   │   │
│   │   ├── middleware/         # Authentication, error handling
│   │   │   └── auth.js
│   │   │
│   │   ├── config/             # Database configuration
│   │   │   └── database.js
│   │   │
│   │   └── server.js           # Entry point
│   │
│   ├── package.json            # Dependencies
│   ├── .env.example            # Environment variables template
│   └── .env                    # Environment variables (local)
│
├── frontend/                   # Client-side (HTML/CSS/JS)
├── views/                  # Thư mục chứa file HTML (Views)
│   ├── index.html          # Trang chủ
│   ├── about.html          # Trang giới thiệu    
│   ├── courses.html        # Trang khóa học
│   ├── teachers.html       # Trang giảng viên
│   ├── contact.html        # Trang liên hệ        
│   ├── login.html          # Trang đăng nhập
│   ├── signup.html         # Trang đăng ký    
│   └── test.html           # Trang test
│
├── css/                    # Thư mục CSS
│   ├── styles.css          # CSS chung (import Tailwind)
│   └── pages/              # CSS riêng cho từng trang
│       ├── about.css       # Custom CSS trang giới thiệu
│       ├── contact.css     # Custom CSS trang liên hệ
│       ├── courses.css     # Custom CSS trang khóa học
│       ├── index.css       # Custom CSS trang chủ
│       ├── teachers.css    # Custom CSS trang giảng viên
│       └── test.css        # Custom CSS trang test
│
├── controllers/            # Thư mục chứa logic xử lý (Controllers)
│   ├── auth.js             # Frontend auth handling
│   ├── contact.js          # Frontend contact handling
│   ├── courses.js          # Frontend courses handling
│   └── main.js             # Frontend global behavior
│
├── js/                     # Thư mục chứa utilities & helpers
│   ├── utils.js            # Hàm tiện ích
│   ├── validation.js       # Validation functions
│   ├── api.js              # API Client (gọi backend)
│   ├── services/           # API Services
│   │   ├── authService.js
│   │   ├── courseService.js
│   │   └── contactService.js
│   ├── controllers/        # Frontend logic controllers (form handling, UI logic)
│   ├── animations/         # Animation & UI behaviors (scroll, lazy load, anchors)
│   └── ui/                 # Shared UI helpers (toasts, modals)
│
├── assets/                 # Static files
│   ├── images/
│   └── fonts/
│
├── index.html              # Landing page
│
├── README.md               # File hướng dẫn
├── MIGRATION_GUIDE.md      # Hướng dẫn migration
└── package.json            # Root dependencies (optional)
```

## 🔄 Kiến trúc Backend-Frontend

### Backend (Node.js/Express)
- **Port**: 5000
- **API Base**: `http://localhost:5000/api`
- **Database**: MongoDB
- **Authentication**: JWT (JSON Web Tokens)

### Frontend (Vanilla HTML/CSS/JS)
- **Port**: 3000 (khi dùng Live Server)
- **API Client**: Fetch API
- **Storage**: localStorage (sessions, tokens)

## 📚 API Endpoints

### Authentication
```
POST   /api/auth/register        - Đăng ký tài khoản mới
POST   /api/auth/login           - Đăng nhập
POST   /api/auth/logout          - Đăng xuất
POST   /api/auth/refresh-token   - Làm mới token
```

### Courses
```
GET    /api/courses              - Lấy danh sách khóa học
GET    /api/courses/:id          - Lấy chi tiết khóa học
POST   /api/courses              - Tạo khóa học (admin)
PUT    /api/courses/:id          - Cập nhật khóa học (admin)
DELETE /api/courses/:id          - Xóa khóa học (admin)
```

### Contacts
```
POST   /api/contacts             - Gửi form liên hệ
GET    /api/contacts             - Lấy danh sách liên hệ (admin)
GET    /api/contacts/:id         - Lấy chi tiết liên hệ (admin)
PUT    /api/contacts/:id/status  - Cập nhật trạng thái (admin)
DELETE /api/contacts/:id         - Xóa liên hệ (admin)
```

### Users
```
GET    /api/users/profile        - Lấy thông tin người dùng (auth required)
PUT    /api/users/profile        - Cập nhật thông tin (auth required)
POST   /api/users/change-password - Đổi mật khẩu (auth required)
GET    /api/users                - Danh sách người dùng (admin)
```

## 🚀 Cài đặt và Chạy

### 1️⃣ Cài đặt Backend

```bash
# Di chuyển vào thư mục backend
cd backend

# Cài đặt dependencies
npm install

# Tạo file .env từ .env.example
cp .env.example .env

# Cấu hình .env với thông tin MongoDB của bạn
# DB_URI=mongodb://localhost:27017/hai-au-english
# JWT_SECRET=your_secret_key
# PORT=5000

# Chạy development server
npm run dev

# Server sẽ chạy tại http://localhost:5000
```

### 2️⃣ Chạy Frontend

**Option A: Live Server (VS Code)**
```
1. Cài extension "Live Server"
2. Click phải vào frontend/views/index.html
3. Chọn "Open with Live Server"
4. Server sẽ chạy tại http://localhost:5500
```

**Option B: Python HTTP Server**
```bash
# Từ thư mục gốc
python -m http.server 3000

# Truy cập http://localhost:3000/frontend/views/
```

### 3️⃣ Cấu hình API URL

Sửa file `frontend/js/api.js`:
```javascript
const API_BASE_URL = 'http://localhost:5000/api';
```

## 💾 Yêu cầu Hệ thống

- **Node.js**: v14+ (cho backend)
- **MongoDB**: v4.4+ (cơ sở dữ liệu)
- **Browser**: Chrome, Firefox, Safari, Edge (mới nhất)

## 📝 Hướng dẫn Phát triển

### Thêm tính năng mới trong Backend

1. **Tạo Controller** (`backend/src/controllers/`)
     ```javascript
     export const handleRequest = async (req, res) => {
         // Business logic
     };
     ```

2. **Tạo Route** (`backend/src/routes/`)
     ```javascript
     import { handleRequest } from '../controllers/...';
     router.get('/path', handleRequest);
     ```

3. **Đăng ký Route** trong `server.js`
     ```javascript
     import newRoutes from './routes/...';
     app.use('/api/endpoint', newRoutes);
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
- Express.js - Web framework
- MongoDB - Database
- Mongoose - ODM (Object Data Modeling)
- JWT - Authentication
- bcryptjs - Password hashing
- Nodemon - Development tool

**Frontend:**
- HTML5 - Markup
- CSS3 + Tailwind - Styling
- Vanilla JavaScript - Interaction
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
- [ ] MongoDB Models
- [ ] Password hashing (bcryptjs)
- [ ] JWT token generation
- [ ] Authentication middleware
- [ ] Input validation middleware
- [ ] Error handling middleware
- [ ] Email notifications
- [ ] Admin dashboard
- [ ] Tests

## 🐛 Troubleshooting

### Backend không chạy
```bash
# Kiểm tra Node.js version
node --version  # Should be v14+

# Kiểm tra MongoDB
# Ensure MongoDB service is running

# Xóa node_modules và cài lại
rm -r backend/node_modules
cd backend && npm install
```

### Frontend không kết nối được backend
```javascript
// Kiểm tra API URL trong frontend/js/api.js
const API_BASE_URL = 'http://localhost:5000/api';

// Kiểm tra CORS setting trong backend/src/server.js
// Origin phải là frontend URL của bạn
```

### Token hết hạn
```javascript
// Tự động refresh token
const refreshToken = async () => {
    const response = await APIClient.post('/auth/refresh-token', {});
    APIClient.setToken(response.token);
};
```

## 📖 Tài liệu Thêm

- [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) - Hướng dẫn chi tiết migration từ single-page sang full-stack
- [Express.js Documentation](https://expressjs.com)
- [MongoDB Documentation](https://docs.mongodb.com)
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

- [ ] Backend API integration
- [ ] Real authentication system
- [ ] Database connection
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
