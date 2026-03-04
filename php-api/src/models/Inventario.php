<?php

namespace App\Models;

class Inventario {
    public $id;
    public $uuid;
    public $producto_id;
    public $cantidad_disponible;
    public $cantidad_reservada;
    public $cantidad_minima;
    public $cantidad_maxima;
    public $ubicacion_almacen;
    public $lote;
    public $fecha_vencimiento;
    public $created_at;
    public $updated_at;

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
            'cantidad_disponible' => (int) $this->cantidad_disponible,
            'cantidad_reservada' => (int) $this->cantidad_reservada,
            'cantidad_minima' => (int) $this->cantidad_minima,
            'cantidad_maxima' => (int) $this->cantidad_maxima,
            'ubicacion_almacen' => $this->ubicacion_almacen,
            'lote' => $this->lote,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Obtiene la cantidad disponible para venta
     */
    public function getDisponible() {
        return $this->cantidad_disponible - $this->cantidad_reservada;
    }

    /**
     * Verifica si el stock está bajo el mínimo
     */
    public function isStockBajo() {
        return $this->cantidad_disponible < $this->cantidad_minima;
    }

    /**
     * Verifica si el stock está sobre el máximo
     */
    public function isStockAlto() {
        return $this->cantidad_disponible > $this->cantidad_maxima;
    }
}
