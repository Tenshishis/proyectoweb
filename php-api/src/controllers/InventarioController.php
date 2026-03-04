<?php

namespace App\Controllers;

use App\Services\InventarioService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use Exception;

class InventarioController {
    private $inventarioService;

    public function __construct() {
        $this->inventarioService = new InventarioService();
    }

    /**
     * GET /inventario/:producto_id
     */
    public function getInventario($producto_id) {
        try {
            AuthMiddleware::authenticate();
            
            $inventario = $this->inventarioService->getInventario($producto_id);

            Response::success($inventario->toArray(), "Inventario obtenido correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    /**
     * POST /inventario/:producto_id/entrada
     * Solo administrador y vendedor
     */
    public function registrarEntrada($producto_id) {
        try {
            $user = AuthMiddleware::adminOrVendedor();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $cantidad = $input['cantidad'] ?? null;
            $motivo = $input['motivo'] ?? 'Entrada de inventario';

            $movimiento = $this->inventarioService->registrarEntrada(
                $producto_id,
                $cantidad,
                $motivo,
                $user['userId']
            );

            Response::success($movimiento, "Entrada registrada exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /inventario/:producto_id/salida
     * Solo administrador y vendedor
     */
    public function registrarSalida($producto_id) {
        try {
            $user = AuthMiddleware::adminOrVendedor();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $cantidad = $input['cantidad'] ?? null;
            $motivo = $input['motivo'] ?? 'Salida de inventario';

            $movimiento = $this->inventarioService->registrarSalida(
                $producto_id,
                $cantidad,
                $motivo,
                $user['userId']
            );

            Response::success($movimiento, "Salida registrada exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /inventario/:producto_id/ajuste
     * Solo administrador
     */
    public function registrarAjuste($producto_id) {
        try {
            $user = AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $cantidad_nueva = $input['cantidad_nueva'] ?? null;
            $motivo = $input['motivo'] ?? 'Ajuste de inventario';

            $movimiento = $this->inventarioService->registrarAjuste(
                $producto_id,
                $cantidad_nueva,
                $motivo,
                $user['userId']
            );

            Response::success($movimiento, "Ajuste registrado exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /inventario/:producto_id/reserva
     * Solo administrador y vendedor
     */
    public function reservarProducto($producto_id) {
        try {
            $user = AuthMiddleware::adminOrVendedor();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $cantidad = $input['cantidad'] ?? null;

            $movimiento = $this->inventarioService->reservarProducto(
                $producto_id,
                $cantidad,
                $user['userId']
            );

            Response::success($movimiento, "Producto reservado exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /inventario/:producto_id/liberar-reserva
     * Solo administrador y vendedor
     */
    public function liberarReserva($producto_id) {
        try {
            $user = AuthMiddleware::adminOrVendedor();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $cantidad = $input['cantidad'] ?? null;

            $movimiento = $this->inventarioService->liberarReserva(
                $producto_id,
                $cantidad,
                $user['userId']
            );

            Response::success($movimiento, "Reserva liberada exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * PUT /inventario/:producto_id/parametros
     * Solo administrador
     */
    public function actualizarParametros($producto_id) {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $cantidad_minima = $input['cantidad_minima'] ?? null;
            $cantidad_maxima = $input['cantidad_maxima'] ?? null;
            $ubicacion_almacen = $input['ubicacion_almacen'] ?? null;
            $lote = $input['lote'] ?? null;
            $fecha_vencimiento = $input['fecha_vencimiento'] ?? null;

            $inventario = $this->inventarioService->actualizarParametros(
                $producto_id,
                $cantidad_minima,
                $cantidad_maxima,
                $ubicacion_almacen,
                $lote,
                $fecha_vencimiento
            );

            Response::success($inventario->toArray(), "Parámetros actualizados exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /inventario/bajo-stock
     * Solo administrador
     */
    public function getBajoStock() {
        try {
            AuthMiddleware::adminOnly();
            
            $bajoStock = $this->inventarioService->getProductosBajoStock();

            Response::success($bajoStock, "Productos con bajo stock obtenidos");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /inventario/:producto_id/disponibilidad
     */
    public function validarDisponibilidad($producto_id) {
        try {
            AuthMiddleware::authenticate();
            
            $cantidad = $_GET['cantidad'] ?? 1;

            $disponibilidad = $this->inventarioService->validarDisponibilidad($producto_id, $cantidad);

            Response::success($disponibilidad, "Disponibilidad validada");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
