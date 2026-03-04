# 🔌 ENDPOINTS VENTAS Y REPORTES - REFERENCIA COMPLETA

## 📋 TABLA DE CONTENIDOS
1. [Endpoints de Ventas](#ventas)
2. [Endpoints de Reportes](#reportes)
3. [Códigos HTTP](#códigos-http)
4. [Ejemplos cURL](#ejemplos)

---

## VENTAS
<a name="ventas"></a>

### 1. Crear Venta Nueva
```
POST /ventas
```

**Autenticación:** Requerida (vendedor, admin)

**Body:**
```json
{
  "usuario_id": 1,
  "observaciones": "Nota sobre la venta (opcional)"
}
```

**Respuesta 201:**
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "id": 1,
    "numero_venta": "VTA-000001",
    "usuario_id": 1
  },
  "timestamp": "2024-04-15T10:30:00Z"
}
```

---

### 2. Obtener Todas las Ventas
```
GET /ventas?page=1&per_page=20
```

**Autenticación:** Requerida (cualquier rol)

**Parámetros:**
- `page` (opcional): Número de página (default: 1)
- `per_page` (opcional): Items por página (default: 20)

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Ventas obtenidas",
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "numero_venta": "VTA-000001",
      "usuario_id": 1,
      "usuario_nombre": "Admin",
      "total": 1000.00,
      "estado": "completada",
      "cantidad_items": 3,
      "created_at": "2024-04-15...",
      "updated_at": "2024-04-15..."
    }
  ],
  "timestamp": "2024-04-15T10:30:00Z"
}
```

---

### 3. Obtener Venta por ID
```
GET /ventas/:id
```

**Autenticación:** Requerida

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Venta obtenida",
  "data": {
    "venta": {
      "id": 1,
      "numero_venta": "VTA-000001",
      "total": 1000.00,
      "estado": "completada",
      "observaciones": "..."
    },
    "items": [
      {
        "id": 1,
        "venta_id": 1,
        "producto_id": 5,
        "producto_nombre": "Laptop Dell",
        "producto_sku": "LAP-001",
        "cantidad": 2,
        "precio_unitario": 400.00,
        "descuento": 10,
        "subtotal": 720.00
      }
    ],
    "total_items": 1,
    "total": 1000.00
  }
}
```

---

### 4. Agregar Item a Venta ⭐ (DESCUENTA AUTOMÁTICO)
```
POST /ventas/:id/items
```

**Autenticación:** Requerida (vendedor, admin)

**Body:**
```json
{
  "producto_id": 5,
  "cantidad": 10,
  "descuento_porcentaje": 5
}
```

**Proceso Automático:**
1. ✅ Valida que el producto existe
2. ✅ Valida cantidad > 0
3. ✅ Valida stock disponible (genera error si insuficiente)
4. ✅ **Descuenta automáticamente del inventario**
5. ✅ Registra movimiento en movimientos_inventario
6. ✅ Calcula subtotal con descuento
7. ✅ Recalcula total de venta

**Respuesta 201:**
```json
{
  "success": true,
  "message": "Item agregado a la venta",
  "data": {
    "item_id": 1,
    "venta_id": 5,
    "producto_id": 5,
    "cantidad": 10,
    "subtotal": 4750.00
  }
}
```

**Errores Posibles:**
- 400: "Stock insuficiente. Disponible: 3"
- 400: "Producto no encontrado"
- 400: "La cantidad debe ser mayor a 0"

---

### 5. Completar Venta ⭐ (REGISTRA REPORTES)
```
POST /ventas/:id/completar
```

**Autenticación:** Requerida (vendedor, admin)

**Proceso Automático:**
1. ✅ Valida que venta existe
2. ✅ Valida que venta tiene items
3. ✅ Cambia estado a 'completada'
4. ✅ **Registra cada item en tabla reporte_ventas** (desnormalizada)
5. ✅ Los datos quedan disponibles para reportes

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Venta completada exitosamente",
  "data": {
    "venta_id": 1,
    "estado": "completada",
    "items_procesados": 3
  }
}
```

---

### 6. Cancelar Venta ⭐ (REVIERTE INVENTARIO)
```
POST /ventas/:id/cancelar
```

**Autenticación:** Requerida (vendedor, admin)

**Proceso Automático:**
1. ✅ Obtiene todos los items
2. ✅ **Revierte descuentos: llama registrarEntrada() por cada item**
3. ✅ Registra movimientos de entrada
4. ✅ Cambia estado a 'cancelada'

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Venta cancelada exitosamente",
  "data": {
    "venta_id": 1,
    "estado": "cancelada",
    "items_revertidos": 3
  }
}
```

---

### 7. Aplicar Descuento a Item (Admin Only)
```
PUT /ventas/:id/items/:itemId/descuento
```

**Autenticación:** Requerida (admin)

**Body:**
```json
{
  "descuento_porcentaje": 15
}
```

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Descuento aplicado",
  "data": {
    "item_id": 1,
    "descuento": 15,
    "subtotal": 850.00
  }
}
```

