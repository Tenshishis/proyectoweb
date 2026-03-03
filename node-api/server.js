// ...existing code...
// Serve admin redirect page (only for admin)
app.get('/admin-redirect', (req, res) => {
  const user = getUserFromCookie(req);
  if (!user || user.rol !== 'ADMIN') return res.status(403).send('<h3>No autorizado</h3>');
  res.sendFile(path.join(__dirname, 'public', 'templates', 'admin_redirect.html'));
});
// Helper to get user from JWT cookie
function getUserFromCookie(req) {
  const token = req.cookies.token;
  if (!token) return null;
  try {
    const jwt = require('jsonwebtoken');
    return jwt.verify(token, process.env.JWT_SECRET);
  } catch {
    return null;
  }
}
const cookieParser = require('cookie-parser');
const { verifyToken } = require('./src/middleware/authMiddleware');
const { authorize } = require('./src/middleware/roleMiddleware');
const fetch = require('node-fetch');

require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const db = require('./src/config/db');
const routes = require('./src/routes');
const cors = require('cors');
const path = require('path');


const app = express();
app.use(cookieParser());

// Serve static files (Bootstrap CDN used, but for images/assets if needed)
app.use('/static', express.static(path.join(__dirname, 'public')));

// Serve register page
app.get('/register', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'templates', 'register.html'));
});

// Serve login page
app.get('/login', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'templates', 'login.html'));
});

// Parse request bodies before routes
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Handle register POST (proxy to API)
// Directly call controller logic for register
const authController = require('./src/controllers/authController');
app.post('/register', async (req, res) => {
  // Use a mock response object to capture controller output
  let statusSent = 200;
  let jsonSent = null;
  let errorSent = null;
  const mockRes = {
    status(code) { statusSent = code; return this; },
    json(obj) { jsonSent = obj; return this; },
    send(obj) { jsonSent = obj; return this; }
  };
  try {
    await authController.register(req, mockRes);
    if (statusSent === 201) {
      res.send('<h3>Registrado. Espera que un administrador te asigne un rol.</h3><a href="/login">Iniciar sesión</a>');
    } else {
      res.status(statusSent).send(`<h3>Error: ${(jsonSent && (jsonSent.error || jsonSent.message)) || jsonSent}</h3><a href="/register">Volver</a>`);
    }
  } catch (err) {
    console.error('Register error:', err);
    res.status(500).send('<h3>Error de servidor</h3>');
  }
});

// Handle login POST (proxy to API)
app.post('/login', async (req, res) => {
  let statusSent = 200;
  let jsonSent = null;
  const mockRes = {
    status(code) { statusSent = code; return this; },
    json(obj) { jsonSent = obj; return this; },
    send(obj) { jsonSent = obj; return this; }
  };
  try {
    await authController.login(req, mockRes);
    if (statusSent === 200 && jsonSent && jsonSent.token) {
      res.cookie('token', jsonSent.token, { httpOnly: true });
      const jwt = require('jsonwebtoken');
      const payload = jwt.decode(jsonSent.token);
      if (payload && payload.rol === 'ADMIN') {
        res.redirect('/admin-redirect');
      } else if (payload && payload.rol === 'VENDEDOR') {
        res.redirect('/vendedor');
      } else if (payload && payload.rol === 'CONSULTOR') {
        res.redirect('/consultor');
      } else {
        res.redirect('/espera-rol');
      }
    } else {
      res.status(statusSent).send(`<h3>Error: ${(jsonSent && (jsonSent.error || jsonSent.message)) || jsonSent}</h3><a href="/login">Volver</a>`);
    }
  } catch (err) {
    console.error('Login error:', err);
    res.status(500).send('<h3>Error de servidor</h3>');
  }
});

// middlewares
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// enable CORS for frontend (Flask default: http://127.0.0.1:5000)
app.use(cors({ origin: ['http://127.0.0.1:5000','http://localhost:5000'], credentials: true }));


// root route placeholder
app.get('/', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Bienvenido a ProyectoWeb</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center" style="height: 100vh;">
      <div class="container text-center">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="card shadow p-4">
              <h1 class="mb-4">Bienvenido a ProyectoWeb</h1>
              <p class="mb-4">Sistema de registro, login y roles.</p>
              <a href="/register" class="btn btn-primary btn-lg me-2">Registrarse</a>
              <a href="/login" class="btn btn-outline-primary btn-lg">Iniciar Sesión</a>
            </div>
          </div>
        </div>
      </div>
    </body>
    </html>
  `);
});

// routes
app.use('/api', routes);

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
