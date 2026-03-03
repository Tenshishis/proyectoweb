const adminService = require('../services/adminService');

exports.asignarRol = async (req, res) => {
  try {
    console.log("BODY:", req.body);

    const { userId, nuevoRol } = req.body;

    const result = await adminService.asignarRol(userId, nuevoRol);

    res.json(result);

  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};

exports.listUsers = async (req, res) => {
  try {
    const users = await adminService.listAllUsers();
    res.json({ users });
  } catch (err) {
    res.status(500).json({ error: 'Error fetching users' });
  }
};

exports.listAllUsers = async (req, res) => {
  try {
    const users = await adminService.listAllUsers();
    res.json({ users });
  } catch (err) {
    res.status(500).json({ error: 'Error fetching users' });
  }
};

exports.deleteUser = async (req, res) => {
  try {
    const userId = req.params.id;
    const result = await adminService.deleteUser(userId);
    res.json(result);
  } catch (err) {
    res.status(err.status || 500).json({ error: err.message });
  }
};