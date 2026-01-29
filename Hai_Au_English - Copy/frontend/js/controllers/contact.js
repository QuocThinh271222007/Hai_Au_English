// Contact page logic (validation, submission)
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    if (!contactForm) return;

    const submitButton = contactForm.querySelector('button[type="submit"]');
    const formMessage = document.getElementById('form-message');

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

    function showError(fieldId, message) {
        const errorElement = document.getElementById(`${fieldId}-error`);
        const inputElement = document.getElementById(fieldId);
        if (errorElement) errorElement.textContent = message;
        if (inputElement) {
            if (message) { inputElement.classList.add('error-input'); inputElement.classList.remove('success-input'); }
            else { inputElement.classList.remove('error-input'); inputElement.classList.add('success-input'); }
        }
    }

    function validateField(fieldId, value) {
        if (validators[fieldId]) {
            const error = validators[fieldId](value);
            showError(fieldId, error);
            return !error;
        }
        return true;
    }

    ['fullname','email','phone','course'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', () => validateField(fieldId, field.value));
            field.addEventListener('input', () => { if (field.classList.contains('error-input')) showError(fieldId, ''); });
        }
    });

    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(contactForm);
        let isValid = true;
        ['fullname','email','phone','course'].forEach(fieldId => {
            const value = formData.get(fieldId);
            if (!validateField(fieldId, value)) isValid = false;
        });

        if (!isValid) return;
        if (!formData.get('agreement')) { showFormMessage('error', 'Vui lòng đồng ý với chính sách bảo mật và điều khoản sử dụng'); return; }

        submitButton.disabled = true; submitButton.classList.add('loading'); formMessage.classList.add('hidden');

        try {
            // TODO: replace with contactService.submitContact
            await new Promise(resolve => setTimeout(resolve, 1200));
            showFormMessage('success', 'Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ.');
            contactForm.reset();
            ['fullname','email','phone','course'].forEach(fieldId => { const f = document.getElementById(fieldId); if (f) f.classList.remove('error-input','success-input'); showError(fieldId,''); });
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (err) {
            showFormMessage('error', 'Có lỗi xảy ra khi gửi thông tin. Vui lòng thử lại sau.');
        } finally {
            submitButton.disabled = false; submitButton.classList.remove('loading');
        }
    });

    function showFormMessage(type, message) {
        formMessage.textContent = message;
        formMessage.className = type;
        formMessage.classList.remove('hidden');
        setTimeout(() => formMessage.classList.add('hidden'), 5000);
    }
});

export default null;
