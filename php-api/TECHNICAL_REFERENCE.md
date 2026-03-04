# 📚 Resumen Técnico - ProyectoWeb

Referencia rápida de la arquitectura, endpoints y características técnicas del sistema.

## 🏗️ Arquitectura

### Backend (PHP)
```
API REST → Router → Controllers → Services → Repositories → Database
           ↑                                              ↓
        Middleware (Auth, CORS)                    PostgreSQL
```

### Frontend (Dashboard)
```
HTML → CSS → JavaScript
↓
Bootstrap 5 (UI Framework)
Chart.js (Visualización)
Font Awesome (Icons)
↓
API Client (fetch + JWT)
```

### Base de Datos (PostgreSQL)
```
8 Tablas normalizadas en 3FN
├── usuarios
├── categorias
├── proveedores
├── productos
├── producto_proveedor (M:M)
├── inventario
├── movimientos_inventario
└── reporte_ventas (denormalizada)
```

---

## 🔑 Conceptos Clave

### Autenticación JWT

1. User envía email + password a `/auth/login`
2. API genera JWT token (24 horas expiracion)
3. Dashboard guarda token en localStorage
4. Cada request incluye token en header: `Authorization: Bearer <token>`
5. API valida token y devuelve usuario actual

```javascript
// Frontend
const response = await fetch('http://localhost:8000/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'admin@tienda.com', password: 'admin123456' })
});

const data = await response.json();
localStorage.setItem('token', data.data.token); // Guardar token
```

### Roles y Permisos

| Rol | Productos | Inventario | Usuarios | Permisos |
|-----|-----------|-----------|----------|----------|
| Admin | CRUD | CRUD | CRUD | Full access |
| Vendedor | R | CRUD | - | Read productos, Manage inventory |
| Consultor | R | R | - | Read-only |

### Estructura de Respuesta API

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "id": 1,
    "name": "Producto A",
    "price": 99.99
  },
  "timestamp": "2024-04-15T10:30:00Z"
}
```

---

## 📍 Endpoints Principales

### Autenticación
```
POST   /auth/register
POST   /auth/login
POST   /auth/change-password
```

### Productos
```
GET    /productos                    # Listar (paginado)
GET    /productos/:id                # Obtener uno
GET    /productos/search/:keyword    # Buscar
POST   /productos                    # Crear
PUT    /productos/:id                # Actualizar
DELETE /productos/:id                # Eliminar
```

### Categorías
```
GET    /categorias                   # Listar
GET    /categorias/:id               # Obtener
GET    /categorias/:id/productos     # Productos de categoría
POST   /categorias                   # Crear
PUT    /categorias/:id               # Actualizar
DELETE /categorias/:id               # Eliminar
```

### Proveedores
```
GET    /proveedores
GET    /proveedores/:id
POST   /proveedores
PUT    /proveedores/:id
DELETE /proveedores/:id
```

### Inventario
```
GET    /inventario/:producto_id            # Stock de producto
PUT    /inventario/:producto_id/entrada    # Entrada de stock
PUT    /inventario/:producto_id/salida     # Salida de stock
PUT    /inventario/:producto_id/ajuste     # Ajuste manual
GET    /inventario/:producto_id/disponibilidad
POST   /inventario/reservar                # Reservar stock
POST   /inventario/liberar-reserva         # Liberar reserva
```

### Usuarios
```
GET    /usuarios                  # Listar
GET    /usuarios/:id              # Obtener uno
GET    /usuarios/uuid/:uuid       # Por UUID
GET    /usuarios/rol/:rol         # Por rol
PUT    /usuarios/:id              # Actualizar
DELETE /usuarios/:id              # Eliminar
GET    /me                        # Perfil actual
```

---

## 💾 Tablas de Base de Datos

### usuarios
```sql
id (PK)
uuid (CHAR)
nombre (VARCHAR)
email (VARCHAR, UNIQUE)
password (VARCHAR hashed)
rol (ENUM: Admin, Vendedor, Consultor)
activo (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP, soft delete)
```

### categorias
```sql
id (PK)
nombre (VARCHAR)
descripcion (TEXT)
activa (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)
```

### productos
```sql
id (PK)
sku (VARCHAR, UNIQUE)
nombre (VARCHAR)
descripcion (TEXT)
categoria_id (FK)
precio_compra (DECIMAL)
precio_venta (DECIMAL)
activo (BOOLEAN)
created_at (TIMESTAMP)
updated_at (TIMESTAMP)
deleted_at (TIMESTAMP)
```

### inventario
```sql
id (PK)
producto_id (FK, UNIQUE)
cantidad (INT)
cantidad_reservada (INT)
cantidad_disponible (COMPUTED)
cantidad_minima (INT)
cantidad_maxima (INT)
ubicacion_almacen (VARCHAR)
updated_at (TIMESTAMP)
```

### movimientos_inventario
```sql
id (PK)
producto_id (FK)
tipo (ENUM: Entrada, Salida, Ajuste, Reserva)
cantidad (INT)
motivo (VARCHAR)
usuario_id (FK)
created_at (TIMESTAMP)
```

---

## 🎨 Dashboard - Páginas

### Página: Dashboard
- 4 tarjetas de estadística (Productos, Stock, Stock bajo, Usuarios)
- Gráfico doughnut: Productos por categoría
- Gráfico bar: Distribución de stock
- Tabla: Últimos 10 productos

### Página: Productos
- Tabla con búsqueda y filtro
- Columnas: SKU, Nombre, Categoría, Precio, Stock
- Botones: Ver, Editar, Eliminar (solo admin)
- Búsqueda en tiempo real

### Página: Inventario
- Tabla con estado de stock
- Columnas: Producto, Stock Disponible, Reservado, Min, Max, Ubicación
- Alerta: Stock bajo (disponible < mínimo)
- Botones: Registrar entrada/salida/ajuste

### Página: Usuarios
- Tabla de usuarios (solo visible para Admin)
- Columnas: Nombre, Email, Rol, Activo
- Botones: Ver, Editar (solo admin)

---

## 🛠️ Stack Tecnológico

### Backend
- **PHP 7.4+** - Lenguaje
- **PostgreSQL 12+** - Base de datos
- **Composer** - Gestor de dependencias
- **Firebase/PHP-JWT** - Autenticación
- **PDO** - Abstracción de BD

### Frontend
- **Bootstrap 5.3.0** - Framework CSS
- **Chart.js 4.4.0** - Gráficos
- **Font Awesome 6.4.0** - Iconografía
- **Vanilla JavaScript** - Lógica

### Herramientas
- **Git** - Control de versiones
- **Apache/Nginx/Node** - Servidor web

---

## 🔒 Seguridad

### Medidas Implementadas
1. **Hashing de Contraseñas** - bcrypt (password_hash)
2. **JWT Tokens** - 24 horas expiracion
3. **CORS Headers** - Control de origen
4. **Prepared Statements** - Prevención SQL injection
5. **Validación Input** - Servidor y cliente
6. **Soft Deletes** - No eliminación real
7. **Roles y Permisos** - Control de acceso

### Headers HTTP
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
Content-Type: application/json; charset=utf-8
```

