const Joi = require('joi');

const createVentaSchema = Joi.object({
  fecha: Joi.date().optional(),
  productos: Joi.array()
    .items(
      Joi.object({
        id_producto: Joi.string().trim().required(),
        cantidad: Joi.number().integer().greater(0).required()
      })
    )
    .min(1)
    .required()
});

module.exports = {
  createVentaSchema
};
