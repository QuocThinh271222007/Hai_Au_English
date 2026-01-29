import express from 'express';
import { 
  getUserProfile, 
  updateUserProfile, 
  changePassword, 
  getAllUsers 
} from '../controllers/userController.js';

const router = express.Router();

// Protected routes - TODO: Add authentication middleware
router.get('/profile', getUserProfile);
router.put('/profile', updateUserProfile);
router.post('/change-password', changePassword);
router.get('/', getAllUsers); // Admin only

export default router;
