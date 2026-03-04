<?php

namespace App\Models;

class Proveedor {
    public $id;
    public $uuid;
    public $nombre;
    public $email;
    public $telefono;
    public $direccion;
    public $ciudad;
    public $pais;
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
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
