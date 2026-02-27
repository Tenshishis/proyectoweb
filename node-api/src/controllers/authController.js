const authService = require("../services/authService");
const { registerSchema, loginSchema } = require("../validators/authValidator");

exports.register = async (req, res) => {
  try {
    await registerSchema.validateAsync(req.body);
    const user = await authService.register(req.body);

    res.status(201).json({
      message: "Usuario registrado. Esperando asignación de rol.",
      user
    });
  } catch (err) {
    res.status(400).json({ error: err.message });
  }
};

exports.login = async (req, res) => {
  try {
    await loginSchema.validateAsync(req.body);

    const { identifier, password } = req.body;
    const result = await authService.login(identifier, password);

    if (!result.user.rol)
      return res.status(403).json({
        message: "Usuario pendiente de asignación de rol"
      });

    res.json(result);
  } catch (err) {
    res.status(401).json({ error: err.message });
  }
};
