const bcrypt = require('bcrypt');
const db = require('../config/db');
require('dotenv').config();

async function run() {
  await db.connect();
  const pwd = await bcrypt.hash('admin123', 10);
  await db.query(
    `INSERT INTO users (nombre, username, email, password, role_id, activo)
     VALUES (
       $1,
       $2,
       $3,
       $4,
       (SELECT id FROM roles WHERE nombre = 'ADMIN'),
       TRUE
     )
     ON CONFLICT (email) DO UPDATE
     SET nombre = EXCLUDED.nombre,
         username = EXCLUDED.username,
         password = EXCLUDED.password,
         role_id = EXCLUDED.role_id,
         activo = TRUE,
         updated_at = NOW()`,
    ['Administrador', 'admin', 'admin@local', pwd]
  );
  console.log('Admin creado');
}

run()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
