PHP backend for contact form (XAMPP)

Steps to run on XAMPP (Windows):

1. Copy `backend/php` folder into your XAMPP `htdocs` directory (for example `C:/xampp/htdocs/hai_au_backend`).
2. Import the SQL schema: open phpMyAdmin (http://localhost/phpmyadmin), create or import `backend/create_db.sql` to create the `hai_au_english` database and `contacts` table.
3. Ensure MySQL user is `root` with empty password (default XAMPP). If different, edit `db.php` and update credentials.
4. From the website frontend (served by any static server or also from XAMPP), POST the contact form to `http://localhost/hai_au_backend/contact.php` (or the relative path from your frontend to the PHP folder).

Notes:
- The handler accepts JSON or form-encoded POST requests.
- For local dev CORS is allowed for localhost origins; tighten this in production.

# Hướng dẫn chạy hệ thống PHP với XAMPP

## 1. Cài đặt XAMPP
- Tải và cài đặt XAMPP tại https://www.apachefriends.org/index.html
- Khởi động Apache và MySQL trong XAMPP Control Panel.

## 2. Import database
- Mở phpMyAdmin tại http://localhost/phpmyadmin
- Nhấn "Import", chọn file `backend/create_db.sql` và `backend/update_db.sql` để tạo các bảng `contacts`, `users`, `courses`.

## 3. Cấu hình thư mục
- Copy toàn bộ thư mục `backend/php` vào `C:/xampp/htdocs/hai_au_backend`
- Copy toàn bộ thư mục `frontend` vào `C:/xampp/htdocs/hai_au_frontend`
- Đảm bảo các file PHP backend nằm ở: `C:/xampp/htdocs/hai_au_backend/`
- Đảm bảo các file frontend (HTML, JS, CSS) nằm ở: `C:/xampp/htdocs/hai_au_frontend/`

## 4. Cấu hình kết nối MySQL
- Mặc định user là `root`, password rỗng. Nếu khác, sửa lại trong `hai_au_backend/db.php`.

## 5. Truy cập website
- Mở trình duyệt, truy cập: http://localhost/hai_au_frontend/pages/index.html
- Các form liên hệ, đăng ký, đăng nhập, quản lý khóa học sẽ tự động gửi dữ liệu về PHP backend.

## 6. Test chức năng
- Đăng ký tài khoản mới ở trang đăng ký.
- Đăng nhập bằng tài khoản vừa tạo.
- Gửi form liên hệ ở trang liên hệ.
- Thêm/xóa khóa học (nếu có giao diện admin).

## 7. Lưu ý
- Nếu muốn sửa đường dẫn API, hãy cập nhật các file JS service trong `hai_au_frontend/js/services/`.
- Nếu gặp lỗi CORS, hãy đảm bảo truy cập từ `localhost` và không chặn bởi trình duyệt.

## 8. Hỗ trợ
- Nếu có lỗi kết nối database, kiểm tra lại user/password và trạng thái MySQL.
- Nếu có lỗi 404, kiểm tra lại đường dẫn file PHP và JS.
