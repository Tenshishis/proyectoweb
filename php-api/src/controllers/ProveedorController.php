<?php

namespace App\Controllers;

use App\Repositories\ProveedorRepository;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use Exception;

class ProveedorController {
    private $proveedorRepository;

    public function __construct() {
        $this->proveedorRepository = new ProveedorRepository();
    }

    /**
     * GET /proveedores
     */
    public function getAll() {
        try {
            AuthMiddleware::authenticate();
            
            $page = $_GET['page'] ?? 1;
            $perPage = $_GET['per_page'] ?? 20;

            $proveedores = $this->proveedorRepository->getAll($page, $perPage);
            $total = $this->proveedorRepository->count();

            Response::paginated($proveedores, $total, $page, $perPage);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /proveedores/:id
     */
    public function getById($id) {
        try {
            AuthMiddleware::authenticate();
            
            $proveedor = $this->proveedorRepository->getById($id);
            if (!$proveedor) {
                throw new Exception("Proveedor no encontrado");
            }

            Response::success($proveedor->toArray(), "Proveedor obtenido correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    /**
     * POST /proveedores
     * Solo administrador
     */
    public function create() {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $email = $input['email'] ?? null;
            $telefono = $input['telefono'] ?? null;
            $direccion = $input['direccion'] ?? null;
            $ciudad = $input['ciudad'] ?? null;
            $pais = $input['pais'] ?? null;

            $proveedor = $this->proveedorRepository->create(
                $nombre,
                $email,
                $telefono,
                $direccion,
                $ciudad,
                $pais
            );

            Response::success($proveedor->toArray(), "Proveedor creado exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * PUT /proveedores/:id
     * Solo administrador
     */
    public function update($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $email = $input['email'] ?? null;
            $telefono = $input['telefono'] ?? null;
            $direccion = $input['direccion'] ?? null;
            $ciudad = $input['ciudad'] ?? null;
            $pais = $input['pais'] ?? null;
            $activo = $input['activo'] ?? null;

            $proveedor = $this->proveedorRepository->update($id, $nombre, $email, $telefono, $direccion, $ciudad, $pais, $activo);
            if (!$proveedor) {
                throw new Exception("Proveedor no encontrado");
            }

            Response::success($proveedor->toArray(), "Proveedor actualizado exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * DELETE /proveedores/:id
     * Solo administrador
     */
    public function delete($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $deleted = $this->proveedorRepository->delete($id);
            if (!$deleted) {
                throw new Exception("Proveedor no encontrado");
            }

            Response::success(null, "Proveedor eliminado exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
