<?php
$pageTitle = 'Tuyển dụng - Hải Âu English';
$pageDescription = 'Cơ hội nghề nghiệp tại Trung tâm Anh ngữ Hải Âu English. Tìm kiếm vị trí phù hợp và gia nhập đội ngũ của chúng tôi!';
$currentPage = 'recruitment';
$additionalCSS = [];

require_once __DIR__ . '/../components/base_config.php';
require_once __DIR__ . '/../components/content_helper.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <?php include __DIR__ . '/../components/head.php'; ?>
    <style>
    .job-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .job-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .job-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-featured {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: white;
    }

    .badge-fulltime {
        background: #dcfce7;
        color: #166534;
    }

    .badge-parttime {
        background: #e0f2fe;
        color: #0369a1;
    }

    .badge-contract {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-intern {
        background: #f3e8ff;
        color: #7c3aed;
    }

    .job-detail-content h4 {
        color: #1f2937;
        font-weight: 600;
        margin-top: 20px;
        margin-bottom: 12px;
    }

    .job-detail-content ul {
        list-style: none;
        padding: 0;
    }

    .job-detail-content li {
        position: relative;
        padding-left: 24px;
        margin-bottom: 8px;
        color: #4b5563;
    }

    .job-detail-content li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #22c55e;
        font-weight: bold;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-backdrop.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        max-width: 800px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }
    </style>
</head>

