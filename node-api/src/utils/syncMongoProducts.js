const Producto = require('../models/Producto');

async function syncMongoProducts(products) {
  const seedNames = products.map((producto) => producto.nombre);

  await Producto.deleteMany({ nombre: { $nin: seedNames } });

  for (const producto of products) {
    await Producto.updateOne(
      { nombre: producto.nombre },
      { $set: producto },
      { upsert: true }
    );
  }
}

module.exports = syncMongoProducts;