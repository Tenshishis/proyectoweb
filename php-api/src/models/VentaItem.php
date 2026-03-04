<?php

namespace App\Models;

/**
 * Model: VentaItem
 * Representa un item (producto) dentro de una venta
 * 
 * Campos:
 * - id: ID único del item
 * - venta_id: ID de la venta a la que pertenece
 * - producto_id: ID del producto
 * - cantidad: Cantidad vendida
 * - precio_unitario: Precio unitario al momento de venta
 * - descuento: Descuento aplicado (porcentaje)
 * - subtotal: Subtotal del item (cantidad * precio_unitario - descuento)
 */
class VentaItem
{
    public $id;
    public $venta_id;
    public $producto_id;
    public $cantidad;
    public $precio_unitario;
    public $descuento; // porcentaje de descuento
    public $subtotal;
    public $created_at;

    public function __construct(
        $venta_id = null,
        $producto_id = null,
        $cantidad = 0,
        $precio_unitario = 0,
        $descuento = 0
    ) {
        $this->venta_id = $venta_id;
        $this->producto_id = $producto_id;
        $this->cantidad = $cantidad;
        $this->precio_unitario = $precio_unitario;
        $this->descuento = $descuento;
        $this->calcularSubtotal();
    }

    /**
     * Calcular subtotal automáticamente
     */
    public function calcularSubtotal()
    {
        $base = $this->cantidad * $this->precio_unitario;
        $descuentoMonto = $base * ($this->descuento / 100);
        $this->subtotal = $base - $descuentoMonto;
    }

    /**
     * Validar datos del item
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->producto_id) || !is_numeric($this->producto_id)) {
            $errors[] = 'Producto es requerido y debe ser válido';
        }

        if ($this->cantidad <= 0) {
            $errors[] = 'La cantidad debe ser mayor a 0';
        }

        if ($this->precio_unitario <= 0) {
            $errors[] = 'El precio unitario debe ser mayor a 0';
        }

        if ($this->descuento < 0 || $this->descuento > 100) {
            $errors[] = 'El descuento debe estar entre 0 y 100';
        }

        return $errors;
    }
}