---

## 📊 Flujo de Datos

### 1. Login
```
User Input (email, password)
    ↓
POST /auth/login
    ↓
Database Lookup
    ↓
Password Verification (bcrypt)
    ↓
JWT Generation (24h)
    ↓
Response with Token
    ↓
localStorage.setItem('token', token)
```

### 2. CRUD Producto
```
User Click (Editar)
    ↓
Modal Form (Producto data)
    ↓
PUT /productos/:id (con token en header)
    ↓
API Validation
    ↓
Service Layer (Business logic)
    ↓
Repository (SQL UPDATE)
    ↓
PostgreSQL Update
    ↓
Response Success
    ↓
Dashboard Refresh
    ↓
Tabla Updated
```

### 3. Inventario Entrada
```
User Click (Entrada)
    ↓
Modal (Cantidad + Motivo)
    ↓
PUT /inventario/:id/entrada
    ↓
Stock Validation
    ↓
Inventory Update
    ↓
Movement Log (auditoria)
    ↓
Dashboard Stats Update
```

---

## 🚀 Mejoras Implementadas

✅ **Completado**
- Autenticación con JWT
- CRUD de Productos
- Gestión de Inventario
- Control de Usuarios
- Dashboard con gráficos
- Búsqueda y filtros
- Validación datos
- Soft deletes

⏳ **Posibles mejoras**
- [ ] Roles más granulares (permisos específicos)
- [ ] Historial de cambios (audit trail completo)
- [ ] Excel/PDF export
- [ ] Notificaciones en tiempo real
- [ ] Dark mode
- [ ] Autenticación 2FA
- [ ] Integración OAuth
- [ ] API rate limiting

---

## 📝 Ejemplo: Crear Producto

### cURL
```bash
curl -X POST http://localhost:8000/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -d '{
    "sku": "PROD001",
    "nombre": "Laptop Dell",
    "descripcion": "Laptop 15 pulgadas",
    "categoria_id": 1,
    "precio_compra": 800.00,
    "precio_venta": 1200.00
  }'
```

### JavaScript (Fetch)
```javascript
const response = await fetch('http://localhost:8000/productos', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  },
  body: JSON.stringify({
    sku: 'PROD001',
    nombre: 'Laptop Dell',
    descripcion: 'Laptop 15 pulgadas',
    categoria_id: 1,
    precio_compra: 800.00,
    precio_venta: 1200.00
  })
});

const data = await response.json();
console.log(data.data); // { id: 5, sku: 'PROD001', ... }
```

### Response
```json
{
  "success": true,
  "message": "Producto creado exitosamente",
  "data": {
    "id": 5,
    "sku": "PROD001",
    "nombre": "Laptop Dell",
    "descripcion": "Laptop 15 pulgadas",
    "categoria_id": 1,
    "precio_compra": "800.00",
    "precio_venta": "1200.00",
    "activo": true,
    "created_at": "2024-04-15T10:30:00Z",
    "updated_at": "2024-04-15T10:30:00Z"
  },
  "timestamp": "2024-04-15T10:30:00Z"
}
```

---

## 🎓 Conceptos por Aprender

Para dominar este proyecto, es útil entender:

1. **REST API Design** - Recursos, métodos, status codes
2. **JWT Authentication** - Token generation, validation, refresh
3. **Database Normalization** - 3FN, relationships, constraints
4. **OOP in PHP** - Classes, inheritance, design patterns
5. **SQL Transactions** - ACID properties, rollback
6. **HTTP Headers** - CORS, Authorization, Content-Type
7. **Frontend State Management** - localStorage, session
8. **Chart.js Library** - Data visualization
9. **Bootstrap Grid** - Responsive design
10. **Password Security** - Hashing, bcrypt, collision

---

## 📞 Debugging Tips

### Ver logs en tiempo real
```bash
# Windows PowerShell
Get-Date -Format "HH:mm:ss" | ForEach-Object { Write-Host $_; composer serve }

# Linux
tail -f logs/api.log | grep ERROR
```

### Verificar BD
```bash
psql -U postgres -d proyectoweb

# Ver estructura
\d usuarios
\d productos

# Ver datos
SELECT * FROM usuarios;
SELECT * FROM productos;
```

### Revisar respuesta API
```bash
# F12 en navegador → Network tab
# Ver request headers, response status, response body
```

---

**Documento de referencia | Última actualización: 2024-04-15**
