const fs = require('fs/promises');
const path = require('path');
const bcrypt = require('bcrypt');
const Producto = require('../models/Producto');
const defaultProducts = require('../utils/defaultProducts');
const syncMongoProducts = require('../utils/syncMongoProducts');

let bootstrapped = false;

async function ensurePostgresSchema(query) {
  const schemaPath = path.join(__dirname, '..', '..', 'sql', 'schema_operativo_productos_ventas.sql');
  const schemaSql = await fs.readFile(schemaPath, 'utf8');
  await query(schemaSql);
}

async function ensureDefaultAdmin(query) {
  const countResult = await query('SELECT COUNT(*)::int AS total FROM users');
  const totalUsers = countResult.rows[0] ? countResult.rows[0].total : 0;
  if (totalUsers > 0) {
    return;
  }

  const adminName = process.env.DEFAULT_ADMIN_NAME || 'Administrador';
  const adminUsername = process.env.DEFAULT_ADMIN_USERNAME || 'admin';
  const adminEmail = process.env.DEFAULT_ADMIN_EMAIL || 'admin@local';
  const adminPassword = process.env.DEFAULT_ADMIN_PASSWORD || 'admin123';
  const hashedPassword = await bcrypt.hash(adminPassword, 10);

  await query(
    `INSERT INTO users (nombre, username, email, password, role_id, activo)
     VALUES (
       $1,
       $2,
       $3,
       $4,
       (SELECT id FROM roles WHERE nombre = 'ADMIN'),
       TRUE
     )`,
    [adminName, adminUsername, adminEmail, hashedPassword]
  );

  console.log(`[bootstrap] Admin inicial creado: ${adminEmail}`);
  if (!process.env.DEFAULT_ADMIN_PASSWORD) {
    console.log('[bootstrap] Credencial temporal por defecto: admin123');
  }
}

async function ensureMongoProducts() {
  const forceSeed = process.env.FORCE_MONGO_PRODUCT_SEED === 'true';
  const totalProductos = await Producto.countDocuments();

  if (totalProductos > 0 && !forceSeed) {
    return;
  }

  await syncMongoProducts(defaultProducts);
  console.log(`[bootstrap] Catalogo de MongoDB sincronizado: ${defaultProducts.length} productos`);
}

async function bootstrapDatastores({ query, mongoReady }) {
  if (bootstrapped) {
    return;
  }

  await ensurePostgresSchema(query);
  await ensureDefaultAdmin(query);

  if (mongoReady) {
    await ensureMongoProducts();
  }

  bootstrapped = true;
}

module.exports = {
  bootstrapDatastores
};