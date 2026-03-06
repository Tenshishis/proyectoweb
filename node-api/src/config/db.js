const { Pool } = require('pg');
const mongoose = require('mongoose');

const shouldUseSSL =
  process.env.PGSSLMODE === 'require' ||
  process.env.NODE_ENV === 'production';

const pool = new Pool({
  connectionString: process.env.DATABASE_URL || undefined,
  host: process.env.PGHOST,
  port: process.env.PGPORT ? Number(process.env.PGPORT) : undefined,
  database: process.env.PGDATABASE,
  user: process.env.PGUSER,
  password: process.env.PGPASSWORD,
  ssl: shouldUseSSL ? { rejectUnauthorized: false } : false
});

const connect = async () => {
  const client = await pool.connect();
  try {
    await client.query('SELECT 1');
  } finally {
    client.release();
  }

  if (process.env.MONGO_URI && mongoose.connection.readyState === 0) {
    await mongoose.connect(process.env.MONGO_URI);
  }
};

const query = (text, params = []) => pool.query(text, params);
const getClient = () => pool.connect();

module.exports = {
  connect,
  query,
  getClient
};
