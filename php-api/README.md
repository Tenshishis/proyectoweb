# ProyectoWeb - Servicio de Productos e Inventario (PHP + PostgreSQL)

API REST para gestión completa de productos e inventario con autenticación JWT, roles de usuario y validación de stock.

## Características

✅ **CRUD Completo de Productos**
- Crear, leer, actualizar y eliminar productos
- Búsqueda avanzada por nombre, SKU o descripción
- Categorización de productos

✅ **Gestión de Inventario**
- Control separado del inventario
- Validación automática de stock
- Registro de movimientos (entrada, salida, ajuste)
- Reservas de productos
- Alertas de stock bajo

✅ **Autenticación y Autorización**
- Token JWT para seguridad
- Tres roles de usuario: Admin, Vendedor, Consultor
- Control granular de permisos por endpoint

✅ **Base de Datos Normalizada**
- Diseño en 3ª Forma Normal (3FN)
- Tablas: usuarios, categorías, proveedores, productos, inventario, movimientos
- Tabla desnormalizada de reportes para análisis

## Instalación

### Requisitos Previos

- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Composer
- Apache con mod_rewrite habilitado

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repo-url>
cd php-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
# Editar .env con tus credenciales de base de datos
```

4. **Crear la base de datos**
```bash
psql -U postgres -c "CREATE DATABASE proyectoweb;"
```

5. **Ejecutar el script SQL**
```bash
psql -U postgres -d proyectoweb -f sql/schema.sql
```

6. **Iniciar el servidor**
```bash
# Opción 1: PHP built-in server
composer serve

# Opción 2: Apache (configurar vhost)
# Asegurar que el DocumentRoot apunte a /path/to/php-api
```

## Estructura de Carpetas

```
php-api/
├── index.php                 # Punto de entrada principal
├── composer.json             # Dependencias de PHP
├── .env.example              # Variables de entorno (ejemplo)
├── .htaccess                 # Configuración de Apache
├── sql/
│   └── schema.sql            # Script de base de datos
├── src/
│   ├── config/
│   │   ├── app.php           # Configuración general
│   │   └── Database.php      # Conexión PDO
│   ├── controllers/          # Controladores de rutas
│   │   ├── AuthController.php
│   │   ├── ProductoController.php
│   │   ├── InventarioController.php
│   │   ├── UsuarioController.php
│   │   ├── CategoriaController.php
│   │   └── ProveedorController.php
│   ├── models/               # Modelos de datos
│   │   ├── Producto.php
│   │   ├── Inventario.php
│   │   ├── Usuario.php
│   │   └── ...
│   ├── repositories/         # Acceso a datos
│   │   ├── ProductoRepository.php
│   │   ├── InventarioRepository.php
│   │   └── ...
│   ├── services/             # Lógica de negocio
│   │   ├── ProductoService.php
│   │   ├── InventarioService.php
│   │   ├── AuthService.php
│   │   └── ...
│   ├── middleware/           # Middleware
│   │   └── AuthMiddleware.php
│   ├── utils/                # Utilidades
│   │   ├── Response.php
│   │   ├── JwtHandler.php
│   │   └── Validator.php
│   └── Router.php            # Enrutador
```

## Documentación de API

### Autenticación

#### Registrar Usuario
```http
POST /auth/register
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "password": "securePassword123",
  "confirmPassword": "securePassword123"
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "securePassword123"
}

# Respuesta
{
  "success": true,
  "message": "Sesión iniciada exitosamente",
  "data": {
    "token": "eyJhbGc...",
    "user": {
      "id": 1,
      "uuid": "...",
      "nombre": "Juan Pérez",
      "email": "juan@example.com",
      "rol": "vendedor",
      "activo": true
    }
  }
}
```

#### Cambiar Contraseña
```http
POST /auth/change-password
Authorization: Bearer <token>
Content-Type: application/json

