// User Controller - Xử lý thông tin người dùng

export const getUserProfile = async (req, res) => {
  try {
    // TODO: Get user from database (from auth token)
    const user = {
      id: 1,
      fullName: 'John Doe',
      email: 'john@example.com',
      phone: '0123456789',
      enrolledCourses: []
    };

    res.json({ user });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi lấy thông tin người dùng: ' + error.message });
  }
};

export const updateUserProfile = async (req, res) => {
  try {
    const { fullName, phone, address } = req.body;
    
    // TODO: Update user in database
    
    res.json({ 
      message: 'Cập nhật thông tin thành công',
      user: { fullName, phone, address }
    });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi cập nhật thông tin: ' + error.message });
  }
};

export const changePassword = async (req, res) => {
  try {
    const { currentPassword, newPassword, confirmPassword } = req.body;

    if (!currentPassword || !newPassword || !confirmPassword) {
      return res.status(400).json({ message: 'Vui lòng điền đầy đủ thông tin' });
    }

    if (newPassword !== confirmPassword) {
      return res.status(400).json({ message: 'Mật khẩu mới không khớp' });
    }

    // TODO: Verify current password
    // TODO: Hash new password
    // TODO: Update in database

    res.json({ message: 'Đổi mật khẩu thành công' });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi đổi mật khẩu: ' + error.message });
  }
};

export const getAllUsers = async (req, res) => {
  try {
    // TODO: Admin only - fetch all users
    const users = [];
    res.json({ users });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi lấy danh sách người dùng: ' + error.message });
  }
};
