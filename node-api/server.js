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
