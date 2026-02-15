// Frontend main controller: menu toggle, active nav, basic contact form handling
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            const isHidden = mobileMenu.classList.contains('hidden');
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                menuIcon?.classList.add('hidden');
                closeIcon?.classList.remove('hidden');
            } else {
                mobileMenu.classList.add('hidden');
                menuIcon?.classList.remove('hidden');
                closeIcon?.classList.add('hidden');
            }
        });
    }

    // Close mobile menu when clicking on a link
    const mobileLinks = mobileMenu?.querySelectorAll('a');
    mobileLinks?.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenu.classList.add('hidden');
            menuIcon?.classList.remove('hidden');
            closeIcon?.classList.add('hidden');
        });
    });

    // Active navigation highlight - hỗ trợ cả URL đẹp và URL cũ
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('nav a');
    
    // Map URL đẹp sang tên trang
    const urlMap = {
        '/': '/TrangChu',
        '/TrangChu': '/TrangChu',
        '/trangchu': '/TrangChu',
        '/GioiThieu': '/GioiThieu',
        '/gioithieu': '/GioiThieu',
        '/KhoaHoc': '/KhoaHoc',
        '/khoahoc': '/KhoaHoc',
        '/GiangVien': '/GiangVien',
        '/giangvien': '/GiangVien',
        '/LienHe': '/LienHe',
        '/lienhe': '/LienHe'
    };
    
    const normalizedPath = urlMap[currentPath] || currentPath;
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref === normalizedPath || 
            (normalizedPath === '/TrangChu' && (linkHref === '/' || linkHref === '/TrangChu'))) {
            link.classList.add('nav-active');
        }
    });

    // Contact form is handled by contact.js module - DO NOT add handler here
    // The old placeholder code was removed to avoid conflicts

    // Floating Contact Sidebar Toggle (mobile only)
    const floatingToggleBtn = document.getElementById('floating-toggle-btn');
    const floatingContactIcons = document.querySelector('.floating-contact-icons');
    
    if (floatingToggleBtn && floatingContactIcons) {
        floatingToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            floatingContactIcons.classList.toggle('expanded');
            floatingToggleBtn.classList.toggle('active');
        });
        
        // Close when clicking outside (only when expanded)
        document.addEventListener('click', function(e) {
            if (floatingContactIcons.classList.contains('expanded') && !e.target.closest('.floating-contact')) {
                floatingContactIcons.classList.remove('expanded');
                floatingToggleBtn.classList.remove('active');
            }
        });
    }
});
