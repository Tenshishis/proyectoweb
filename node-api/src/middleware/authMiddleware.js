const jwt = require('jsonwebtoken');
const User = require('../models/User');

exports.verifyToken = async (req, res, next) => {
  const header = req.headers.authorization;
  if (!header) return res.status(401).json({ message: 'No token provided' });

  const parts = header.split(' ');
  if (parts.length !== 2 || parts[0] !== 'Bearer')
    return res.status(401).json({ message: 'Invalid authorization format' });

  const token = parts[1];

  try {
    const payload = jwt.verify(token, process.env.JWT_SECRET);
    // optionally you can reload user from database
    const user = await User.findById(payload.id);
    if (!user) return res.status(401).json({ message: 'User not found' });

    req.user = { id: user._id, rol: user.rol };
    next();
  } catch (err) {
    return res.status(401).json({ message: 'Token invalid or expired' });
  }
};
