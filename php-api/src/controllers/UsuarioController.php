<?php

namespace App\Controllers;

use App\Services\UsuarioService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use Exception;

class UsuarioController {
    private $usuarioService;

    public function __construct() {
        $this->usuarioService = new UsuarioService();
    }

    /**
     * GET /usuarios
     * Solo administrador
     */
    public function getAll() {
        try {
            AuthMiddleware::adminOnly();
            
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $result = $this->usuarioService->getAllUsuarios($page, $perPage);

            Response::paginated($result['usuarios'], $result['total'], $result['page'], $result['per_page']);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /usuarios/:id
     */
    public function getById($id) {
        try {
            $user = AuthMiddleware::authenticate();
            
            // Un usuario solo puede ver su propio perfil, excepción: admin
            if ($user['userId'] != $id && $user['rol'] !== 'admin') {
                Response::error("No tiene permiso para ver este usuario", 403);
            }

            $usuario = $this->usuarioService->getUsuarioById($id);

            Response::success($usuario->toArray(), "Usuario obtenido correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    /**
     * GET /usuarios/uuid/:uuid
     */
    public function getByUuid($uuid) {
        try {
            AuthMiddleware::authenticate();
            
            $usuario = $this->usuarioService->getUsuarioByUuid($uuid);

            Response::success($usuario->toArray(), "Usuario obtenido correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    /**
     * PUT /usuarios/:id
     * Solo administrador o el usuario mismo
     */
    public function update($id) {
        try {
            $user = AuthMiddleware::authenticate();
            
            // Un usuario solo puede actualizar su propio perfil, excepción: admin
            if ($user['userId'] != $id && $user['rol'] !== 'admin') {
                Response::error("No tiene permiso para actualizar este usuario", 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $email = $input['email'] ?? null;
            
            // Solo admin puede cambiar rol
            $rol = null;
            if ($user['rol'] === 'admin') {
                $rol = $input['rol'] ?? null;
            }
            
            // Solo admin puede cambiar estado
            $activo = null;
            if ($user['rol'] === 'admin') {
                $activo = $input['activo'] ?? null;
            }

            $usuario = $this->usuarioService->actualizarUsuario($id, $nombre, $email, $rol, $activo);

            Response::success($usuario->toArray(), "Usuario actualizado exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * DELETE /usuarios/:id
     * Solo administrador
     */
    public function delete($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $this->usuarioService->eliminarUsuario($id);

            Response::success(null, "Usuario eliminado exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /usuarios/rol/:rol
     * Solo administrador
     */
    public function getByRol($rol) {
        try {
            AuthMiddleware::adminOnly();
            
            $usuarios = $this->usuarioService->getUsuariosByRol($rol);

            Response::success($usuarios, "Usuarios obtenidos por rol");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /me
     * Obtiene el perfil del usuario autenticado
     */
    public function getProfile() {
        try {
            $user = AuthMiddleware::authenticate();
            
            $usuario = $this->usuarioService->getUsuarioById($user['userId']);

            Response::success($usuario->toArray(), "Perfil obtenido correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }
}
