<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\VentaItem;
use PDO;

/**
 * Repository: VentaItemRepository
 * Maneja todas las operaciones de BD para items de venta
 */
class VentaItemRepository
{
    private $db;
    private $table = 'venta_items';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los items de una venta
     */
    public function getByVentaId($ventaId)
    {
        $sql = "SELECT vi.*, 
                p.nombre as producto_nombre,
                p.sku as producto_sku,
                c.nombre as categoria_nombre
                FROM {$this->table} vi
                JOIN productos p ON vi.producto_id = p.id
                JOIN categorias c ON p.categoria_id = c.id
                WHERE vi.venta_id = :venta_id
                ORDER BY vi.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':venta_id', $ventaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener item por ID
     */
    public function getById($id)
    {
        $sql = "SELECT vi.*, 
                p.nombre as producto_nombre,
                p.sku as producto_sku
                FROM {$this->table} vi
                JOIN productos p ON vi.producto_id = p.id
                WHERE vi.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear item de venta
     */
    public function create(VentaItem $item)
    {
        $sql = "INSERT INTO {$this->table}
                (venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal)
                VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':venta_id', $item->venta_id, PDO::PARAM_INT);
        $stmt->bindValue(':producto_id', $item->producto_id, PDO::PARAM_INT);
        $stmt->bindValue(':cantidad', $item->cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':precio_unitario', $item->precio_unitario);
        $stmt->bindValue(':descuento', $item->descuento);
        $stmt->bindValue(':subtotal', $item->subtotal);

        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualizar item de venta
     */
    public function update($id, VentaItem $item)
    {
        $sql = "UPDATE {$this->table}
                SET cantidad = :cantidad,
                    precio_unitario = :precio_unitario,
                    descuento = :descuento,
                    subtotal = :subtotal
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':cantidad', $item->cantidad, PDO::PARAM_INT);
        $stmt->bindValue(':precio_unitario', $item->precio_unitario);
        $stmt->bindValue(':descuento', $item->descuento);
        $stmt->bindValue(':subtotal', $item->subtotal);

        return $stmt->execute();
    }

    /**
     * Eliminar item de venta
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar todos los items de una venta
     */
    public function deleteByVentaId($ventaId)
    {
        $sql = "DELETE FROM {$this->table} WHERE venta_id = :venta_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':venta_id', $ventaId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Obtener cantidad de items en venta
     */
    public function countByVentaId($ventaId)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE venta_id = :venta_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':venta_id', $ventaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Obtener cantidad total vendida de un producto
     */
    public function getTotalVendidoProducto($productoId)
    {
        $sql = "SELECT SUM(cantidad) as total FROM {$this->table}
                WHERE producto_id = :producto_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
