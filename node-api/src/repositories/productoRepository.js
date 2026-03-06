const db = require('../config/db');

class ProductoRepository {
  async getAll() {
    const { rows } = await db.query(
      `SELECT id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at
       FROM productos
       WHERE activo = TRUE
       ORDER BY id DESC`
    );
    return rows;
  }

  async getAllForAdmin() {
    const { rows } = await db.query(
      `SELECT id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at
       FROM productos
       ORDER BY id DESC`
    );
    return rows;
  }

  async getById(id) {
    const { rows } = await db.query(
      `SELECT id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at
       FROM productos
       WHERE id = $1
       LIMIT 1`,
      [id]
    );
    return rows[0] || null;
  }

  async searchByKeyword(keyword) {
    const { rows } = await db.query(
      `SELECT id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at
       FROM productos
       WHERE activo = TRUE
         AND (nombre ILIKE $1 OR descripcion ILIKE $1)
       ORDER BY id DESC`,
      [`%${keyword}%`]
    );
    return rows;
  }

  async getByCategory(categoria) {
    const { rows } = await db.query(
      `SELECT id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at
       FROM productos
       WHERE activo = TRUE AND categoria = $1
       ORDER BY id DESC`,
      [categoria]
    );
    return rows;
  }

  async create(data) {
    const { nombre, descripcion = '', precio, categoria = 'General', stock = 0, activo = true } = data;
    const { rows } = await db.query(
      `INSERT INTO productos (nombre, descripcion, precio, categoria, stock, activo)
       VALUES ($1, $2, $3, $4, $5, $6)
       RETURNING id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at`,
      [nombre, descripcion, precio, categoria, stock, activo]
    );
    return rows[0];
  }

  async updateById(id, data) {
    const fields = [];
    const values = [];

    const allowed = ['nombre', 'descripcion', 'precio', 'categoria', 'stock', 'activo'];
    for (const key of allowed) {
      if (Object.prototype.hasOwnProperty.call(data, key)) {
        fields.push(`${key} = $${values.length + 1}`);
        values.push(data[key]);
      }
    }

    if (fields.length === 0) {
      return this.getById(id);
    }

    values.push(id);
    const { rows } = await db.query(
      `UPDATE productos
       SET ${fields.join(', ')}
       WHERE id = $${values.length}
       RETURNING id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at`,
      values
    );
    return rows[0] || null;
  }

  async softDeleteById(id) {
    const { rows } = await db.query(
      `UPDATE productos
       SET activo = FALSE
       WHERE id = $1
       RETURNING id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at`,
      [id]
    );
    return rows[0] || null;
  }

  async decrementStockIfAvailable(id, cantidad) {
    const { rows } = await db.query(
      `UPDATE productos
       SET stock = stock - $2
       WHERE id = $1 AND activo = TRUE AND stock >= $2
       RETURNING id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at`,
      [id, cantidad]
    );
    return rows[0] || null;
  }

  async incrementStock(id, cantidad) {
    const { rows } = await db.query(
      `UPDATE productos
       SET stock = stock + $2
       WHERE id = $1
       RETURNING id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at`,
      [id, cantidad]
    );
    return rows[0] || null;
  }

  async reactivateAndAddStock(id, cantidad) {
    const { rows } = await db.query(
      `UPDATE productos
       SET activo = TRUE,
           stock = stock + $2
       WHERE id = $1
       RETURNING id, nombre, descripcion, precio, categoria, stock, activo, created_at, updated_at`,
      [id, cantidad]
    );
    return rows[0] || null;
  }
}

module.exports = new ProductoRepository();
