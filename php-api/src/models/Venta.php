<?php

namespace App\Models;

/**
 * Model: Venta
 * Representa una venta en el sistema
 * 
 * Campos:
 * - id: ID único de la venta
 * - uuid: UUID único de la venta
 * - numero_venta: Número secuencial de venta
 * - usuario_id: Usuario que realizó la venta
 * - fecha_venta: Fecha y hora de la venta
 * - total: Total de la venta
 * - estado: Estado de la venta (pendiente, completada, cancelada)
 * - observaciones: Notas sobre la venta
 * 
 * Relaciones:
 * - Muchas ventas pueden pertenecer a 1 usuario
 * - Muchos items pueden pertenecer a 1 venta
 */
class Venta
{
    public $id;
    public $uuid;
    public $numero_venta;
    public $usuario_id;
    public $fecha_venta;
    public $total;
    public $estado; // 'pendiente', 'completada', 'cancelada'
    public $observaciones;
    public $created_at;
    public $updated_at;

    public function __construct(
        $numero_venta = null,
        $usuario_id = null,
        $total = 0,
        $estado = 'pendiente',
        $observaciones = ''
    ) {
        $this->numero_venta = $numero_venta;
        $this->usuario_id = $usuario_id;
        $this->total = $total;
        $this->estado = $estado;
        $this->observaciones = $observaciones;
    }

    /**
     * Validar datos de venta
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->numero_venta)) {
            $errors[] = 'Número de venta es requerido';
        }

        if (empty($this->usuario_id) || !is_numeric($this->usuario_id)) {
            $errors[] = 'Usuario es requerido y debe ser válido';
        }

        if ($this->total < 0) {
            $errors[] = 'El total no puede ser negativo';
        }

        if (!in_array($this->estado, ['pendiente', 'completada', 'cancelada'])) {
            $errors[] = 'El estado debe ser: pendiente, completada o cancelada';
        }

        return $errors;
    }
}
