const userRepo = require("../repositories/userRepository");
const VALID_ROLES = require("../utils/roles");

class AdminService {

  async asignarRol(userId, nuevoRol) {

    if (!nuevoRol) {
      const error = new Error("Debe proporcionar un rol");
      error.status = 400;
      throw error;
    }

    // 🔥 Normalización profesional
    const rolNormalizado = nuevoRol.trim().toUpperCase();

    if (!VALID_ROLES.includes(rolNormalizado)) {
      const error = new Error("Rol inválido");
      error.status = 400;
      throw error;
    }

    const user = await userRepo.updateRole(userId, rolNormalizado);

    if (!user) {
      const error = new Error("Usuario no encontrado");
      error.status = 404;
      throw error;
    }

    return {
      message: "Rol actualizado correctamente",
      user: {
        id: user._id,
        nombre: user.nombre,
        rol: user.rol
      }
    };
  }
  
    async listNonAdminUsers() {
      return userRepo.findNonAdminUsers();
    }

    async listAllUsers() {
      return userRepo.findAllUsers();
    }

    async deleteUser(userId) {
      const user = await userRepo.findById(userId);
      if (!user) {
        const error = new Error('Usuario no encontrado');
        error.status = 404;
        throw error;
      }


      // Allow deleting/changing admins (including self)

      const deleted = await userRepo.deleteById(userId);
      return { message: 'Usuario eliminado', user: { id: deleted._id, username: deleted.username, email: deleted.email } };
    }
}

module.exports = new AdminService();