<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Utils\Validator;
use Exception;

class UsuarioService {
    private $usuarioRepository;

    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }

    /**
     * Obtiene todos los usuarios
     */
    public function getAllUsuarios($page = 1, $perPage = 20) {
        Validator::validatePositive($page, 'page');
        Validator::validatePositive($perPage, 'per_page');
        
        $usuarios = $this->usuarioRepository->getAll($page, $perPage);
        $total = $this->usuarioRepository->count();

        return [
            'usuarios' => $usuarios,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Obtiene un usuario por ID
     */
    public function getUsuarioById($id) {
        Validator::validatePositive($id, 'id');
        
        $usuario = $this->usuarioRepository->getById($id);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        return $usuario;
    }

    /**
     * Obtiene un usuario por UUID
     */
    public function getUsuarioByUuid($uuid) {
        $usuario = $this->usuarioRepository->getByUuid($uuid);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        return $usuario;
    }

    /**
     * Actualiza un usuario
     */
    public function actualizarUsuario($id, $nombre = null, $email = null, $rol = null, $activo = null) {
        if ($email !== null) {
            if (!Validator::validateEmail($email)) {
                throw new Exception("Email inválido");
            }
        }
        if ($rol !== null) {
            Validator::validateRole($rol);
        }

        $usuario = $this->usuarioRepository->update($id, $nombre, $email, $rol, $activo);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        return $usuario;
    }

    /**
     * Elimina un usuario
     */
    public function eliminarUsuario($id) {
        Validator::validatePositive($id, 'id');
        
        $usuario = $this->usuarioRepository->getById($id);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        $deleted = $this->usuarioRepository->delete($id);
        if (!$deleted) {
            throw new Exception("No se pudo eliminar el usuario");
        }

        return true;
    }

    /**
     * Obtiene usuarios por rol
     */
    public function getUsuariosByRol($rol) {
        Validator::validateRole($rol);
        
        // Obtener todos y filtrar (una alternativa sería crear un método en el repository)
        $usuarios = $this->usuarioRepository->getAll(1, 1000);
        $filtered = [];

        foreach ($usuarios as $usuario) {
            if ($usuario->rol === $rol) {
                $filtered[] = $usuario;
            }
        }

        return $filtered;
    }
}
