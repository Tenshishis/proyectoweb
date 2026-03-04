<?php

namespace App\Models;

/**
 * Model: ReporteVenta
 * Representa un registro en la tabla desnormalizada reporte_ventas
 * Optimizada para consultas de reportes
 * 
 * Campos:
 * - id: ID único del registro
 * - fecha_venta: Fecha de la venta
 * - producto_id: ID del producto
 * - categoria_nombre: Nombre de categoría (desnormalizado)
 * - proveedor_id: ID del proveedor
 * - proveedor_nombre: Nombre del proveedor (desnormalizado)
 * - cantidad_vendida: Cantidad vendida
 * - precio_unitario: Precio al momento de venta
 * - total_venta: Total de la venta
 * - usuario_id: ID del usuario que realizó venta
 * - created_at: Fecha de creación del registro
 */
class ReporteVenta
{
    public $id;
    public $fecha_venta;
    public $producto_id;
    public $categoria_nombre;
    public $proveedor_id;
    public $proveedor_nombre;
    public $cantidad_vendida;
    public $precio_unitario;
    public $total_venta;
    public $usuario_id;
    public $created_at;

    public function __construct(
        $fecha_venta = null,
        $producto_id = null,
        $categoria_nombre = '',
        $proveedor_id = null,
        $proveedor_nombre = '',
        $cantidad_vendida = 0,
        $precio_unitario = 0,
        $usuario_id = null
    ) {
        $this->fecha_venta = $fecha_venta ?? date('Y-m-d');
        $this->producto_id = $producto_id;
        $this->categoria_nombre = $categoria_nombre;
        $this->proveedor_id = $proveedor_id;
        $this->proveedor_nombre = $proveedor_nombre;
        $this->cantidad_vendida = $cantidad_vendida;
        $this->precio_unitario = $precio_unitario;
        $this->total_venta = $cantidad_vendida * $precio_unitario;
        $this->usuario_id = $usuario_id;
    }

    /**
     * Validar datos del reporte
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->fecha_venta)) {
            $errors[] = 'Fecha de venta es requerida';
        }

        if (empty($this->producto_id) || !is_numeric($this->producto_id)) {
            $errors[] = 'Producto es requerido y debe ser válido';
        }

        if ($this->cantidad_vendida <= 0) {
            $errors[] = 'La cantidad vendida debe ser mayor a 0';
        }

        if ($this->precio_unitario <= 0) {
            $errors[] = 'El precio unitario debe ser mayor a 0';
        }

        return $errors;
    }
}
