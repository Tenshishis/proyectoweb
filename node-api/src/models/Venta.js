const mongoose = require('mongoose');

const ventaProductoSchema = new mongoose.Schema(
  {
    id_producto: {
      type: mongoose.Schema.Types.ObjectId,
      ref: 'Producto',
      required: true
    },
    nombre: { type: String, required: true },
    cantidad: { type: Number, required: true, min: 1 },
    precio_unitario: { type: Number, required: true, min: 0 },
    subtotal: { type: Number, required: true, min: 0 }
  },
  { _id: false }
);

const ventaSchema = new mongoose.Schema(
  {
    fecha: { type: Date, default: Date.now },
    usuario: {
      id: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
      rol: { type: String, required: true }
    },
    productos: { type: [ventaProductoSchema], required: true },
    total: { type: Number, required: true, min: 0 }
  },
  { timestamps: true }
);

module.exports = mongoose.model('Venta', ventaSchema);
