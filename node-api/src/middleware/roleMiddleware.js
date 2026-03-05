exports.authorize = (...rolesPermitidos) => {
  // allow both authorize('ADMIN') and authorize(['ADMIN','GERENTE'])
  const allowed = Array.isArray(rolesPermitidos[0]) ? rolesPermitidos[0] : rolesPermitidos;

  return (req, res, next) => {
    if (!req.user || !allowed.includes(req.user.rol))
      return res.status(403).json({ message: "No autorizado" });
    next();
  };
};
