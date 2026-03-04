<?php

namespace App\Models;

class Producto {
    public $id;
    public $uuid;
    public $nombre;
    public $descripcion;
    public $categoria_id;
    public $precio_unitario;
    public $sku;
    public $codigo_barras;
    public $activo;
    public $created_at;
    public $updated_at;
    public $deleted_at;

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
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'categoria_id' => $this->categoria_id,
            'precio_unitario' => (float) $this->precio_unitario,
            'sku' => $this->sku,
            'codigo_barras' => $this->codigo_barras,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
