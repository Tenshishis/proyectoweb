<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Categoria;
use PDOException;

class CategoriaRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las categorías activas
     */
    public function getAll($activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "WHERE deleted_at IS NULL AND activo = true" : "WHERE deleted_at IS NULL";
            
            $query = "SELECT * FROM categorias $whereClause ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $categorias = [];
            while ($row = $stmt->fetch()) {
                $categorias[] = new Categoria($row);
            }
            
            return $categorias;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener categorías: " . $e->getMessage());
        }
    }

    /**
     * Obtiene una categoría por ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM categorias WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Categoria($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener categoría: " . $e->getMessage());
        }
    }

    /**
     * Obtiene una categoría por UUID
     */
    public function getByUuid($uuid) {
        try {
            $query = "SELECT * FROM categorias WHERE uuid = :uuid AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Categoria($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener categoría: " . $e->getMessage());
        }
    }

    /**
     * Crea una nueva categoría
     */
    public function create($nombre, $descripcion = null) {
        try {
            $query = "INSERT INTO categorias (nombre, descripcion, activo)
                      VALUES (:nombre, :descripcion, true)
                      RETURNING id, uuid, nombre, descripcion, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return new Categoria($row);
        } catch (PDOException $e) {
            throw new PDOException("Error al crear categoría: " . $e->getMessage());
        }
    }

    /**
     * Actualiza una categoría
     */
    public function update($id, $nombre = null, $descripcion = null, $activo = null) {
        try {
            $updates = [];
            $params = [];

            if ($nombre !== null) {
                $updates[] = "nombre = :nombre";
                $params[':nombre'] = $nombre;
            }
            if ($descripcion !== null) {
                $updates[] = "descripcion = :descripcion";
                $params[':descripcion'] = $descripcion;
            }
            if ($activo !== null) {
                $updates[] = "activo = :activo";
                $params[':activo'] = $activo;
            }

            if (empty($updates)) {
                return $this->getById($id);
            }

            $params[':id'] = $id;
            $updateClause = implode(", ", $updates);
            
            $query = "UPDATE categorias 
                      SET $updateClause
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id, uuid, nombre, descripcion, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            return $row ? new Categoria($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar categoría: " . $e->getMessage());
        }
    }

    /**
     * Soft delete de una categoría
     */
    public function delete($id) {
        try {
            $query = "UPDATE categorias 
                      SET deleted_at = CURRENT_TIMESTAMP
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar categoría: " . $e->getMessage());
        }
    }
}
