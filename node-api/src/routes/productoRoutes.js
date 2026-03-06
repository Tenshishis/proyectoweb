const express = require('express');
const router = express.Router();

const productoController = require('../controllers/productoController');
const { verifyToken } = require('../middleware/authMiddleware');
const { authorize } = require('../middleware/roleMiddleware');

router.get('/', verifyToken, authorize('ADMIN', 'VENDEDOR', 'CONSULTOR'), productoController.listar);
router.get('/search/:keyword', verifyToken, authorize('ADMIN', 'VENDEDOR', 'CONSULTOR'), productoController.buscar);
router.get('/categoria/:categoria', verifyToken, authorize('ADMIN', 'VENDEDOR', 'CONSULTOR'), productoController.listarPorCategoria);
router.get('/:id', verifyToken, authorize('ADMIN', 'VENDEDOR', 'CONSULTOR'), productoController.obtenerPorId);

router.post('/', verifyToken, authorize('ADMIN'), productoController.crear);
router.put('/:id', verifyToken, authorize('ADMIN'), productoController.actualizar);
router.delete('/:id', verifyToken, authorize('ADMIN'), productoController.eliminar);

module.exports = router;
