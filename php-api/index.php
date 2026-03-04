<?php

// Auto-loader PSR-4
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Manejo de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Cargar configuración
require_once __DIR__ . '/src/config/app.php';

// Manejador de excepciones
try {
    // Obtener método y ruta
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Eliminar el prefijo de la aplicación si existe
    $basePath = '/proyectoweb/php-api';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Asegurar que la ruta comience con /
    if (empty($path)) {
        $path = '/';
    }

    // Crear router
    $router = new \App\Router();

    // RUTAS DE AUTENTICACIÓN
    $router->route('POST', '/auth/register', function() {
        $controller = new \App\Controllers\AuthController();
        $controller->register();
    });

    $router->route('POST', '/auth/login', function() {
        $controller = new \App\Controllers\AuthController();
        $controller->login();
    });

    $router->route('POST', '/auth/change-password', function() {
        $controller = new \App\Controllers\AuthController();
        $controller->changePassword();
    });

    // RUTAS DE PRODUCTOS
    $router->route('GET', '/productos', function() {
        $controller = new \App\Controllers\ProductoController();
        $controller->getAll();
    });

    $router->route('GET', '/productos/:id', function($id) {
        $controller = new \App\Controllers\ProductoController();
        $controller->getById($id);
    });

    $router->route('POST', '/productos', function() {
        $controller = new \App\Controllers\ProductoController();
        $controller->create();
    });

    $router->route('PUT', '/productos/:id', function($id) {
        $controller = new \App\Controllers\ProductoController();
        $controller->update($id);
    });

    $router->route('DELETE', '/productos/:id', function($id) {
        $controller = new \App\Controllers\ProductoController();
        $controller->delete($id);
    });

    $router->route('GET', '/productos/search/:keyword', function($keyword) {
        $controller = new \App\Controllers\ProductoController();
        $controller->search($keyword);
    });

    $router->route('GET', '/productos/categoria/:categoria_id', function($categoria_id) {
        $controller = new \App\Controllers\ProductoController();
        $controller->getByCategoria($categoria_id);
    });

    // RUTAS DE CATEGORÍAS
    $router->route('GET', '/categorias', function() {
        $controller = new \App\Controllers\CategoriaController();
        $controller->getAll();
    });

    $router->route('GET', '/categorias/:id', function($id) {
        $controller = new \App\Controllers\CategoriaController();
        $controller->getById($id);
    });

    $router->route('POST', '/categorias', function() {
        $controller = new \App\Controllers\CategoriaController();
        $controller->create();
    });

    $router->route('PUT', '/categorias/:id', function($id) {
        $controller = new \App\Controllers\CategoriaController();
        $controller->update($id);
    });

    $router->route('DELETE', '/categorias/:id', function($id) {
        $controller = new \App\Controllers\CategoriaController();
        $controller->delete($id);
    });

    // RUTAS DE PROVEEDORES
    $router->route('GET', '/proveedores', function() {
        $controller = new \App\Controllers\ProveedorController();
        $controller->getAll();
    });

    $router->route('GET', '/proveedores/:id', function($id) {
        $controller = new \App\Controllers\ProveedorController();
        $controller->getById($id);
    });

    $router->route('POST', '/proveedores', function() {
        $controller = new \App\Controllers\ProveedorController();
        $controller->create();
    });

    $router->route('PUT', '/proveedores/:id', function($id) {
        $controller = new \App\Controllers\ProveedorController();
        $controller->update($id);
    });

    $router->route('DELETE', '/proveedores/:id', function($id) {
        $controller = new \App\Controllers\ProveedorController();
        $controller->delete($id);
    });

    // RUTAS DE INVENTARIO
    $router->route('GET', '/inventario/:producto_id', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->getInventario($producto_id);
    });

    $router->route('POST', '/inventario/:producto_id/entrada', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->registrarEntrada($producto_id);
    });

    $router->route('POST', '/inventario/:producto_id/salida', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->registrarSalida($producto_id);
    });

    $router->route('POST', '/inventario/:producto_id/ajuste', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->registrarAjuste($producto_id);
    });

    $router->route('POST', '/inventario/:producto_id/reserva', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->reservarProducto($producto_id);
    });

    $router->route('POST', '/inventario/:producto_id/liberar-reserva', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->liberarReserva($producto_id);
    });

    $router->route('PUT', '/inventario/:producto_id/parametros', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->actualizarParametros($producto_id);
    });

    $router->route('GET', '/inventario/bajo-stock', function() {
        $controller = new \App\Controllers\InventarioController();
        $controller->getBajoStock();
    });

    $router->route('GET', '/inventario/:producto_id/disponibilidad', function($producto_id) {
        $controller = new \App\Controllers\InventarioController();
        $controller->validarDisponibilidad($producto_id);
    });

    // RUTAS DE USUARIOS
    $router->route('GET', '/usuarios', function() {
        $controller = new \App\Controllers\UsuarioController();
        $controller->getAll();
    });

    $router->route('GET', '/usuarios/:id', function($id) {
        $controller = new \App\Controllers\UsuarioController();
        $controller->getById($id);
    });

    $router->route('GET', '/usuarios/uuid/:uuid', function($uuid) {
        $controller = new \App\Controllers\UsuarioController();
        $controller->getByUuid($uuid);
    });

    $router->route('PUT', '/usuarios/:id', function($id) {
        $controller = new \App\Controllers\UsuarioController();
        $controller->update($id);
    });

    $router->route('DELETE', '/usuarios/:id', function($id) {
        $controller = new \App\Controllers\UsuarioController();
        $controller->delete($id);
    });

    $router->route('GET', '/usuarios/rol/:rol', function($rol) {
        $controller = new \App\Controllers\UsuarioController();
        $controller->getByRol($rol);
    });

    $router->route('GET', '/me', function() {
        $controller = new \App\Controllers\UsuarioController();
        $controller->getProfile();
    });

    // ========================================
    // RUTAS DE VENTAS
    // ========================================
    $router->route('GET', '/ventas', function() {
        $controller = new \App\Controllers\VentasController();
        $controller->getAll();
    });

    $router->route('GET', '/ventas/:id', function($id) {
        $controller = new \App\Controllers\VentasController();
        $controller->getById($id);
    });

    $router->route('POST', '/ventas', function() {
        $controller = new \App\Controllers\VentasController();
        $controller->create();
    });

    $router->route('POST', '/ventas/:id/items', function($id) {
        $controller = new \App\Controllers\VentasController();
        $controller->agregarItem($id);
    });

    $router->route('POST', '/ventas/:id/completar', function($id) {
        $controller = new \App\Controllers\VentasController();
        $controller->completarVenta($id);
    });

    $router->route('POST', '/ventas/:id/cancelar', function($id) {
        $controller = new \App\Controllers\VentasController();
        $controller->cancelarVenta($id);
    });

    $router->route('PUT', '/ventas/:id/items/:itemId/descuento', function($id, $itemId) {
        $controller = new \App\Controllers\VentasController();
        $controller->aplicarDescuentoItem($id, $itemId);
    });

    $router->route('GET', '/ventas/usuario/:usuario_id', function($usuario_id) {
        $controller = new \App\Controllers\VentasController();
        $controller->getByUsuario($usuario_id);
    });

    $router->route('GET', '/ventas/fechas', function() {
        $controller = new \App\Controllers\VentasController();
        $controller->getByFechas();
    });

    // ========================================
    // RUTAS DE REPORTES
    // ========================================
    $router->route('GET', '/reportes', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->listarReportes();
    });

    $router->route('GET', '/reportes/ventas-por-fecha', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->ventasPorFecha();
    });

    $router->route('GET', '/reportes/productos-mas-vendidos', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->productosMasVendidos();
    });

    $router->route('GET', '/reportes/ventas-por-categoria', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->ventasPorCategoria();
    });

    $router->route('GET', '/reportes/ventas-por-proveedor', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->ventasPorProveedor();
    });

    $router->route('GET', '/reportes/ventas-por-usuario', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->ventasPorUsuario();
    });

    $router->route('GET', '/reportes/resumen-general', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->resumenGeneral();
    });

    $router->route('GET', '/reportes/ranking-productos-ingresos', function() {
        $controller = new \App\Controllers\ReportesController();
        $controller->rankingProductosPorIngresos();
    });

    // Ruta de bienvenida
    $router->route('GET', '/', function() {
        \App\Utils\Response::success([
            'version' => '1.0.0',
            'name' => 'ProyectoWeb - Product & Inventory API',
            'status' => 'active'
        ], 'API en línea');
    });

    // Despachar la solicitud
    $router->dispatch($method, $path);

} catch (\Exception $e) {
    $statusCode = $e->getCode() ?: 400;
    
    if ($e->getCode() === 404) {
        http_response_code(404);
        \App\Utils\Response::error("Ruta no encontrada", 404);
    } else {
        http_response_code($statusCode);
        \App\Utils\Response::error($e->getMessage(), $statusCode);
    }
}
