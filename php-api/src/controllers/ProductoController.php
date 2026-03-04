<?php

namespace App\Controllers;

use App\Services\ProductoService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use Exception;

class ProductoController {
    private $productoService;

    public function __construct() {
        $this->productoService = new ProductoService();
    }

    /**
     * GET /productos
     */
    public function getAll() {
        try {
            // Autenticación requerida
            AuthMiddleware::authenticate();
            
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $result = $this->productoService->getAllProductos($page, $perPage);

            Response::paginated($result['productos'], $result['total'], $result['page'], $result['per_page']);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /productos/:id
     */
    public function getById($id) {
        try {
            AuthMiddleware::authenticate();
            
            $producto = $this->productoService->getProductoById($id);

            Response::success($producto->toArray(), "Producto obtenido correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    /**
     * POST /productos
     * Solo administrador
     */
    public function create() {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $descripcion = $input['descripcion'] ?? null;
            $categoria_id = $input['categoria_id'] ?? null;
            $precio_unitario = $input['precio_unitario'] ?? null;
            $sku = $input['sku'] ?? null;
            $codigo_barras = $input['codigo_barras'] ?? null;
            $cantidad_inicial = $input['cantidad_inicial'] ?? 0;

            $producto = $this->productoService->crearProducto(
                $nombre,
                $descripcion,
                $categoria_id,
                $precio_unitario,
                $sku,
                $codigo_barras,
                $cantidad_inicial
            );

            Response::success($producto->toArray(), "Producto creado exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * PUT /productos/:id
     * Solo administrador
     */
    public function update($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $descripcion = $input['descripcion'] ?? null;
            $categoria_id = $input['categoria_id'] ?? null;
            $precio_unitario = $input['precio_unitario'] ?? null;
            $sku = $input['sku'] ?? null;
            $codigo_barras = $input['codigo_barras'] ?? null;
            $activo = $input['activo'] ?? null;

            $producto = $this->productoService->actualizarProducto(
                $id,
                $nombre,
                $descripcion,
                $categoria_id,
                $precio_unitario,
                $sku,
                $codigo_barras,
                $activo
            );

            Response::success($producto->toArray(), "Producto actualizado exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * DELETE /productos/:id
     * Solo administrador
     */
    public function delete($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $this->productoService->eliminarProducto($id);

            Response::success(null, "Producto eliminado exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /productos/search/:keyword
     */
    public function search($keyword) {
        try {
            AuthMiddleware::authenticate();
            
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $productos = $this->productoService->buscarProductos($keyword, $page, $perPage);

            Response::success($productos, "Búsqueda completada");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /productos/categoria/:categoria_id
     */
    public function getByCategoria($categoria_id) {
        try {
            AuthMiddleware::authenticate();
            
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $productos = $this->productoService->getProductosByCategoria($categoria_id, $page, $perPage);

            Response::success($productos, "Productos por categoría obtenidos");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
