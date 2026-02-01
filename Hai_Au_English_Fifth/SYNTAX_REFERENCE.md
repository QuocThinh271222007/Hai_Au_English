# 📚 Tài liệu Kỹ thuật Chi tiết - Hải Âu English Project

> Tài liệu giải thích chi tiết cú pháp và kỹ thuật sử dụng trong dự án với **vị trí cụ thể trong code**

---

## 📑 Mục lục

1. [HTML](#1-html)
2. [CSS & Tailwind](#2-css--tailwind)
3. [JavaScript](#3-javascript)
4. [PHP](#4-php)
5. [SQL](#5-sql)
6. [Kiến trúc 3 tầng](#6-kiến-trúc-3-tầng)

---

## 1. HTML

### 1.1. Cấu trúc cơ bản

| Thành phần | Mô tả | Vị trí trong dự án |
|------------|-------|-------------------|
| `<!DOCTYPE html>` | Khai báo HTML5 | Tất cả file `.html` - Dòng 1 |
| `<html lang="vi">` | Ngôn ngữ tiếng Việt | Tất cả file `.html` - Dòng 2 |
| `<meta charset="UTF-8">` | Hỗ trợ tiếng Việt | Tất cả file `.html` - Dòng 4 |
| `<meta name="viewport">` | Responsive mobile | Tất cả file `.html` - Dòng 5 |

**📍 Ví dụ từ `frontend/pages/admin.html` - Dòng 1-10:**
```html
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hải Âu English</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/pages/profile.css">
</head>
```

### 1.2. Semantic HTML Tags

| Thẻ | Mục đích | Vị trí ví dụ |
|-----|----------|--------------|
| `<header>` | Phần đầu trang (logo, menu) | `admin.html` dòng 12-35 |
| `<nav>` | Menu điều hướng | `contact.html` dòng 25-42 |
| `<main>` | Nội dung chính | `admin.html` dòng 145 |
| `<section>` | Phân đoạn nội dung | `admin.html` dòng 229 (users section) |
| `<aside>` | Sidebar menu | `admin.html` dòng 37-143 |
| `<footer>` | Chân trang | `contact.html` dòng 450+ |
| `<form>` | Form nhập liệu | `contact.html` dòng 121 |

**📍 Ví dụ Header từ `frontend/pages/admin.html` - Dòng 12-35:**
```html
<header class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-sm shadow-sm z-50">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="index.html" class="inline-block">
                    <img src="../assets/images/logo.png" alt="logo" class="h-20">
                </a>
            </div>
            <!-- Title -->
            <h1 class="text-xl font-bold text-blue-600">Admin Dashboard</h1>
            <!-- User Info -->
            <div class="flex items-center gap-4">
                <span id="header-username" class="text-gray-700 font-medium">Admin</span>
                <button id="logout-btn" class="text-red-600 hover:text-red-700">Đăng xuất</button>
            </div>
        </div>
    </div>
</header>
```

### 1.3. Form Elements

| Element | Mô tả | Thuộc tính quan trọng | Vị trí ví dụ |
|---------|-------|----------------------|--------------|
| `<input type="text">` | Nhập văn bản | `id`, `name`, `required`, `placeholder` | `contact.html` dòng 127 |
| `<input type="email">` | Nhập email (tự validate) | `required` | `login.html` dòng 35 |
| `<input type="password">` | Nhập mật khẩu (ẩn) | `minlength` | `login.html` dòng 47 |
| `<input type="tel">` | Nhập số điện thoại | `pattern` | `signup.html` dòng 61 |
| `<input type="checkbox">` | Ô tick chọn | `checked` | `contact.html` dòng 205 |
| `<select>` | Dropdown chọn | `<option>` bên trong | `contact.html` dòng 168 |
| `<textarea>` | Nhập nhiều dòng | `rows` | `contact.html` dòng 197 |
| `<button type="submit">` | Nút gửi form | - | `contact.html` dòng 220 |

**📍 Ví dụ Form từ `frontend/pages/contact.html` - Dòng 121-230:**
```html
<form id="contact-form" class="space-y-6">
    <!-- Họ tên -->
    <div>
        <label for="fullname" class="block text-sm font-medium text-gray-700 mb-2">
            Họ và tên <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="fullname" 
               name="fullname" 
               required
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
               placeholder="Nhập họ và tên của bạn">
    </div>
    
    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email <span class="text-red-500">*</span>
        </label>
        <input type="email" 
               id="email" 
               name="email" 
               required
               class="w-full px-4 py-3 border border-gray-300 rounded-lg"
               placeholder="example@email.com">
    </div>
    
    <!-- Select dropdown -->
    <div>
        <label for="course" class="block text-sm font-medium text-gray-700 mb-2">
            Khóa học quan tâm <span class="text-red-500">*</span>
        </label>
        <select id="course" name="course" required class="w-full px-4 py-3 border rounded-lg">
            <option value="">-- Chọn khóa học --</option>
            <option value="ielts-foundation">IELTS Foundation</option>
            <option value="ielts-intensive">IELTS Intensive</option>
            <option value="ielts-advanced">IELTS Advanced</option>
        </select>
    </div>
    
    <!-- Checkbox -->
    <div class="flex items-start gap-3">
        <input type="checkbox" id="agreement" name="agreement" required class="mt-1">
        <label for="agreement" class="text-sm text-gray-600">
            Tôi đồng ý với <a href="#" class="text-blue-600">chính sách bảo mật</a>
        </label>
    </div>
    
    <!-- Submit button -->
    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
        Gửi thông tin
    </button>
</form>
```

### 1.4. Data Attributes

| Attribute | Mục đích | Vị trí ví dụ |
|-----------|----------|--------------|
| `data-id` | Lưu ID cho JavaScript | `admin.js` dòng 163 |
| `data-section` | Xác định section | `admin.html` dòng 55 |
| `data-user` | Lưu object JSON | `admin.js` dòng 165 |
| `data-active` | Trạng thái active | `admin.js` dòng 171 |

**📍 Ví dụ từ `frontend/js/controllers/admin.js` - Dòng 160-175:**
```javascript
// Render bảng users với data attributes
tbody.innerHTML = result.users.map(u => `
    <tr>
        <td>${u.id}</td>
        <td>${escapeHtml(u.fullname)}</td>
        <td>${escapeHtml(u.email)}</td>
        <td>
            <!-- data-id lưu ID user -->
            <button class="admin-action-btn secondary edit-user-btn" 
                    data-user='${JSON.stringify(u).replace(/'/g, "&#39;")}'>
                Sửa
            </button>
            <!-- data-id và data-active cho toggle -->
            <button class="admin-action-btn toggle-user-btn" 
                    data-id="${u.id}" 
                    data-active="${u.is_active ? '0' : '1'}">
                ${u.is_active ? 'Khóa' : 'Mở khóa'}
            </button>
        </td>
    </tr>
`).join('');
```

### 1.5. Script Module

| Cách import | Mô tả | Vị trí ví dụ |
|-------------|-------|--------------|
| `type="module"` | Cho phép ES6 import/export | `admin.html` dòng 667 |
| Không có type | Script thông thường | `contact.html` dòng 519 |

**📍 Ví dụ từ `frontend/pages/admin.html` - Dòng cuối:**
```html
<!-- Toast không cần module -->
<div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>

<!-- Controller dùng ES6 module để import services -->
<script type="module" src="../js/controllers/admin.js"></script>
```

---

## 2. CSS & Tailwind

### 2.1. Tailwind CSS Classes - Bảng tổng hợp

#### Layout Classes

| Class | CSS tương đương | Ví dụ sử dụng |
|-------|-----------------|---------------|
| `flex` | `display: flex` | `admin.html` dòng 15 |
| `grid` | `display: grid` | `admin.html` dòng 155 |
| `grid-cols-2` | `grid-template-columns: repeat(2, 1fr)` | `admin.html` dòng 155 |
| `grid-cols-4` | `grid-template-columns: repeat(4, 1fr)` | `admin.html` dòng 155 |
| `gap-4` | `gap: 1rem` | `admin.html` dòng 155 |
| `gap-6` | `gap: 1.5rem` | `admin.html` dòng 155 |
| `items-center` | `align-items: center` | `admin.html` dòng 15 |
| `justify-between` | `justify-content: space-between` | `admin.html` dòng 15 |
| `justify-center` | `justify-content: center` | `contact.html` dòng 91 |

**📍 Ví dụ từ `frontend/pages/admin.html` - Dòng 155-180:**
```html
<!-- Grid 4 cột cho stat cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Card 1 -->
    <div class="stat-card">
        <div class="stat-card-icon blue">👥</div>
        <div>
            <div class="stat-card-label">Tổng học viên</div>
            <div class="stat-card-value" id="stat-users">0</div>
        </div>
    </div>
    <!-- Stat Card 2 -->
    <div class="stat-card">
        <div class="stat-card-icon green">📚</div>
        <div>
            <div class="stat-card-label">Khóa học</div>
            <div class="stat-card-value" id="stat-courses">0</div>
        </div>
    </div>
</div>
```

#### Spacing Classes

| Class | CSS tương đương | Giá trị |
|-------|-----------------|---------|
| `m-4` | `margin: 1rem` | 16px |
| `mt-8` | `margin-top: 2rem` | 32px |
| `mb-4` | `margin-bottom: 1rem` | 16px |
| `mx-auto` | `margin-left/right: auto` | Center |
| `p-4` | `padding: 1rem` | 16px |
| `p-6` | `padding: 1.5rem` | 24px |
| `px-4` | `padding-left/right: 1rem` | 16px |
| `py-3` | `padding-top/bottom: 0.75rem` | 12px |

#### Typography Classes

| Class | CSS tương đương | Vị trí ví dụ |
|-------|-----------------|--------------|
| `text-sm` | `font-size: 0.875rem` | Labels |
| `text-xl` | `font-size: 1.25rem` | Tiêu đề |
| `text-3xl` | `font-size: 1.875rem` | Tiêu đề lớn |
| `font-medium` | `font-weight: 500` | Text quan trọng |
| `font-bold` | `font-weight: 700` | Tiêu đề |
| `text-gray-600` | `color: #4b5563` | Text phụ |
| `text-gray-800` | `color: #1f2937` | Text chính |
| `text-blue-600` | `color: #2563eb` | Link, highlight |
| `text-red-500` | `color: #ef4444` | Lỗi, cảnh báo |
| `text-green-600` | `color: #16a34a` | Thành công |

#### Background & Border Classes

| Class | CSS tương đương | Mô tả |
|-------|-----------------|-------|
| `bg-white` | `background: white` | Nền trắng |
| `bg-gray-100` | `background: #f3f4f6` | Nền xám nhạt |
| `bg-blue-600` | `background: #2563eb` | Nền xanh primary |
| `bg-white/95` | `background: rgba(255,255,255,0.95)` | Nền trắng 95% opacity |
| `border` | `border: 1px solid` | Viền 1px |
| `border-gray-300` | `border-color: #d1d5db` | Màu viền xám |
| `rounded-lg` | `border-radius: 0.5rem` | Bo góc 8px |
| `rounded-xl` | `border-radius: 0.75rem` | Bo góc 12px |
| `rounded-full` | `border-radius: 9999px` | Bo tròn hoàn toàn |
| `shadow-sm` | `box-shadow: 0 1px 2px rgba(0,0,0,0.05)` | Bóng nhẹ |
| `shadow-lg` | `box-shadow: 0 10px 15px rgba(0,0,0,0.1)` | Bóng đậm |

#### Responsive Breakpoints

| Prefix | Min-width | Ví dụ |
|--------|-----------|-------|
| (none) | 0px | `grid-cols-1` (mobile) |
| `sm:` | 640px | `sm:px-6` |
| `md:` | 768px | `md:grid-cols-2` (tablet) |
| `lg:` | 1024px | `lg:grid-cols-4` (desktop) |
| `xl:` | 1280px | `xl:px-8` |

**📍 Ví dụ Responsive từ `frontend/pages/contact.html` - Dòng 14-16:**
```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- 
    - Mobile: px-4 (padding 16px)
    - Tablet (>=640px): sm:px-6 (padding 24px)  
    - Desktop (>=1024px): lg:px-8 (padding 32px)
    -->
</div>
```

### 2.2. Custom CSS Classes

**📍 Từ `frontend/css/styles.css`:**

| Class | Mục đích | Thuộc tính chính |
|-------|----------|------------------|
| `.profile-card` | Card container | `background`, `border-radius`, `padding`, `box-shadow` |
| `.profile-form-input` | Input styling | `width`, `padding`, `border`, `border-radius` |
| `.profile-form-label` | Label styling | `font-weight`, `margin-bottom`, `color` |
| `.status-badge` | Badge trạng thái | `padding`, `border-radius`, `font-size` |
| `.status-badge.active` | Badge xanh | `background: #dcfce7`, `color: #166534` |
| `.status-badge.pending` | Badge vàng | `background: #fef3c7`, `color: #92400e` |
| `.status-badge.cancelled` | Badge đỏ | `background: #fee2e2`, `color: #991b1b` |
| `.admin-action-btn` | Nút action | `padding`, `border-radius`, `font-size` |
| `.admin-action-btn.primary` | Nút xanh | `background: #2563eb`, `color: white` |
| `.admin-action-btn.danger` | Nút đỏ | `background: #dc2626`, `color: white` |
| `.admin-action-btn.warning` | Nút vàng | `background: #f59e0b`, `color: white` |

**📍 Ví dụ từ `frontend/css/pages/profile.css`:**
```css
/* Card container */
.profile-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

/* Form input */
.profile-form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.profile-form-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background-color: #dcfce7;
    color: #166534;
}

.status-badge.pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-badge.cancelled {
    background-color: #fee2e2;
    color: #991b1b;
}

.status-badge.completed {
    background-color: #dbeafe;
    color: #1e40af;
}
```

---

## 3. JavaScript

### 3.1. ES6 Modules

| Cú pháp | Mô tả | Vị trí ví dụ |
|---------|-------|--------------|
| `export const` | Export named | `adminService.js` dòng 7 |
| `export default` | Export mặc định | `adminService.js` dòng cuối |
| `import { name }` | Import named | `admin.js` dòng 6 |
| `import name` | Import default | - |

**📍 Ví dụ từ `frontend/js/services/adminService.js` - Dòng 1-15:**
```javascript
/**
 * Admin Service - Gọi API admin
 */

const API_URL = '../../backend/php/admin.php';

// Named export
export const adminService = {
    // Methods...
    async getDashboard() { /* ... */ },
    async getUsers() { /* ... */ }
};

// Default export
export default adminService;
```

**📍 Ví dụ import từ `frontend/js/controllers/admin.js` - Dòng 5-7:**
```javascript
// Import named exports
import { adminService } from '../services/adminService.js';
import { showToast } from '../ui/toast.js';
```

### 3.2. Async/Await

| Cú pháp | Mô tả | Vị trí ví dụ |
|---------|-------|--------------|
| `async function` | Khai báo async function | `admin.js` dòng 19 |
| `await` | Đợi Promise resolve | `admin.js` dòng 21 |
| `try/catch` | Xử lý lỗi | `admin.js` dòng 20-30 |

**📍 Ví dụ từ `frontend/js/controllers/admin.js` - Dòng 19-31:**
```javascript
// Kiểm tra quyền admin
async function checkAdmin() {
    try {
        // await đợi Promise từ API call
        const result = await adminService.getDashboard();
        
        if (result.error) {
            // Nếu lỗi, redirect về login
            window.location.href = 'login.html';
            return null;
        }
        return result;
    } catch (error) {
        // Xử lý exception
        window.location.href = 'login.html';
        return null;
    }
}
```

### 3.3. Fetch API

| Tham số | Mô tả | Giá trị |
|---------|-------|---------|
| `method` | HTTP method | `'GET'`, `'POST'`, `'PUT'`, `'DELETE'` |
| `headers` | HTTP headers | `{'Content-Type': 'application/json'}` |
| `credentials` | Gửi cookies | `'include'` để gửi session |
| `body` | Request body | `JSON.stringify(data)` |

**📍 Ví dụ GET từ `frontend/js/services/adminService.js` - Dòng 36-44:**
```javascript
async getUsers() {
    try {
        const response = await fetch(`${API_URL}?action=users`, {
            credentials: 'include'  // Gửi kèm session cookie
        });
        const data = await response.json();
        return { success: data.success, users: data.data || [] };
    } catch (error) {
        return { error: error.message };
    }
}
```

**📍 Ví dụ POST từ `frontend/js/services/adminService.js` - Dòng 46-58:**
```javascript
async createUser(userData) {
    try {
        const response = await fetch(`${API_URL}?action=user-create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(userData)
        });
        return await response.json();
    } catch (error) {
        return { error: error.message };
    }
}
```

### 3.4. DOM Manipulation

| Method | Mô tả | Vị trí ví dụ |
|--------|-------|--------------|
| `getElementById()` | Lấy element theo ID | `admin.js` dòng 141 |
| `querySelector()` | Lấy element theo CSS selector | `admin.js` dòng 710 |
| `querySelectorAll()` | Lấy tất cả elements | `admin.js` dòng 175 |
| `.innerHTML` | Set/get HTML content | `admin.js` dòng 149 |
| `.textContent` | Set/get text content | `admin.js` dòng 12-16 |
| `.classList.add()` | Thêm class | `admin.js` dòng 952 |
| `.classList.remove()` | Xóa class | `admin.js` dòng 953 |
| `.classList.toggle()` | Toggle class | - |
| `.addEventListener()` | Gắn event listener | `admin.js` dòng 175 |

**📍 Ví dụ từ `frontend/js/controllers/admin.js` - Dòng 139-180:**
```javascript
async function renderUsers() {
    // getElementById lấy element theo ID
    const tbody = document.getElementById('users-tbody');
    
    try {
        const result = await adminService.getUsers();
        
        if (!result.success || !result.users?.length) {
            // innerHTML set nội dung HTML
            tbody.innerHTML = '<tr><td colspan="7">Chưa có học viên</td></tr>';
            return;
        }

        // Template literals để tạo HTML
        tbody.innerHTML = result.users.map(u => `
            <tr>
                <td>${u.id}</td>
                <td>${escapeHtml(u.fullname)}</td>
                <td>${escapeHtml(u.email)}</td>
                <td>
                    <button class="edit-user-btn" data-user='${JSON.stringify(u)}'>
                        Sửa
                    </button>
                    <button class="delete-user-btn" data-id="${u.id}">
                        Xóa
                    </button>
                </td>
            </tr>
        `).join('');

        // querySelectorAll + forEach để gắn event
        tbody.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const user = JSON.parse(btn.dataset.user);
                showUserModal(user);
            });
        });
        
        tbody.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Bạn có chắc muốn xóa?')) return;
                await adminService.deleteUser(btn.dataset.id);
                renderUsers(); // Re-render sau khi xóa
            });
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7">Lỗi tải dữ liệu</td></tr>';
    }
}
```

### 3.5. Event Handling

| Event | Mô tả | Vị trí ví dụ |
|-------|-------|--------------|
| `click` | Click chuột | `admin.js` dòng 175 |
| `submit` | Submit form | `admin.js` dòng 1005 |
| `change` | Thay đổi input/select | `admin.js` dòng 1392 |
| `DOMContentLoaded` | DOM đã load xong | `admin.js` dòng 1540 |

**📍 Ví dụ Form Submit từ `frontend/js/controllers/admin.js` - Dòng 1000-1020:**
```javascript
document.getElementById('course-form').addEventListener('submit', async (e) => {
    // Ngăn form submit mặc định (reload page)
    e.preventDefault();
    
    // Lấy data từ form
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    // Xử lý checkbox
    data.is_active = formData.has('is_active') ? 1 : 0;
    
    // Gọi API
    try {
        const result = isEdit 
            ? await adminService.updateCourse(data)
            : await adminService.createCourse(data);
        
        if (result.success) {
            showToast('Thành công!', 'success');
            hideModal();
            renderCourses();
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        showToast('Lỗi kết nối', 'error');
    }
});
```

### 3.6. Helper Functions

**📍 Từ `frontend/js/controllers/admin.js` - Dòng 11-50:**

| Function | Mục đích | Dòng |
|----------|----------|------|
| `escapeHtml(text)` | Chống XSS | 11-16 |
| `formatDate(dateStr)` | Format ngày | 33-37 |
| `formatDateTime(dateStr)` | Format ngày giờ | 39-43 |
| `formatMoney(amount)` | Format tiền VND | 45-48 |
| `getStatusBadge(status)` | Tạo badge HTML | 50-58 |

```javascript
// Chống XSS - escape HTML special characters
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;  // Tự động escape
    return div.innerHTML;
}

