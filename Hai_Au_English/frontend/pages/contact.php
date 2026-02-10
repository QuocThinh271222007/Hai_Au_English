<?php
$pageTitle = 'Liên hệ - Hải Âu English';
$currentPage = 'contact';
$additionalCss = ['css/pages/contact.css'];

// Load dynamic content from database
include __DIR__ . '/../components/content_helper.php';
$content = getSiteContent('contact');
?>
<?php include __DIR__ . '/../components/head.php'; ?>
<body class="min-h-screen bg-white">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="pt-40 pb-12 bg-gradient-to-br from-blue-50 to-indigo-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                        <?php echo c('contact', 'hero', 'title', 'Liên hệ với chúng tôi'); ?>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('contact', 'hero', 'subtitle', 'Chúng tôi sẵn sàng tư vấn và hỗ trợ bạn 24/7'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact Form & Info Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Contact Form -->
                    <div class="contact-form-container">
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">
                            <?php echo c('contact', 'form', 'title', 'ĐĂNG KÝ HỌC/TƯ VẤN'); ?>
                        </h2>
                        <p class="text-gray-600 mb-8">
                            <?php echo c('contact', 'form', 'subtitle', 'Điền thông tin và chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ'); ?>
                        </p>

                        <form id="contact-form" class="space-y-6">
                            <div>
                                <label for="fullname" class="form-label">
                                    Họ và tên <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="fullname" 
                                    name="fullname"
                                    class="form-input"
                                    placeholder="Nguyễn Văn A"
                                    required>
                                <span class="error-message" id="fullname-error"></span>
                            </div>

                            <div>
                                <label for="email" class="form-label">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email"
                                    class="form-input"
                                    placeholder="example@email.com"
                                    required>
                                <span class="error-message" id="email-error"></span>
                            </div>

                            <div>
                                <label for="phone" class="form-label">
                                    Số điện thoại <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone"
                                    class="form-input"
                                    placeholder="0901234567"
                                    required>
                                <span class="error-message" id="phone-error"></span>
                            </div>

                            <div>
                                <label for="course" class="form-label">
                                    Khóa học quan tâm <span class="text-red-500">*</span>
                                </label>
                                <select id="course" name="course" class="form-input" required>
                                    <option value="">Chọn khóa học</option>
                                    <?php
                                    // Load dynamic course options from content (support up to 20 courses)
                                    $hasOptions = false;
                                    for ($i = 1; $i <= 20; $i++) {
                                        $value = c('contact', 'courses', "course{$i}_value", '');
                                        $label = c('contact', 'courses', "course{$i}_label", '');
                                        if (!empty($value) && !empty($label)) {
                                            echo "<option value=\"" . htmlspecialchars($value) . "\">" . htmlspecialchars($label) . "</option>\n";
                                            $hasOptions = true;
                                        }
                                    }
                                    // Fallback to default options if none configured
                                    if (!$hasOptions) {
                                        echo '<option value="foundation">IELTS Foundation</option>';
                                        echo '<option value="intermediate">IELTS Intermediate</option>';
                                        echo '<option value="advanced">IELTS Advanced</option>';
                                        echo '<option value="1on1">IELTS 1-1 Cá nhân</option>';
                                        echo '<option value="online">IELTS Online</option>';
                                        echo '<option value="weekend">IELTS Weekend</option>';
                                    }
                                    ?>
                                </select>
                                <span class="error-message" id="course-error"></span>
                            </div>

                            <div>
                                <label for="level" class="form-label">
                                    Trình độ hiện tại
                                </label>
                                <select id="level" name="level" class="form-input">
                                    <option value="">Chọn trình độ</option>
                                    <?php
                                    // Load dynamic level options from content (support up to 15 levels)
                                    $hasLevels = false;
                                    for ($i = 1; $i <= 15; $i++) {
                                        $value = c('contact', 'levels', "level{$i}_value", '');
                                        $label = c('contact', 'levels', "level{$i}_label", '');
                                        if (!empty($value) && !empty($label)) {
                                            echo "<option value=\"" . htmlspecialchars($value) . "\">" . htmlspecialchars($label) . "</option>\n";
                                            $hasLevels = true;
                                        }
                                    }
                                    // Fallback to default options if none configured
                                    if (!$hasLevels) {
                                        echo '<option value="beginner">Mới bắt đầu (3.0-4.5)</option>';
                                        echo '<option value="intermediate">Trung cấp (5.0-6.0)</option>';
                                        echo '<option value="advanced">Nâng cao (6.5-7.0)</option>';
                                        echo '<option value="expert">Chuyên sâu (7.5+)</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label for="message" class="form-label">
                                    Lời nhắn
                                </label>
                                <textarea 
                                    id="message" 
                                    name="message"
                                    rows="4"
                                    class="form-input"
                                    placeholder="Nhập lời nhắn của bạn..."></textarea>
                            </div>

                            <div class="flex items-start">
                                <input 
                                    type="checkbox" 
                                    id="agreement" 
                                    name="agreement"
                                    class="mt-1 mr-2"
                                    required>
                                <label for="agreement" class="text-sm text-gray-600">
                                    Tôi đồng ý với <a href="#" class="text-blue-600 hover:underline">chính sách bảo mật</a> 
                                    và <a href="#" class="text-blue-600 hover:underline">điều khoản sử dụng</a> của Hải Âu English
                                </label>
                            </div>

                            <button type="submit" class="submit-button">
                                <span class="button-text">Gửi thông tin</span>
                                <span class="button-loader hidden">
                                    <span class="spinner"></span>
                                    Đang gửi...
                                </span>
                            </button>

                            <div id="form-message" class="hidden"></div>
                        </form>

                        <!-- Social Links - Inside Form Container -->
                        <div class="social-links-section">
                            <div class="social-divider">
                                <span>Hoặc liên hệ qua</span>
                            </div>
                            <div class="social-links-row">
                                <a href="https://www.facebook.com/AnhNguHaiAu" target="_blank" class="social-btn social-btn-facebook" title="Facebook">
                                    <img src="<?php echo $assetsPath; ?>/assets/images/logo_fb.png" alt="Facebook">
                                    <span>Facebook</span>
                                </a>
                                <a href="https://zalo.me/0931828960" target="_blank" class="social-btn social-btn-zalo" title="Zalo">
                                    <img src="<?php echo $assetsPath; ?>/assets/images/logo_zalo.png" alt="Zalo">
                                    <span>Zalo</span>
                                </a>
                                <a href="tel:0931828960" class="social-btn social-btn-phone" title="Gọi điện">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
                                    </svg>
                                    <span>Hotline</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="contact-info-container">
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">
                            <?php echo c('contact', 'info', 'section_title', 'Thông tin liên hệ'); ?>
                        </h2>

                        <div class="space-y-6 mb-12">
                            <div class="info-item">
                                <div class="info-icon bg-blue-100">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="info-title">Địa chỉ</h3>
                                    <p class="info-content"><?php echo c('contact', 'info', 'address', '14/2A Trương Phước Phan, Phường Bình Trị Đông, Thành phố Hồ Chí Minh'); ?></p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon bg-green-100">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="info-title">Điện thoại</h3>
                                    <p class="info-content"><?php echo c('contact', 'info', 'phone', 'Mobile: 0931 828 960'); ?></p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon bg-purple-100">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="info-title">Email</h3>
                                    <p class="info-content"><?php echo c('contact', 'info', 'email', 'haiauenglish@gmail.com'); ?></p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon bg-orange-100">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="info-title">Giờ làm việc</h3>
                                    <p class="info-content"><?php echo c('contact', 'info', 'working_hours1', 'Thứ 2 - Thứ 6: 08h30 - 10h30, 14h00 - 16h00, 17h45 - 21h00'); ?></p>
                                    <p class="info-content"><?php echo c('contact', 'info', 'working_hours2', 'Thứ 7 - Chủ nhật: 07h30 - 11h45, 13h00 - 17h00'); ?></p>
                                </div>
                            </div>
                        </div>


                        <div class="map-container">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d692.8721848696737!2d106.61367816585437!3d10.774734376028718!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752dfb0b4a0beb%3A0x36355698f06e8f9!2sHAI%20AU%20ENGLISH!5e0!3m2!1svi!2s!4v1770299819477!5m2!1svi!2s"                                     
                                width="100%" 
                                height="500" 
                                style="border:0; min-height: 250px;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <!-- <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Vị trí trung tâm
                    </h2>
                    <p class="text-lg text-gray-600">
                        Tìm đường đến Hải Âu English
                    </p>
                    <div class="gradient_line"></div>
                </div>
            </div>
        </section> -->

        <!-- FAQ Section -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('contact', 'faq', 'title', 'Câu hỏi thường gặp'); ?>
                    </h2>
                    <p class="text-lg text-gray-600">
                        <?php echo c('contact', 'faq', 'subtitle', 'Giải đáp những thắc mắc phổ biến'); ?>
                    </p>
                    <div class="gradient_line"></div>
                </div>

                <div class="space-y-4">
                    <div class="faq-item">
                        <button class="faq-question">
                            <span><?php echo c('contact', 'faq', 'q1', 'Làm thế nào để đăng ký học tại Hải Âu English?'); ?></span>
                            <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>
                                <?php echo c('contact', 'faq', 'a1', 'Bạn có thể đăng ký qua form trên website này, gọi hotline 0931 828 960, hoặc đến trực tiếp trung tâm. Chúng tôi sẽ tư vấn chi tiết và sắp xếp lịch học thử miễn phí cho bạn.'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span><?php echo c('contact', 'faq', 'q2', 'Có được học thử miễn phí không?'); ?></span>
                            <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>
                                <?php echo c('contact', 'faq', 'a2', 'Có, tất cả học viên mới đều được học thử miễn phí 1 buổi. Bạn sẽ trải nghiệm phương pháp giảng dạy, gặp gỡ giảng viên và làm quen với môi trường học tập trước khi quyết định đăng ký.'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span><?php echo c('contact', 'faq', 'q3', 'Trung tâm có cơ sở nào khác không?'); ?></span>
                            <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>
                                <?php echo c('contact', 'faq', 'a3', 'Hiện tại chúng tôi đang có cơ sở tại quận Bình Tân, TP.HCM. Ngoài ra, chúng tôi cũng có các khóa học online để phục vụ học viên ở các tỉnh thành khác.'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question">
                            <span><?php echo c('contact', 'faq', 'q4', 'Thời gian phản hồi sau khi gửi form là bao lâu?'); ?></span>
                            <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>
                                <?php echo c('contact', 'faq', 'a4', 'Chúng tôi cam kết phản hồi trong vòng 24 giờ (giờ làm việc). Trong trường hợp khẩn cấp, bạn có thể gọi trực tiếp hotline hoặc nhắn tin qua Zalo/Facebook để được hỗ trợ nhanh hơn.'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php include __DIR__ . '/../components/floating-contact.php'; ?>
    <?php include __DIR__ . '/../components/scroll-to-top.php'; ?>

    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/animations/uiAnimations.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/contact.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/main.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/headerAuth.js"></script>
</body>
</html>
