<?php

namespace App\Models;

/**
 * Entidad que representa un movimiento de inventario
 * Almacena el historial de cambios en el inventario
 */
class MovimientoInventario {
    public $id;
    public $uuid;
    public $producto_id;
    public $tipo_movimiento; // entrada, salida, ajuste, reserva, liberacion_reserva
    public $cantidad;
    public $cantidad_anterior;
    public $cantidad_nueva;
    public $motivo;
    public $usuario_id;
    public $created_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'producto_id' => $this->producto_id,
            'tipo_movimiento' => $this->tipo_movimiento,
            'cantidad' => (int) $this->cantidad,
            'cantidad_anterior' => (int) $this->cantidad_anterior,
            'cantidad_nueva' => (int) $this->cantidad_nueva,
            'motivo' => $this->motivo,
            'usuario_id' => $this->usuario_id,
            'created_at' => $this->created_at,
        ];
    }
}
