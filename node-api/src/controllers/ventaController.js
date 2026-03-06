const ventaService = require('../services/ventaService');
const { createVentaSchema } = require('../validators/ventaValidator');

exports.listar = async (req, res) => {
  try {
    const ventas = await ventaService.listar(req.user);
    res.json({ ventas });
  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};

exports.obtenerPorId = async (req, res) => {
  try {
    const venta = await ventaService.obtenerPorId(req.params.id, req.user);
    res.json({ venta });
  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};

exports.crear = async (req, res) => {
  try {
    await createVentaSchema.validateAsync(req.body);
    const venta = await ventaService.crear(req.body, req.user);
    res.status(201).json({ message: 'Venta registrada correctamente', venta });
  } catch (err) {
    res.status(err.status || 400).json({ error: err.message });
  }
};

exports.eliminar = async (req, res) => {
  try {
    const result = await ventaService.eliminar(req.params.id);
    res.json(result);
  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};
