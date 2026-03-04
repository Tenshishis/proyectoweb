<?php

namespace App\Controllers;

use App\Repositories\CategoriaRepository;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;
use Exception;

class CategoriaController {
    private $categoriaRepository;

    public function __construct() {
        $this->categoriaRepository = new CategoriaRepository();
    }

    /**
     * GET /categorias
     */
    public function getAll() {
        try {
            AuthMiddleware::authenticate();
            
            $categorias = $this->categoriaRepository->getAll();

            Response::success($categorias, "Categorías obtenidas correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /categorias/:id
     */
    public function getById($id) {
        try {
            AuthMiddleware::authenticate();
            
            $categoria = $this->categoriaRepository->getById($id);
            if (!$categoria) {
                throw new Exception("Categoría no encontrada");
            }

            Response::success($categoria->toArray(), "Categoría obtenida correctamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 404);
        }
    }

    /**
     * POST /categorias
     * Solo administrador
     */
    public function create() {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $descripcion = $input['descripcion'] ?? null;

            $categoria = $this->categoriaRepository->create($nombre, $descripcion);

            Response::success($categoria->toArray(), "Categoría creada exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * PUT /categorias/:id
     * Solo administrador
     */
    public function update($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $descripcion = $input['descripcion'] ?? null;
            $activo = $input['activo'] ?? null;

            $categoria = $this->categoriaRepository->update($id, $nombre, $descripcion, $activo);
            if (!$categoria) {
                throw new Exception("Categoría no encontrada");
            }

            Response::success($categoria->toArray(), "Categoría actualizada exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * DELETE /categorias/:id
     * Solo administrador
     */
    public function delete($id) {
        try {
            AuthMiddleware::adminOnly();
            
            $deleted = $this->categoriaRepository->delete($id);
            if (!$deleted) {
                throw new Exception("Categoría no encontrada");
            }

            Response::success(null, "Categoría eliminada exitosamente");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
