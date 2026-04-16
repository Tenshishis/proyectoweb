const mongoose = require('mongoose');
const defaultProducts = require('./defaultProducts');
const syncMongoProducts = require('./syncMongoProducts');
require('dotenv').config();

async function run() {
  if (!process.env.MONGO_URI) {
    throw new Error('MONGO_URI no esta configurado');
  }

  await mongoose.connect(process.env.MONGO_URI);

  await syncMongoProducts(defaultProducts);

  console.log(`Productos sincronizados en MongoDB: ${defaultProducts.length}`);
  await mongoose.disconnect();
}

run()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });