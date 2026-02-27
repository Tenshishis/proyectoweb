// script para crear un usuario administrador rápidamente
const mongoose = require('mongoose');
const bcrypt = require('bcrypt');
const User = require('../models/User');
require('dotenv').config();

async function run() {
  await mongoose.connect(process.env.MONGO_URI, { useNewUrlParser: true, useUnifiedTopology: true });
  const pwd = await bcrypt.hash('admin123', 10);
  const admin = new User({ nombre: 'Administrador', username: 'admin', email: 'admin@local', password: pwd, rol: 'ADMIN' });
  await admin.save();
  console.log('Admin creado');
  mongoose.disconnect();
}

run().catch(console.error);
