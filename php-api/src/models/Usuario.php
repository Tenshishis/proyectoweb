<?php

namespace App\Models;

class Usuario {
    public $id;
    public $uuid;
    public $nombre;
    public $email;
    public $password;
    public $rol;
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

    public function toArray($includePassword = false) {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
            'activo' => (bool) $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];

        if ($includePassword) {
            $data['password'] = $this->password;
        }

        return $data;
    }

    /**
     * Verifica si la contraseña es correcta
     */
    public function verifyPassword($plainPassword) {
        return password_verify($plainPassword, $this->password);
    }

    /**
     * Hashea una contraseña
     */
    public static function hashPassword($plainPassword) {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin() {
        return $this->rol === 'admin';
    }

    /**
     * Verifica si el usuario es vendedor
     */
    public function isVendedor() {
        return $this->rol === 'vendedor';
    }

    /**
     * Verifica si el usuario es consultor
     */
    public function isConsultor() {
        return $this->rol === 'consultor';
    }
}
