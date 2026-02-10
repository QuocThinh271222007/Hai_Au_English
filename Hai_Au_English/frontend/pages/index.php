<?php
$pageTitle = 'Hải Âu English - Trang chủ';
$currentPage = 'home';
$additionalCss = ['/frontend/css/pages/reviews-achievements.css'];

// Load dynamic content from database
include __DIR__ . '/../components/content_helper.php';
$content = getSiteContent('home');
?>
<?php include __DIR__ . '/../components/head.php'; ?>
<body class="min-h-screen bg-white">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="pt-16 bg-gradient-to-br from-blue-50 to-indigo-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 md:py-28" style="padding-top: 5rem; padding-bottom: 3rem;">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                            <?php echo c('home', 'hero', 'title', 'Chinh phục IELTS'); ?>
                            <span class="text-blue-600"><?php echo c('home', 'hero', 'title_highlight', '8.0+'); ?></span>
                        </h1>
                        <p class="text-lg md:text-xl text-gray-600 mb-8">
                            <?php echo c('home', 'hero', 'description', 'Phương pháp học tập hiệu quả với đội ngũ giảng viên chứng chỉ 8.0+, cam kết đầu ra và học lại miễn phí nếu không đạt mục tiêu.'); ?>
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="<?php echo $paths['contact']; ?>" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors text-center font-bold">
                                <?php echo c('home', 'hero', 'cta_primary', 'Đăng ký học thử miễn phí'); ?>
                            </a>
                            <a href="<?php echo $paths['courses']; ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors border-2 border-blue-600 text-center font-bold">
                                <?php echo c('home', 'hero', 'cta_secondary', 'Xem khóa học'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="relative">
                        <?php 
                        $heroImage = c('home', 'hero', 'image1', '');
                        // Nếu có ảnh upload, dùng basePath + ảnh. Nếu không, dùng ảnh mặc định
                        if (!empty($heroImage)) {
                            $heroImagePath = $basePath . $heroImage;
                        } else {
                            $heroImagePath = $assetsPath . '/assets/images/places/z7459977810848_5e453152d0061eb2d753a253cbb33926.jpg';
                        }
                        ?>
                        <img src="<?php echo $heroImagePath; ?>" 
                             alt="Cơ sở Hải Âu English" 
                             class="rounded-2xl shadow-2xl">
                        <div class="stats-box absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="stats-icon bg-blue-100 p-3 rounded-lg">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="stats-number text-2xl font-bold text-gray-900"><?php echo c('home', 'hero', 'stat_number', '1000+'); ?></p>
                                    <p class="stats-label text-gray-600"><?php echo c('home', 'hero', 'stat_label', 'Học viên đạt 7.0+'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('home', 'about', 'title', 'Về Hải Âu English'); ?>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('home', 'about', 'description', 'Trung tâm đào tạo IELTS hàng đầu với phương pháp giảng dạy độc quyền và đội ngũ giảng viên chất lượng cao'); ?>
                    </p>
                    <!-- Gradient Line -->
                    <div class="gradient_line"></div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center p-6 rounded-xl hover:shadow-lg transition-shadow border-2" style="border-color: rgb(197, 223, 255);">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo c('home', 'stats', 'stat1_number', '5000+'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'stats', 'stat1_label', 'Học viên đã tin tưởng'); ?></p>
                    </div>

                    <div class="text-center p-6 rounded-xl hover:shadow-lg transition-shadow border-2" style="border-color: rgb(197, 223, 255);">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo c('home', 'stats', 'stat2_number', '98%'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'stats', 'stat2_label', 'Tỷ lệ đạt mục tiêu'); ?></p>
                    </div>

                    <div class="text-center p-6 rounded-xl hover:shadow-lg transition-shadow border-2" style="border-color: rgb(197, 223, 255);">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo c('home', 'stats', 'stat3_number', '50+'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'stats', 'stat3_label', 'Giảng viên 8.0+'); ?></p>
                    </div>

                    <div class="text-center p-6 rounded-xl hover:shadow-lg transition-shadow border-2" style="border-color: rgb(197, 223, 255);">
                        <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo c('home', 'stats', 'stat4_number', '10+'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'stats', 'stat4_label', 'Năm kinh nghiệm'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="index_feature_section bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('home', 'why_choose', 'title', 'Vì sao chọn chúng tôi?'); ?>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('home', 'why_choose', 'subtitle', 'Những lợi ích vượt trội khi học tại Hải Âu English'); ?>
                    </p>
                    <div class="gradient_line"></div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 feature_grid_section">
                    <div class="feature-card bg-white p-8 rounded-xl">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon bg-blue-100">
                                <svg class="text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo c('home', 'why_choose', 'item1_title', 'Giáo trình độc quyền'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'why_choose', 'item1_desc', 'Tài liệu học tập được biên soạn bởi đội ngũ giảng viên 8.5+ với kinh nghiệm lâu năm'); ?></p>
                    </div>

                    <div class="feature-card bg-white p-8 rounded-xl">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon bg-green-100">
                                <svg class="text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo c('home', 'why_choose', 'item2_title', 'Lớp học nhỏ'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'why_choose', 'item2_desc', 'Tối đa 8-10 học viên/lớp để đảm bảo chất lượng giảng dạy và chăm sóc cá nhân'); ?></p>
                    </div>

                    <div class="feature-card bg-white p-8 rounded-xl">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon bg-purple-100">
                                <svg class="text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo c('home', 'why_choose', 'item3_title', 'Cam kết đầu ra'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'why_choose', 'item3_desc', 'Cam kết đầu ra rõ ràng, học lại miễn phí nếu không đạt mục tiêu'); ?></p>
                    </div>

                    <div class="feature-card bg-white p-8 rounded-xl">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon bg-orange-100">
                                <svg class="text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo c('home', 'why_choose', 'item4_title', 'Lộ trình cá nhân hóa'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'why_choose', 'item4_desc', 'Xây dựng lộ trình học tập riêng phù hợp với trình độ và mục tiêu của từng học viên'); ?></p>
                    </div>

                    <div class="feature-card bg-white p-8 rounded-xl">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon bg-red-100">
                                <svg class="text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo c('home', 'why_choose', 'item5_title', 'Học liệu đa dạng'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'why_choose', 'item5_desc', 'Tài liệu phong phú từ sách giáo trình đến video bài giảng và bài tập online'); ?></p>
                    </div>

                    <div class="feature-card bg-white p-8 rounded-xl">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon bg-indigo-100">
                                <svg class="text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo c('home', 'why_choose', 'item6_title', 'Hỗ trợ 24/7'); ?></h3>
                        <p class="text-gray-600"><?php echo c('home', 'why_choose', 'item6_desc', 'Đội ngũ hỗ trợ học tập và giải đáp thắc mắc 24/7 qua nhiều kênh'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Student Achievements Section -->
        <section id="achievements-section">
            <div class="achievements-header">
                <h2>🏆 Thành tích học viên tiêu biểu</h2>
                <p>Những gương mặt xuất sắc đã chinh phục mục tiêu IELTS cùng Hải Âu English</p>
            </div>
            
            <div class="achievements-container">
                <div id="achievements-carousel">
                    <!-- Achievements will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <section id="reviews-section">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="reviews-header">
                    <h2>💬 Đánh giá từ học viên</h2>
                    <p>Hàng nghìn học viên đã tin tưởng và đạt được mục tiêu cùng chúng tôi</p>
                    
                    <div class="reviews-stats">
                        <div class="reviews-stat-item">
                            <div class="stat-value" id="reviews-avg-rating">5.0</div>
                            <div class="stat-stars">★★★★★</div>
                            <div class="stat-label">Đánh giá trung bình</div>
                        </div>
                        <div class="reviews-stat-item">
                            <div class="stat-value" id="reviews-total">0</div>
                            <div class="stat-label">Lượt đánh giá</div>
                        </div>
                    </div>
                </div>
                
                <div class="reviews-container">
                    <div id="reviews-carousel">
                        <!-- Reviews will be loaded here -->
                    </div>
                </div>
                
                <!-- Review Form -->
                <div class="review-form-section">
                    <h3 class="review-form-title">Chia sẻ trải nghiệm của bạn</h3>
                    
                    <!-- Login Prompt (hiển thị khi chưa đăng nhập) -->
                    <div id="review-login-prompt" class="login-prompt">
                        <p>Bạn cần đăng nhập để có thể đánh giá</p>
                        <a href="<?php echo $paths['login']; ?>">Đăng nhập ngay</a>
                    </div>
                    
                    <!-- Review Form (hiển thị khi đã đăng nhập) -->
                    <div id="review-form-container" class="hidden">
                        <form id="review-form">
                            <input type="hidden" id="review-rating" value="0">
                            
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
                                id="review-comment" 
                                class="review-textarea" 
                                placeholder="Chia sẻ trải nghiệm học tập của bạn tại Hải Âu English..."
                                required
                                minlength="10"
                                maxlength="1000"
                            ></textarea>
                            
                            <!-- Image Upload -->
                            <div class="review-image-upload">
                                <label class="image-upload-label" for="review-image">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Thêm ảnh (tùy chọn)</span>
                                </label>
                                <input type="file" id="review-image" accept="image/*" class="hidden">
                                
                                <div id="image-preview-container" class="hidden">
                                    <img id="image-preview" src="" alt="Preview">
                                    <button type="button" class="remove-image-btn" onclick="window.reviewsController?.removeImagePreview()">×</button>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" id="review-submit-btn" class="review-submit-btn">
                                Gửi đánh giá
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php include __DIR__ . '/../components/floating-contact.php'; ?>
    <?php include __DIR__ . '/../components/scroll-to-top.php'; ?>

    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/animations/uiAnimations.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/main.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/headerAuth.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/achievements.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/reviews.js"></script>
</body>
</html>
