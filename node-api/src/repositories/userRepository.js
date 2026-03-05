const User = require('../models/User');

class UserRepository {
  async create(data) {
    return User.create(data);
  }

  async findByEmailOrUsername(identifier) {
    return User.findOne({
      $or: [
        { email: identifier },
        { username: identifier }
      ]
    });
  }

  async findById(id) {
    return User.findById(id);
  }

  async findNonAdminUsers() {
    return User.find({ rol: { $ne: 'ADMIN' } }).select('nombre username email rol');
  }

  async findAllUsers() {
    return User.find({}).select('nombre username email rol');
  }

  async deleteById(userId) {
    return User.findByIdAndDelete(userId);
  }

  async updateRole(userId, rol) {
    return User.findByIdAndUpdate(userId, { rol }, { new: true });
  }
}

module.exports = new UserRepository();