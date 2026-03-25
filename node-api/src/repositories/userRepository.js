const db = require('../config/db');

class UserRepository {
  async create(data) {
    const { nombre, username, email, password, rol = 'PENDIENTE', activo = true } = data;
    const { rows } = await db.query(
      `INSERT INTO users (nombre, username, email, password, role_id, activo)
       VALUES (
         $1,
         $2,
         $3,
         $4,
         (SELECT id FROM roles WHERE nombre = $5),
         $6
       )
       RETURNING id, nombre, username, email, activo, created_at, updated_at,
         (SELECT nombre FROM roles WHERE id = users.role_id) AS rol`,
      [nombre, username, email, password, rol, activo]
    );
    return rows[0] || null;
  }

  async findByEmailOrUsername(identifier) {
    const { rows } = await db.query(
      `SELECT u.id, u.nombre, u.username, u.email, u.password, u.activo, u.created_at, u.updated_at,
              r.nombre AS rol
       FROM users u
       JOIN roles r ON r.id = u.role_id
       WHERE u.email = $1 OR u.username = $1
       LIMIT 1`,
      [identifier]
    );
    return rows[0] || null;
  }

  async findById(id) {
    const { rows } = await db.query(
      `SELECT u.id, u.nombre, u.username, u.email, u.password, u.activo, u.created_at, u.updated_at,
              r.nombre AS rol
       FROM users u
       JOIN roles r ON r.id = u.role_id
       WHERE u.id = $1
       LIMIT 1`,
      [id]
    );
    return rows[0] || null;
  }

  async findNonAdminUsers() {
    const { rows } = await db.query(
      `SELECT u.id, u.nombre, u.username, u.email, u.activo, r.nombre AS rol
       FROM users u
       JOIN roles r ON r.id = u.role_id
       WHERE r.nombre <> 'ADMIN'
       ORDER BY u.id DESC`
    );
    return rows;
  }

  async findAllUsers() {
    const { rows } = await db.query(
      `SELECT u.id, u.nombre, u.username, u.email, u.activo, r.nombre AS rol
       FROM users u
       JOIN roles r ON r.id = u.role_id
       ORDER BY u.id DESC`
    );
    return rows;
  }

  async deleteById(userId) {
    const { rows } = await db.query(
      `DELETE FROM users
       WHERE id = $1
       RETURNING id, nombre, username, email`,
      [userId]
    );
    return rows[0] || null;
  }

  async updateRole(userId, rol) {
    const { rows } = await db.query(
      `UPDATE users
       SET role_id = (SELECT id FROM roles WHERE nombre = $2),
           updated_at = NOW()
       WHERE id = $1
       RETURNING id, nombre, username, email, activo, created_at, updated_at,
         (SELECT nombre FROM roles WHERE id = users.role_id) AS rol`,
      [userId, rol]
    );
    return rows[0] || null;
  }
}

module.exports = new UserRepository();