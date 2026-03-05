const Joi = require("joi");

exports.registerSchema = Joi.object({
  nombre: Joi.string().min(3).required(),
  username: Joi.string().min(4).required(),
  email: Joi.string().email().required(),
  password: Joi.string().min(6).required()
});

exports.loginSchema = Joi.object({
  identifier: Joi.string().required(),
  password: Joi.string().required()
});
