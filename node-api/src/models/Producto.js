const mongoose = require('mongoose');

const productoSchema = new mongoose.Schema(
  {
    nombre: { type: String, required: true, trim: true },
    descripcion: { type: String, default: '', trim: true },
    precio: { type: Number, required: true, min: 0 },
    categoria: { type: String, default: 'General', trim: true },
    stock: { type: Number, required: true, min: 0, default: 0 },
    activo: { type: Boolean, default: true }
  },
  { timestamps: true }
);

productoSchema.index({ nombre: 'text', descripcion: 'text' });

module.exports = mongoose.model('Producto', productoSchema);