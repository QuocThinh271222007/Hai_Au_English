# HƯỚNG DẪN TÍCH HỢP KHÓA HỌC & GIẢNG VIÊN VỚI ADMIN

## 📋 Tổng quan các thay đổi

1. **courses.php** - Đã được cập nhật để load khóa học từ database
2. **courses.js** - Đã được cập nhật để hiển thị khóa học theo category
3. **update_site_content.sql** - Đã thêm các content keys cho courses
4. **update_courses.sql** - Chứa 20 khóa học mẫu (Tiểu học, THCS, IELTS)

---

## 🚀 BƯỚC 1: Upload files lên Hostinger

Upload các file sau lên hosting (thay thế file cũ):

### File PHP (frontend/pages/)
- `courses.php` - Trang hiển thị khóa học (đã tối ưu)

### File JavaScript (frontend/js/controllers/)
- `courses.js` - Controller xử lý courses

### File SQL (backend/)
- `update_site_content.sql` - Cập nhật nội dung tĩnh
- `update_courses.sql` - Thêm 20 khóa học mẫu

---

## 🚀 BƯỚC 2: Chạy SQL trên phpMyAdmin

### Truy cập phpMyAdmin:
1. Đăng nhập Hostinger → Databases → phpMyAdmin

### Chạy file update_courses.sql:
```sql
-- Copy nội dung từ file update_courses.sql và chạy trong phpMyAdmin
```

### Chạy file update_site_content.sql:
```sql
-- Copy nội dung từ file update_site_content.sql và chạy trong phpMyAdmin
```

---

## 🎯 BƯỚC 3: Kiểm tra hoạt động

### Test trang Khóa học:
1. Truy cập: `https://yoursite.com/KhoaHoc`
2. Phải thấy 3 section: Tiểu học, THCS, IELTS
3. Mỗi section load khóa học từ database
4. Click filter tabs để lọc theo category

### Test trang Giảng viên:
1. Truy cập: `https://yoursite.com/GiangVien`
2. Phải thấy danh sách giảng viên từ database

### Test Admin Panel:
1. Truy cập: `https://yoursite.com/admin`
2. Đăng nhập với tài khoản admin
3. Vào "Quản lý khóa học" → Thêm/sửa/xóa khóa học
4. Vào "Quản lý giảng viên" → Thêm/sửa/xóa giảng viên

---

## 📝 Cấu trúc khóa học trong Database

Mỗi khóa học có các trường:
- `name`: Tên khóa học
- `description`: Mô tả ngắn
- `category`: `tieuhoc`, `thcs`, hoặc `ielts`
- `level`: `beginner`, `intermediate`, hoặc `advanced`
- `duration`: Thời lượng (VD: "3 tháng")
- `price`: Giá (số)
- `price_unit`: Đơn vị (VD: "/khóa")
- `features`: Danh sách tính năng (phân cách bằng dấu `|`)
- `image_url`: URL hình ảnh
- `badge`: Nhãn (VD: "Hot", "Mới")
- `badge_type`: Loại badge (`hot`, `new`, `popular`)

---

## ⚠️ Lưu ý quan trọng

1. **Backup database** trước khi chạy SQL
2. Các file SQL sẽ **xóa dữ liệu cũ** và thêm dữ liệu mới
3. Sau khi thêm khóa học mới trong Admin, trang web sẽ tự động hiển thị
4. Nếu không thấy thay đổi, **xóa cache** trình duyệt (Ctrl+F5)

---

## 📊 Danh sách 20 khóa học đã chuẩn bị

### Tiểu học (7 khóa):
1. English for Pre-Starters (Dự bị)
2. English for Starters (Lớp 1-2)
3. LT Cambridge Starters (Luyện thi)
4. English for Movers (Lớp 3-4)
5. LT Cambridge Movers (Luyện thi)
6. English for Flyers (Lớp 5)
7. LT Cambridge Flyers (Luyện thi)

### THCS (6 khóa):
1. English 6 (Lớp 6)
2. English 7 (Lớp 7)
3. English 8 (Lớp 8)
4. English 9 (Lớp 9)
5. LT Cambridge KET (A2)
6. LT Cambridge PET (B1)

### IELTS (7 khóa):
1. IELTS Foundation (5.0-5.5)
2. IELTS Intermediate (6.0-6.5)
3. IELTS Advanced (7.0+)
4. IELTS 1-1 (Cá nhân)
5. IELTS Online
6. IELTS Writing Intensive
7. IELTS Speaking Intensive

---

Nếu gặp lỗi, liên hệ hỗ trợ!
