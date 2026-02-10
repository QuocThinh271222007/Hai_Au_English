<?php
$pageTitle = 'Giới thiệu - Hải Âu English';
$currentPage = 'about';
$additionalCss = ['/frontend/css/pages/about.css'];

// Load dynamic content from database
include __DIR__ . '/../components/content_helper.php';
$content = getSiteContent('about');
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
                        <?php echo c('about', 'hero', 'title', 'Về Hải Âu English'); ?>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('about', 'hero', 'subtitle', 'Trung tâm đào tạo IELTS hàng đầu với hơn 10 năm kinh nghiệm'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Story Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="story-content">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                            <?php echo c('about', 'story', 'title', 'Câu chuyện của chúng tôi'); ?>
                        </h2>
                        <p class="text-gray-600 mb-4 leading-relaxed">
                            <?php echo c('about', 'story', 'paragraph1', 'Hải Âu English được thành lập năm 2016 với sứ mệnh giúp học viên Việt Nam chinh phục chứng chỉ IELTS một cách hiệu quả và bền vững. Chúng tôi tin rằng mỗi học viên đều có tiềm năng đạt được mục tiêu của mình với phương pháp học tập phù hợp.'); ?>
                        </p>
                        <p class="text-gray-600 mb-4 leading-relaxed">
                            <?php echo c('about', 'story', 'paragraph2', 'Qua hơn 10 năm hoạt động, chúng tôi đã đào tạo hơn 5000+ học viên thành công với tỷ lệ đạt mục tiêu 98%. Đội ngũ giảng viên của chúng tôi đều có chứng chỉ IELTS 8.0+ và nhiều năm kinh nghiệm giảng dạy.'); ?>
                        </p>
                        <p class="text-gray-600 leading-relaxed">
                            <?php echo c('about', 'story', 'paragraph3', 'Chúng tôi không ngừng cải tiến phương pháp giảng dạy, cập nhật tài liệu và áp dụng công nghệ hiện đại để mang đến trải nghiệm học tập tốt nhất cho học viên.'); ?>
                        </p>
                    </div>
                    <div class="story-image">
                        <?php 
                        $storyImage = c('about', 'story', 'image', '');
                        $storyImageSrc = $storyImage ? $basePath . $storyImage : $assetsPath . '/assets/images/places/z7459977818398_bc96fddb5796f8e10fa9d6f4d25ff4d9.jpg';
                        ?>
                        <img src="<?php echo $storyImageSrc; ?>" 
                             alt="Cơ sở Hải Âu English" 
                             class="rounded-2xl shadow-xl">
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="mission-card bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="mission-icon bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4"><?php echo c('about', 'mission', 'title', 'Sứ mệnh'); ?></h3>
                        <p class="text-gray-600 leading-relaxed">
                            <?php echo c('about', 'mission', 'description', 'Giúp mỗi học viên tự tin chinh phục IELTS và mở ra cơ hội học tập, làm việc quốc tế thông qua phương pháp giảng dạy hiệu quả, đội ngũ giảng viên chất lượng cao và môi trường học tập chuyên nghiệp.'); ?>
                        </p>
                    </div>
                    <div class="vision-card bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="vision-icon bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4"><?php echo c('about', 'vision', 'title', 'Tầm nhìn'); ?></h3>
                        <p class="text-gray-600 leading-relaxed">
                            <?php echo c('about', 'vision', 'description', 'Trở thành trung tâm đào tạo IELTS số 1 Việt Nam, được công nhận quốc tế với chất lượng giảng dạy xuất sắc, đóng góp vào việc nâng cao trình độ tiếng Anh của người Việt và kết nối họ với thế giới.'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cơ sở vật chất Gallery -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('about', 'facilities', 'title', 'Cơ sở vật chất'); ?>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('about', 'facilities', 'subtitle', 'Không gian học tập hiện đại, thoải mái và đầy cảm hứng'); ?>
                    </p>
                </div>

                <?php
                // Default facility images
                $defaultImages = [
                    '/assets/images/places/z7459974109800_8dcc878754cddd1da2a2dbe61385527d.jpg',
                    '/assets/images/places/z7459977810848_5e453152d0061eb2d753a253cbb33926.jpg',
                    '/assets/images/places/z7459977818398_bc96fddb5796f8e10fa9d6f4d25ff4d9.jpg',
                    '/assets/images/places/z7459977840942_4799faba7f550c631fdafc1de17314d1.jpg',
                    '/assets/images/places/z7459977841986_24c59a2648310f4c1e7df59e2e269137.jpg',
                    '/assets/images/places/z7459977845938_345c8dd94e8a02596f8252329ab8f519.jpg',
                    '/assets/images/places/z7459977863545_2f8045b8a254ae613ea1501ae1e0a38e.jpg'
                ];
                $imageAlts = ['Cơ sở Hải Âu English', 'Phòng học Hải Âu English', 'Không gian học tập', 'Lớp học IELTS', 'Khu vực tự học', 'Phòng chờ', 'Toàn cảnh cơ sở'];
                ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                    <?php
                    $facilityImg = c('about', 'facilities', 'image' . $i, '');
                    $imgSrc = $facilityImg ? $basePath . $facilityImg : $assetsPath . $defaultImages[$i - 1];
                    ?>
                    <div class="overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-shadow">
                        <img src="<?php echo $imgSrc; ?>" 
                             alt="<?php echo $imageAlts[$i - 1]; ?>" 
                             class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <?php endfor; ?>
                    <?php
                    $facilityImg7 = c('about', 'facilities', 'image7', '');
                    $imgSrc7 = $facilityImg7 ? $basePath . $facilityImg7 : $assetsPath . $defaultImages[6];
                    ?>
                    <div class="overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-shadow col-span-2">
                        <img src="<?php echo $imgSrc7; ?>" 
                             alt="Toàn cảnh cơ sở" 
                             class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Giá trị cốt lõi
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        Những giá trị định hình nên văn hóa và chất lượng của chúng tôi
                    </p>
                    <div class="gradient_line"></div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="value-card text-center p-6">
                        <div class="value-icon bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Chất lượng</h3>
                        <p class="text-gray-600">
                            Cam kết chất lượng giảng dạy cao nhất với đội ngũ giảng viên xuất sắc
                        </p>
                    </div>

                    <div class="value-card text-center p-6">
                        <div class="value-icon bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Tận tâm</h3>
                        <p class="text-gray-600">
                            Luôn đặt sự thành công của học viên lên hàng đầu
                        </p>
                    </div>

                    <div class="value-card text-center p-6">
                        <div class="value-icon bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Đổi mới</h3>
                        <p class="text-gray-600">
                            Không ngừng cải tiến phương pháp và công nghệ giảng dạy
                        </p>
                    </div>

                    <div class="value-card text-center p-6">
                        <div class="value-icon bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Trách nhiệm</h3>
                        <p class="text-gray-600">
                            Trách nhiệm với kết quả học tập của từng học viên
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">
                        Con số ấn tượng
                    </h2>
                    <p class="text-lg text-blue-100 max-w-3xl mx-auto">
                        Những thành tích đáng tự hào của chúng tôi
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="stat-card text-center">
                        <div class="stat-number text-5xl font-bold mb-2">5000+</div>
                        <div class="stat-label text-blue-100">Học viên đã tin tưởng</div>
                    </div>
                    <div class="stat-card text-center">
                        <div class="stat-number text-5xl font-bold mb-2">98%</div>
                        <div class="stat-label text-blue-100">Tỷ lệ đạt mục tiêu</div>
                    </div>
                    <div class="stat-card text-center">
                        <div class="stat-number text-5xl font-bold mb-2">50+</div>
                        <div class="stat-label text-blue-100">Giảng viên 8.0+</div>
                    </div>
                    <div class="stat-card text-center">
                        <div class="stat-number text-5xl font-bold mb-2">10+</div>
                        <div class="stat-label text-blue-100">Năm kinh nghiệm</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Hành trình phát triển
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        Những cột mốc quan trọng trong lịch sử của Hải Âu English
                    </p>
                </div>

                <div class="timeline-container">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-year"><?php echo c('about', 'timeline', 'year1', '2016'); ?></div>
                            <h3 class="timeline-title"><?php echo c('about', 'timeline', 'title1', 'Thành lập'); ?></h3>
                            <p class="timeline-description">
                                <?php echo c('about', 'timeline', 'desc1', 'Hải Âu English chính thức ra đời với 5 giảng viên đầu tiên và cơ sở đầu tiên tại Quận 1, TP.HCM'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-year"><?php echo c('about', 'timeline', 'year2', '2018'); ?></div>
                            <h3 class="timeline-title"><?php echo c('about', 'timeline', 'title2', 'Mở rộng quy mô'); ?></h3>
                            <p class="timeline-description">
                                <?php echo c('about', 'timeline', 'desc2', 'Mở thêm 2 cơ sở mới tại Hà Nội và Đà Nẵng, đội ngũ giảng viên tăng lên 20 người'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-year"><?php echo c('about', 'timeline', 'year3', '2020'); ?></div>
                            <h3 class="timeline-title"><?php echo c('about', 'timeline', 'title3', 'Chuyển đổi số'); ?></h3>
                            <p class="timeline-description">
                                <?php echo c('about', 'timeline', 'desc3', 'Ra mắt nền tảng học tập trực tuyến, phát triển ứng dụng di động hỗ trợ học IELTS'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-year"><?php echo c('about', 'timeline', 'year4', '2023'); ?></div>
                            <h3 class="timeline-title"><?php echo c('about', 'timeline', 'title4', 'Đạt mốc 5000 học viên'); ?></h3>
                            <p class="timeline-description">
                                <?php echo c('about', 'timeline', 'desc4', 'Đạt cột mốc 5000+ học viên thành công với tỷ lệ đạt mục tiêu 98%'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-year"><?php echo c('about', 'timeline', 'year5', '2026'); ?></div>
                            <h3 class="timeline-title"><?php echo c('about', 'timeline', 'title5', 'Mở rộng quốc tế'); ?></h3>
                            <p class="timeline-description">
                                <?php echo c('about', 'timeline', 'desc5', 'Hợp tác với các trung tâm IELTS quốc tế, mở rộng thị trường ra khu vực Đông Nam Á'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Sẵn sàng bắt đầu hành trình IELTS?
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Hãy để chúng tôi đồng hành cùng bạn chinh phục mục tiêu IELTS
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo $paths['contact']; ?>" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        Đăng ký học thử miễn phí
                    </a>
                    <a href="<?php echo $paths['courses']; ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors border-2 border-blue-600">
                        Xem các khóa học
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
    <script src="<?php echo $assetsPath; ?>/js/controllers/main.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/headerAuth.js"></script>
</body>
</html>
