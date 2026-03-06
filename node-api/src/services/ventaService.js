const ventaRepo = require('../repositories/ventaRepository');

class VentaService {
  async listar(user) {
    if (user.rol === 'ADMIN' || user.rol === 'CONSULTOR') {
      return ventaRepo.getAll();
    }

    return ventaRepo.getByUser(user.id);
  }

  async obtenerPorId(id, user) {
    const venta = await ventaRepo.getById(id);
    if (!venta) {
      const error = new Error('Venta no encontrada');
      error.status = 404;
      throw error;
    }

    if (
      user.rol !== 'ADMIN' &&
      user.rol !== 'CONSULTOR' &&
      String(venta.usuario_id) !== String(user.id)
    ) {
      const error = new Error('No autorizado para ver esta venta');
      error.status = 403;
      throw error;
    }

    return venta;
  }

  async crear({ productos, fecha }, user) {
    if (!Array.isArray(productos) || productos.length === 0) {
      const error = new Error('Debes enviar al menos un producto');
      error.status = 400;
      throw error;
    }

    return ventaRepo.createWithItems({
      usuario_id: user.id,
      fecha,
      productos
    });
  }

  async eliminar(id) {
    const deleted = await ventaRepo.deleteById(id);
    if (!deleted) {
      const error = new Error('Venta no encontrada');
      error.status = 404;
      throw error;
    }
    return { message: 'Venta eliminada correctamente' };
  }
}

module.exports = new VentaService();
