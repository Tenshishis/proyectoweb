-- Crear extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Tabla de usuarios (para autenticación JWT)
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL CHECK(rol IN ('admin', 'vendedor', 'consultor')),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Tabla de categorías (1FN, 2FN, 3FN)
CREATE TABLE IF NOT EXISTS categorias (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    CONSTRAINT nombre_no_vacio CHECK (TRIM(nombre) != '')
);

-- Tabla de proveedores (1FN, 2FN, 3FN)
CREATE TABLE IF NOT EXISTS proveedores (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    nombre VARCHAR(150) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    pais VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    CONSTRAINT nombre_proveedor_no_vacio CHECK (TRIM(nombre) != '')
);

-- Tabla de productos (1FN, 2FN, 3FN)
-- Depende de categorias y proveedores (relación muchos a muchos con proveedores)
CREATE TABLE IF NOT EXISTS productos (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    categoria_id INTEGER NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL CHECK(precio_unitario > 0),
    sku VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(50) UNIQUE,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    CONSTRAINT fk_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    CONSTRAINT nombre_producto_no_vacio CHECK (TRIM(nombre) != ''),
    CONSTRAINT sku_no_vacio CHECK (TRIM(sku) != '')
);

-- Tabla intermedia: relación muchos a muchos entre productos y proveedores
CREATE TABLE IF NOT EXISTS producto_proveedor (
    id SERIAL PRIMARY KEY,
    producto_id INTEGER NOT NULL,
    proveedor_id INTEGER NOT NULL,
    codigo_proveedor VARCHAR(50),
    precio_costo DECIMAL(10, 2) NOT NULL CHECK(precio_costo > 0),
    lead_time_dias INTEGER DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    CONSTRAINT fk_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT,
    CONSTRAINT unique_producto_proveedor UNIQUE(producto_id, proveedor_id)
);

-- Tabla de inventario (1FN, 2FN, 3FN)
CREATE TABLE IF NOT EXISTS inventario (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    producto_id INTEGER NOT NULL,
    cantidad_disponible INTEGER DEFAULT 0 CHECK(cantidad_disponible >= 0),
    cantidad_reservada INTEGER DEFAULT 0 CHECK(cantidad_reservada >= 0),
    cantidad_minima INTEGER DEFAULT 10 CHECK(cantidad_minima >= 0),
    cantidad_maxima INTEGER DEFAULT 1000 CHECK(cantidad_maxima >= cantidad_minima),
    ubicacion_almacen VARCHAR(100),
    lote VARCHAR(50),
    fecha_vencimiento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventario_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    CONSTRAINT unique_inventario_por_producto UNIQUE(producto_id)
);

-- Tabla de movimientos de inventario (para auditoría)
CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    producto_id INTEGER NOT NULL,
    tipo_movimiento VARCHAR(50) NOT NULL CHECK(tipo_movimiento IN ('entrada', 'salida', 'ajuste', 'reserva', 'liberacion_reserva')),
    cantidad INTEGER NOT NULL,
    cantidad_anterior INTEGER,
    cantidad_nueva INTEGER,
    motivo TEXT,
    usuario_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimiento_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movimiento_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla desnormalizada para reportes de ventas (reporte_ventas)
