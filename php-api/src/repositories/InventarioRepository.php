<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Inventario;
use PDOException;

class InventarioRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene el inventario de un producto
     */
    public function getByProductoId($producto_id) {
        try {
            $query = "SELECT * FROM inventario WHERE producto_id = :producto_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Inventario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener inventario: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el inventario por UUID
     */
    public function getByUuid($uuid) {
        try {
            $query = "SELECT * FROM inventario WHERE uuid = :uuid";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return $row ? new Inventario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al obtener inventario: " . $e->getMessage());
        }
    }

    /**
     * Crea un nuevo registro de inventario
     */
    public function create($producto_id, $cantidad_disponible = 0, $cantidad_minima = 10, $cantidad_maxima = 1000, $ubicacion_almacen = null) {
        try {
            $query = "INSERT INTO inventario (producto_id, cantidad_disponible, cantidad_reservada, cantidad_minima, cantidad_maxima, ubicacion_almacen)
                      VALUES (:producto_id, :cantidad_disponible, 0, :cantidad_minima, :cantidad_maxima, :ubicacion_almacen)
                      RETURNING id, uuid, producto_id, cantidad_disponible, cantidad_reservada, cantidad_minima, cantidad_maxima, ubicacion_almacen, lote, fecha_vencimiento, created_at, updated_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id, \PDO::PARAM_INT);
            $stmt->bindParam(':cantidad_disponible', $cantidad_disponible, \PDO::PARAM_INT);
            $stmt->bindParam(':cantidad_minima', $cantidad_minima, \PDO::PARAM_INT);
            $stmt->bindParam(':cantidad_maxima', $cantidad_maxima, \PDO::PARAM_INT);
            $stmt->bindParam(':ubicacion_almacen', $ubicacion_almacen);
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return new Inventario($row);
        } catch (PDOException $e) {
            throw new PDOException("Error al crear inventario: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el inventario
     */
    public function update($producto_id, $cantidad_disponible = null, $cantidad_reservada = null, $cantidad_minima = null, $cantidad_maxima = null, $ubicacion_almacen = null, $lote = null, $fecha_vencimiento = null) {
        try {
            $updates = [];
            $params = [];

            if ($cantidad_disponible !== null) {
                $updates[] = "cantidad_disponible = :cantidad_disponible";
                $params[':cantidad_disponible'] = $cantidad_disponible;
            }
            if ($cantidad_reservada !== null) {
                $updates[] = "cantidad_reservada = :cantidad_reservada";
                $params[':cantidad_reservada'] = $cantidad_reservada;
            }
            if ($cantidad_minima !== null) {
                $updates[] = "cantidad_minima = :cantidad_minima";
                $params[':cantidad_minima'] = $cantidad_minima;
            }
            if ($cantidad_maxima !== null) {
                $updates[] = "cantidad_maxima = :cantidad_maxima";
                $params[':cantidad_maxima'] = $cantidad_maxima;
            }
            if ($ubicacion_almacen !== null) {
                $updates[] = "ubicacion_almacen = :ubicacion_almacen";
                $params[':ubicacion_almacen'] = $ubicacion_almacen;
            }
            if ($lote !== null) {
                $updates[] = "lote = :lote";
                $params[':lote'] = $lote;
            }
            if ($fecha_vencimiento !== null) {
                $updates[] = "fecha_vencimiento = :fecha_vencimiento";
                $params[':fecha_vencimiento'] = $fecha_vencimiento;
            }

            if (empty($updates)) {
                return $this->getByProductoId($producto_id);
            }

            $params[':producto_id'] = $producto_id;
            $updateClause = implode(", ", $updates);
            
            $query = "UPDATE inventario 
                      SET $updateClause
                      WHERE producto_id = :producto_id
                      RETURNING id, uuid, producto_id, cantidad_disponible, cantidad_reservada, cantidad_minima, cantidad_maxima, ubicacion_almacen, lote, fecha_vencimiento, created_at, updated_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            
            return $row ? new Inventario($row) : null;
        } catch (PDOException $e) {
            throw new PDOException("Error al actualizar inventario: " . $e->getMessage());
        }
    }

    /**
     * Registra un movimiento de inventario
     */
    public function registrarMovimiento($producto_id, $tipo_movimiento, $cantidad, $motivo = null, $usuario_id = null) {
        try {
            $inventario = $this->getByProductoId($producto_id);
            if (!$inventario) {
                throw new PDOException("Inventario no encontrado para el producto");
            }

            $cantidad_anterior = $inventario->cantidad_disponible;
            $cantidad_nueva = $cantidad_anterior;

            // Realizar el movimiento según el tipo
            switch ($tipo_movimiento) {
                case 'entrada':
                    $cantidad_nueva = $cantidad_anterior + $cantidad;
                    break;
                case 'salida':
                    $cantidad_nueva = $cantidad_anterior - $cantidad;
                    if ($cantidad_nueva < 0) {
                        throw new PDOException("Stock insuficiente para realizar esta operación");
                    }
                    break;
                case 'ajuste':
                    $cantidad_nueva = $cantidad;
                    break;
                case 'reserva':
                    $cantidad_reservada = $inventario->cantidad_reservada + $cantidad;
                    if (($cantidad_anterior - $cantidad_reservada) < 0) {
                        throw new PDOException("Stock insuficiente para reservar");
                    }
                    $this->update($producto_id, null, $cantidad_reservada);
                    break;
                case 'liberacion_reserva':
                    $cantidad_reservada = max(0, $inventario->cantidad_reservada - $cantidad);
                    $this->update($producto_id, null, $cantidad_reservada);
                    break;
            }

            // Guardar el movimiento
            if (in_array($tipo_movimiento, ['entrada', 'salida', 'ajuste'])) {
                $this->update($producto_id, $cantidad_nueva);
            }

            $query = "INSERT INTO movimientos_inventario (producto_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, usuario_id)
                      VALUES (:producto_id, :tipo_movimiento, :cantidad, :cantidad_anterior, :cantidad_nueva, :motivo, :usuario_id)
                      RETURNING id, uuid, producto_id, tipo_movimiento, cantidad, cantidad_anterior, cantidad_nueva, motivo, usuario_id, created_at";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':producto_id', $producto_id, \PDO::PARAM_INT);
            $stmt->bindParam(':tipo_movimiento', $tipo_movimiento);
            $stmt->bindParam(':cantidad', $cantidad, \PDO::PARAM_INT);
            $stmt->bindParam(':cantidad_anterior', $cantidad_anterior, \PDO::PARAM_INT);
            $stmt->bindParam(':cantidad_nueva', $cantidad_nueva, \PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->bindParam(':usuario_id', $usuario_id, \PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new PDOException("Error al registrar movimiento: " . $e->getMessage());
        }
    }
}
