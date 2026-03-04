<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Producto;
use PDOException;

class ProductoRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los productos activos
     */
    public function getAll($page = 1, $perPage = 20, $activeOnly = true) {
        try {
            $offset = ($page - 1) * $perPage;
            $whereClause = $activeOnly ? "WHERE p.deleted_at IS NULL AND p.activo = true" : "WHERE p.deleted_at IS NULL";
            
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      $whereClause
                      ORDER BY p.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto($row);
            }
            
            return $productos;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener productos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el total de productos
     */
    public function count($activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "WHERE deleted_at IS NULL AND activo = true" : "WHERE deleted_at IS NULL";
            
            $query = "SELECT COUNT(*) as total FROM productos $whereClause";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            throw new PDOException("Error al contar productos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un producto por ID
     */
    public function getById($id) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE p.id = :id AND p.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Producto($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener producto: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un producto por UUID
     */
    public function getByUuid($uuid) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE p.uuid = :uuid AND p.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Producto($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener producto: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un producto por SKU
     */
    public function getBySku($sku) {
        try {
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE p.sku = :sku AND p.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':sku', $sku);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Producto($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener producto: " . $e->getMessage());
        }
    }

    /**
     * Crea un nuevo producto
     */
    public function create($nombre, $descripcion, $categoria_id, $precio_unitario, $sku, $codigo_barras = null) {
        try {
            $query = "INSERT INTO productos (nombre, descripcion, categoria_id, precio_unitario, sku, codigo_barras, activo)
                      VALUES (:nombre, :descripcion, :categoria_id, :precio_unitario, :sku, :codigo_barras, true)
                      RETURNING id, uuid, nombre, descripcion, categoria_id, precio_unitario, sku, codigo_barras, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':categoria_id', $categoria_id, \PDO::PARAM_INT);
            $stmt->bindParam(':precio_unitario', $precio_unitario);
            $stmt->bindParam(':sku', $sku);
            $stmt->bindParam(':codigo_barras', $codigo_barras);
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return new Producto($row);
        } catch (PDOException $e) {
            throw new PDOException("Error al crear producto: " . $e->getMessage());
        }
    }

    /**
     * Actualiza un producto
     */
    public function update($id, $nombre = null, $descripcion = null, $categoria_id = null, $precio_unitario = null, $sku = null, $codigo_barras = null, $activo = null) {
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
            if ($categoria_id !== null) {
                $updates[] = "categoria_id = :categoria_id";
                $params[':categoria_id'] = $categoria_id;
            }
            if ($precio_unitario !== null) {
                $updates[] = "precio_unitario = :precio_unitario";
                $params[':precio_unitario'] = $precio_unitario;
            }
            if ($sku !== null) {
                $updates[] = "sku = :sku";
                $params[':sku'] = $sku;
            }
            if ($codigo_barras !== null) {
                $updates[] = "codigo_barras = :codigo_barras";
                $params[':codigo_barras'] = $codigo_barras;
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
            
            $query = "UPDATE productos 
                      SET $updateClause
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id, uuid, nombre, descripcion, categoria_id, precio_unitario, sku, codigo_barras, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            return $row ? new Producto($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar producto: " . $e->getMessage());
        }
    }

    /**
     * Soft delete de un producto
     */
    public function delete($id) {
        try {
            $query = "UPDATE productos 
                      SET deleted_at = CURRENT_TIMESTAMP
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar producto: " . $e->getMessage());
        }
    }

    /**
     * Busca productos por nombre
     */
    public function search($keyword, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            $searchTerm = "%$keyword%";
            
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE (p.nombre ILIKE :keyword OR p.sku ILIKE :keyword OR p.descripcion ILIKE :keyword)
                      AND p.deleted_at IS NULL
                      AND p.activo = true
                      ORDER BY p.nombre ASC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':keyword', $searchTerm);
            $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto($row);
            }
            
            return $productos;
        } catch (PDOException $e) {
            throw new PDOException("Error al buscar productos: " . $e->getMessage());
        }
    }

    /**
     * Obtiene productos por categoría
     */
    public function getByCategoria($categoria_id, $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            
            $query = "SELECT p.*, c.nombre as categoria_nombre 
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      WHERE p.categoria_id = :categoria_id
                      AND p.deleted_at IS NULL
                      AND p.activo = true
                      ORDER BY p.nombre ASC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':categoria_id', $categoria_id, \PDO::PARAM_INT);
            $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $productos = [];
            while ($row = $stmt->fetch()) {
                $productos[] = new Producto($row);
            }
            
            return $productos;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener productos por categoría: " . $e->getMessage());
        }
    }
}
