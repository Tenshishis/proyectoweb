require('dotenv').config();
const express = require('express');
const db = require('./src/config/db');
const routes = require('./src/routes');
const cors = require('cors');


const app = express();

function getAllowedOrigins() {
  const defaults = ['http://127.0.0.1:5000', 'http://localhost:5000'];
  const rawOrigins = process.env.CORS_ORIGINS || '';
  const envOrigins = rawOrigins
    .split(',')
    .map(origin => origin.trim())
    .filter(Boolean);

  return [...new Set([...defaults, ...envOrigins])];
}
// Parse request bodies before routes
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// enable CORS for frontend domains
const allowedOrigins = getAllowedOrigins();
app.use(cors({ origin: allowedOrigins, credentials: true }));

app.get('/', (req, res) => {
  res.json({
    service: 'proyectoweb-backend',
    status: 'ok',
    docs: 'Use /api/* endpoints from the Python frontend service'
  });
});
// routes
app.use('/api', routes);

// Debug route: list registered routes for quick API inspection
app.get('/_routes', (req, res) => {
  try {
    const routes = [];
    app._router.stack.forEach(mw => {
      if (mw.route && mw.route.path) {
        const methods = Object.keys(mw.route.methods).join(',').toUpperCase();
        routes.push({ path: mw.route.path, methods });
      }
    });
    res.json(routes);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// error handler
app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).json({ error: 'Server error' });
});

const PORT = process.env.PORT || 4000;

db.connect()
  .then(() => {
    app.listen(PORT, () => {
      console.log(`Server running on port ${PORT}`);
    });
  })
  .catch(err => {
    console.error('Database connection failed', err);
  });
