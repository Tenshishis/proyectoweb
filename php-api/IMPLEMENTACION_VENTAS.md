# 📋 IMPLEMENTACIÓN PASO A PASO - BACKEND VENTAS E INVENTARIO

## Estado Actual

✅ **COMPLETADO:**
- Modelos: `Venta.php`, `VentaItem.php`, `ReporteVenta.php`
- Repositories: `VentaRepository.php`, `VentaItemRepository.php`, `ReporteVentaRepository.php`
- Services: `VentasService.php`, `ReportesService.php`
- Controllers: `VentasController.php`, `ReportesController.php`
- Routes: Todas las rutas agregadas en `index.php`
- Database Schema: Tablas de `ventas`, `venta_items`, índices e triggers

---

## 🚀 PASOS PARA IMPLEMENTAR

### PASO 1: Crear tablas en BD
```bash
# Aplica el script SQL actualizado
psql -U postgres -d proyectoweb -f sql/schema.sql

# Verifica que se crearon las tablas
psql -U postgres -d proyectoweb -c "\d ventas"
psql -U postgres -d proyectoweb -c "\d venta_items"
```

### PASO 2: Verificar estructura de carpetas
```
src/
├── models/
│   ├── Venta.php              ✅ Nuevo
│   ├── VentaItem.php          ✅ Nuevo
│   └── ReporteVenta.php       ✅ Nuevo
├── repositories/
│   ├── VentaRepository.php    ✅ Nuevo
│   ├── VentaItemRepository.php ✅ Nuevo
│   └── ReporteVentaRepository.php ✅ Nuevo
├── services/
│   ├── VentasService.php      ✅ Nuevo
│   └── ReportesService.php    ✅ Nuevo
├── controllers/
│   ├── VentasController.php   ✅ Nuevo
│   └── ReportesController.php ✅ Nuevo
└── ...
```

### PASO 3: Revisar el InventarioService (YA DEBE EXISTIR)
El `InventarioService` debe tener estos métodos (debe existir en tu proyecto):
- `registrarEntrada()` - Aumenta stock
- `registrarSalida()` - Disminuye stock (USADO POR VENTAS)
- `registrarAjuste()` - Ajuste manual
- `validarDisponibilidad()` - Valida stock

Si falta, crear con similitud a este código:

```php
// En: src/services/InventarioService.php
public function registrarSalida($productoId, $cantidad, $motivo)
{
    // Validar stock disponible
    $inventario = $inventarioRepo->getByProductoId($productoId);
    if ($inventario['cantidad_disponible'] < $cantidad) {
        throw new \Exception('Stock insuficiente');
    }
    
    // Descontar del inventario
    $nuevoStock = $inventario['cantidad_disponible'] - $cantidad;
    $inventarioRepo->update($productoId, [
        'cantidad_disponible' => $nuevoStock
    ]);
    
    // Registrar movimiento
    $movimiento = [
        'producto_id' => $productoId,
        'tipo_movimiento' => 'salida',
        'cantidad' => -$cantidad,
        'motivo' => $motivo,
        'usuario_id' => $usuarioId
    ];
    return $movimientoRepo->create($movimiento);
}
```

### PASO 4: Probar rutas de ventas

#### 4.1 Crear venta
```bash
curl -X POST http://localhost:8000/ventas \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_JWT" \
  -d '{
    "usuario_id": 1,
    "observaciones": "Venta de prueba"
  }'
```

Respuesta esperada:
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "id": 1,
    "numero_venta": "VTA-000001",
    "usuario_id": 1
  }
}
```

#### 4.2 Agregar item a venta (DESCUENTA AUTOMÁTICO)
```bash
curl -X POST http://localhost:8000/ventas/1/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_JWT" \
  -d '{
    "producto_id": 1,
    "cantidad": 5,
    "descuento_porcentaje": 10
  }'
```

**IMPORTANTE:** Este endpoint automáticamente:
1. Valida que hay stock disponible
2. Descuenta del inventario (llama a `InventarioService::registrarSalida`)
3. Calcula el subtotal con descuento
4. Actualiza el total de la venta

Respuesta:
```json
{
  "success": true,
  "data": {
    "item_id": 1,
    "venta_id": 1,
    "producto_id": 1,
    "cantidad": 5,
    "subtotal": 450  // precio * cantidad - descuento
  }
}
```

#### 4.3 Completar venta (REGISTRA EN REPORTES)
```bash
curl -X POST http://localhost:8000/ventas/1/completar \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

**IMPORTANTE:** Este endpoint:
1. Cambia estado a "completada"
2. Registra cada item en tabla `reporte_ventas` (desnormalizada)
3. Puede ser usado después para generar reportes

#### 4.4 Cancelar venta (REVIERTE INVENTARIO)
```bash
curl -X POST http://localhost:8000/ventas/1/cancelar \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

**IMPORTANTE:** Este endpoint:
1. Revierte todos los descuentos de inventario
2. Llama a `InventarioService::registrarEntrada()` por cada item
3. Cambia estado a "cancelada"

### PASO 5: Probar reportes

#### 5.1 Listar reportes disponibles
```bash
curl -X GET http://localhost:8000/reportes \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

