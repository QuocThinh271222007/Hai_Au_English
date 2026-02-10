<?php
$pageTitle = 'Khóa học - Hải Âu English';
$currentPage = 'courses';
$additionalCss = ['css/pages/courses.css'];

// Load dynamic content from database
include __DIR__ . '/../components/content_helper.php';
$content = getSiteContent('courses');

// Load course fee items from database
function getCourseFeeItems() {
    try {
        require_once __DIR__ . '/../../backend/php/db.php';
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM course_fee_items WHERE is_active = 1 ORDER BY category, display_order ASC");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by category
        $grouped = ['tieuhoc' => [], 'thcs' => [], 'ielts' => []];
        foreach ($items as $item) {
            $grouped[$item['category']][] = $item;
        }
        return $grouped;
    } catch (Exception $e) {
        return ['tieuhoc' => [], 'thcs' => [], 'ielts' => []];
    }
}
$courseFeeItems = getCourseFeeItems();
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
                        <?php echo c('courses', 'hero', 'title', 'Chương trình đào tạo'); ?>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('courses', 'hero', 'subtitle', 'Lựa chọn khóa học phù hợp với độ tuổi và trình độ của bạn'); ?>
                    </p>
                </div>
            </div>
        </section>

        <!-- Filter Tabs -->
        <section id="filter-tabs-section" class="py-8 bg-white border-b" style="background-color: rgb(219, 235, 255);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap justify-center gap_seclection_courses gap-4">
                    <button class="filter-tab active" data-filter="all">
                        <?php echo c('courses', 'filter', 'all', 'Tất cả khóa học'); ?>
                    </button>
                    <button class="filter-tab" data-filter="tieuhoc">
                        <?php echo c('courses', 'filter', 'tieuhoc', 'Tiểu học'); ?>
                    </button>
                    <button class="filter-tab" data-filter="thcs">
                        <?php echo c('courses', 'filter', 'thcs', 'THCS'); ?>
                    </button>
                    <button class="filter-tab" data-filter="ielts">
                        <?php echo c('courses', 'filter', 'ielts', 'IELTS'); ?>
                    </button>
                </div>
            </div>
        </section>

        <!-- All Courses Section - Dynamic from Database -->
        <section class="py-20 bg-gray-50" id="courses-section">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Tiểu học Section -->
                <div class="course-section mb-16" data-section="tieuhoc">
                    <h2 class="text-2xl md:text-3xl font-bold text-green-600 mb-8 text-center">
                        <?php echo c('courses', 'sections', 'tieuhoc_title', '📚 CHƯƠNG TRÌNH TIẾNG ANH CẤP TIỂU HỌC'); ?>
                    </h2>
                    <div id="courses-grid-tieuhoc" class="courses-category-grid grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                        <div class="col-span-full text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                            <p class="mt-2 text-gray-600">Đang tải khóa học...</p>
                        </div>
                    </div>
                    <!-- Bảng chi tiết Tiểu học -->
                    <div class="overflow-x-auto mt-8">
                        <table class="course-detail-table w-full" id="table-tieuhoc">
                            <thead>
                                <tr class="bg-green-500 text-white">
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col1', 'Level'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col2', 'Giáo trình'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col3', 'Course length'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col4', 'Fee/month'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courseFeeItems['tieuhoc'])): ?>
                                    <?php foreach ($courseFeeItems['tieuhoc'] as $item): ?>
                                        <tr class="<?php echo $item['is_highlight'] ? 'highlight-row bg-green-50 font-semibold' : ''; ?>">
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['level']); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['curriculum'] ?? '-'); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['duration'] ?? '-'); ?></td>
                                            <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($item['fee'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">Chưa có dữ liệu học phí</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- THCS Section -->
                <div class="course-section mb-16" data-section="thcs">
                    <h2 class="text-2xl md:text-3xl font-bold text-blue-600 mb-8 text-center">
                        <?php echo c('courses', 'sections', 'thcs_title', '📖 CHƯƠNG TRÌNH TIẾNG ANH CẤP THCS'); ?>
                    </h2>
                    <div id="courses-grid-thcs" class="courses-category-grid grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                        <div class="col-span-full text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <p class="mt-2 text-gray-600">Đang tải khóa học...</p>
                        </div>
                    </div>
                    <!-- Bảng chi tiết THCS -->
                    <div class="overflow-x-auto mt-8">
                        <table class="course-detail-table w-full" id="table-thcs">
                            <thead>
                                <tr class="bg-blue-500 text-white">
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col1', 'Level'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col2', 'Giáo trình'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col3', 'Course length'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col4', 'Fee/month'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courseFeeItems['thcs'])): ?>
                                    <?php foreach ($courseFeeItems['thcs'] as $item): ?>
                                        <tr class="<?php echo $item['is_highlight'] ? 'highlight-row bg-blue-50 font-semibold' : ''; ?>">
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['level']); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['curriculum'] ?? '-'); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['duration'] ?? '-'); ?></td>
                                            <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($item['fee'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">Chưa có dữ liệu học phí</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- IELTS Section -->
                <div class="course-section mb-16" data-section="ielts">
                    <h2 class="text-2xl md:text-3xl font-bold text-purple-600 mb-8 text-center">
                        <?php echo c('courses', 'sections', 'ielts_title', '🎯 CHƯƠNG TRÌNH IELTS VÀ LUYỆN THI IELTS'); ?>
                    </h2>
                    <div id="courses-grid-ielts" class="courses-category-grid grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                        <div class="col-span-full text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                            <p class="mt-2 text-gray-600">Đang tải khóa học...</p>
                        </div>
                    </div>
                    <!-- Bảng chi tiết IELTS -->
                    <div class="overflow-x-auto mt-8">
                        <table class="course-detail-table w-full" id="table-ielts">
                            <thead>
                                <tr class="bg-purple-500 text-white">
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col1', 'Level'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col2', 'Giáo trình'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col3', 'Course length'); ?></th>
                                    <th class="px-4 py-3 text-left"><?php echo c('courses', 'table', 'col4', 'Fee/month'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courseFeeItems['ielts'])): ?>
                                    <?php foreach ($courseFeeItems['ielts'] as $item): ?>
                                        <tr class="<?php echo $item['is_highlight'] ? 'highlight-row bg-purple-50 font-semibold' : ''; ?>">
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['level']); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['curriculum'] ?? '-'); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($item['duration'] ?? '-'); ?></td>
                                            <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($item['fee'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-gray-500">Chưa có dữ liệu học phí</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo c('courses', 'why', 'title', 'Vì sao chọn khóa học tại Hải Âu English?'); ?>
                    </h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        <?php echo c('courses', 'why', 'subtitle', 'Cam kết chất lượng và kết quả tốt nhất cho học viên'); ?>
                    </p>
                    <div class="gradient_line"></div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center p-6">
                        <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo c('courses', 'why', 'item1_title', 'Cam kết đầu ra'); ?></h3>
                        <p class="text-gray-600"><?php echo c('courses', 'why', 'item1_desc', 'Học lại miễn phí nếu không đạt mục tiêu'); ?></p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo c('courses', 'why', 'item2_title', 'Lớp học nhỏ'); ?></h3>
                        <p class="text-gray-600"><?php echo c('courses', 'why', 'item2_desc', 'Tối đa 8-12 học viên/lớp, chú trọng từng cá nhân'); ?></p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo c('courses', 'why', 'item3_title', 'Giáo trình chuẩn'); ?></h3>
                        <p class="text-gray-600"><?php echo c('courses', 'why', 'item3_desc', 'Giáo trình Cambridge, tài liệu độc quyền'); ?></p>
                    </div>
                    <div class="text-center p-6">
                        <div class="w-16 h-16 mx-auto mb-4 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo c('courses', 'why', 'item4_title', 'Học phí hợp lý'); ?></h3>
                        <p class="text-gray-600"><?php echo c('courses', 'why', 'item4_desc', 'Nhiều ưu đãi, hỗ trợ trả góp 0%'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Câu hỏi thường gặp
                    </h2>
                    <div class="gradient_line"></div>
                </div>

                <div class="space-y-4" id="faq-container">
                    <div class="faq-item bg-white rounded-lg shadow-sm">
                        <button class="faq-question w-full text-left p-6 flex justify-between items-center">
                            <span class="font-medium text-gray-900"><?php echo c('courses', 'faq', 'q1', 'Làm thế nào để biết mình phù hợp với khóa học nào?'); ?></span>
                            <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer hidden px-6 pb-6">
                            <p class="text-gray-600"><?php echo c('courses', 'faq', 'a1', 'Bạn có thể đăng ký kiểm tra đầu vào miễn phí tại trung tâm. Chúng tôi sẽ đánh giá trình độ và tư vấn khóa học phù hợp nhất với mục tiêu và thời gian của bạn.'); ?></p>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-lg shadow-sm">
                        <button class="faq-question w-full text-left p-6 flex justify-between items-center">
                            <span class="font-medium text-gray-900"><?php echo c('courses', 'faq', 'q2', 'Có thể học thử trước khi đăng ký không?'); ?></span>
                            <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer hidden px-6 pb-6">
                            <p class="text-gray-600"><?php echo c('courses', 'faq', 'a2', 'Có, chúng tôi có chương trình học thử miễn phí 1 buổi. Bạn có thể trải nghiệm lớp học, phương pháp giảng dạy và gặp gỡ giảng viên trước khi quyết định.'); ?></p>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-lg shadow-sm">
                        <button class="faq-question w-full text-left p-6 flex justify-between items-center">
                            <span class="font-medium text-gray-900"><?php echo c('courses', 'faq', 'q3', 'Học phí có bao gồm tài liệu không?'); ?></span>
                            <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer hidden px-6 pb-6">
                            <p class="text-gray-600"><?php echo c('courses', 'faq', 'a3', 'Học phí đã bao gồm đầy đủ giáo trình và tài liệu học tập. Học viên không cần phải mua thêm tài liệu nào khác.'); ?></p>
                        </div>
                    </div>
                    
                    <div class="faq-item bg-white rounded-lg shadow-sm">
                        <button class="faq-question w-full text-left p-6 flex justify-between items-center">
                            <span class="font-medium text-gray-900"><?php echo c('courses', 'faq', 'q4', 'Chính sách hoàn học phí như thế nào?'); ?></span>
                            <svg class="faq-icon w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="faq-answer hidden px-6 pb-6">
                            <p class="text-gray-600"><?php echo c('courses', 'faq', 'a4', 'Học viên có thể yêu cầu hoàn học phí trong vòng 7 ngày đầu tiên nếu không hài lòng với khóa học (trừ phí tài liệu).'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700 text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    <?php echo c('courses', 'cta', 'title', 'Bắt đầu hành trình học tiếng Anh ngay hôm nay'); ?>
                </h2>
                <p class="text-lg text-blue-100 mb-8">
                    <?php echo c('courses', 'cta', 'subtitle', 'Đăng ký tư vấn miễn phí và nhận ưu đãi đặc biệt cho học viên mới'); ?>
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo $paths['contact']; ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-medium">
                        Đăng ký tư vấn miễn phí
                    </a>
                    <a href="tel:<?php echo preg_replace('/\s+/', '', s('contact_phone', '0931828960')); ?>" class="border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors font-medium">
                        Gọi ngay: <?php echo s('contact_phone', '0931 828 960'); ?>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php include __DIR__ . '/../components/floating-contact.php'; ?>
    <?php include __DIR__ . '/../components/scroll-to-top.php'; ?>

    <script src="<?php echo $assetsPath; ?>/js/ui/toast.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/animations/uiAnimations.js"></script>
    <script type="module" src="<?php echo $assetsPath; ?>/js/controllers/courses.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/main.js"></script>
    <script src="<?php echo $assetsPath; ?>/js/controllers/headerAuth.js"></script>
    
    <!-- FAQ Toggle Script -->
    <script>
        document.querySelectorAll('.faq-question').forEach(btn => {
            btn.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('.faq-icon');
                answer.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });
    </script>
</body>
</html>
