const express = require('express');
const router = express.Router();

const ventaController = require('../controllers/ventaController');
const { verifyToken } = require('../middleware/authMiddleware');
const { authorize } = require('../middleware/roleMiddleware');

router.get('/', verifyToken, authorize('ADMIN', 'VENDEDOR', 'CONSULTOR'), ventaController.listar);
router.get('/:id', verifyToken, authorize('ADMIN', 'VENDEDOR', 'CONSULTOR'), ventaController.obtenerPorId);
router.post('/', verifyToken, authorize('ADMIN', 'VENDEDOR'), ventaController.crear);
router.delete('/:id', verifyToken, authorize('ADMIN'), ventaController.eliminar);

module.exports = router;
