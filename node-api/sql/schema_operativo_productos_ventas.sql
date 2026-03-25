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

CREATE TABLE IF NOT EXISTS ventas (
  id SERIAL PRIMARY KEY,
  fecha TIMESTAMP NOT NULL DEFAULT NOW(),
  usuario_id INTEGER NOT NULL REFERENCES users(id),
  total NUMERIC(12,2) NOT NULL CHECK (total >= 0),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS detalle_ventas (
  id SERIAL PRIMARY KEY,
  venta_id INTEGER NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
  producto_id VARCHAR(64) NOT NULL,
  nombre_producto VARCHAR(160) NOT NULL,
  cantidad INTEGER NOT NULL CHECK (cantidad > 0),
  precio_unitario NUMERIC(12,2) NOT NULL CHECK (precio_unitario >= 0),
  subtotal NUMERIC(12,2) NOT NULL CHECK (subtotal >= 0)
);

CREATE TABLE IF NOT EXISTS reporte_ventas (
  id SERIAL PRIMARY KEY,
  venta_id INTEGER NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
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

-- Compatibility patch for legacy databases.
-- If tables existed with old columns (e.g. usuario_ref), add missing fields
-- so index creation and runtime queries do not fail on first deploy.
ALTER TABLE ventas
  ADD COLUMN IF NOT EXISTS usuario_id INTEGER;

ALTER TABLE detalle_ventas
  ADD COLUMN IF NOT EXISTS producto_id VARCHAR(64);

ALTER TABLE detalle_ventas
  ALTER COLUMN producto_id TYPE VARCHAR(64)
  USING producto_id::VARCHAR(64);

ALTER TABLE reporte_ventas
  ADD COLUMN IF NOT EXISTS usuario_id INTEGER,
  ADD COLUMN IF NOT EXISTS usuario_nombre VARCHAR(120),
  ADD COLUMN IF NOT EXISTS usuario_email VARCHAR(160),
  ADD COLUMN IF NOT EXISTS usuario_rol VARCHAR(50),
  ADD COLUMN IF NOT EXISTS producto_id VARCHAR(64);

ALTER TABLE reporte_ventas
  ALTER COLUMN producto_id TYPE VARCHAR(64)
  USING producto_id::VARCHAR(64);

CREATE INDEX IF NOT EXISTS idx_roles_nombre ON roles(nombre);
CREATE INDEX IF NOT EXISTS idx_users_role_id ON users(role_id);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_ventas_usuario_id ON ventas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_ventas_fecha ON ventas(fecha);
CREATE INDEX IF NOT EXISTS idx_detalle_ventas_venta_id ON detalle_ventas(venta_id);
CREATE INDEX IF NOT EXISTS idx_detalle_ventas_producto_id ON detalle_ventas(producto_id);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_fecha ON reporte_ventas(fecha);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_producto ON reporte_ventas(producto_id);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_usuario_id ON reporte_ventas(usuario_id);
