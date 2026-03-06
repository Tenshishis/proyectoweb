const productoRepo = require('../repositories/productoRepository');

class ProductoService {
  async listar() {
    return productoRepo.getAll();
  }

  async obtenerPorId(id) {
    const producto = await productoRepo.getById(id);
    if (!producto || !producto.activo) {
      const error = new Error('Producto no encontrado');
      error.status = 404;
      throw error;
    }
    return producto;
  }

  async buscar(keyword) {
    return productoRepo.searchByKeyword(keyword);
  }

  async listarPorCategoria(categoria) {
    return productoRepo.getByCategory(categoria);
  }

  async crear(data) {
    return productoRepo.create(data);
  }

  async actualizar(id, data) {
    const producto = await productoRepo.updateById(id, data);
    if (!producto) {
      const error = new Error('Producto no encontrado');
      error.status = 404;
      throw error;
    }
    return producto;
  }

  async eliminar(id) {
    const producto = await productoRepo.softDeleteById(id);
    if (!producto) {
      const error = new Error('Producto no encontrado');
      error.status = 404;
      throw error;
    }
    return { message: 'Producto eliminado correctamente' };
  }

  async agregarStock(id, cantidad) {
    const cantidadNumerica = Number(cantidad);
    if (!Number.isInteger(cantidadNumerica) || cantidadNumerica <= 0) {
      const error = new Error('La cantidad a agregar debe ser un entero mayor que 0');
      error.status = 400;
      throw error;
    }

    const producto = await productoRepo.incrementStock(id, cantidadNumerica);
    if (!producto) {
      const error = new Error('Producto no encontrado');
      error.status = 404;
      throw error;
    }

    return producto;
  }
}

module.exports = new ProductoService();