-- Esta tabla está optimizada para consultas de reportes y no para transacciones
CREATE TABLE IF NOT EXISTS reporte_ventas (
    id SERIAL PRIMARY KEY,
    fecha_venta DATE NOT NULL,
    producto_id INTEGER NOT NULL,
    categoria_nombre VARCHAR(100),
    proveedor_id INTEGER,
    proveedor_nombre VARCHAR(150),
    cantidad_vendida INTEGER NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    total_venta DECIMAL(12, 2) NOT NULL,
    usuario_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reporte_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_reporte_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Índices para mejorar rendimiento
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_uuid ON usuarios(uuid);
CREATE INDEX idx_categorias_uuid ON categorias(uuid);
CREATE INDEX idx_proveedores_uuid ON proveedores(uuid);
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_sku ON productos(sku);
CREATE INDEX idx_productos_uuid ON productos(uuid);
CREATE INDEX idx_producto_proveedor_producto ON producto_proveedor(producto_id);
CREATE INDEX idx_producto_proveedor_proveedor ON producto_proveedor(proveedor_id);
CREATE INDEX idx_inventario_producto ON inventario(producto_id);
CREATE INDEX idx_inventario_uuid ON inventario(uuid);
CREATE INDEX idx_movimientos_producto ON movimientos_inventario(producto_id);
CREATE INDEX idx_movimientos_usuario ON movimientos_inventario(usuario_id);
CREATE INDEX idx_movimientos_fecha ON movimientos_inventario(created_at);
CREATE INDEX idx_reporte_ventas_fecha ON reporte_ventas(fecha_venta);
CREATE INDEX idx_reporte_ventas_producto ON reporte_ventas(producto_id);
CREATE INDEX idx_reporte_ventas_usuario ON reporte_ventas(usuario_id);

-- Función para actualizar updated_at automáticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Triggers para actualizar updated_at
CREATE TRIGGER update_usuarios_updated_at BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_categorias_updated_at BEFORE UPDATE ON categorias
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_proveedores_updated_at BEFORE UPDATE ON proveedores
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_productos_updated_at BEFORE UPDATE ON productos
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_producto_proveedor_updated_at BEFORE UPDATE ON producto_proveedor
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_inventario_updated_at BEFORE UPDATE ON inventario
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Tabla de ventas
CREATE TABLE IF NOT EXISTS ventas (
    id SERIAL PRIMARY KEY,
    uuid UUID UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
    numero_venta VARCHAR(50) UNIQUE NOT NULL,
    usuario_id INTEGER NOT NULL,
    total DECIMAL(12, 2) DEFAULT 0 CHECK(total >= 0),
    estado VARCHAR(50) NOT NULL CHECK(estado IN ('pendiente', 'completada', 'cancelada')) DEFAULT 'pendiente',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    CONSTRAINT fk_venta_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- Tabla de items de venta (detalles de cada producto en una venta)
CREATE TABLE IF NOT EXISTS venta_items (
    id SERIAL PRIMARY KEY,
    venta_id INTEGER NOT NULL,
    producto_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL CHECK(cantidad > 0),
    precio_unitario DECIMAL(10, 2) NOT NULL CHECK(precio_unitario > 0),
    descuento DECIMAL(5, 2) DEFAULT 0 CHECK(descuento >= 0 AND descuento <= 100),
    subtotal DECIMAL(12, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_venta_item_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    CONSTRAINT fk_venta_item_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
);

-- Índices para optimizar queries de ventas
CREATE INDEX idx_ventas_uuid ON ventas(uuid);
CREATE INDEX idx_ventas_numero_venta ON ventas(numero_venta);
CREATE INDEX idx_ventas_usuario_id ON ventas(usuario_id);
CREATE INDEX idx_ventas_estado ON ventas(estado);
CREATE INDEX idx_ventas_fecha ON ventas(created_at);
CREATE INDEX idx_venta_items_venta_id ON venta_items(venta_id);
CREATE INDEX idx_venta_items_producto_id ON venta_items(producto_id);

-- Índices para optimizar tabla de reportes
CREATE INDEX idx_reporte_ventas_fecha ON reporte_ventas(fecha_venta);
CREATE INDEX idx_reporte_ventas_producto ON reporte_ventas(producto_id);
CREATE INDEX idx_reporte_ventas_categoria ON reporte_ventas(categoria_nombre);
CREATE INDEX idx_reporte_ventas_proveedor ON reporte_ventas(proveedor_id);
CREATE INDEX idx_reporte_ventas_usuario ON reporte_ventas(usuario_id);

-- Trigger para actualizar updated_at en ventas
CREATE TRIGGER update_ventas_updated_at BEFORE UPDATE ON ventas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Seed inicial de datos
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónica', 'Productos electrónicos variados'),
('Ropa', 'Prendas de vestir'),
('Alimentos', 'Productos alimenticios'),
('Hogar', 'Artículos para el hogar')
ON CONFLICT (nombre) DO NOTHING;

INSERT INTO proveedores (nombre, email, telefono, ciudad, pais) VALUES
('Proveedor Global SA', 'info@proveedor-global.com', '+34 912 34 56 78', 'Madrid', 'España'),
('Importadores Unidos', 'contacto@importadores.com', '+34 934 56 78 90', 'Barcelona', 'España'),
('Comercio Directo', 'ventas@comercio-directo.com', '+34 954 32 10 98', 'Sevilla', 'España'),
('Distribuidores Internacionales', 'info@distr-int.com', '+34 919 87 65 43', 'Madrid', 'España')
ON CONFLICT (nombre) DO NOTHING;

-- Usuario admin por defecto (contraseña: admin123456)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@tienda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Vendedor Demo', 'vendedor@tienda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
('Consultor Demo', 'consultor@tienda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consultor')
ON CONFLICT (email) DO NOTHING;