---

### 8. Obtener Ventas por Usuario
```
GET /ventas/usuario/:usuario_id?page=1&per_page=20
```

**Autenticación:** Requerida

**Parámetros:**
- `usuario_id` (requerido): ID del usuario/vendedor

**Respuesta 200:** Array de ventas del usuario

---

### 9. Obtener Ventas por Rango de Fechas
```
GET /ventas/fechas?fecha_inicio=2024-01-01&fecha_fin=2024-12-31&page=1&per_page=20
```

**Autenticación:** Requerida

**Parámetros requeridos:**
- `fecha_inicio`: YYYY-MM-DD
- `fecha_fin`: YYYY-MM-DD

**Respuesta 200:** Array de ventas en el rango

---

## REPORTES
<a name="reportes"></a>

### 1. Listar Reportes Disponibles
```
GET /reportes
```

**Autenticación:** Requerida (admin, consultor, vendedor)

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Reportes disponibles",
  "data": {
    "reportes": [
      {
        "nombre": "Ventas por Fecha",
        "endpoint": "/reportes/ventas-por-fecha",
        "parametros": ["fecha_inicio", "fecha_fin"],
        "descripcion": "Resumen de ventas agrupadas por fecha"
      },
      // ... más reportes
    ]
  }
}
```

---

### 2. Reporte: Ventas por Fecha
```
GET /reportes/ventas-por-fecha?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Autenticación:** Requerida (admin, consultor)

**Parámetros requeridos:**
- `fecha_inicio`: YYYY-MM-DD
- `fecha_fin`: YYYY-MM-DD

**Respuesta 200:**
```json
{
  "success": true,
  "message": "Reporte de ventas por fecha",
  "data": {
    "tipo_reporte": "Ventas por Fecha",
    "rango_fechas": {
      "desde": "2024-01-01",
      "hasta": "2024-12-31"
    },
    "datos": [
      {
        "fecha_venta": "2024-04-15",
        "numero_ventas": 5,
        "cantidad_total": 50,
        "total_venta": 12500.00
      }
    ],
    "total_registros": 90,
    "total_ventas": 500000.00
  }
}
```

---

### 3. Reporte: Productos Más Vendidos
```
GET /reportes/productos-mas-vendidos?limite=10&fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Autenticación:** Requerida (admin, consultor)

**Parámetros:**
- `limite` (requerido): Top N productos
- `fecha_inicio` (opcional): YYYY-MM-DD
- `fecha_fin` (opcional): YYYY-MM-DD

**Respuesta 200:**
```json
{
  "success": true,
  "data": {
    "tipo_reporte": "Productos Más Vendidos",
    "datos": [
      {
        "producto_id": 5,
        "categoria_nombre": "Electrónica",
        "cantidad_total": 450,
        "total_venta": 67500.00,
        "numero_ventas": 85,
        "precio_promedio": 150.00
      }
    ],
    "total_registros": 10
  }
}
```

---

### 4. Reporte: Ventas por Categoría
```
GET /reportes/ventas-por-categoria?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Autenticación:** Requerida (admin, consultor)

**Parámetros:**
- `fecha_inicio` (opcional): YYYY-MM-DD
- `fecha_fin` (opcional): YYYY-MM-DD

**Respuesta 200:**
```json
{
  "data": {
    "tipo_reporte": "Ventas por Categoría",
    "datos": [
      {
        "categoria_nombre": "Electrónica",
        "cantidad_total": 1200,
        "total_venta": 180000.00,
        "numero_ventas": 250
      },
      {
        "categoria_nombre": "Ropa",
        "cantidad_total": 800,
        "total_venta": 48000.00,
        "numero_ventas": 400
      }
    ],
    "total_registros": 4,
    "total_ventas": 500000.00
  }
}
```

---

### 5. Reporte: Ventas por Proveedor
```
GET /reportes/ventas-por-proveedor?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Respuesta 200:**
```json
{
  "data": {
    "tipo_reporte": "Ventas por Proveedor",
    "datos": [
      {
        "proveedor_id": 1,
        "proveedor_nombre": "Proveedor Global SA",
        "cantidad_total": 600,
        "total_venta": 90000.00,
        "numero_ventas": 120
      }
    ]
  }
}
```

---

### 6. Reporte: Ventas por Usuario (Desempeño)
```
GET /reportes/ventas-por-usuario?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Respuesta 200:**
```json
{
  "data": {
    "tipo_reporte": "Ventas por Usuario",
    "datos": [
      {
        "usuario_id": 2,
        "cantidad_total": 300,
        "total_venta": 75000.00,
        "numero_ventas": 60
      }
    ]
  }
}
```

