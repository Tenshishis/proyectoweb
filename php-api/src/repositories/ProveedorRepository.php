<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Proveedor;
use PDOException;

class ProveedorRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los proveedores activos
     */
    public function getAll($page = 1, $perPage = 20, $activeOnly = true) {
        try {
            $offset = ($page - 1) * $perPage;
            $whereClause = $activeOnly ? "WHERE deleted_at IS NULL AND activo = true" : "WHERE deleted_at IS NULL";
            
            $query = "SELECT * FROM proveedores $whereClause ORDER BY nombre ASC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $proveedores = [];
            while ($row = $stmt->fetch()) {
                $proveedores[] = new Proveedor($row);
            }
            
            return $proveedores;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener proveedores: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el total de proveedores
     */
    public function count($activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "WHERE deleted_at IS NULL AND activo = true" : "WHERE deleted_at IS NULL";
            
            $query = "SELECT COUNT(*) as total FROM proveedores $whereClause";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            throw new PDOException("Error al contar proveedores: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un proveedor por ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM proveedores WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Proveedor($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener proveedor: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un proveedor por UUID
     */
    public function getByUuid($uuid) {
        try {
            $query = "SELECT * FROM proveedores WHERE uuid = :uuid AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Proveedor($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener proveedor: " . $e->getMessage());
        }
    }

    /**
     * Crea un nuevo proveedor
     */
    public function create($nombre, $email = null, $telefono = null, $direccion = null, $ciudad = null, $pais = null) {
        try {
            $query = "INSERT INTO proveedores (nombre, email, telefono, direccion, ciudad, pais, activo)
                      VALUES (:nombre, :email, :telefono, :direccion, :ciudad, :pais, true)
                      RETURNING id, uuid, nombre, email, telefono, direccion, ciudad, pais, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':ciudad', $ciudad);
            $stmt->bindParam(':pais', $pais);
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return new Proveedor($row);
        } catch (PDOException $e) {
            throw new PDOException("Error al crear proveedor: " . $e->getMessage());
        }
    }

    /**
     * Actualiza un proveedor
     */
    public function update($id, $nombre = null, $email = null, $telefono = null, $direccion = null, $ciudad = null, $pais = null, $activo = null) {
        try {
            $updates = [];
            $params = [];

            if ($nombre !== null) {
                $updates[] = "nombre = :nombre";
                $params[':nombre'] = $nombre;
            }
            if ($email !== null) {
                $updates[] = "email = :email";
                $params[':email'] = $email;
            }
            if ($telefono !== null) {
                $updates[] = "telefono = :telefono";
                $params[':telefono'] = $telefono;
            }
            if ($direccion !== null) {
                $updates[] = "direccion = :direccion";
                $params[':direccion'] = $direccion;
            }
            if ($ciudad !== null) {
                $updates[] = "ciudad = :ciudad";
                $params[':ciudad'] = $ciudad;
            }
            if ($pais !== null) {
                $updates[] = "pais = :pais";
                $params[':pais'] = $pais;
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
            
            $query = "UPDATE proveedores 
                      SET $updateClause
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id, uuid, nombre, email, telefono, direccion, ciudad, pais, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            return $row ? new Proveedor($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar proveedor: " . $e->getMessage());
        }
    }

    /**
     * Soft delete de un proveedor
     */
    public function delete($id) {
        try {
            $query = "UPDATE proveedores 
                      SET deleted_at = CURRENT_TIMESTAMP
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar proveedor: " . $e->getMessage());
        }
    }
}
