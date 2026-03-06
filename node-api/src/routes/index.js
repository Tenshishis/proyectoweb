const express = require('express');
const router = express.Router();

const authRoutes = require('./authRoutes');
const adminRoutes = require('./adminRoutes');
const productoRoutes = require('./productoRoutes');
const ventaRoutes = require('./ventaRoutes');

router.use('/auth', authRoutes);
router.use('/admin', adminRoutes);
router.use('/productos', productoRoutes);
router.use('/ventas', ventaRoutes);

// additional routers can be mounted here later

module.exports = router;