// Format ngày theo locale Việt Nam
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN');
    // Output: "01/02/2026"
}

// Format số tiền
function formatMoney(amount) {
    if (!amount) return '-';
    return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    // Output: "5.000.000đ"
}

// Tạo status badge HTML
function getStatusBadge(status) {
    const labels = {
        'active': 'Đang học',
        'pending': 'Chờ xử lý',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
    };
    return `<span class="status-badge ${status}">${labels[status] || status}</span>`;
}
```

### 3.7. Chart.js

**📍 Từ `frontend/js/controllers/profile.js` - Dòng 200-300:**

```javascript
// Line Chart - Biểu đồ đường
new Chart(document.getElementById('score-line-chart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr'],  // Trục X
        datasets: [{
            label: 'Overall Score',
            data: [6.0, 6.5, 7.0, 7.5],        // Dữ liệu
            borderColor: '#2563eb',            // Màu đường
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.3,                      // Độ cong
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { min: 0, max: 9 }  // Thang điểm IELTS
        }
    }
});

// Pie Chart - Biểu đồ tròn
new Chart(document.getElementById('score-pie-chart'), {
    type: 'pie',
    data: {
        labels: ['Listening', 'Reading', 'Writing', 'Speaking'],
        datasets: [{
            data: [7.0, 7.5, 6.5, 7.0],
            backgroundColor: [
                '#10b981',  // Green
                '#f59e0b',  // Yellow
                '#8b5cf6',  // Purple
                '#ec4899'   // Pink
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
```

---

## 4. PHP

### 4.1. Headers và CORS

**📍 Từ `backend/php/admin.php` - Dòng 8-16:**

| Header | Mục đích |
|--------|----------|
| `Content-Type: application/json` | Response là JSON |
| `Access-Control-Allow-Origin` | Cho phép domain nào gọi API |
| `Access-Control-Allow-Credentials: true` | Cho phép gửi cookies |
| `Access-Control-Allow-Methods` | Các HTTP methods được phép |
| `Access-Control-Allow-Headers` | Các headers được phép |

```php
<?php
// Khai báo response type
header('Content-Type: application/json; charset=utf-8');

// CORS headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

### 4.2. Database Connection

**📍 Từ `backend/php/db.php`:**

| Connection | Mô tả | Sử dụng tại |
|------------|-------|-------------|
| `mysqli` | MySQL Improved | `auth.php`, `contact.php` |
| `PDO` | PHP Data Objects | `admin.php`, `profile.php` |

```php
<?php
// Cấu hình database
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hai_au_english';

// MySQLi connection
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
$mysqli->set_charset('utf8mb4');

// PDO connection
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Handle error
}

return $mysqli;
```

### 4.3. Session và Authentication

**📍 Từ `backend/php/admin.php` - Dòng 23-42:**

| Function | Mục đích |
|----------|----------|
| `session_start()` | Bắt đầu/resume session |
| `$_SESSION['user_id']` | Lưu user ID vào session |
| `checkAdmin()` | Kiểm tra quyền admin |

```php
session_start();

// Kiểm tra quyền admin
function checkAdmin() {
    // Kiểm tra đã đăng nhập chưa
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    
    // Kiểm tra role
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền admin']);
        exit;
    }
    
    return $_SESSION['user_id'];
}
```

### 4.4. Prepared Statements (PDO)

**📍 Từ `backend/php/admin.php`:**

| Method | Mô tả | Ví dụ |
|--------|-------|-------|
| `prepare()` | Chuẩn bị SQL | `$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?")` |
| `execute()` | Thực thi với params | `$stmt->execute([$userId])` |
| `fetch()` | Lấy 1 row | `$user = $stmt->fetch()` |
| `fetchAll()` | Lấy tất cả rows | `$users = $stmt->fetchAll()` |
| `fetchColumn()` | Lấy 1 giá trị | `$count = $stmt->fetchColumn()` |
| `lastInsertId()` | ID của row vừa insert | `$newId = $pdo->lastInsertId()` |

```php
// SELECT với WHERE
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// SELECT với JOIN
$stmt = $pdo->query("
    SELECT e.*, u.fullname, c.name as course_name
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    JOIN courses c ON e.course_id = c.id
    ORDER BY e.created_at DESC
");
$enrollments = $stmt->fetchAll();

// INSERT
$stmt = $pdo->prepare("
    INSERT INTO users (fullname, email, password, role) 
    VALUES (?, ?, ?, 'student')
");
$stmt->execute([
    $data['fullname'],
    $data['email'],
    password_hash($data['password'], PASSWORD_DEFAULT)
]);
$newUserId = $pdo->lastInsertId();

// UPDATE
$stmt = $pdo->prepare("
    UPDATE users SET fullname = ?, phone = ?, updated_at = NOW()
    WHERE id = ?
");
$stmt->execute([$fullname, $phone, $userId]);

// DELETE
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);

// COUNT
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$totalUsers = $stmt->fetchColumn();
```

### 4.5. JSON Handling

**📍 Từ `backend/php/admin.php`:**

| Function | Mô tả |
|----------|-------|
| `json_decode()` | Parse JSON string thành array/object |
| `json_encode()` | Convert array/object thành JSON string |
| `file_get_contents('php://input')` | Đọc request body |

```php
// Đọc JSON từ request body
$data = json_decode(file_get_contents('php://input'), true);
// true = trả về array, false = trả về object

// Validate required fields
if (empty($data['fullname']) || empty($data['email'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Vui lòng điền đầy đủ thông tin'
    ]);
    exit;
}

// Trả về JSON response
echo json_encode([
    'success' => true,
    'message' => 'Thành công',
    'data' => $result
]);

// Trả về lỗi với HTTP status code
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Dữ liệu không hợp lệ'
]);
```

### 4.6. Switch-Case Router

**📍 Từ `backend/php/admin.php` - Dòng 44-200:**

```php
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'stats':
            checkAdmin();
            // Xử lý thống kê
            $stats = [];
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            $stats['users'] = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'users':
            checkAdmin();
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                $users = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $users]);
            }
            break;
            
        case 'user-create':
            checkAdmin();
            $data = json_decode(file_get_contents('php://input'), true);
            // Validate & insert...
            echo json_encode(['success' => true, 'message' => 'Thêm thành công']);
            break;
            
        case 'user-update':
            checkAdmin();
            $data = json_decode(file_get_contents('php://input'), true);
            // Validate & update...
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
            break;
            
        case 'user-delete':
            checkAdmin();
            $data = json_decode(file_get_contents('php://input'), true);
            // Soft delete to trash...
            echo json_encode(['success' => true, 'message' => 'Đã xóa']);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### 4.7. Password Hashing

**📍 Từ `backend/php/auth.php`:**

| Function | Mô tả |
|----------|-------|
| `password_hash($password, PASSWORD_DEFAULT)` | Hash mật khẩu (bcrypt) |
| `password_verify($password, $hash)` | Kiểm tra mật khẩu |

```php
// Đăng ký - Hash password trước khi lưu
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->execute([$email, $hashedPassword]);

// Đăng nhập - Verify password
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($inputPassword, $user['password'])) {
    // Password đúng - tạo session
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    // Password sai
    echo json_encode(['success' => false, 'message' => 'Sai email hoặc mật khẩu']);
}
```

---

## 5. SQL

### 5.1. Bảng Users

**📍 Từ `backend/create_db.sql`:**

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| `id` | INT AUTO_INCREMENT | Khóa chính, tự tăng |
| `fullname` | VARCHAR(255) | Họ tên |
| `email` | VARCHAR(255) UNIQUE | Email (duy nhất) |
| `phone` | VARCHAR(20) | Số điện thoại |
| `password` | VARCHAR(255) | Mật khẩu (đã hash) |
| `role` | ENUM | 'student' hoặc 'admin' |
| `is_active` | TINYINT(1) | 1=active, 0=locked |
| `created_at` | TIMESTAMP | Thời gian tạo |
| `updated_at` | TIMESTAMP | Thời gian cập nhật |

### 5.2. Bảng Courses

```sql
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration VARCHAR(50),
    price INT DEFAULT 0,
    total_sessions INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.3. Bảng Scores

```sql
CREATE TABLE scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT,
    test_date DATE,
    listening DECIMAL(3,1),      -- 0.0 - 9.0
    reading DECIMAL(3,1),
    writing DECIMAL(3,1),
    speaking DECIMAL(3,1),
    overall DECIMAL(3,1),        -- Tự tính trung bình
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.4. Bảng Schedules

```sql
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    teacher_id INT,
    day_of_week TINYINT NOT NULL,     -- 2=Thứ 2, 3=Thứ 3, ..., 8=CN
    period TINYINT DEFAULT 1,          -- Tiết bắt đầu (1-15)
    period_count TINYINT DEFAULT 1,    -- Số tiết học
    session ENUM('morning', 'afternoon', 'evening'),
    room VARCHAR(50),
    class_name VARCHAR(100),
    group_name VARCHAR(100),
    academic_year VARCHAR(20),         -- VD: "2025-2026"
    semester TINYINT,                  -- 1 hoặc 2
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.5. Bảng Trash (Soft Delete)

```sql
CREATE TABLE trash (
    id INT PRIMARY KEY AUTO_INCREMENT,
    original_table VARCHAR(50) NOT NULL,    -- 'users', 'courses', etc.
    original_id INT NOT NULL,               -- ID gốc
    data JSON NOT NULL,                     -- Toàn bộ data dạng JSON
    deleted_by INT,                         -- Admin đã xóa
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_restored TINYINT(1) DEFAULT 0,       -- Đã khôi phục chưa
    
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.6. Common Queries

```sql
-- Đếm với điều kiện
SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1;

-- JOIN nhiều bảng
SELECT 
    e.id,
    u.fullname as student_name,
    c.name as course_name,
    e.status,
    e.created_at
FROM enrollments e
JOIN users u ON e.user_id = u.id
JOIN courses c ON e.course_id = c.id
WHERE e.status = 'active'
ORDER BY e.created_at DESC;

-- Aggregate functions (Tính trung bình điểm)
SELECT 
    user_id,
    AVG(listening) as avg_listening,
    AVG(reading) as avg_reading,
    AVG(writing) as avg_writing,
    AVG(speaking) as avg_speaking,
    AVG(overall) as avg_overall
FROM scores
WHERE user_id = 5
GROUP BY user_id;

-- Subquery
SELECT * FROM users
WHERE id IN (
    SELECT DISTINCT user_id FROM scores WHERE overall >= 7.0
);

-- LIMIT và OFFSET (phân trang)
SELECT * FROM users
ORDER BY created_at DESC
LIMIT 10 OFFSET 0;  -- Trang 1: 10 records đầu
-- OFFSET 10 = Trang 2, OFFSET 20 = Trang 3, ...
```

---

## 6. Kiến trúc 3 tầng

### 6.1. Tổng quan

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION TIER                             │
│                      (Frontend)                                  │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  HTML Pages + CSS + JavaScript                             │  │
│  │  - pages/*.html (View/Giao diện)                          │  │
│  │  - js/controllers/*.js (UI Logic)                         │  │
│  │  - js/services/*.js (API Client)                          │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP Request (Fetch API)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION TIER                              │
│                     (Backend PHP)                                │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  PHP API Endpoints                                         │  │
│  │  - auth.php (Authentication Logic)                        │  │
│  │  - admin.php (Business Logic)                             │  │
│  │  - profile.php (User Logic)                               │  │
│  │  - contact.php, courses.php                               │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ SQL Queries (PDO/MySQLi)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       DATA TIER                                  │
│                       (MySQL)                                    │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  Database: hai_au_english                                  │  │
│  │  Tables: users, courses, enrollments, scores,             │  │
│  │          teachers, feedback, schedules, contacts, trash   │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2. Data Flow - Ví dụ Thêm Học Viên

```
┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│  HTML Form   │ -> │  JavaScript  │ -> │ PHP Backend  │ -> │  MySQL DB    │
│ (admin.html) │    │ (admin.js)   │    │ (admin.php)  │    │ (users)      │
└──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘

1. User điền form          2. JS gọi API           3. PHP xử lý         4. Lưu vào DB
   - fullname                 fetch(POST)             - Validate            INSERT INTO
   - email                    adminService            - Hash password       users (...)
   - password                 .createUser()           - Insert DB
```

### 6.3. File Mapping

| Chức năng | Frontend (HTML) | Controller (JS) | Service (JS) | Backend (PHP) | Database |
|-----------|-----------------|-----------------|--------------|---------------|----------|
| Đăng nhập | `login.html` | `auth.js` | `authService.js` | `auth.php` | `users` |
| Dashboard | `admin.html` | `admin.js` | `adminService.js` | `admin.php` | Multiple |
| Quản lý học viên | `admin.html` (section users) | `admin.js` (renderUsers) | `adminService.js` (getUsers, createUser) | `admin.php` (case 'users') | `users` |
| Quản lý điểm | `admin.html` (section scores) | `admin.js` (renderScores) | `adminService.js` (getScores, createScore) | `admin.php` (case 'scores') | `scores` |
| Profile học viên | `profile.html` | `profile.js` | `profileService.js` | `profile.php` | `users`, `scores` |
| Liên hệ | `contact.html` | `contact.js` | `contactService.js` | `contact.php` | `contacts` |

---

---

## 7. Bảng tổng hợp API Endpoints

### 7.1. Authentication API (`auth.php`)

| Endpoint | Method | Body | Response | Vị trí JS |
|----------|--------|------|----------|-----------|
| `?action=login` | POST | `{email, password}` | `{success, user}` | `authService.js:15` |
| `?action=register` | POST | `{fullname, email, password, phone}` | `{success, message}` | `authService.js:30` |
| `?action=logout` | POST | - | `{success}` | `authService.js:45` |
| `?action=check` | GET | - | `{success, user}` | `authService.js:55` |

### 7.2. Admin API (`admin.php`)

#### Dashboard & Users

| Endpoint | Method | Body | Response | Mô tả |
|----------|--------|------|----------|-------|
| `?action=dashboard` | GET | - | `{success, stats, recent_enrollments}` | Dashboard stats |
| `?action=users` | GET | - | `{success, data: [users]}` | Danh sách học viên |
| `?action=user-create` | POST | `{fullname, email, password, phone, is_active}` | `{success, message}` | Thêm học viên |
| `?action=user-update` | POST | `{id, fullname, phone, is_active}` | `{success, message}` | Sửa học viên |
| `?action=user-toggle` | POST | `{id, is_active}` | `{success, message}` | Khóa/mở khóa |
| `?action=user-delete` | POST | `{id}` | `{success, message}` | Xóa (soft delete) |

#### Courses

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| `?action=courses` | GET | - | `{success, data: [courses]}` |
| `?action=course-create` | POST | `{name, level, duration, fee, sessions, description}` | `{success}` |
| `?action=course-update` | POST | `{id, name, level, ...}` | `{success}` |
| `?action=course-delete` | POST | `{id}` | `{success}` |

#### Enrollments

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| `?action=enrollments` | GET | - | `{success, data: [enrollments]}` |
| `?action=enrollment-create` | POST | `{user_id, course_id, status, academic_year, semester}` | `{success}` |
| `?action=enrollment-update` | POST | `{id, status, ...}` | `{success}` |
| `?action=enrollment-delete` | POST | `{id}` | `{success}` |

#### Scores

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| `?action=scores` | GET | - | `{success, data: [scores]}` |
| `?action=score-create` | POST | `{user_id, course_id, test_date, listening, reading, writing, speaking, notes}` | `{success}` |
| `?action=score-update` | POST | `{id, ...}` | `{success}` |
| `?action=score-delete` | POST | `{id}` | `{success}` |

#### Schedules

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| `?action=schedules` | GET | `?academic_year=&semester=` | `{success, data: [schedules]}` |
| `?action=schedule-create` | POST | `{course_id, teacher_id, day_of_week, period, period_count, session, room, class_name, group_name, academic_year, semester, start_date, end_date}` | `{success}` |
| `?action=schedule-update` | POST | `{id, ...}` | `{success}` |
| `?action=schedule-delete` | POST | `{id}` | `{success}` |

#### Trash

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| `?action=trash` | GET | `?type=` | `{success, data: [trash_items]}` |
| `?action=trash-restore` | POST | `{id}` | `{success}` |
| `?action=trash-delete` | POST | `{id}` | `{success}` |
| `?action=trash-clear` | POST | - | `{success}` |

### 7.3. Profile API (`profile.php`)

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| `?action=info` | GET | - | `{success, user}` |
| `?action=update` | POST | `{fullname, phone, password?}` | `{success}` |
| `?action=scores` | GET | - | `{success, scores: []}` |
| `?action=schedules` | GET | `?academic_year=&semester=` | `{success, schedules: []}` |
| `?action=feedback` | GET | - | `{success, feedback: []}` |

### 7.4. Contact API (`contact.php`)

| Endpoint | Method | Body | Response |
|----------|--------|------|----------|
| (default) | POST | `{fullname, email, phone, course, level, message}` | `{success, message}` |

---

## 8. Bảng tổng hợp Files quan trọng

### 8.1. Frontend Files

| File | Dòng code | Chức năng chính | Dependencies |
|------|-----------|-----------------|--------------|
| `pages/admin.html` | ~670 | Dashboard admin | `admin.js`, Tailwind |
| `pages/profile.html` | ~450 | Profile học viên | `profile.js`, Chart.js |
| `pages/contact.html` | ~530 | Form liên hệ | `contact.js` |
| `pages/login.html` | ~200 | Form đăng nhập | `auth.js` |
| `js/controllers/admin.js` | ~1550 | Logic admin page | `adminService.js`, `toast.js` |
| `js/controllers/profile.js` | ~600 | Logic profile page | `profileService.js`, Chart.js |
| `js/services/adminService.js` | ~350 | API client admin | Fetch API |
| `js/services/authService.js` | ~80 | API client auth | Fetch API |
| `css/styles.css` | ~400 | CSS chung | - |
| `css/pages/profile.css` | ~200 | CSS profile/admin | - |

### 8.2. Backend Files

| File | Dòng code | Chức năng chính | Database |
|------|-----------|-----------------|----------|
| `php/db.php` | ~50 | Kết nối database | MySQL |
| `php/auth.php` | ~150 | Authentication | `users` |
| `php/admin.php` | ~600 | CRUD admin API | All tables |
| `php/profile.php` | ~200 | User profile API | `users`, `scores`, `schedules` |
| `php/contact.php` | ~80 | Contact form API | `contacts` |
| `php/courses.php` | ~50 | Public courses API | `courses` |
| `create_db.sql` | ~200 | Database schema | - |
| `update_db.sql` | ~100 | Schema updates | - |

### 8.3. Database Tables

| Table | Số cột | PK | FKs | Mô tả |
|-------|--------|----|----|-------|
| `users` | 9 | `id` | - | Tài khoản người dùng |
| `courses` | 10 | `id` | - | Khóa học IELTS |
| `teachers` | 8 | `id` | - | Giảng viên |
| `enrollments` | 8 | `id` | `user_id`, `course_id` | Đăng ký khóa học |
| `scores` | 12 | `id` | `user_id`, `course_id` | Điểm IELTS |
| `feedback` | 6 | `id` | `user_id`, `teacher_id` | Nhận xét học viên |
| `schedules` | 15 | `id` | `course_id`, `teacher_id` | Lịch học |
| `contacts` | 9 | `id` | - | Form liên hệ |
| `trash` | 6 | `id` | `deleted_by` | Soft delete storage |

---

## 📖 Tham khảo thêm

| Công nghệ | Link tài liệu | Phiên bản sử dụng |
|-----------|---------------|-------------------|
| HTML5 | https://developer.mozilla.org/en-US/docs/Web/HTML | HTML5 |
| Tailwind CSS | https://tailwindcss.com/docs | 3.x (CDN) |
| JavaScript ES6 | https://developer.mozilla.org/en-US/docs/Web/JavaScript | ES6+ Modules |
| PHP | https://www.php.net/manual/ | 7.4+ |
| MySQL | https://dev.mysql.com/doc/ | 5.7+ / MariaDB 10.4+ |
| Chart.js | https://www.chartjs.org/docs/ | 4.x (CDN) |
| PDO | https://www.php.net/manual/en/book.pdo.php | PHP built-in |

---

**Cập nhật:** 2026-02-01  
**Phiên bản:** 2.0