#### 5.2 Reporte: Ventas por fecha
```bash
curl -X GET "http://localhost:8000/reportes/ventas-por-fecha?fecha_inicio=2024-01-01&fecha_fin=2024-12-31" \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

#### 5.3 Reporte: Productos más vendidos
```bash
curl -X GET "http://localhost:8000/reportes/productos-mas-vendidos?limite=10" \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

#### 5.4 Reporte: Resumen general (KPIs)
```bash
curl -X GET "http://localhost:8000/reportes/resumen-general?fecha_inicio=2024-01-01&fecha_fin=2024-12-31" \
  -H "Authorization: Bearer TU_TOKEN_JWT"
```

---

## 📊 FLUJO COMPLETO DE UNA VENTA

```
1. POST /ventas → Crear venta nueva (estado: pendiente)
   ↓
2. POST /ventas/:id/items → Agregar item
   → Valida stock
   → Descuenta automático del inventario
   → Registra movimiento de inventario
   ↓
3. (Opcional) Agregar más items
   ↓
4. POST /ventas/:id/completar → Completar venta
   → Cambia estado a 'completada'
   → Registra en tabla reporte_ventas
   → Genera datos para reportes
   ↓
5. GET /reportes/* → Consultar reportes generados
```

---

## 🔄 TRANSACCIONES Y SEGURIDAD

### Transacciones
El código usa transacciones en operaciones críticas:
- Al agregar item (validación + descuento + inserción)
- Al completar venta (cambio estado + registro en reportes)
- Al cancelar venta (revertir descuentos)

Si hay error, se hace **ROLLBACK** automático.

### Validaciones
1. **Stock Disponible**
   - Antes de vender, valida cantidad disponible
   - Si no hay, lanza excepción

2. **Descuentos**
   - Rango: 0-100%
   - Calcula subtotal automático

3. **Estados válidos**
   - `pendiente` → `completada` ✅
   - `pendiente` → `cancelada` ✅
   - `completada` → cancelada ❌ (se bloquea)

---

## 📈 TABLA REPORTE_VENTAS (Desnormalizada)

Se llena cuando completas una venta. Datos incluidos:
- Fecha de venta
- Producto ID
- Categoría (desnormalizada)
- Proveedor ID
- Proveedor nombre (desnormalizado)
- Cantidad vendida
- Precio unitario
- Total venta
- Usuario que vendió

Optimizada para **queries rápidas** sin JOINs.

---

## 🔧 DEPURACIÓN

### Ver logs de BD
```bash
# Ventajas con stock
SELECT * FROM inventario WHERE producto_id = 1;

# Ver movimientos
SELECT * FROM movimientos_inventario WHERE tipo_movimiento = 'salida';

# Ver ventas
SELECT * FROM ventas ORDER BY created_at DESC;

# Ver reportes
SELECT * FROM reporte_ventas;
```

### Ver respuesta error
```bash
# F12 en navegador → Network → Ver response
# O con cURL:

curl -v -X POST http://localhost:8000/ventas/99/items \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"producto_id": 1, "cantidad": 100}'

# Verá el error JSON en la respuesta
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [ ] Creé tablas en BD (`ventas`, `venta_items`)
- [ ] Verifiqué que InventarioService existe y tiene `registrarSalida()`
- [ ] Probé crear venta con POST /ventas
- [ ] Probé agregar item con POST /ventas/:id/items
- [ ] Verifiqué que se descuentó el inventario (SELECT * FROM inventario)
- [ ] Probé completar venta con POST /ventas/:id/completar
- [ ] Verifiqué que se registró en reporte_ventas
- [ ] Probé reportes GET /reportes/*
- [ ] Probé cancelar venta y verificar que se revertió inventario
- [ ] Probé con descuentos aplicados
- [ ] Revisé que todos los endpoints devuelven status HTTP correcto
- [ ] Probé permisos (vendedor puede vender, consultor NO puede)

---

## 🎯 PRÓXIMOS PASOS (OPCIONALES)

### Generar PDF/Excel
Una vez que tengas reportes funcionando, puedes:
1. Instalar librería: `composer require phpoffice/phpspreadsheet`
2. O: `composer require dompdf/dompdf`
3. Crear método en ReportesService: `generarPDFReporte()`

### Webhook de ventas completadas
Enviar evento a Node.js/MongoDB cuando venta se completa:
```php
// En VentasService::completarVenta()
// Después de registrar en reporte_ventas:
$this->enviarWebhookVentaCompletada($venta);
```

### Dashboard en tiempo real
Agregar websocket para actualizar Dashboard cuando:
- Nueva venta completada
- Stock se actualiza
- Reportes se generan

---

## 📞 SOPORTE

Si tienes errores:

1. **"Stock insuficiente"**
   - Verifica que el producto tiene inventario asociado
   - SELECT * FROM inventario WHERE producto_id = X;

2. **"Tabla no encontrada"**
   - Vuelve a ejecutar schema.sql
   - psql -U postgres -d proyectoweb -f sql/schema.sql

3. **"Token inválido"**
   - GET /auth/login con tu usuario
   - Copia el token de la respuesta
   - Úsalo en header: Authorization: Bearer TOKEN

4. **"Transacción fallida"**
   - Revisa que la BD está disponible
   - Ver archivo de logs de PostgreSQL

---

**¡LISTO PARA EMPEZAR!** 🚀

Comenza con PASO 1 y ve avanzando en orden.
