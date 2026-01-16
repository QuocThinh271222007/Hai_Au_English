// Contact page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    const submitButton = contactForm.querySelector('button[type="submit"]');
    const formMessage = document.getElementById('form-message');

    // Form validation
    const validators = {
        fullname: (value) => {
            if (!value.trim()) return 'Vui lòng nhập họ và tên';
            if (value.trim().length < 3) return 'Họ và tên phải có ít nhất 3 ký tự';
            return '';
        },
        email: (value) => {
            if (!value.trim()) return 'Vui lòng nhập email';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) return 'Email không hợp lệ';
            return '';
        },
        phone: (value) => {
            if (!value.trim()) return 'Vui lòng nhập số điện thoại';
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(value.replace(/\s/g, ''))) return 'Số điện thoại không hợp lệ';
            return '';
        },
        course: (value) => {
            if (!value) return 'Vui lòng chọn khóa học';
            return '';
        }
    };

    // Show error message
    function showError(fieldId, message) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const inputElement = document.getElementById(fieldId);
        
        if (errorElement) {
            errorElement.textContent = message;
        }
        
        if (inputElement) {
            if (message) {
                inputElement.classList.add('error-input');
                inputElement.classList.remove('success-input');
            } else {
                inputElement.classList.remove('error-input');
                inputElement.classList.add('success-input');
            }
        }
    }

    // Validate field
    function validateField(fieldId, value) {
        if (validators[fieldId]) {
            const error = validators[fieldId](value);
            showError(fieldId, error);
            return !error;
        }
        return true;
    }

    // Real-time validation
    ['fullname', 'email', 'phone', 'course'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', () => {
                validateField(fieldId, field.value);
            });

            field.addEventListener('input', () => {
                // Clear error when user starts typing
                if (field.classList.contains('error-input')) {
                    showError(fieldId, '');
                }
            });
        }
    });

    // Form submission
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate all fields
        const formData = new FormData(contactForm);
        let isValid = true;

        ['fullname', 'email', 'phone', 'course'].forEach(fieldId => {
            const value = formData.get(fieldId);
            if (!validateField(fieldId, value)) {
                isValid = false;
            }
        });

        if (!isValid) {
            return;
        }

        // Check agreement checkbox
        if (!formData.get('agreement')) {
            showFormMessage('error', 'Vui lòng đồng ý với chính sách bảo mật và điều khoản sử dụng');
            return;
        }

        // Show loading state
        submitButton.disabled = true;
        submitButton.classList.add('loading');
        formMessage.classList.add('hidden');

        // Simulate API call (replace with actual API call)
        try {
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Success
            showFormMessage('success', 
                'Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ.'
            );
            
            // Reset form
            contactForm.reset();
            
            // Clear all validation states
            ['fullname', 'email', 'phone', 'course'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.classList.remove('error-input', 'success-input');
                }
                showError(fieldId, '');
            });

            // Scroll to message
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

        } catch (error) {
            // Error
            showFormMessage('error', 
                'Có lỗi xảy ra khi gửi thông tin. Vui lòng thử lại sau.'
            );
        } finally {
            // Remove loading state
            submitButton.disabled = false;
            submitButton.classList.remove('loading');
        }
    });

    // Show form message
    function showFormMessage(type, message) {
        formMessage.textContent = message;
        formMessage.className = type;
        formMessage.classList.remove('hidden');

        // Auto hide after 5 seconds
        setTimeout(() => {
            formMessage.classList.add('hidden');
        }, 5000);
    }

    // FAQ functionality
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', function() {
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });

            // Toggle current FAQ item
            item.classList.toggle('active');
        });
    });
});
