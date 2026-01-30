// contactService.js - Giao tiếp với backend PHP cho liên hệ
const API_BASE = '/hai_au_english/backend/php/contact.php';

export const contactService = {
  async submitContact(contactData) {
    const res = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(contactData)
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Lỗi gửi liên hệ');
    return data;
  }
};

export default contactService;
