<?php
$pageTitle = 'Đội ngũ giảng viên - Hải Âu English';
$currentPage = 'teachers';
$additionalCss = ['css/pages/teachers.css', 'css/pages/reviews-achievements.css'];

// Load dynamic content from database
include __DIR__ . '/../components/content_helper.php';
$content = getSiteContent('teachers');
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
                        <?php echo c('teachers', 'hero', 'title', 'Đội ngũ giảng viên'); ?>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('teachers', 'hero', 'subtitle', 'Giảng viên chứng chỉ 8.0+ với nhiều năm kinh nghiệm giảng dạy'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Team Stats Section -->
        <section class="py-12 bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo c('teachers', 'stats', 'stat1_number', '50+'); ?></div>
                        <div class="text-gray-600"><?php echo c('teachers', 'stats', 'stat1_label', 'Giảng viên'); ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo c('teachers', 'stats', 'stat2_number', '8.5+'); ?></div>
                        <div class="text-gray-600"><?php echo c('teachers', 'stats', 'stat2_label', 'Điểm TB IELTS'); ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo c('teachers', 'stats', 'stat3_number', '10+'); ?></div>
                        <div class="text-gray-600"><?php echo c('teachers', 'stats', 'stat3_label', 'Năm kinh nghiệm'); ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2"><?php echo c('teachers', 'stats', 'stat4_number', '100%'); ?></div>
                        <div class="text-gray-600"><?php echo c('teachers', 'stats', 'stat4_label', 'Được đào tạo'); ?></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Teachers Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('teachers', 'featured', 'title', 'Giảng viên nổi bật'); ?>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('teachers', 'featured', 'subtitle', 'Những giảng viên xuất sắc của Hải Âu English'); ?>
                    </p>
                    <div class="gradient_line"></div>
                </div>

                <!-- Dynamic Teachers Grid - loaded from database -->
                <div id="teachers-grid" class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Teachers will be loaded dynamically by teachers.js -->
                    <div class="col-span-full text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Đang tải danh sách giảng viên...</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Qualifications Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('teachers', 'qualifications', 'title', 'Tiêu chuẩn giảng viên'); ?>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('teachers', 'qualifications', 'subtitle', 'Chúng tôi đặt ra những tiêu chuẩn cao cho đội ngũ giảng viên'); ?>
                    </p>
                    <div class="gradient_line"></div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="qualification-card">
                        <div class="qualification-icon bg-blue-100">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="qualification-title"><?php echo c('teachers', 'qualifications', 'qual1_title', 'Chứng chỉ IELTS 8.0+'); ?></h3>
                        <p class="qualification-description">
                            <?php echo c('teachers', 'qualifications', 'qual1_desc', 'Tất cả giảng viên đều có chứng chỉ IELTS 8.0 trở lên, đảm bảo trình độ tiếng Anh xuất sắc'); ?>
                        </p>
                    </div>

                    <div class="qualification-card">
                        <div class="qualification-icon bg-green-100">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="qualification-title"><?php echo c('teachers', 'qualifications', 'qual2_title', 'Kinh nghiệm giảng dạy'); ?></h3>
                        <p class="qualification-description">
                            <?php echo c('teachers', 'qualifications', 'qual2_desc', 'Tối thiểu 3 năm kinh nghiệm giảng dạy IELTS với hồ sơ học viên thành công rõ ràng'); ?>
                        </p>
                    </div>

                    <div class="qualification-card">
                        <div class="qualification-icon bg-purple-100">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="qualification-title"><?php echo c('teachers', 'qualifications', 'qual3_title', 'Đào tạo chuyên sâu'); ?></h3>
                        <p class="qualification-description">
                            <?php echo c('teachers', 'qualifications', 'qual3_desc', 'Được đào tạo về phương pháp giảng dạy hiện đại và kỹ năng sư phạm chuyên nghiệp'); ?>
                        </p>
                    </div>

                    <div class="qualification-card">
                        <div class="qualification-icon bg-orange-100">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="qualification-title"><?php echo c('teachers', 'qualifications', 'qual4_title', 'Kỹ năng giao tiếp'); ?></h3>
                        <p class="qualification-description">
                            <?php echo c('teachers', 'qualifications', 'qual4_desc', 'Khả năng truyền đạt kiến thức hiệu quả, tạo động lực và kết nối với học viên'); ?>
                        </p>
                    </div>

                    <div class="qualification-card">
                        <div class="qualification-icon bg-red-100">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="qualification-title"><?php echo c('teachers', 'qualifications', 'qual5_title', 'Cập nhật liên tục'); ?></h3>
                        <p class="qualification-description">
                            <?php echo c('teachers', 'qualifications', 'qual5_desc', 'Thường xuyên cập nhật xu hướng thi, đề thi mới và phương pháp giảng dạy hiện đại'); ?>
                        </p>
                    </div>

                    <div class="qualification-card">
                        <div class="qualification-icon bg-indigo-100">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="qualification-title"><?php echo c('teachers', 'qualifications', 'qual6_title', 'Tâm huyết với nghề'); ?></h3>
                        <p class="qualification-description">
                            <?php echo c('teachers', 'qualifications', 'qual6_desc', 'Yêu thích giảng dạy, luôn đặt sự thành công của học viên lên hàng đầu'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Teacher Reviews Section (similar to index reviews) -->
        <section id="teacher-reviews-section" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="reviews-header">
                    <h2>💬 <?php echo c('teachers', 'testimonials', 'title', 'Học viên nói gì về giảng viên'); ?></h2>
                    <p><?php echo c('teachers', 'testimonials', 'subtitle', 'Đánh giá chân thực từ học viên về chất lượng giảng dạy'); ?></p>
                    
                    <div class="reviews-stats">
                        <div class="reviews-stat-item">
                            <div class="stat-value" id="teacher-reviews-avg-rating">5.0</div>
                            <div class="stat-stars">★★★★★</div>
                            <div class="stat-label">Đánh giá trung bình</div>
                        </div>
                        <div class="reviews-stat-item">
                            <div class="stat-value" id="teacher-reviews-total">0</div>
                            <div class="stat-label">Lượt đánh giá</div>
                        </div>
                    </div>
                </div>
                
                <div class="reviews-container">
                    <div id="teacher-reviews-carousel">
                        <!-- Reviews will be loaded here by JS -->
                        <div class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <p class="mt-2 text-gray-600">Đang tải đánh giá...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Teacher Review Form -->
                <div class="review-form-section">
                    <h3 class="review-form-title">Chia sẻ đánh giá của bạn về giảng viên</h3>
                    
                    <!-- Login Prompt (hiển thị khi chưa đăng nhập) -->
                    <div id="teacher-review-login-prompt" class="login-prompt">
                        <p>Bạn cần đăng nhập để có thể đánh giá giảng viên</p>
                        <a href="<?php echo $paths['login']; ?>">Đăng nhập ngay</a>
                    </div>
                    
                    <!-- Review Form (hiển thị khi đã đăng nhập) -->
                    <div id="teacher-review-form-container" class="hidden">
                        <form id="teacher-review-form">
                            <input type="hidden" id="teacher-review-rating" value="0">
                            
                            <!-- Star Rating -->
                            <div class="star-rating-input">
                                <button type="button" class="star-btn" data-rating="1">☆</button>
                                <button type="button" class="star-btn" data-rating="2">☆</button>
                                <button type="button" class="star-btn" data-rating="3">☆</button>
                                <button type="button" class="star-btn" data-rating="4">☆</button>
                                <button type="button" class="star-btn" data-rating="5">☆</button>
                            </div>
                            
                            <!-- Comment -->
                            <textarea 
                                id="teacher-review-comment" 
                                class="review-textarea" 
                                placeholder="Chia sẻ trải nghiệm của bạn về giảng viên tại Hải Âu English..."
                                required
                                minlength="10"
                                maxlength="1000"
                            ></textarea>
                            
                            <!-- Submit Button -->
                            <button type="submit" id="teacher-review-submit-btn" class="review-submit-btn">
                                Gửi đánh giá
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    <?php echo c('teachers', 'cta', 'title', 'Học với đội ngũ giảng viên xuất sắc'); ?>
                </h2>
                <p class="text-lg text-blue-100 mb-8">
                    <?php echo c('teachers', 'cta', 'subtitle', 'Đăng ký ngay để được tư vấn và sắp xếp lớp học phù hợp'); ?>
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo $paths['contact']; ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                        Đăng ký tư vấn
                    </a>
                    <a href="<?php echo $paths['courses']; ?>" class="border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-medium">
                        Xem khóa học
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php include __DIR__ . '/../components/floating-contact.php'; ?>
    <?php include __DIR__ . '/../components/scroll-to-top.php'; ?>

    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/animations/uiAnimations.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/teachers.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/teacherReviews.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/main.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/headerAuth.js"></script>
</body>
</html>
