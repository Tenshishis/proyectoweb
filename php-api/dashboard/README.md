# Dashboard - Gestión de Productos e Inventario

Dashboard web moderno para administrar productos, inventario y usuarios de la API ProyectoWeb.

## 🎯 Características

✅ **Autenticación JWT** - Login seguro con tokens  
✅ **Dashboard** - Estadísticas en tiempo real con gráficos  
✅ **Gestión de Productos** - CRUD completo (solo admin)  
✅ **Control de Inventario** - Entrada, salida, ajustes y reservas  
✅ **Gestión de Usuarios** - Administración de usuarios (solo admin)  
✅ **Búsqueda y Filtros** - Búsqueda por keyword y categoría  
✅ **Responsive Design** - Funciona en desktop y mobile  
✅ **Interface Moderna** - Bootstrap 5 + Chart.js  

## 🚀 Uso Rápido

### 1. Asegúrate que la API está corriendo

```bash
cd C:\Users\bruno\AndroidStudioProjects\ProyectoWeb\php-api
composer serve
```

El servidor correrá en: `http://localhost:8000`

### 2. Abre el Dashboard

En tu navegador abre:

```
http://localhost:8000/dashboard
```

o

```
file:///C:/Users/bruno/AndroidStudioProjects/ProyectoWeb/php-api/dashboard/index.html
```

### 3. Ingresa con tus credenciales

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@tienda.com | admin123456 | Admin (acceso completo) |
| vendedor@tienda.com | admin123456 | Vendedor (productos e inventario) |
| consultor@tienda.com | admin123456 | Consultor (solo lectura) |

## 📋 Páginas y Funcionalidades

### Dashboard
- **Vista General**: Resumen de estadísticas claves
- **Gráficos**: 
  - Productos por categoría (Doughnut chart)
  - Distribución de stock (Bar chart)
- **Últimos Productos**: Tabla con los últimos 10 productos
- **Métricas**:
  - Total de productos
  - Stock disponible total
  - Productos con bajo stock
  - Usuarios activos

### Productos (Admin + Vendedor)
- **Listar productos** - Tabla paginada
- **Buscar** - Búsqueda por nombre, SKU o descripción
- **Filtrar** - Por categoría
- **Ver detalles** - Stock, precio, categoría
- **Crear** - Nuevo producto (admin)
- **Editar** - Modificar datos (admin)
- **Eliminar** - Soft delete (admin)

### Inventario (Admin + Vendedor)
- **Consultar stock** - Ver inventario de todos los productos
- **Validar disponibilidad** - Verificar si hay suficiente stock
- **Alertas** - Productos con stock bajo
- **Movimientos** - Registrar entrada/salida
- **Reservas** - Reservar y liberar productos
- **Parámetros** - Configurar límites min/máx (admin)

### Usuarios (Admin Only)
- **Listar usuarios** - Tabla de todos los usuarios
- **Ver perfil** - Mi información
- **Crear usuario** - Registrar nuevo usuario (admin)
- **Cambiar rol** - Asignar rol (admin)
- **Desactivar** - Inhabilitar usuario (admin)

## 🔐 Control de Acceso

**Admin**
- Acceso a todas las páginas
- CRUD completo
- Gestión de usuarios
- Configuración de parámetros

**Vendedor**
- Ver productos
- Gestionar inventario (E/S, reservas)
- No puede crear/editar/eliminar productos
- No puede ver usuarios

**Consultor**
- Solo lectura
- Ver productos y categorías
- Ver inventario
- No puede hacer cambios

## 🛠️ Estructura del Dashboard

```
dashboard/
├── index.html              # Página principal (HTML)
├── assets/
│   ├── css/
│   │   └── style.css       # Estilos Bootstrap + Custom
│   └── js/
│       ├── api.js          # Cliente API (métodos HTTP)
│       └── app.js          # Lógica principal (eventos, navegación)
└── README.md               # Este archivo
```