---

### 7. Reporte: Resumen General (KPIs) ⭐
```
GET /reportes/resumen-general?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Respuesta 200:**
```json
{
  "data": {
    "tipo_reporte": "Resumen General de Ventas",
    "resumen": {
      "total_registros": 500,
      "cantidad_total_vendida": 5000,
      "total_ventas": 500000.00,
      "venta_promedio": 1000.00,
      "venta_maxima": 5000.00,
      "venta_minima": 100.00,
      "dias_con_ventas": 250
    }
  }
}
```

---

### 8. Reporte: Ranking Productos por Ingresos
```
GET /reportes/ranking-productos-ingresos?limite=10&fecha_inicio=2024-01-01&fecha_fin=2024-12-31
```

**Respuesta 200:**
```json
{
  "data": {
    "tipo_reporte": "Ranking de Productos por Ingresos",
    "datos": [
      {
        "producto_id": 5,
        "categoria_nombre": "Electrónica",
        "ingreso_total": 150000.00,
        "cantidad_vendida": 250,
        "numero_ventas": 100,
        "precio_promedio_real": 600.00
      }
    ],
    "ingreso_total": 500000.00
  }
}
```

---

## CÓDIGOS HTTP
<a name="códigos-http"></a>

| Código | Significado | Ejemplo |
|--------|-------------|---------|
| 200 | OK - Operación exitosa | GET /ventas |
| 201 | Created - Recurso creado | POST /ventas |
| 400 | Bad Request - Error en datos | Producto no existe |
| 401 | Unauthorized - Sin autenticación | Falta Bearer token |
| 403 | Forbidden - Sin permisos | Consultor intenta crear |
| 404 | Not Found - Recurso no existe | GET /ventas/999 |
| 500 | Server Error - Error en BD | Database connection error |

---

## EJEMPLOS
<a name="ejemplos"></a>

### Ejemplo Completo: Flujo de Venta

#### 1. Login
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "vendedor@tienda.com",
    "password": "admin123456"
  }'

# Guarda el token de la respuesta
# export TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."
```

#### 2. Crear venta
```bash
curl -X POST http://localhost:8000/ventas \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "usuario_id": 2,
    "observaciones": "Venta del cliente Juan"
  }'

# Respuesta te da: {"data": {"id": 1, "numero_venta": "VTA-000001", ...}}
# Guarda el ID: export VENTA_ID=1
```

#### 3. Agregar items (descuenta automático)
```bash
# Item 1
curl -X POST http://localhost:8000/ventas/$VENTA_ID/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "producto_id": 1,
    "cantidad": 5,
    "descuento_porcentaje": 10
  }'

# Item 2
curl -X POST http://localhost:8000/ventas/$VENTA_ID/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "producto_id": 2,
    "cantidad": 3,
    "descuento_porcentaje": 0
  }'

# Verifica en BD:
# psql -U postgres -d proyectoweb -c "SELECT * FROM inventario"
# El stock debe haber bajado
```

#### 4. Completar venta (registra reportes)
```bash
curl -X POST http://localhost:8000/ventas/$VENTA_ID/completar \
  -H "Authorization: Bearer $TOKEN"

# Verifica en BD:
# psql -U postgres -d proyectoweb -c "SELECT * FROM reporte_ventas"
# Los datos deben aparecer
```

#### 5. Generar reporte
```bash
curl -X GET "http://localhost:8000/reportes/resumen-general?fecha_inicio=2024-01-01&fecha_fin=2024-12-31" \
  -H "Authorization: Bearer $TOKEN"

# Debe mostrar KPIs de todas las ventas completadas
```

---

## ⚙️ FLUJO DE DATOS

```
POST /ventas/ID/items
    ↓
VentasController::agregarItem()
    ↓
VentasService::agregarItemAVenta()
    ├─ ProductoRepository::getById()          [Obtén producto]
    ├─ InventarioRepository::getByProductoId() [Obtén stock]
    ├─ Validar cantidad disponible            [Si no hay, error]
    ├─ VentaItemRepository::create()          [Guarda item]
    ├─ InventarioService::registrarSalida()  [DESCUENTA] ⭐
    │   └─ MovimientoInventarioRepository::create() [Log movimiento]
    └─ VentaRepository::recalcularTotal()    [Suma subtotales]
    ↓
Response 201 [Item agregado + inventario descuentado]
```

---

**Documento de referencia completo para endpoints de ventas y reportes**
