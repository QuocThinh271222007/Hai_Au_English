// Contact Controller - Xử lý các yêu cầu liên hệ

export const createContact = async (req, res) => {
  try {
    const { fullName, email, phone, course, message } = req.body;

    // Validation
    if (!fullName || !email || !phone || !course || !message) {
      return res.status(400).json({ message: 'Vui lòng điền đầy đủ thông tin' });
    }

    // TODO: Save to database
    // TODO: Send email notification

    res.status(201).json({ 
      message: 'Gửi thông tin liên hệ thành công. Chúng tôi sẽ liên hệ với bạn sớm!',
      contact: { fullName, email, phone, course }
    });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi gửi thông tin: ' + error.message });
  }
};

export const getAllContacts = async (req, res) => {
  try {
    // TODO: Fetch all contacts from database (admin only)
    const contacts = [];
    res.json({ contacts });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi lấy danh sách liên hệ: ' + error.message });
  }
};

export const getContactById = async (req, res) => {
  try {
    const { id } = req.params;
    // TODO: Fetch from database
    
    res.json({ id, fullName: 'John Doe', email: 'john@example.com' });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi lấy thông tin liên hệ: ' + error.message });
  }
};

export const updateContactStatus = async (req, res) => {
  try {
    const { id } = req.params;
    const { status } = req.body; // pending, contacted, resolved
    
    // TODO: Update status in database
    
    res.json({ message: 'Cập nhật trạng thái thành công' });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi cập nhật trạng thái: ' + error.message });
  }
};

export const deleteContact = async (req, res) => {
  try {
    const { id } = req.params;
    // TODO: Delete from database
    
    res.json({ message: 'Xóa liên hệ thành công' });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi xóa liên hệ: ' + error.message });
  }
};