### Archivos Principales

**index.html**
- Estructura HTML
- Modales y formularios
- Plantillas de tablas
- Integración con Bootstrap 5

**assets/css/style.css**
- Estilos personalizados
- Temas y colores
- Animaciones
- Diseño responsive

**assets/js/api.js**
- Cliente HTTP para la API
- Métodos para cada endpoint
- Gestión de tokens JWT
- Validación de respuestas

**assets/js/app.js**
- Lógica de la aplicación
- Navegación entre páginas
- Carga de datos
- Manejo de eventos
- Gráficos con Chart.js

## 📊 Gráficos Soportados

### Chart.js versión 4.4.0

- **Doughnut** - Productos por categoría
- **Bar** - Distribución de stock
- **Responsivo** - Se ajusta al tamaño de pantalla
- **Interactivo** - Hover y tooltips

## 🔗 Conexión con API

El dashboard se conecta a la API mediante el módulo `api.js`:

```javascript
// Login
API.login(email, password)

// Productos
API.getProductos(page, perPage)
API.createProducto(data)
API.updateProducto(id, data)
API.deleteProducto(id)

// Inventario
API.getInventario(productoId)
API.registrarEntrada(productoId, cantidad, motivo)
API.registrarSalida(productoId, cantidad, motivo)

// Usuarios
API.getUsuarios(page, perPage)
API.getProfile()

// Y mucho más...
```

### CORS

Para que funcione correctamente, la API debe permitir CORS desde el dashboard:

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

✅ Ya está configurado en `src/index.php`

## 🎨 Temas y Personalización

### Colores Principales

```css
--primary: #007bff    /* Azul */
--success: #28a745    /* Verde */
--warning: #ffc107    /* Amarillo */
--danger: #dc3545     /* Rojo */
```

### Personalizar Colores

Edita `assets/css/style.css`:

```css
:root {
    --primary: #tu-color;
    --success: #tu-color;
    /* ... */
}
```

## 📱 Responsive Design

El dashboard es totalmente responsive:

- **Desktop** - Ancho completo con sidebar (si se agrega)
- **Tablet** - Ajustes en grillas y tablas
- **Mobile** - Menú hamburguesa, tablas scrollables

## 🐛 Troubleshooting

### "Network Error" o "CORS Error"
- Verifica que la API está corriendo en `http://localhost:8000`
- Revisa que el token sea válido

### Las tablas están vacías
- Verifica que la base de datos tiene datos
- Revisa la consola del navegador (F12 → Console)
- Verifica que tienes el rol correcto

### Los gráficos no cargan
- Asegúrate que Chart.js está cargado correctamente
- Revisa que hay datos en la API

### Sesión se cierra al recargar
- El token se guarda en `localStorage`
- Si borro localStorage, pierdo la sesión
- Intenta hacer login nuevamente

## 📦 Dependencias Externas

- **Bootstrap 5.3.0** - Framework CSS (CDN)
- **Font Awesome 6.4.0** - Iconos (CDN)
- **Chart.js 4.4.0** - Gráficos (CDN)

Todas están importadas por CDN, no necesita instalación.

## 🚀 Mejoras Futuras

- [ ] Exportar reportes a PDF/Excel
- [ ] Gráficos más complejos
- [ ] Calendario de eventos
- [ ] Notificaciones en tiempo real
- [ ] Dark mode
- [ ] Multilanguage
- [ ] Filtros avanzados
- [ ] Historial de cambios

## 📞 Soporte

Para preguntas o problemas:
1. Revisa la consola del navegador (F12)
2. Verifica que la API está corriendo
3. Revisa los logs de la API
4. Consulta la documentación de la API: `/API_EXAMPLES.md`

## 📄 Licencia

MIT - Libre para usar y modificar

---

**Hecho con ❤️ usando Bootstrap, Chart.js y vanilla JavaScript**
