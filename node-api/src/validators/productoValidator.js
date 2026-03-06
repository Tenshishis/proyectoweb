const Joi = require('joi');

const createProductoSchema = Joi.object({
  nombre: Joi.string().trim().min(2).required(),
  descripcion: Joi.string().allow('').optional(),
  precio: Joi.number().greater(0).required(),
  categoria: Joi.string().trim().default('General'),
  stock: Joi.number().integer().min(0).default(0),
  activo: Joi.boolean().optional()
});

const updateProductoSchema = Joi.object({
  nombre: Joi.string().trim().min(2).optional(),
  descripcion: Joi.string().allow('').optional(),
  precio: Joi.number().greater(0).optional(),
  categoria: Joi.string().trim().optional(),
  stock: Joi.number().integer().min(0).optional(),
  activo: Joi.boolean().optional()
}).min(1);

module.exports = {
  createProductoSchema,
  updateProductoSchema
};
