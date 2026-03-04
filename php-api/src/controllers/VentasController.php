<?php

namespace App\Controllers;

use App\Services\VentasService;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;

/**
 * Controller: VentasController
 * Maneja todos los requests HTTP relacionados con ventas
 */
class VentasController
{
    private $ventasService;
    private $auth;

    public function __construct()
    {
        $this->ventasService = new VentasService();
        $this->auth = new AuthMiddleware();
    }

    /**
     * GET /ventas
     * Obtener todas las ventas
     */
    public function getAll()
    {
        try {
            // Validar autenticación
            $this->auth->authenticate();
            
            // Parámetros
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $ventas = $this->ventasService->obtenerVentas($page, $perPage);

            Response::success($ventas, 'Ventas obtenidas');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /ventas/:id
     * Obtener venta por ID con todos sus items
     */
    public function getById($id)
    {
        try {
            $this->auth->authenticate();

            if (!is_numeric($id)) {
                throw new \Exception('ID inválido');
            }

            $venta = $this->ventasService->obtenerVentaCompleta($id);
            
            if (!$venta) {
                http_response_code(404);
                return Response::error('Venta no encontrada', 404);
            }

            Response::success($venta, 'Venta obtenida');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /ventas
     * Crear venta nueva
     * 
     * Body:
     * {
     *   "usuario_id": 1,
     *   "observaciones": "Nota sobre la venta"
     * }
     */
    public function create()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'vendedor']);

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['usuario_id'])) {
                throw new \Exception('usuario_id es requerido');
            }

            $venta = $this->ventasService->crearVenta(
                $data['usuario_id'],
                $data['observaciones'] ?? ''
            );

            http_response_code(201);
            Response::success($venta, 'Venta creada exitosamente', 201);
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /ventas/:id/items
     * Agregar item a venta con validación de stock
     * 
     * Body:
     * {
     *   "producto_id": 5,
     *   "cantidad": 10,
     *   "descuento_porcentaje": 5
     * }
     */
    public function agregarItem($ventaId)
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'vendedor']);

            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['producto_id'])) {
                throw new \Exception('producto_id es requerido');
            }

            if (empty($data['cantidad']) || $data['cantidad'] <= 0) {
                throw new \Exception('cantidad debe ser mayor a 0');
            }

            $descuento = $data['descuento_porcentaje'] ?? 0;
            if ($descuento < 0 || $descuento > 100) {
                $descuento = 0;
            }

            // Agregar item (esto también descuenta automáticamente)
            $item = $this->ventasService->agregarItemAVenta(
                $ventaId,
                $data['producto_id'],
                $data['cantidad'],
                $descuento
            );

            http_response_code(201);
            Response::success($item, 'Item agregado a la venta', 201);
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /ventas/:id/completar
     * Completar venta (cambiar estado a completada)
     * Registra los datos en tabla de reportes
     */
    public function completarVenta($ventaId)
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'vendedor']);

            $resultado = $this->ventasService->completarVenta($ventaId);

            Response::success($resultado, 'Venta completada exitosamente');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /ventas/:id/cancelar
     * Cancelar venta (revertir descuentos)
     */
    public function cancelarVenta($ventaId)
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'vendedor']);

            $resultado = $this->ventasService->cancelarVenta($ventaId);

            Response::success($resultado, 'Venta cancelada exitosamente');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * PUT /ventas/:id/items/:itemId/descuento
     * Aplicar descuento a un item específico
     * 
     * Body:
     * {
     *   "descuento_porcentaje": 10
     * }
     */
    public function aplicarDescuentoItem($ventaId, $itemId)
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin']);

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['descuento_porcentaje'])) {
                throw new \Exception('descuento_porcentaje es requerido');
            }

            $resultado = $this->ventasService->aplicarDescuentoItem(
                $itemId,
                $data['descuento_porcentaje']
            );

            Response::success($resultado, 'Descuento aplicado');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /ventas/usuario/:usuarioId
     * Obtener ventas de un usuario específico
     */
    public function getByUsuario($usuarioId)
    {
        try {
            $this->auth->authenticate();

            if (!is_numeric($usuarioId)) {
                throw new \Exception('Usuario ID inválido');
            }

            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $ventas = $this->ventasService->obtenerVentasUsuario($usuarioId, $page, $perPage);

            Response::success($ventas, 'Ventas del usuario obtenidas');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /ventas/fechas
     * Obtener ventas por rango de fechas
     * 
     * Parámetros:
     * ?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function getByFechas()
    {
        try {
            $this->auth->authenticate();

            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            if (!$fechaInicio || !$fechaFin) {
                throw new \Exception('fecha_inicio y fecha_fin son requeridos');
            }

            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $ventas = $this->ventasService->obtenerVentasPorFechas(
                $fechaInicio,
                $fechaFin,
                $page,
                $perPage
            );

            Response::success($ventas, 'Ventas obtenidas');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }
}
