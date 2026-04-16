const mongoose = require('mongoose');
const Producto = require('../models/Producto');
const defaultProducts = require('./defaultProducts');
require('dotenv').config();

async function run() {
  if (!process.env.MONGO_URI) {
    throw new Error('MONGO_URI no esta configurado');
  }

  await mongoose.connect(process.env.MONGO_URI);

  const seedNames = defaultProducts.map((producto) => producto.nombre);

  await Producto.deleteMany({ nombre: { $nin: seedNames } });

  for (const producto of defaultProducts) {
    await Producto.updateOne(
      { nombre: producto.nombre },
      { $set: producto },
      { upsert: true }
    );
  }

  console.log(`Productos sincronizados en MongoDB: ${defaultProducts.length}`);
  await mongoose.disconnect();
}

run()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });