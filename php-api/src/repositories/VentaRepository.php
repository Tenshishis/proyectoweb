<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\Venta;
use PDO;

/**
 * Repository: VentaRepository
 * Maneja todas las operaciones de BD para ventas
 */
class VentaRepository
{
    private $db;
    private $table = 'ventas';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las ventas con paginación
     */
    public function getAll($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT v.*, 
                u.nombre as usuario_nombre,
                COUNT(vi.id) as cantidad_items
                FROM {$this->table} v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN venta_items vi ON v.id = vi.venta_id
                WHERE v.deleted_at IS NULL
                GROUP BY v.id
                ORDER BY v.created_at DESC
                LIMIT :perPage OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener venta por ID
     */
    public function getById($id)
    {
        $sql = "SELECT v.*, u.nombre as usuario_nombre
                FROM {$this->table} v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.id = :id AND v.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener venta por UUID
     */
    public function getByUuid($uuid)
    {
        $sql = "SELECT v.*, u.nombre as usuario_nombre
                FROM {$this->table} v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.uuid = :uuid AND v.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uuid', $uuid);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener venta por número de venta
     */
    public function getByNumeroVenta($numeroVenta)
    {
        $sql = "SELECT v.*, u.nombre as usuario_nombre
                FROM {$this->table} v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.numero_venta = :numero_venta AND v.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':numero_venta', $numeroVenta);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ventas por usuario
     */
    public function getByUsuarios($usuarioId, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT v.*, u.nombre as usuario_nombre
                FROM {$this->table} v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.usuario_id = :usuario_id AND v.deleted_at IS NULL
                ORDER BY v.created_at DESC
                LIMIT :perPage OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public function getByFechas($fechaInicio, $fechaFin, $page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT v.*, u.nombre as usuario_nombre,
                COUNT(vi.id) as cantidad_items
                FROM {$this->table} v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN venta_items vi ON v.id = vi.venta_id
                WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                AND v.deleted_at IS NULL
                GROUP BY v.id
                ORDER BY v.fecha_venta DESC
                LIMIT :perPage OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_inicio', $fechaInicio);
        $stmt->bindValue(':fecha_fin', $fechaFin);
        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear venta
     */
    public function create(Venta $venta)
    {
        $sql = "INSERT INTO {$this->table} 
                (numero_venta, usuario_id, total, estado, observaciones)
                VALUES (:numero_venta, :usuario_id, :total, :estado, :observaciones)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':numero_venta', $venta->numero_venta);
        $stmt->bindValue(':usuario_id', $venta->usuario_id, PDO::PARAM_INT);
        $stmt->bindValue(':total', $venta->total);
        $stmt->bindValue(':estado', $venta->estado);
        $stmt->bindValue(':observaciones', $venta->observaciones);

        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualizar venta
     */
    public function update($id, Venta $venta)
    {
        $sql = "UPDATE {$this->table} 
                SET total = :total,
                    estado = :estado,
                    observaciones = :observaciones,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':total', $venta->total);
        $stmt->bindValue(':estado', $venta->estado);
        $stmt->bindValue(':observaciones', $venta->observaciones);

        return $stmt->execute();
    }

    /**
     * Recalcular total de venta (suma de items)
     */
    public function recalcularTotal($ventaId)
    {
        $sql = "UPDATE {$this->table} v
                SET total = (
                    SELECT COALESCE(SUM(subtotal), 0)
                    FROM venta_items
                    WHERE venta_id = :venta_id
                ),
                updated_at = CURRENT_TIMESTAMP
                WHERE v.id = :venta_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':venta_id', $ventaId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Cambiar estado de venta
     */
    public function cambiarEstado($id, $nuevoEstado)
    {
        $estadosValidos = ['pendiente', 'completada', 'cancelada'];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET estado = :estado,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':estado', $nuevoEstado);

        return $stmt->execute();
    }

    /**
     * Soft delete venta
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Contar total de ventas
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Generar número de venta único
     */
    public function generarNumeroVenta()
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(numero_venta, POSITION('-' IN numero_venta) + 1) AS INTEGER)) as ultimo
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $numeroSecuencial = ($result['ultimo'] ?? 0) + 1;
        return 'VTA-' . str_pad($numeroSecuencial, 6, '0', STR_PAD_LEFT);
    }
}