{
  "oldPassword": "oldPassword123",
  "newPassword": "newPassword456",
  "confirmPassword": "newPassword456"
}
```

### Productos

#### Obtener Todos los Productos
```http
GET /productos?page=1&per_page=20
Authorization: Bearer <token>
```

#### Obtener Producto por ID
```http
GET /productos/:id
Authorization: Bearer <token>
```

#### Crear Producto (Solo Admin)
```http
POST /productos
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Laptop Dell",
  "descripcion": "Laptop de 15 pulgadas",
  "categoria_id": 1,
  "precio_unitario": 899.99,
  "sku": "DELL-001",
  "codigo_barras": "7891234567890",
  "cantidad_inicial": 50
}
```

#### Actualizar Producto (Solo Admin)
```http
PUT /productos/:id
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Laptop Dell XPS",
  "precio_unitario": 999.99,
  "activo": true
}
```

#### Eliminar Producto (Solo Admin)
```http
DELETE /productos/:id
Authorization: Bearer <token>
```

#### Buscar Productos
```http
GET /productos/search/:keyword?page=1&per_page=20
Authorization: Bearer <token>
```

#### Productos por Categoría
```http
GET /productos/categoria/:categoria_id?page=1&per_page=20
Authorization: Bearer <token>
```

### Categorías

#### Obtener Todas las Categorías
```http
GET /categorias
Authorization: Bearer <token>
```

#### Crear Categoría (Solo Admin)
```http
POST /categorias
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Electrónica",
  "descripcion": "Productos electrónicos en general"
}
```

#### Actualizar Categoría (Solo Admin)
```http
PUT /categorias/:id
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Electrónica Actualizada"
}
```

#### Eliminar Categoría (Solo Admin)
```http
DELETE /categorias/:id
Authorization: Bearer <token>
```

### Proveedores

#### Obtener Todos los Proveedores
```http
GET /proveedores?page=1&per_page=20
Authorization: Bearer <token>
```

#### Crear Proveedor (Solo Admin)
```http
POST /proveedores
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Proveedor Global SA",
  "email": "contacto@proveedor.com",
  "telefono": "+34 912 345 678",
  "ciudad": "Madrid",
  "pais": "España"
}
```

#### Actualizar Proveedor (Solo Admin)
```http
PUT /proveedores/:id
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Proveedor Global SA Actualizado"
}
```

#### Eliminar Proveedor (Solo Admin)
```http
DELETE /proveedores/:id
Authorization: Bearer <token>
```

### Inventario

#### Obtener Inventario de Producto
```http
GET /inventario/:producto_id
Authorization: Bearer <token>
```

#### Registrar Entrada de Inventario
```http
POST /inventario/:producto_id/entrada
Authorization: Bearer <token>
Content-Type: application/json

{
  "cantidad": 100,
  "motivo": "Compra a proveedor"
}
```

#### Registrar Salida de Inventario
```http
POST /inventario/:producto_id/salida
Authorization: Bearer <token>
Content-Type: application/json

{
  "cantidad": 5,
  "motivo": "Venta a cliente"
}
```

#### Registrar Ajuste de Inventario (Solo Admin)
```http
POST /inventario/:producto_id/ajuste
Authorization: Bearer <token>
Content-Type: application/json

{
  "cantidad_nueva": 500,
  "motivo": "Auditoría de inventario"
}
```

#### Reservar Producto
```http
POST /inventario/:producto_id/reserva
Authorization: Bearer <token>
Content-Type: application/json

{
  "cantidad": 10
}
```

#### Liberar Reserva
```http
POST /inventario/:producto_id/liberar-reserva
Authorization: Bearer <token>
Content-Type: application/json

{
  "cantidad": 10
}
```

#### Actualizar Parámetros de Inventario (Solo Admin)
```http
PUT /inventario/:producto_id/parametros
Authorization: Bearer <token>
Content-Type: application/json

{
  "cantidad_minima": 20,
  "cantidad_maxima": 500,
  "ubicacion_almacen": "Pasillo A, Estante 3",
  "lote": "LOTE-2024-001",
  "fecha_vencimiento": "2025-12-31"
}
```

#### Productos con Bajo Stock (Solo Admin)
```http
GET /inventario/bajo-stock
Authorization: Bearer <token>
```

#### Validar Disponibilidad
```http
GET /inventario/:producto_id/disponibilidad?cantidad=50
Authorization: Bearer <token>
```

### Usuarios

#### Obtener Todos los Usuarios (Solo Admin)
```http
GET /usuarios?page=1&per_page=20
Authorization: Bearer <token>
```

#### Obtener Perfil del Usuario Actual
```http
GET /me
Authorization: Bearer <token>
```

#### Obtener Usuario por ID
```http
GET /usuarios/:id
Authorization: Bearer <token>
```

#### Actualizar Usuario
```http
PUT /usuarios/:id
Authorization: Bearer <token>
Content-Type: application/json

