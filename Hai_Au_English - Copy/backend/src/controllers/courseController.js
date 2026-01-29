// Course Controller - Xử lý thông tin khóa học

export const getAllCourses = async (req, res) => {
  try {
    // TODO: Fetch from database
    const courses = [
      {
        id: 1,
        name: 'IELTS Starter',
        level: 'Beginner',
        duration: '8 weeks',
        price: 2000000,
        description: 'Khóa học cơ bản IELTS'
      },
      {
        id: 2,
        name: 'IELTS Intermediate',
        level: 'Intermediate',
        duration: '10 weeks',
        price: 2500000,
        description: 'Khóa học trung cấp IELTS'
      }
    ];

    res.json({ courses, total: courses.length });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi lấy danh sách khóa học: ' + error.message });
  }
};

export const getCourseById = async (req, res) => {
  try {
    const { id } = req.params;
    // TODO: Fetch from database by ID
    
    res.json({ 
      id, 
      name: 'IELTS Starter',
      level: 'Beginner',
      duration: '8 weeks',
      price: 2000000
    });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi lấy thông tin khóa học: ' + error.message });
  }
};

export const createCourse = async (req, res) => {
  try {
    const { name, level, duration, price, description } = req.body;
    
    // TODO: Save to database
    
    res.status(201).json({ 
      message: 'Tạo khóa học thành công',
      course: { name, level, duration, price, description }
    });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi tạo khóa học: ' + error.message });
  }
};

export const updateCourse = async (req, res) => {
  try {
    const { id } = req.params;
    // TODO: Update in database
    
    res.json({ message: 'Cập nhật khóa học thành công' });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi cập nhật khóa học: ' + error.message });
  }
};

export const deleteCourse = async (req, res) => {
  try {
    const { id } = req.params;
    // TODO: Delete from database
    
    res.json({ message: 'Xóa khóa học thành công' });
  } catch (error) {
    res.status(500).json({ message: 'Lỗi xóa khóa học: ' + error.message });
  }
};
