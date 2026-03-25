const db = require('../config/db');
const productoRepository = require('./productoRepository');

class VentaRepository {
  async getAll() {
    const { rows } = await db.query(
      `SELECT v.id, v.fecha, v.usuario_id, v.total, v.created_at, v.updated_at
       FROM ventas v
       ORDER BY v.id DESC`
    );
    return rows;
  }

  async getById(id) {
    const ventaResult = await db.query(
      `SELECT v.id, v.fecha, v.usuario_id, v.total, v.created_at, v.updated_at
       FROM ventas v
       WHERE v.id = $1
       LIMIT 1`,
      [id]
    );

    const venta = ventaResult.rows[0] || null;
    if (!venta) return null;

    const detalleResult = await db.query(
      `SELECT id, venta_id, producto_id, nombre_producto, cantidad, precio_unitario, subtotal
       FROM detalle_ventas
       WHERE venta_id = $1
       ORDER BY id ASC`,
      [id]
    );

    venta.productos = detalleResult.rows;
    return venta;
  }

  async getByUser(userId) {
    const { rows } = await db.query(
      `SELECT v.id, v.fecha, v.usuario_id, v.total, v.created_at, v.updated_at
       FROM ventas v
       WHERE v.usuario_id = $1
       ORDER BY v.id DESC`,
      [Number(userId)]
    );
    return rows;
  }

  async createWithItems({ usuario_id, fecha, productos }) {
    const client = await db.getClient();
    const movimientosAplicados = [];

    try {
      let total = 0;
      const detalle = [];

      for (const item of productos) {
        const { id_producto, cantidad } = item;
        const productoOriginal = await productoRepository.getById(id_producto);

        if (!productoOriginal || !productoOriginal.activo) {
          const error = new Error(`Producto no encontrado: ${id_producto}`);
          error.status = 404;
          throw error;
        }

        if (Number(productoOriginal.stock) < Number(cantidad)) {
          const error = new Error(`Stock insuficiente para: ${productoOriginal.nombre}`);
          error.status = 400;
          throw error;
        }

        const productoActualizado = await productoRepository.decrementStockIfAvailable(id_producto, cantidad);
        if (!productoActualizado) {
          const error = new Error(`Stock insuficiente para: ${productoOriginal.nombre}`);
          error.status = 400;
          throw error;
        }

        movimientosAplicados.push({ id_producto, cantidad: Number(cantidad) });

        const subtotal = Number((Number(productoOriginal.precio) * Number(cantidad)).toFixed(2));
        total += subtotal;

        detalle.push({
          id_producto: String(productoOriginal.id),
          nombre_producto: productoOriginal.nombre,
          cantidad,
          precio_unitario: Number(productoOriginal.precio),
          subtotal
        });
      }

      await client.query('BEGIN');

      const usuarioRes = await client.query(
        `SELECT u.id, u.nombre, u.email, r.nombre AS rol
         FROM users u
         JOIN roles r ON r.id = u.role_id
         WHERE u.id = $1
         LIMIT 1`,
        [usuario_id]
      );

      const usuario = usuarioRes.rows[0] || null;
      if (!usuario) {
        const error = new Error('Usuario no encontrado');
        error.status = 404;
        throw error;
      }

      const ventaRes = await client.query(
        `INSERT INTO ventas (fecha, usuario_id, total)
         VALUES ($1, $2, $3)
         RETURNING id, fecha, usuario_id, total, created_at, updated_at`,
        [fecha || new Date(), Number(usuario_id), Number(total.toFixed(2))]
      );

      const venta = ventaRes.rows[0];

      for (const item of detalle) {
        await client.query(
          `INSERT INTO detalle_ventas (venta_id, producto_id, nombre_producto, cantidad, precio_unitario, subtotal)
           VALUES ($1, $2, $3, $4, $5, $6)`,
          [venta.id, item.id_producto, item.nombre_producto, item.cantidad, item.precio_unitario, item.subtotal]
        );

        await client.query(
          `INSERT INTO reporte_ventas
           (venta_id, fecha, usuario_id, usuario_nombre, usuario_email, usuario_rol, producto_id, nombre_producto, cantidad, precio_unitario, subtotal, total_venta)
           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)`,
          [
            venta.id,
            venta.fecha,
            usuario.id,
            usuario.nombre,
            usuario.email,
            usuario.rol,
            item.id_producto,
            item.nombre_producto,
            item.cantidad,
            item.precio_unitario,
            item.subtotal,
            venta.total
          ]
        );
      }

      await client.query('COMMIT');

      return {
        ...venta,
        productos: detalle
      };
    } catch (error) {
      try {
        await client.query('ROLLBACK');
      } catch (_) {
        // no-op
      }

      for (const movimiento of movimientosAplicados.reverse()) {
        await productoRepository.incrementStock(movimiento.id_producto, movimiento.cantidad);
      }

      throw error;
    } finally {
      client.release();
    }
  }

  async deleteById(id) {
    const { rows } = await db.query(
      `DELETE FROM ventas
       WHERE id = $1
       RETURNING id, fecha, usuario_id, total, created_at, updated_at`,
      [id]
    );
    return rows[0] || null;
  }
}

module.exports = new VentaRepository();
