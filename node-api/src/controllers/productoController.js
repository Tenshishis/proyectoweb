const productoService = require('../services/productoService');
const {
  createProductoSchema,
  updateProductoSchema
} = require('../validators/productoValidator');

exports.listar = async (req, res) => {
  try {
    const productos = await productoService.listar();
    res.json({ productos });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
};

exports.obtenerPorId = async (req, res) => {
  try {
    const producto = await productoService.obtenerPorId(req.params.id);
    res.json({ producto });
  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};

exports.buscar = async (req, res) => {
  try {
    const productos = await productoService.buscar(req.params.keyword);
    res.json({ productos });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
};

exports.listarPorCategoria = async (req, res) => {
  try {
    const productos = await productoService.listarPorCategoria(req.params.categoria);
    res.json({ productos });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
};

exports.crear = async (req, res) => {
  try {
    await createProductoSchema.validateAsync(req.body);
    const producto = await productoService.crear(req.body);
    res.status(201).json({ message: 'Producto creado', producto });
  } catch (err) {
    res.status(err.status || 400).json({ error: err.message });
  }
};

exports.actualizar = async (req, res) => {
  try {
    await updateProductoSchema.validateAsync(req.body);
    const producto = await productoService.actualizar(req.params.id, req.body);
    res.json({ message: 'Producto actualizado', producto });
  } catch (err) {
    res.status(err.status || 400).json({ error: err.message });
  }
};

exports.eliminar = async (req, res) => {
  try {
    const result = await productoService.eliminar(req.params.id);
    res.json(result);
  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};

exports.agregarStock = async (req, res) => {
  try {
    const { cantidad } = req.body;
    const producto = await productoService.agregarStock(req.params.id, cantidad);
    res.json({ message: 'Stock actualizado', producto });
  } catch (err) {
    res.status(err.status || 400).json({ error: err.message });
  }
};
