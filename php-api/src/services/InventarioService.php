<?php

namespace App\Services;

use App\Repositories\InventarioRepository;
use App\Repositories\ProductoRepository;
use App\Utils\Validator;
use Exception;

class InventarioService {
    private $inventarioRepository;
    private $productoRepository;

    public function __construct() {
        $this->inventarioRepository = new InventarioRepository();
        $this->productoRepository = new ProductoRepository();
    }

    /**
     * Obtiene el inventario de un producto
     */
    public function getInventario($producto_id) {
        Validator::validatePositive($producto_id, 'producto_id');
        
        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        $inventario = $this->inventarioRepository->getByProductoId($producto_id);
        if (!$inventario) {
            throw new Exception("Inventario no encontrado");
        }

        return $inventario;
    }

    /**
     * Registra una entrada de inventario
     */
    public function registrarEntrada($producto_id, $cantidad, $motivo = null, $usuario_id = null) {
        Validator::validatePositive($producto_id, 'producto_id');
        Validator::validatePositive($cantidad, 'cantidad');
        
        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        return $this->inventarioRepository->registrarMovimiento($producto_id, 'entrada', $cantidad, $motivo, $usuario_id);
    }

    /**
     * Registra una salida de inventario
     */
    public function registrarSalida($producto_id, $cantidad, $motivo = null, $usuario_id = null) {
        Validator::validatePositive($producto_id, 'producto_id');
        Validator::validatePositive($cantidad, 'cantidad');
        
        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        $inventario = $this->inventarioRepository->getByProductoId($producto_id);
        if (!$inventario) {
            throw new Exception("Inventario no encontrado");
        }

        // Validar stock suficiente
        if ($inventario->getDisponible() < $cantidad) {
            throw new Exception("Stock insuficiente. Disponible: " . $inventario->getDisponible());
        }

        return $this->inventarioRepository->registrarMovimiento($producto_id, 'salida', $cantidad, $motivo, $usuario_id);
    }

    /**
     * Realiza un ajuste de inventario
     */
    public function registrarAjuste($producto_id, $cantidad_nueva, $motivo = null, $usuario_id = null) {
        Validator::validatePositive($producto_id, 'producto_id');
        Validator::validateNumeric($cantidad_nueva, 'cantidad_nueva');
        
        if ($cantidad_nueva < 0) {
            throw new Exception("La cantidad no puede ser negativa");
        }

        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        return $this->inventarioRepository->registrarMovimiento($producto_id, 'ajuste', $cantidad_nueva, $motivo, $usuario_id);
    }

    /**
     * Reserva un producto del inventario
     */
    public function reservarProducto($producto_id, $cantidad, $usuario_id = null) {
        Validator::validatePositive($producto_id, 'producto_id');
        Validator::validatePositive($cantidad, 'cantidad');
        
        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        $inventario = $this->inventarioRepository->getByProductoId($producto_id);
        if (!$inventario) {
            throw new Exception("Inventario no encontrado");
        }

        if ($inventario->getDisponible() < $cantidad) {
            throw new Exception("Stock insuficiente para reservar");
        }

        return $this->inventarioRepository->registrarMovimiento($producto_id, 'reserva', $cantidad, 'Reserva de venta', $usuario_id);
    }

    /**
     * Libera una reserva de inventario
     */
    public function liberarReserva($producto_id, $cantidad, $usuario_id = null) {
        Validator::validatePositive($producto_id, 'producto_id');
        Validator::validatePositive($cantidad, 'cantidad');
        
        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        return $this->inventarioRepository->registrarMovimiento($producto_id, 'liberacion_reserva', $cantidad, 'Liberación de reserva', $usuario_id);
    }

    /**
     * Actualiza los parámetros de inventario
     */
    public function actualizarParametros($producto_id, $cantidad_minima = null, $cantidad_maxima = null, $ubicacion_almacen = null, $lote = null, $fecha_vencimiento = null) {
        Validator::validatePositive($producto_id, 'producto_id');
        
        if ($cantidad_minima !== null) {
            Validator::validateNumeric($cantidad_minima, 'cantidad_minima');
        }
        if ($cantidad_maxima !== null) {
            Validator::validateNumeric($cantidad_maxima, 'cantidad_maxima');
        }

        $producto = $this->productoRepository->getById($producto_id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        $inventario = $this->inventarioRepository->update(
            $producto_id,
            null,
            null,
            $cantidad_minima,
            $cantidad_maxima,
            $ubicacion_almacen,
            $lote,
            $fecha_vencimiento
        );

        if (!$inventario) {
            throw new Exception("No se pudo actualizar el inventario");
        }

        return $inventario;
    }

    /**
     * Obtiene productos con bajo stock
     */
    public function getProductosBajoStock() {
        // Obtener todos los inventarios
        $productos = $this->productoRepository->getAll(1, 1000);
        $bajoStock = [];

        foreach ($productos as $producto) {
            $inventario = $this->inventarioRepository->getByProductoId($producto->id);
            if ($inventario && $inventario->isStockBajo()) {
                $bajoStock[] = [
                    'producto' => $producto,
                    'inventario' => $inventario,
                ];
            }
        }

        return $bajoStock;
    }

    /**
     * Valida la disponibilidad de un producto
     */
    public function validarDisponibilidad($producto_id, $cantidad) {
        Validator::validatePositive($producto_id, 'producto_id');
        Validator::validatePositive($cantidad, 'cantidad');
        
        $inventario = $this->inventarioRepository->getByProductoId($producto_id);
        if (!$inventario) {
            throw new Exception("Inventario no encontrado");
        }

        $disponible = $inventario->getDisponible();
        return [
            'disponible' => $disponible >= $cantidad,
            'requerido' => $cantidad,
            'disponible' => $disponible,
            'insuficiente' => max(0, $cantidad - $disponible)
        ];
    }
}
