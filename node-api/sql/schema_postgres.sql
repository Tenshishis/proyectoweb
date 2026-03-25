CREATE TABLE IF NOT EXISTS roles (
  id SMALLSERIAL PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  descripcion VARCHAR(255),
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

INSERT INTO roles (nombre, descripcion)
VALUES
  ('ADMIN', 'Administrador del sistema'),
  ('VENDEDOR', 'Usuario con permisos de ventas'),
  ('CONSULTOR', 'Usuario con acceso a reportes'),
  ('PENDIENTE', 'Usuario pendiente de asignacion de rol')
ON CONFLICT (nombre) DO NOTHING;

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(160) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id SMALLINT NOT NULL REFERENCES roles(id),
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS reporte_ventas (
  id SERIAL PRIMARY KEY,
  venta_id INTEGER NOT NULL,
  fecha TIMESTAMP NOT NULL,
  usuario_id INTEGER NOT NULL REFERENCES users(id),
  usuario_nombre VARCHAR(120) NOT NULL,
  usuario_email VARCHAR(160) NOT NULL,
  usuario_rol VARCHAR(50) NOT NULL,
  producto_id VARCHAR(64) NOT NULL,
  nombre_producto VARCHAR(160) NOT NULL,
  cantidad INTEGER NOT NULL,
  precio_unitario NUMERIC(12,2) NOT NULL,
  subtotal NUMERIC(12,2) NOT NULL,
  total_venta NUMERIC(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_roles_nombre ON roles(nombre);
CREATE INDEX IF NOT EXISTS idx_users_role_id ON users(role_id);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_fecha ON reporte_ventas(fecha);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_producto ON reporte_ventas(producto_id);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_usuario_id ON reporte_ventas(usuario_id);
