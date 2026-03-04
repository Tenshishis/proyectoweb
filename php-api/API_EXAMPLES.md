# API Request Examples (cURL)

## Autenticación

### 1. Registrar nuevo usuario
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Carlos García",
    "email": "carlos@example.com",
    "password": "SecurePass123",
    "confirmPassword": "SecurePass123"
  }'
```

### 2. Login
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@tienda.com",
    "password": "admin123456"
  }'
```

Guardar el token retornado para usar en las siguientes solicitudes.

### 3. Cambiar contraseña
```bash
curl -X POST http://localhost:8000/auth/change-password \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "oldPassword": "admin123456",
    "newPassword": "NewPassword123",
    "confirmPassword": "NewPassword123"
  }'
```

## Productos

### 4. Obtener todos los productos
```bash
curl -X GET "http://localhost:8000/productos?page=1&per_page=20" \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 5. Obtener producto por ID
```bash
curl -X GET http://localhost:8000/productos/1 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 6. Crear producto (solo admin)
```bash
curl -X POST http://localhost:8000/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "nombre": "Monitor LG 27\"",
    "descripcion": "Monitor IPS Full HD de 27 pulgadas",
    "categoria_id": 1,
    "precio_unitario": 299.99,
    "sku": "LG-MON-27",
    "codigo_barras": "7891234567890",
    "cantidad_inicial": 30
  }'
```

### 7. Actualizar producto (solo admin)
```bash
curl -X PUT http://localhost:8000/productos/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "nombre": "Monitor LG 27\" UltraWide",
    "precio_unitario": 349.99
  }'
```

### 8. Eliminar producto (solo admin)
```bash
curl -X DELETE http://localhost:8000/productos/1 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 9. Buscar productos
```bash
curl -X GET "http://localhost:8000/productos/search/laptop?page=1&per_page=10" \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 10. Productos por categoría
```bash
curl -X GET "http://localhost:8000/productos/categoria/1?page=1&per_page=20" \
  -H "Authorization: Bearer TOKEN_AQUI"
```

## Categorías

### 11. Obtener todas las categorías
```bash
curl -X GET http://localhost:8000/categorias \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 12. Crear categoría (solo admin)
```bash
curl -X POST http://localhost:8000/categorias \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "nombre": "Periféricos",
    "descripcion": "Teclados, mouse, audífonos, etc."
  }'
```

### 13. Actualizar categoría (solo admin)
```bash
curl -X PUT http://localhost:8000/categorias/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "nombre": "Electrónica Premium"
  }'
```

### 14. Eliminar categoría (solo admin)
```bash
curl -X DELETE http://localhost:8000/categorias/1 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

## Proveedores

### 15. Obtener proveedores
```bash
curl -X GET "http://localhost:8000/proveedores?page=1&per_page=20" \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 16. Crear proveedor (solo admin)
```bash
curl -X POST http://localhost:8000/proveedores \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "nombre": "Tech Solutions Ltd",
    "email": "sales@techsolutions.com",
    "telefono": "+1 555 123 4567",
    "direccion": "123 Tech Street",
    "ciudad": "San Francisco",
    "pais": "USA"
  }'
```

### 17. Actualizar proveedor (solo admin)
```bash
curl -X PUT http://localhost:8000/proveedores/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "telefono": "+1 555 987 6543"
  }'
```

### 18. Eliminar proveedor (solo admin)
```bash
curl -X DELETE http://localhost:8000/proveedores/1 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

## Inventario

### 19. Obtener inventario de producto
```bash
curl -X GET http://localhost:8000/inventario/1 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 20. Registrar entrada de inventario
```bash
curl -X POST http://localhost:8000/inventario/1/entrada \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "cantidad": 50,
    "motivo": "Compra a proveedor Tech Solutions"
  }'
```

### 21. Registrar salida de inventario
```bash
curl -X POST http://localhost:8000/inventario/1/salida \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "cantidad": 5,
    "motivo": "Venta a cliente ABC Corporation"
  }'
```

### 22. Registrar ajuste de inventario (solo admin)
```bash
curl -X POST http://localhost:8000/inventario/1/ajuste \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "cantidad_nueva": 100,
    "motivo": "Auditoría física de almacén"
  }'
```

### 23. Reservar producto
```bash
curl -X POST http://localhost:8000/inventario/1/reserva \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "cantidad": 10
  }'
```

### 24. Liberar reserva
```bash
curl -X POST http://localhost:8000/inventario/1/liberar-reserva \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "cantidad": 10
  }'
```

### 25. Actualizar parámetros de inventario (solo admin)
```bash
curl -X PUT http://localhost:8000/inventario/1/parametros \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "cantidad_minima": 20,
    "cantidad_maxima": 500,
    "ubicacion_almacen": "Estantería A-03",
    "lote": "LOTE-2024-Q1",
    "fecha_vencimiento": "2025-12-31"
  }'
```

### 26. Productos con bajo stock (solo admin)
```bash
curl -X GET http://localhost:8000/inventario/bajo-stock \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 27. Validar disponibilidad
```bash
curl -X GET "http://localhost:8000/inventario/1/disponibilidad?cantidad=25" \
  -H "Authorization: Bearer TOKEN_AQUI"
```

## Usuarios

### 28. Obtener todos los usuarios (solo admin)
```bash
curl -X GET "http://localhost:8000/usuarios?page=1&per_page=20" \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 29. Obtener mi perfil
```bash
curl -X GET http://localhost:8000/me \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 30. Obtener usuario por ID
```bash
curl -X GET http://localhost:8000/usuarios/2 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 31. Actualizar usuario
```bash
curl -X PUT http://localhost:8000/usuarios/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "nombre": "Juan Carlos García",
    "email": "jcarlos@example.com"
  }'
```

### 32. Cambiar rol de usuario (solo admin)
```bash
curl -X PUT http://localhost:8000/usuarios/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN_AQUI" \
  -d '{
    "rol": "vendedor"
  }'
```

### 33. Obtener usuarios por rol (solo admin)
```bash
curl -X GET http://localhost:8000/usuarios/rol/vendedor \
  -H "Authorization: Bearer TOKEN_AQUI"
```

### 34. Eliminar usuario (solo admin)
```bash
curl -X DELETE http://localhost:8000/usuarios/1 \
  -H "Authorization: Bearer TOKEN_AQUI"
```

## Notas

- Reemplazar `TOKEN_AQUI` con el token JWT obtenido al hacer login
- Reemplazar `http://localhost:8000` con la URL correcta de tu servidor
- Los endpoints requieren autenticación (token JWT) excepto `/auth/register` y `/auth/login`
- Los endpoints de creación, actualización y eliminación requieren roles específicos:
  - **Admin**: Acceso completo a todas las operaciones
  - **Vendedor**: Puede crear entradas/salidas de inventario y ver productos
  - **Consultor**: Solo lectura de productos e inventario