<body class="bg-gray-50">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4" style="padding-top: 7rem">Cơ hội nghề nghiệp</h1>
                <p class="text-xl text-blue-100 mb-8">
                    Gia nhập đội ngũ Hải Âu English - Nơi bạn có thể phát triển sự nghiệp và đam mê giảng dạy
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div class="bg-white/20 backdrop-blur px-6 py-3 rounded-full">
                        <span class="text-2xl font-bold" id="total-jobs">0</span>
                        <span class="ml-2">Vị trí đang tuyển</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Jobs List Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="filterJobs('all')"
                            class="filter-btn active px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            data-filter="all">
                            Tất cả
                        </button>
                        <button onclick="filterJobs('full-time')"
                            class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            data-filter="full-time">
                            Toàn thời gian
                        </button>
                        <button onclick="filterJobs('part-time')"
                            class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            data-filter="part-time">
                            Bán thời gian
                        </button>
                        <button onclick="filterJobs('intern')"
                            class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                            data-filter="intern">
                            Thực tập
                        </button>
                    </div>
                    <div class="text-gray-500 text-sm">
                        Hiển thị <span id="showing-count">0</span> vị trí
                    </div>
                </div>
            </div>

            <!-- Jobs Grid -->
            <div id="jobs-container" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Jobs will be loaded here -->
                <div class="col-span-full text-center py-12">
                    <div
                        class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-4">
                    </div>
                    <p class="text-gray-500">Đang tải danh sách tuyển dụng...</p>
                </div>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-16">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Chưa có vị trí tuyển dụng</h3>
                <p class="text-gray-500">Vui lòng quay lại sau hoặc gửi CV cho chúng tôi qua email</p>
            </div>
        </div>
    </section>

    <!-- Why Join Us Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Tại sao chọn Hải Âu English?</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Thu nhập hấp dẫn</h3>
                    <p class="text-gray-600 text-sm">Mức lương cạnh tranh cùng các khoản thưởng theo hiệu quả công việc
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Cơ hội phát triển</h3>
                    <p class="text-gray-600 text-sm">Lộ trình thăng tiến rõ ràng và được đào tạo chuyên môn liên tục</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Môi trường năng động</h3>
                    <p class="text-gray-600 text-sm">Đội ngũ trẻ trung, chuyên nghiệp và luôn hỗ trợ lẫn nhau</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Phúc lợi đầy đủ</h3>
                    <p class="text-gray-600 text-sm">Bảo hiểm, du lịch, team building và nhiều quyền lợi khác</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-blue-800">
        <div class="container mx-auto px-4 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Không tìm thấy vị trí phù hợp?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Gửi CV của bạn về email của chúng tôi. Chúng tôi sẽ liên hệ khi có vị trí phù hợp!
            </p>
            <a href="mailto:haiauenglish@gmail.com?subject=Ứng tuyển - Hải Âu English"
                class="inline-flex items-center gap-2 bg-white text-blue-600 px-8 py-4 rounded-full font-semibold hover:bg-blue-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Gửi CV ngay
            </a>
        </div>
    </section>

    <!-- Job Detail Modal -->
    <div id="job-modal" class="modal-backdrop">
        <div class="modal-content">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-800" id="modal-title">Chi tiết công việc</h3>
                <button onclick="closeJobModal()" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modal-body" class="p-6">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php include __DIR__ . '/../components/floating-contact.php'; ?>
    <?php include __DIR__ . '/../components/scroll-to-top.php'; ?>
    <?php include __DIR__ . '/../components/scripts.php'; ?>

    <script>
    (function() {
        const basePath = '<?php echo $basePath; ?>';
        const API = basePath + '/backend/php/recruitment.php';
        let allJobs = [];
        let currentFilter = 'all';

        // Load jobs on page load
        document.addEventListener('DOMContentLoaded', loadJobs);

        async function loadJobs() {
            try {
                const res = await fetch(API + '?action=list&active=1');
                const data = await res.json();

                if (data.success) {
                    allJobs = data.data;
                    document.getElementById('total-jobs').textContent = data.pagination.total;
                    renderJobs(allJobs);
                }
            } catch (err) {
                console.error('Error loading jobs:', err);
                document.getElementById('jobs-container').innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <p class="text-red-500">Lỗi tải dữ liệu. Vui lòng thử lại sau.</p>
                    </div>
                `;
            }
        }

        function renderJobs(jobs) {
            const container = document.getElementById('jobs-container');
            const emptyState = document.getElementById('empty-state');

            if (jobs.length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
                document.getElementById('showing-count').textContent = '0';
                return;
            }

            emptyState.classList.add('hidden');
            document.getElementById('showing-count').textContent = jobs.length;

            container.innerHTML = jobs.map(job => `
                <div class="job-card bg-white rounded-xl p-6 cursor-pointer" onclick="openJobDetail(${job.id})">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            ${job.is_featured ? '<span class="job-badge badge-featured mb-2">⭐ Nổi bật</span>' : ''}
                            <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 transition-colors">
                                ${job.title}
                            </h3>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        ${job.department ? `
                        <div class="flex items-center text-gray-600 text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            ${job.department}
                        </div>
                        ` : ''}
                        <div class="flex items-center text-gray-600 text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            ${job.location}
                        </div>
                        ${job.salary_range ? `
                        <div class="flex items-center text-gray-600 text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ${job.salary_range}
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t">
                        <span class="job-badge badge-${job.employment_type.replace('-', '')}">${job.employment_type_label}</span>
                        ${job.deadline_formatted ? `
                        <span class="text-xs text-gray-500">Hạn: ${job.deadline_formatted}</span>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        window.filterJobs = function(type) {
            currentFilter = type;

            // Update button styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            });
            const activeBtn = document.querySelector(`.filter-btn[data-filter="${type}"]`);
            activeBtn.classList.add('active', 'bg-blue-600', 'text-white');
            activeBtn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');

            // Filter jobs
            const filtered = type === 'all' ?
                allJobs :
                allJobs.filter(job => job.employment_type === type);

            renderJobs(filtered);
        };

        window.openJobDetail = async function(id) {
            const modal = document.getElementById('job-modal');
            const modalBody = document.getElementById('modal-body');

            modalBody.innerHTML =
                '<div class="text-center py-8"><div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto"></div></div>';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            try {
                const res = await fetch(API + '?action=detail&id=' + id);
                const data = await res.json();

                if (data.success) {
                    const job = data.data;
                    document.getElementById('modal-title').textContent = job.title;

                    modalBody.innerHTML = `
                        <div class="job-detail-content">
                            <!-- Header Info -->
                            <div class="flex flex-wrap gap-2 mb-6">
                                <span class="job-badge badge-${job.employment_type.replace('-', '')}">${job.employment_type_label}</span>
                                ${job.is_featured ? '<span class="job-badge badge-featured">⭐ Nổi bật</span>' : ''}
                            </div>
                            
                            <!-- Quick Info -->
                            <div class="grid md:grid-cols-2 gap-4 mb-8 p-4 bg-gray-50 rounded-lg">
                                ${job.department ? `<div><strong>Phòng ban:</strong> ${job.department}</div>` : ''}
                                <div><strong>Địa điểm:</strong> ${job.location}</div>
                                ${job.salary_range ? `<div><strong>Mức lương:</strong> ${job.salary_range}</div>` : ''}
                                ${job.experience ? `<div><strong>Kinh nghiệm:</strong> ${job.experience}</div>` : ''}
                                ${job.deadline_formatted ? `<div><strong>Hạn nộp:</strong> ${job.deadline_formatted}</div>` : ''}
                            </div>
                            
                            <!-- Description -->
                            ${job.description ? `<div class="mb-6">${job.description}</div>` : ''}
                            
                            <!-- Requirements -->
                            ${job.requirements ? `<div class="mb-6">${job.requirements}</div>` : ''}
                            
                            <!-- Benefits -->
                            ${job.benefits ? `<div class="mb-6">${job.benefits}</div>` : ''}
                            
                            <!-- Contact -->
                            <div class="mt-8 p-6 bg-blue-50 rounded-lg">
                                <h4 class="text-lg font-semibold text-blue-800 mb-4">Thông tin liên hệ</h4>
                                <div class="space-y-2">
                                    <p><strong>Email:</strong> <a href="mailto:${job.contact_email}?subject=Ứng tuyển: ${job.title}" class="text-blue-600 hover:underline">${job.contact_email}</a></p>
                                    <p><strong>Điện thoại:</strong> <a href="tel:${job.contact_phone.replace(/\s/g, '')}" class="text-blue-600 hover:underline">${job.contact_phone}</a></p>
                                </div>
                                <a href="mailto:${job.contact_email}?subject=Ứng tuyển: ${job.title}" 
                                   class="inline-flex items-center gap-2 mt-4 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Ứng tuyển ngay
                                </a>
                            </div>
                        </div>
                    `;
                }
            } catch (err) {
                modalBody.innerHTML = '<p class="text-red-500 text-center">Lỗi tải dữ liệu</p>';
            }
        };

        window.closeJobModal = function() {
            document.getElementById('job-modal').classList.remove('active');
            document.body.style.overflow = '';
        };

        // Close modal on backdrop click
        document.getElementById('job-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeJobModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeJobModal();
            }
        });

        // Initialize filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            if (!btn.classList.contains('active')) {
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            } else {
                btn.classList.add('bg-blue-600', 'text-white');
            }
        });
    })();
    </script>
</body>

</html>