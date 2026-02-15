-- Migration: Add recruitment (jobs) table
-- Date: 2026-02-15

CREATE TABLE IF NOT EXISTS recruitments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề vị trí tuyển dụng',
    slug VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL slug',
    department VARCHAR(100) DEFAULT NULL COMMENT 'Phòng ban',
    location VARCHAR(255) DEFAULT 'TP. Hồ Chí Minh' COMMENT 'Địa điểm làm việc',
    employment_type ENUM('full-time', 'part-time', 'contract', 'intern') DEFAULT 'full-time' COMMENT 'Loại hình công việc',
    salary_range VARCHAR(100) DEFAULT NULL COMMENT 'Mức lương (VD: 8-15 triệu)',
    experience VARCHAR(100) DEFAULT NULL COMMENT 'Yêu cầu kinh nghiệm',
    description TEXT COMMENT 'Mô tả công việc',
    requirements TEXT COMMENT 'Yêu cầu ứng viên',
    benefits TEXT COMMENT 'Quyền lợi',
    contact_email VARCHAR(255) DEFAULT 'haiauenglish@gmail.com' COMMENT 'Email liên hệ',
    contact_phone VARCHAR(20) DEFAULT '0931 828 960' COMMENT 'Số điện thoại liên hệ',
    deadline DATE DEFAULT NULL COMMENT 'Hạn nộp hồ sơ',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Đang tuyển hay không',
    is_featured TINYINT(1) DEFAULT 0 COMMENT 'Tin nổi bật',
    view_count INT DEFAULT 0 COMMENT 'Lượt xem',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured),
    INDEX idx_deadline (deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing
INSERT INTO recruitments (title, slug, department, location, employment_type, salary_range, experience, description, requirements, benefits, deadline, is_active, is_featured) VALUES
(
    'Giảng viên Tiếng Anh IELTS',
    'giang-vien-tieng-anh-ielts',
    'Giảng dạy',
    'TP. Hồ Chí Minh',
    'full-time',
    '15-25 triệu',
    'Tối thiểu 2 năm',
    '<h4>Mô tả công việc:</h4>
<ul>
<li>Giảng dạy các khóa học IELTS từ cơ bản đến nâng cao</li>
<li>Soạn giáo án, tài liệu giảng dạy theo chương trình của trung tâm</li>
<li>Theo dõi tiến độ học tập của học viên và đưa ra phương pháp cải thiện</li>
<li>Tham gia các buổi training nâng cao nghiệp vụ</li>
<li>Phối hợp với bộ phận Academic để phát triển chương trình</li>
</ul>',
    '<h4>Yêu cầu ứng viên:</h4>
<ul>
<li>IELTS 7.5+ hoặc tương đương</li>
<li>Tốt nghiệp Đại học chuyên ngành Sư phạm Anh hoặc Ngôn ngữ Anh</li>
<li>Có ít nhất 2 năm kinh nghiệm giảng dạy IELTS</li>
<li>Kỹ năng giao tiếp tốt, nhiệt tình và yêu thích công việc giảng dạy</li>
<li>Có khả năng làm việc theo team</li>
</ul>',
    '<h4>Quyền lợi:</h4>
<ul>
<li>Mức lương cạnh tranh: 15-25 triệu/tháng</li>
<li>Thưởng theo hiệu quả giảng dạy</li>
<li>Được đào tạo nâng cao chuyên môn</li>
<li>Môi trường làm việc chuyên nghiệp, năng động</li>
<li>Cơ hội thăng tiến lên vị trí quản lý</li>
<li>Bảo hiểm xã hội, bảo hiểm y tế đầy đủ</li>
</ul>',
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    1,
    1
),
(
    'Trợ giảng Tiếng Anh',
    'tro-giang-tieng-anh',
    'Giảng dạy',
    'TP. Hồ Chí Minh',
    'part-time',
    '5-8 triệu',
    'Không yêu cầu',
    '<h4>Mô tả công việc:</h4>
<ul>
<li>Hỗ trợ giảng viên trong quá trình giảng dạy</li>
<li>Chấm bài, sửa bài cho học viên</li>
<li>Giải đáp thắc mắc của học viên ngoài giờ học</li>
<li>Chuẩn bị tài liệu, dụng cụ học tập</li>
</ul>',
    '<h4>Yêu cầu ứng viên:</h4>
<ul>
<li>Sinh viên năm 3, 4 ngành Sư phạm Anh hoặc Ngôn ngữ Anh</li>
<li>IELTS 6.5+ hoặc tương đương</li>
<li>Nhiệt tình, chịu khó, có tinh thần trách nhiệm</li>
<li>Có thể làm việc tối thiểu 3 buổi/tuần</li>
</ul>',
    '<h4>Quyền lợi:</h4>
<ul>
<li>Thu nhập: 5-8 triệu/tháng</li>
<li>Được đào tạo kỹ năng giảng dạy</li>
<li>Cơ hội trở thành giảng viên chính thức</li>
<li>Học bổng học tiếng Anh miễn phí</li>
<li>Giờ làm việc linh hoạt</li>
</ul>',
    DATE_ADD(NOW(), INTERVAL 60 DAY),
    1,
    0
),
(
    'Nhân viên Tư vấn Tuyển sinh',
    'nhan-vien-tu-van-tuyen-sinh',
    'Kinh doanh',
    'TP. Hồ Chí Minh',
    'full-time',
    '10-18 triệu',
    'Tối thiểu 1 năm',
    '<h4>Mô tả công việc:</h4>
<ul>
<li>Tư vấn khóa học phù hợp cho học viên</li>
<li>Tiếp nhận và xử lý đăng ký học</li>
<li>Chăm sóc khách hàng trước và sau khi đăng ký</li>
<li>Đạt KPI doanh số được giao</li>
<li>Tham gia các sự kiện marketing của trung tâm</li>
</ul>',
    '<h4>Yêu cầu ứng viên:</h4>
<ul>
<li>Tốt nghiệp Cao đẳng trở lên</li>
<li>Có ít nhất 1 năm kinh nghiệm tư vấn bán hàng/dịch vụ</li>
<li>Kỹ năng giao tiếp, thuyết phục tốt</li>
<li>Ngoại hình khá, giọng nói dễ nghe</li>
<li>Có tinh thần cầu tiến và chịu được áp lực</li>
</ul>',
    '<h4>Quyền lợi:</h4>
<ul>
<li>Lương cơ bản + hoa hồng hấp dẫn: 10-18 triệu/tháng</li>
<li>Thưởng doanh số không giới hạn</li>
<li>Được đào tạo kỹ năng bán hàng chuyên nghiệp</li>
<li>Môi trường làm việc trẻ trung, năng động</li>
<li>Team building, du lịch hàng năm</li>
<li>Đầy đủ bảo hiểm theo quy định</li>
</ul>',
    DATE_ADD(NOW(), INTERVAL 45 DAY),
    1,
    1
);
