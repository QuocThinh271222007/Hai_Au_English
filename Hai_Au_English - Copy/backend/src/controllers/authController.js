// Auth Controller - Xử lý đăng nhập, đăng ký, authentication

export const register = async (req, res) => {
  try {
    const { fullName, email, password, confirmPassword } = req.body;

    // Validation
    if (!fullName || !email || !password || !confirmPassword) {
      return res.status(400).json({ message: 'Vui lòng điền đầy đủ thông tin' });
    }

    if (password !== confirmPassword) {
      return res.status(400).json({ message: 'Mật khẩu không khớp' });
    }

    if (password.length < 6) {
      return res.status(400).json({ message: 'Mật khẩu phải có ít nhất 6 ký tự' });
    }

    // TODO: Hash password, save to database
    // TODO: Check if email already exists

    res.status(201).json({ 
      message: 'Đăng ký thành công',
      user: { fullName, email }
    });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi đăng ký: ' + error.message });
  }
};

export const login = async (req, res) => {
  try {
    const { email, password } = req.body;

    // Validation
    if (!email || !password) {
      return res.status(400).json({ message: 'Vui lòng nhập email và mật khẩu' });
    }

    // TODO: Find user in database
    // TODO: Verify password
    // TODO: Generate JWT token

    res.json({ 
      message: 'Đăng nhập thành công',
      token: 'jwt_token_here'
    });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi đăng nhập: ' + error.message });
  }
};

export const logout = (req, res) => {
  res.json({ message: 'Đăng xuất thành công' });
};

export const refreshToken = (req, res) => {
  try {
    // TODO: Verify refresh token
    // TODO: Generate new access token
    res.json({ token: 'new_jwt_token_here' });
  } catch (error) {
    res.status(401).json({ message: 'Token không hợp lệ' });
  }
};
