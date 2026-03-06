const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");
const userRepo = require("../repositories/userRepository");

class AuthService {
  async register(data) {
    const { nombre, username, email, password } = data;

    const hashedPassword = await bcrypt.hash(password, 10);

    // 🔥 usamos el repository, no el modelo directo
    const user = await userRepo.create({
      nombre,
      username,
      email,
      password: hashedPassword
      // rol NO se envía, usa default "PENDIENTE"
    });

    return {
      id: user._id,
      nombre: user.nombre,
      username: user.username,
      email: user.email,
      rol: user.rol,
      activo: user.activo
    };
  }

  async login(identifier, password) {
    const user = await userRepo.findByEmailOrUsername(identifier);

    if (!user) {
      const error = new Error("Usuario no encontrado");
      error.status = 404;
      throw error;
    }

    if (user.rol === "PENDIENTE") {
      const error = new Error("Usuario pendiente de asignación de rol");
      error.status = 403;
      throw error;
    }

    const valid = await bcrypt.compare(password, user.password);
    if (!valid) {
      const error = new Error("Credenciales inválidas");
      error.status = 401;
      throw error;
    }

    const token = jwt.sign(
      { id: user._id, rol: user.rol },
      process.env.JWT_SECRET,
      { expiresIn: "4h" }
    );

    return {
      token,
      user: {
        id: user._id,
        nombre: user.nombre,
        rol: user.rol
      }
    };
  }
}

module.exports = new AuthService();