// courses.js - Xử lý hiển thị và quản lý khóa học với backend PHP
import courseService from '../services/courseService.js';

document.addEventListener('DOMContentLoaded', function() {
  const courseList = document.getElementById('course-list');
  const addForm = document.getElementById('add-course-form');

  async function loadCourses() {
    if (!courseList) return;
    try {
      const courses = await courseService.getAllCourses();
      courseList.innerHTML = courses.map(c => `<li>${c.name} - <button data-id="${c.id}" class="delete-btn">Xóa</button></li>`).join('');
    } catch (err) {
      courseList.innerHTML = `<li>${err.message}</li>`;
    }
  }

  if (addForm) {
    addForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const name = addForm.name.value;
      const description = addForm.description.value;
      try {
        await courseService.addCourse({ name, description });
        addForm.reset();
        loadCourses();
      } catch (err) {
        alert(err.message);
      }
    });
  }

  if (courseList) {
    courseList.addEventListener('click', async function(e) {
      if (e.target.classList.contains('delete-btn')) {
        const id = e.target.getAttribute('data-id');
        if (confirm('Xóa khóa học này?')) {
          try {
            await courseService.deleteCourse(id);
            loadCourses();
          } catch (err) {
            alert(err.message);
          }
        }
      }
    });
    loadCourses();
  }
});

export default null;


