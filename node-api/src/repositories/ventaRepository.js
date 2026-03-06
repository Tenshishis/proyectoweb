const db = require('../config/db');

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
      [String(userId)]
    );
    return rows;
  }

  async createWithItems({ usuario_id, fecha, productos }) {
    const client = await db.getClient();
    try {
      await client.query('BEGIN');

      let total = 0;
      const detalle = [];

      for (const item of productos) {
        const { id_producto, cantidad } = item;
        const productoRes = await client.query(
          `SELECT id, nombre, precio, stock, activo
           FROM productos
           WHERE id = $1
           FOR UPDATE`,
          [id_producto]
        );

        const producto = productoRes.rows[0];
        if (!producto || !producto.activo) {
          const error = new Error(`Producto no encontrado: ${id_producto}`);
          error.status = 404;
          throw error;
        }

        if (producto.stock < cantidad) {
          const error = new Error(`Stock insuficiente para: ${producto.nombre}`);
          error.status = 400;
          throw error;
        }

        await client.query(
          `UPDATE productos
           SET stock = stock - $2
           WHERE id = $1`,
          [id_producto, cantidad]
        );

        const subtotal = Number((Number(producto.precio) * Number(cantidad)).toFixed(2));
        total += subtotal;

        detalle.push({
          id_producto: producto.id,
          nombre_producto: producto.nombre,
          cantidad,
          precio_unitario: Number(producto.precio),
          subtotal
        });
      }

      const ventaRes = await client.query(
        `INSERT INTO ventas (fecha, usuario_id, total)
         VALUES ($1, $2, $3)
         RETURNING id, fecha, usuario_id, total, created_at, updated_at`,
        [fecha || new Date(), String(usuario_id), Number(total.toFixed(2))]
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
           (venta_id, fecha, usuario_ref, producto_id, nombre_producto, cantidad, precio_unitario, subtotal, total_venta)
           VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)`,
          [
            venta.id,
            venta.fecha,
            String(usuario_id),
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
      await client.query('ROLLBACK');
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
