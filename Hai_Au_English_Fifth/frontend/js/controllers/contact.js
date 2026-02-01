// contact.js - Xử lý form liên hệ với backend PHP
import contactService from '../services/contactService.js';

document.addEventListener('DOMContentLoaded', function() {
  const contactForm = document.getElementById('contact-form');
  const formMessage = document.getElementById('form-message');
  if (!contactForm) return;

  contactForm.addEventListener('submit', async function(e) {
    e.preventDefault();
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
      contactForm.reset();
    } catch (err) {
      showFormMessage('error', err.message || 'Có lỗi xảy ra khi gửi thông tin. Vui lòng thử lại sau.');
    }
  });

  function showFormMessage(type, message) {
    if (!formMessage) return;
    formMessage.textContent = message;
    formMessage.className = type;
    formMessage.classList.remove('hidden');
    setTimeout(() => formMessage.classList.add('hidden'), 5000);
  }
});

export default null;
