const express = require('express');
const router = express.Router();
const adminController = require('../controllers/adminController');
const { verifyToken } = require('../middleware/authMiddleware');
const { authorize } = require('../middleware/roleMiddleware');

router.put('/asignar-rol', verifyToken, authorize('ADMIN'), adminController.asignarRol);
router.get('/users', verifyToken, authorize('ADMIN'), adminController.listUsers);
router.get('/all-users', verifyToken, authorize('ADMIN'), adminController.listAllUsers);
router.delete('/users/:id', verifyToken, authorize('ADMIN'), adminController.deleteUser);

module.exports = router;