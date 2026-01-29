import express from 'express';
import { 
  createContact, 
  getAllContacts, 
  getContactById, 
  updateContactStatus, 
  deleteContact 
} from '../controllers/contactController.js';

const router = express.Router();

// Public route
router.post('/', createContact);

// Protected routes (admin only) - TODO: Add authentication middleware
router.get('/', getAllContacts);
router.get('/:id', getContactById);
router.put('/:id/status', updateContactStatus);
router.delete('/:id', deleteContact);

export default router;
