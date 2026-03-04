<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Usuario;
use PDOException;

class UsuarioRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los usuarios activos
     */
    public function getAll($page = 1, $perPage = 20, $activeOnly = true) {
        try {
            $offset = ($page - 1) * $perPage;
            $whereClause = $activeOnly ? "WHERE deleted_at IS NULL AND activo = true" : "WHERE deleted_at IS NULL";
            
            $query = "SELECT id, uuid, nombre, email, rol, activo, created_at, updated_at, deleted_at FROM usuarios $whereClause ORDER BY nombre ASC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $usuarios = [];
            while ($row = $stmt->fetch()) {
                $usuarios[] = new Usuario($row);
            }
            
            return $usuarios;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el total de usuarios
     */
    public function count($activeOnly = true) {
        try {
            $whereClause = $activeOnly ? "WHERE deleted_at IS NULL AND activo = true" : "WHERE deleted_at IS NULL";
            
            $query = "SELECT COUNT(*) as total FROM usuarios $whereClause";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return (int) $result['total'];
        } catch (PDOException $e) {
            throw new PDOException("Error al contar usuarios: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un usuario por ID (incluye password)
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM usuarios WHERE id = :id AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Usuario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener usuario: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un usuario por email (incluye password)
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM usuarios WHERE email = :email AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Usuario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener usuario: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un usuario por UUID
     */
    public function getByUuid($uuid) {
        try {
            $query = "SELECT id, uuid, nombre, email, rol, activo, created_at, updated_at, deleted_at FROM usuarios WHERE uuid = :uuid AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Usuario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener usuario: " . $e->getMessage());
        }
    }

    /**
     * Crea un nuevo usuario
     */
    public function create($nombre, $email, $password, $rol = 'consultor') {
        try {
            $passwordHash = Usuario::hashPassword($password);
            
            $query = "INSERT INTO usuarios (nombre, email, password, rol, activo)
                      VALUES (:nombre, :email, :password, :rol, true)
                      RETURNING id, uuid, nombre, email, rol, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':rol', $rol);
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return new Usuario($row);
        } catch (PDOException $e) {
            throw new PDOException("Error al crear usuario: " . $e->getMessage());
        }
    }

    /**
     * Actualiza un usuario
     */
    public function update($id, $nombre = null, $email = null, $rol = null, $activo = null) {
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
            if ($rol !== null) {
                $updates[] = "rol = :rol";
                $params[':rol'] = $rol;
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
            
            $query = "UPDATE usuarios 
                      SET $updateClause
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id, uuid, nombre, email, rol, activo, created_at, updated_at, deleted_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            return $row ? new Usuario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    /**
     * Soft delete de un usuario
     */
    public function delete($id) {
        try {
            $query = "UPDATE usuarios 
                      SET deleted_at = CURRENT_TIMESTAMP
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    /**
     * Cambia la contraseña de un usuario
     */
    public function changePassword($id, $newPassword) {
        try {
            $passwordHash = Usuario::hashPassword($newPassword);
            
            $query = "UPDATE usuarios 
                      SET password = :password
                      WHERE id = :id AND deleted_at IS NULL
                      RETURNING id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Error al cambiar contraseña: " . $e->getMessage());
        }
    }
}
