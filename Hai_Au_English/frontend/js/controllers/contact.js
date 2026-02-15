// contact.js - Xử lý form liên hệ với backend PHP
import contactService from '../services/contactService.js';

document.addEventListener('DOMContentLoaded', function() {
  const contactForm = document.getElementById('contact-form');
  const formMessage = document.getElementById('form-message');
  const submitBtn = contactForm ? contactForm.querySelector('button[type="submit"]') : null;
  
  if (!contactForm) return;

  contactForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Disable button and show loading
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Đang gửi...';
    }
    
    const formData = new FormData(contactForm);
    const payload = {
      fullname: formData.get('fullname'),
      email: formData.get('email'),
      phone: formData.get('phone'),
      course: formData.get('course'),
      level: formData.get('level') || '',
      message: formData.get('message') || '',
      agreement: formData.get('agreement') ? true : false
    };
    
    try {
      const result = await contactService.submitContact(payload);
      if (!result || !result.success) throw new Error(result && result.error ? result.error : 'Lỗi server');
      showFormMessage('success', 'Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ.');
      window.showToast?.('Gửi thông tin thành công!', 'success');
      contactForm.reset();
    } catch (err) {
      showFormMessage('error', err.message || 'Có lỗi xảy ra khi gửi thông tin. Vui lòng thử lại sau.');
    } finally {
      // Re-enable button
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Gửi thông tin';
      }
    }
  });

  function showFormMessage(type, message) {
    if (!formMessage) return;
    formMessage.textContent = message;
    formMessage.className = type === 'success' 
      ? 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'
      : 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
    formMessage.classList.remove('hidden');
    
    // Auto hide after 8 seconds
    setTimeout(() => formMessage.classList.add('hidden'), 8000);
  }
});

export default null;
