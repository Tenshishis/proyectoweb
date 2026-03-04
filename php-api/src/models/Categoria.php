<?php

namespace App\Models;

class Categoria {
    public $id;
    public $uuid;
    public $nombre;
    public $descripcion;
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
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
