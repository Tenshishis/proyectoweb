CREATE TABLE IF NOT EXISTS reporte_ventas (
  id SERIAL PRIMARY KEY,
  venta_id INTEGER NOT NULL,
  fecha TIMESTAMP NOT NULL,
  usuario_ref VARCHAR(64) NOT NULL,
  producto_id INTEGER NOT NULL,
  nombre_producto VARCHAR(160) NOT NULL,
  cantidad INTEGER NOT NULL,
  precio_unitario NUMERIC(12,2) NOT NULL,
  subtotal NUMERIC(12,2) NOT NULL,
  total_venta NUMERIC(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_reporte_ventas_fecha ON reporte_ventas(fecha);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_producto ON reporte_ventas(producto_id);
CREATE INDEX IF NOT EXISTS idx_reporte_ventas_usuario_ref ON reporte_ventas(usuario_ref);