{
  "nombre": "Juan Pérez Actualizado",
  "email": "juannuevo@example.com"
}
```

#### Cambiar Rol de Usuario (Solo Admin)
```http
PUT /usuarios/:id
Authorization: Bearer <token>
Content-Type: application/json

{
  "rol": "admin"
}
```

#### Obtener Usuarios por Rol (Solo Admin)
```http
GET /usuarios/rol/:rol
Authorization: Bearer <token>

# Roles válidos: admin, vendedor, consultor
```

#### Eliminar Usuario (Solo Admin)
```http
DELETE /usuarios/:id
Authorization: Bearer <token>
```

## Códigos de Respuesta

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Creado - Recurso creado exitosamente |
| 400 | Bad Request - Error en los datos enviados |
| 401 | Unauthorized - Token inválido o no proporcionado |
| 403 | Forbidden - No tiene permisos |
| 404 | Not Found - Recurso no encontrado |
| 500 | Internal Server Error - Error en el servidor |

## Formatos de Respuesta

### Respuesta de Éxito
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    // Datos de la respuesta
  },
  "timestamp": "2024-03-04 14:30:45"
}
```

### Respuesta Paginada
```json
{
  "success": true,
  "message": "Datos obtenidos correctamente",
  "data": [
    // Array de elementos
  ],
  "pagination": {
    "total": 100,
    "page": 1,
    "per_page": 20,
    "total_pages": 5
  },
  "timestamp": "2024-03-04 14:30:45"
}
```

### Respuesta de Error
```json
{
  "success": false,
  "message": "Error en la operación",
  "errors": null,
  "timestamp": "2024-03-04 14:30:45"
}
```

## Roles y Permisos

### Admin
- ✅ CRUD completo de productos
- ✅ CRUD completo de categorías
- ✅ CRUD completo de proveedores
- ✅ Gestión completa de inventario
- ✅ Gestión de usuarios
- ✅ Visualización de reportes

### Vendedor
- ✅ Leer productos y categorías
- ✅ Registrar entrada y salida de inventario
- ✅ Reservar productos
- ✅ Ver su propio perfil
- ❌ Crear, editar o eliminar productos
- ❌ Gestionar usuarios

### Consultor
- ✅ Leer productos y categorías
- ✅ Ver inventario
- ✅ Ver su propio perfil
- ❌ Modificar datos
- ❌ Gestionar usuarios

## Usuarios de Prueba

La base de datos proporciona 3 usuarios de prueba:

| Email | Contraseña | Rol |
|-------|------------|-----|
| admin@tienda.com | admin123456 | admin |
| vendedor@tienda.com | admin123456 | vendedor |
| consultor@tienda.com | admin123456 | consultor |

**Nota:** Cambiar las contraseñas en producción.

## Configuración en Apache

### Virtual Host
```apache
<VirtualHost *:80>
    ServerName api.proyectoweb.local
    DocumentRoot /path/to/php-api
    
    <Directory /path/to/php-api>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Desarrollo

### Ejecutar Tests
```bash
composer test
```

### Server Local
```bash
composer serve
# Acceder en http://localhost:8000
```

## Seguridad

- Todas las contraseñas se hashean con bcrypt
- Las solicitudes se validan contra JWT
- Se implementan controles CORS
- Las consultas usan prepared statements (PDO)
- Se implementan headers de seguridad
- Validación de entrada en todos los endpoints

## Mantenimiento

### Logs
Los errores se registran en la salida estándar. En producción, configurar un servicio de logging.

### Backups
Realizar backups regulares de PostgreSQL:
```bash
pg_dump -U postgres -d proyectoweb > backup.sql
```

## Licencia

Este proyecto está bajo la licencia MIT.

## Soporte

Para reportar bugs o solicitar features, abrir un issue en el repositorio.
