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
              <a href="/api/auth/register" class="btn btn-primary btn-lg me-2">Registrarse (API)</a>
              <a href="/api/auth/login" class="btn btn-outline-primary btn-lg">Iniciar Sesión (API)</a>
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
