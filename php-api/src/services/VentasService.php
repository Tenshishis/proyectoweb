<?php

namespace App\Services;

use App\Repositories\VentaRepository;
use App\Repositories\VentaItemRepository;
use App\Repositories\ReporteVentaRepository;
use App\Repositories\InventarioRepository;
use App\Repositories\ProductoRepository;
use App\Repositories\CategoriaRepository;
use App\Repositories\ProveedorRepository;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\ReporteVenta;
use App\Config\Database;

/**
 * Service: VentasService
 * Lógica de negocio para ventas
 * 
 * Responsabilidades:
 * - Crear ventas
 * - Agregar items a ventas
 * - Validar stock disponible
 * - Descontar del inventario automáticamente
 * - Registrar en tabla de reportes
 * - Manejar descuentos
 * - Transacciones
 */
class VentasService
{
    private $ventaRepo;
    private $ventaItemRepo;
    private $reporteRepo;
    private $inventarioRepo;
    private $productoRepo;
    private $categoriaRepo;
    private $proveedorRepo;
    private $db;

    public function __construct()
    {
        $this->ventaRepo = new VentaRepository();
        $this->ventaItemRepo = new VentaItemRepository();
        $this->reporteRepo = new ReporteVentaRepository();
        $this->inventarioRepo = new InventarioRepository();
        $this->productoRepo = new ProductoRepository();
        $this->categoriaRepo = new CategoriaRepository();
        $this->proveedorRepo = new ProveedorRepository();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear venta nueva
     */
    public function crearVenta($usuarioId, $observaciones = '')
    {
        try {
            // Validar usuario
            if (!is_numeric($usuarioId) || $usuarioId <= 0) {
                throw new \Exception('Usuario inválido');
            }

            // Generar número de venta único
            $numeroVenta = $this->ventaRepo->generarNumeroVenta();

            // Crear venta
            $venta = new Venta($numeroVenta, $usuarioId, 0, 'pendiente', $observaciones);
            $ventaId = $this->ventaRepo->create($venta);

            if (!$ventaId) {
                throw new \Exception('No se pudo crear la venta');
            }

            return [
                'id' => $ventaId,
                'numero_venta' => $numeroVenta,
                'usuario_id' => $usuarioId
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al crear venta: ' . $e->getMessage());
        }
    }

    /**
     * Agregar item a venta
     * Valida stock y descuenta automáticamente del inventario
     */
    public function agregarItemAVenta($ventaId, $productoId, $cantidad, $descuentoPorcentaje = 0)
    {
        try {
            // Iniciar transacción
            $this->db->beginTransaction();

            // Validar producto existe
            $producto = $this->productoRepo->getById($productoId);
            if (!$producto) {
                throw new \Exception('Producto no encontrado');
            }

            // Validar cantidad
            if ($cantidad <= 0) {
                throw new \Exception('La cantidad debe ser mayor a 0');
            }

            // Obtener inventario
            $inventario = $this->inventarioRepo->getByProductoId($productoId);
            if (!$inventario) {
                throw new \Exception('No hay inventario para este producto');
            }

            // Validar stock disponible
            $disponible = $inventario['cantidad_disponible'] - $inventario['cantidad_reservada'];
            if ($disponible < $cantidad) {
                throw new \Exception("Stock insuficiente. Disponible: {$disponible}");
            }

            // Crear item
            $precioUnitario = $producto['precio_unitario'];
            $item = new VentaItem($ventaId, $productoId, $cantidad, $precioUnitario, $descuentoPorcentaje);

            // Validar item
            $errores = $item->validate();
            if (!empty($errores)) {
                throw new \Exception(implode(', ', $errores));
            }

            // Guardar item
            $itemId = $this->ventaItemRepo->create($item);
            if (!$itemId) {
                throw new \Exception('No se pudo agregar el item a la venta');
            }

            // **DESCONTAR INVENTARIO AUTOMÁTICAMENTE**
            $movimientoInventario = new InventarioService();
            $movimientoInventario->registrarSalida(
                $productoId,
                $cantidad,
                "Venta #VTA-{$ventaId}"
            );

            // Recalcular total de venta
            $this->ventaRepo->recalcularTotal($ventaId);

            // Confirmar transacción
            $this->db->commit();

            return [
                'item_id' => $itemId,
                'venta_id' => $ventaId,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'subtotal' => $item->subtotal
            ];

        } catch (\Exception $e) {
            // Revertir transacción si hay error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Completar venta (cambiar estado a completada)
     * Genera registro en tabla de reportes
     */
    public function completarVenta($ventaId)
    {
        try {
            // Obtener venta
            $venta = $this->ventaRepo->getById($ventaId);
            if (!$venta) {
                throw new \Exception('Venta no encontrada');
            }

            // Obtener items
            $items = $this->ventaItemRepo->getByVentaId($ventaId);
            if (empty($items)) {
                throw new \Exception('La venta debe tener al menos 1 item');
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            // Cambiar estado a completada
            $this->ventaRepo->cambiarEstado($ventaId, 'completada');

            // Registrar cada item en tabla de reportes
            foreach ($items as $item) {
                $producto = $this->productoRepo->getById($item['producto_id']);
                $categoria = $this->categoriaRepo->getById($producto['categoria_id']);

                // Obtener proveedor principal del producto (si existe)
                $proveedor = null;
                // Tu lógica para obtener proveedor principal

                $reporte = new ReporteVenta(
                    date('Y-m-d'),
                    $item['producto_id'],
                    $categoria['nombre'],
                    $proveedor ? $proveedor['id'] : null,
                    $proveedor ? $proveedor['nombre'] : '',
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $venta['usuario_id']
                );

                $this->reporteRepo->registrarVenta($reporte);
            }

            // Confirmar transacción
            $this->db->commit();

            return [
                'venta_id' => $ventaId,
                'estado' => 'completada',
                'items_procesados' => count($items)
            ];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Cancelar venta (revertir descuentos)
     */
    public function cancelarVenta($ventaId)
    {
        try {
            // Obtener venta
            $venta = $this->ventaRepo->getById($ventaId);
            if (!$venta) {
                throw new \Exception('Venta no encontrada');
            }

            if ($venta['estado'] === 'cancelada') {
                throw new \Exception('La venta ya estaba cancelada');
            }

            // Obtener items
            $items = $this->ventaItemRepo->getByVentaId($ventaId);

            // Iniciar transacción
            $this->db->beginTransaction();

            // Revertir descuentos del inventario
            $movimientoInventario = new InventarioService();
            foreach ($items as $item) {
                $movimientoInventario->registrarEntrada(
                    $item['producto_id'],
                    $item['cantidad'],
                    "Cancelación de venta #VTA-{$ventaId}"
                );
            }

            // Cambiar estado a cancelada
            $this->ventaRepo->cambiarEstado($ventaId, 'cancelada');

            // Confirmar transacción
            $this->db->commit();

            return [
                'venta_id' => $ventaId,
                'estado' => 'cancelada',
                'items_revertidos' => count($items)
            ];

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener venta con todos sus items
     */
    public function obtenerVentaCompleta($ventaId)
    {
        $venta = $this->ventaRepo->getById($ventaId);
        if (!$venta) {
            return null;
        }

        $items = $this->ventaItemRepo->getByVentaId($ventaId);

        return [
            'venta' => $venta,
            'items' => $items,
            'total_items' => count($items),
            'total' => $venta['total']
        ];
    }

    /**
     * Obtener todas las ventas con paginación
     */
    public function obtenerVentas($page = 1, $perPage = 20)
    {
        return $this->ventaRepo->getAll($page, $perPage);
    }

    /**
     * Obtener ventas por usuario
     */
    public function obtenerVentasUsuario($usuarioId, $page = 1, $perPage = 20)
    {
        return $this->ventaRepo->getByUsuarios($usuarioId, $page, $perPage);
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public function obtenerVentasPorFechas($fechaInicio, $fechaFin, $page = 1, $perPage = 20)
    {
        return $this->ventaRepo->getByFechas($fechaInicio, $fechaFin, $page, $perPage);
    }

    /**
     * Aplicar descuento adicional a un item
     */
    public function aplicarDescuentoItem($itemId, $nuevoDescuento)
    {
        try {
            if ($nuevoDescuento < 0 || $nuevoDescuento > 100) {
                throw new \Exception('Descuento debe estar entre 0 y 100');
            }

            $item = $this->ventaItemRepo->getById($itemId);
            if (!$item) {
                throw new \Exception('Item no encontrado');
            }

            // Actualizar descuento
            $item['descuento'] = $nuevoDescuento;
            
            // Recalcular subtotal
            $base = $item['cantidad'] * $item['precio_unitario'];
            $item['subtotal'] = $base - ($base * ($nuevoDescuento / 100));

            // Guardar
            $ventaItem = new VentaItem(
                $item['venta_id'],
                $item['producto_id'],
                $item['cantidad'],
                $item['precio_unitario'],
                $nuevoDescuento
            );

            if (!$this->ventaItemRepo->update($itemId, $ventaItem)) {
                throw new \Exception('No se pudo actualizar el descuento');
            }

            // Recalcular total de venta
            $this->ventaRepo->recalcularTotal($item['venta_id']);

            return [
                'item_id' => $itemId,
                'descuento' => $nuevoDescuento,
                'subtotal' => $item['subtotal']
            ];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
