const Producto = require('../models/Producto');
const mongoose = require('mongoose');

function isValidId(id) {
  return mongoose.Types.ObjectId.isValid(id);
}

function mapProducto(producto) {
  if (!producto) return null;

  const source = typeof producto.toObject === 'function' ? producto.toObject() : producto;
  return {
    id: String(source._id),
    nombre: source.nombre,
    descripcion: source.descripcion,
    precio: source.precio,
    categoria: source.categoria,
    stock: source.stock,
    activo: source.activo,
    created_at: source.createdAt,
    updated_at: source.updatedAt
  };
}

class ProductoRepository {
  async getAll() {
    const productos = await Producto.find({ activo: true }).sort({ createdAt: -1 });
    return productos.map(mapProducto);
  }

  async getAllForAdmin() {
    const productos = await Producto.find({}).sort({ createdAt: -1 });
    return productos.map(mapProducto);
  }

  async getById(id) {
    if (!isValidId(id)) return null;
    const producto = await Producto.findById(id);
    return mapProducto(producto);
  }

  async searchByKeyword(keyword) {
    const productos = await Producto.find(
      { activo: true, $text: { $search: keyword } },
      { score: { $meta: 'textScore' } }
    ).sort({ score: { $meta: 'textScore' }, createdAt: -1 });

    return productos.map(mapProducto);
  }

  async getByCategory(categoria) {
    const productos = await Producto.find({ activo: true, categoria }).sort({ createdAt: -1 });
    return productos.map(mapProducto);
  }

  async create(data) {
    const { nombre, descripcion = '', precio, categoria = 'General', stock = 0, activo = true } = data;
    const producto = await Producto.create({ nombre, descripcion, precio, categoria, stock, activo });
    return mapProducto(producto);
  }

  async updateById(id, data) {
    if (!isValidId(id)) return null;
    const allowed = ['nombre', 'descripcion', 'precio', 'categoria', 'stock', 'activo'];
    const update = {};

    for (const key of allowed) {
      if (Object.prototype.hasOwnProperty.call(data, key)) {
        update[key] = data[key];
      }
    }

    if (Object.keys(update).length === 0) {
      return this.getById(id);
    }

    const producto = await Producto.findByIdAndUpdate(id, update, { new: true });
    return mapProducto(producto);
  }

  async softDeleteById(id) {
    if (!isValidId(id)) return null;
    const producto = await Producto.findByIdAndUpdate(id, { activo: false }, { new: true });
    return mapProducto(producto);
  }

  async decrementStockIfAvailable(id, cantidad) {
    if (!isValidId(id)) return null;
    const producto = await Producto.findOneAndUpdate(
      { _id: id, activo: true, stock: { $gte: cantidad } },
      { $inc: { stock: -cantidad } },
      { new: true }
    );
    return mapProducto(producto);
  }

  async incrementStock(id, cantidad) {
    if (!isValidId(id)) return null;
    const producto = await Producto.findByIdAndUpdate(
      id,
      { $inc: { stock: cantidad } },
      { new: true }
    );
    return mapProducto(producto);
  }

  async reactivateAndAddStock(id, cantidad) {
    if (!isValidId(id)) return null;
    const producto = await Producto.findByIdAndUpdate(
      id,
      { activo: true, $inc: { stock: cantidad } },
      { new: true }
    );
    return mapProducto(producto);
  }
}

module.exports = new ProductoRepository();
