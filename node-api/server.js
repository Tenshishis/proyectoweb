const path = require('path');
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

// Handle register POST (proxy to API)
app.post('/register', async (req, res) => {
  try {
    const response = await fetch(`${process.env.API_BASE || 'http://localhost:4000'}/api/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(req.body)
    });
    const data = await response.json();
    if (response.status === 201) {
      res.send('<h3>Registrado. Espera que un administrador te asigne un rol.</h3><a href="/login">Iniciar sesión</a>');
    } else {
      res.status(response.status).send(`<h3>Error: ${data.error || data}</h3><a href="/register">Volver</a>`);
    }
  } catch (err) {
    res.status(500).send('<h3>Error de servidor</h3>');
  }
});

// Handle login POST (proxy to API)
app.post('/login', async (req, res) => {
  try {
    const response = await fetch(`${process.env.API_BASE || 'http://localhost:4000'}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(req.body)
    });
    const data = await response.json();
    if (response.status === 200) {
      res.send('<h3>Login exitoso. (Aquí deberías redirigir al panel de usuario)</h3>');
    } else {
      res.status(response.status).send(`<h3>Error: ${data.error || data}</h3><a href="/login">Volver</a>`);
    }
  } catch (err) {
    res.status(500).send('<h3>Error de servidor</h3>');
  }
});
require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const db = require('./src/config/db');
const routes = require('./src/routes');
const cors = require('cors');

const app = express();

// middlewares
app.use(bodyParser.json());

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
