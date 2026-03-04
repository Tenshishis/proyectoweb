<?php

namespace App\Repositories;

use App\Config\Database;
use App\Models\ReporteVenta;
use PDO;

/**
 * Repository: ReporteVentaRepository
 * Maneja consultas a la tabla desnormalizada reporte_ventas
 * Optimizada para reportes y análisis
 */
class ReporteVentaRepository
{
    private $db;
    private $table = 'reporte_ventas';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registrar venta en tabla de reportes (desnormalizada)
     * Se llama cada vez que se completa una venta
     */
    public function registrarVenta(ReporteVenta $reporte)
    {
        $sql = "INSERT INTO {$this->table}
                (fecha_venta, producto_id, categoria_nombre, proveedor_id, proveedor_nombre, cantidad_vendida, precio_unitario, total_venta, usuario_id)
                VALUES (:fecha_venta, :producto_id, :categoria_nombre, :proveedor_id, :proveedor_nombre, :cantidad_vendida, :precio_unitario, :total_venta, :usuario_id)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_venta', $reporte->fecha_venta);
        $stmt->bindValue(':producto_id', $reporte->producto_id, PDO::PARAM_INT);
        $stmt->bindValue(':categoria_nombre', $reporte->categoria_nombre);
        $stmt->bindValue(':proveedor_id', $reporte->proveedor_id);
        $stmt->bindValue(':proveedor_nombre', $reporte->proveedor_nombre);
        $stmt->bindValue(':cantidad_vendida', $reporte->cantidad_vendida, PDO::PARAM_INT);
        $stmt->bindValue(':precio_unitario', $reporte->precio_unitario);
        $stmt->bindValue(':total_venta', $reporte->total_venta);
        $stmt->bindValue(':usuario_id', $reporte->usuario_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Obtener reporte de ventas por fecha
     */
    public function getVentasPorFecha($fechaInicio, $fechaFin)
    {
        $sql = "SELECT 
                fecha_venta,
                COUNT(DISTINCT id) as numero_ventas,
                SUM(cantidad_vendida) as cantidad_total,
                SUM(total_venta) as total_venta
                FROM {$this->table}
                WHERE fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY fecha_venta
                ORDER BY fecha_venta DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fecha_inicio', $fechaInicio);
        $stmt->bindValue(':fecha_fin', $fechaFin);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener productos más vendidos
     */
    public function getProductosMasVendidos($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                producto_id,
                categoria_nombre,
                SUM(cantidad_vendida) as cantidad_total,
                SUM(total_venta) as total_venta,
                COUNT(*) as numero_ventas,
                AVG(precio_unitario) as precio_promedio
                FROM {$this->table}";

        if ($fechaInicio && $fechaFin) {
            $sql .= " WHERE fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $sql .= " GROUP BY producto_id, categoria_nombre
                  ORDER BY cantidad_total DESC
                  LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':fecha_inicio', $fechaInicio);
            $stmt->bindValue(':fecha_fin', $fechaFin);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ventas por categoría
     */
    public function getVentasPorCategoria($fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                categoria_nombre,
                SUM(cantidad_vendida) as cantidad_total,
                SUM(total_venta) as total_venta,
                COUNT(*) as numero_ventas
                FROM {$this->table}";

        if ($fechaInicio && $fechaFin) {
            $sql .= " WHERE fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $sql .= " GROUP BY categoria_nombre
                  ORDER BY total_venta DESC";

        $stmt = $this->db->prepare($sql);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':fecha_inicio', $fechaInicio);
            $stmt->bindValue(':fecha_fin', $fechaFin);
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ventas por proveedor
     */
    public function getVentasPorProveedor($fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                proveedor_id,
                proveedor_nombre,
                SUM(cantidad_vendida) as cantidad_total,
                SUM(total_venta) as total_venta,
                COUNT(*) as numero_ventas
                FROM {$this->table}
                WHERE proveedor_id IS NOT NULL";

        if ($fechaInicio && $fechaFin) {
            $sql .= " AND fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $sql .= " GROUP BY proveedor_id, proveedor_nombre
                  ORDER BY total_venta DESC";

        $stmt = $this->db->prepare($sql);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':fecha_inicio', $fechaInicio);
            $stmt->bindValue(':fecha_fin', $fechaFin);
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ventas por usuario (vendedor)
     */
    public function getVentasPorUsuario($fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                usuario_id,
                SUM(cantidad_vendida) as cantidad_total,
                SUM(total_venta) as total_venta,
                COUNT(*) as numero_ventas
                FROM {$this->table}";

        if ($fechaInicio && $fechaFin) {
            $sql .= " WHERE fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $sql .= " GROUP BY usuario_id
                  ORDER BY total_venta DESC";

        $stmt = $this->db->prepare($sql);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':fecha_inicio', $fechaInicio);
            $stmt->bindValue(':fecha_fin', $fechaFin);
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener resumen general de ventas
     */
    public function getResumenGeneral($fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                COUNT(*) as total_registros,
                SUM(cantidad_vendida) as cantidad_total_vendida,
                SUM(total_venta) as total_ventas,
                AVG(total_venta) as venta_promedio,
                MAX(total_venta) as venta_maxima,
                MIN(total_venta) as venta_minima,
                COUNT(DISTINCT fecha_venta) as dias_con_ventas
                FROM {$this->table}";

        if ($fechaInicio && $fechaFin) {
            $sql .= " WHERE fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $stmt = $this->db->prepare($sql);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':fecha_inicio', $fechaInicio);
            $stmt->bindValue(':fecha_fin', $fechaFin);
        }
        
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener ranking de productos por ingresos
     */
    public function getRankingProductosPorIngresos($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        $sql = "SELECT 
                producto_id,
                categoria_nombre,
                SUM(total_venta) as ingreso_total,
                SUM(cantidad_vendida) as cantidad_vendida,
                COUNT(*) as numero_ventas,
                (SUM(total_venta) / SUM(cantidad_vendida)) as precio_promedio_real
                FROM {$this->table}";

        if ($fechaInicio && $fechaFin) {
            $sql .= " WHERE fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $sql .= " GROUP BY producto_id, categoria_nombre
                  ORDER BY ingreso_total DESC
                  LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        
        if ($fechaInicio && $fechaFin) {
            $stmt->bindValue(':fecha_inicio', $fechaInicio);
            $stmt->bindValue(':fecha_fin', $fechaFin);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las ventas con paginación
     */
    public function getAll($page = 1, $perPage = 20, $filtros = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";
        }

        if (!empty($filtros['categoria'])) {
            $sql .= " AND categoria_nombre = :categoria";
        }

        if (!empty($filtros['proveedor_id'])) {
            $sql .= " AND proveedor_id = :proveedor_id";
        }

        $sql .= " ORDER BY fecha_venta DESC LIMIT :perPage OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $stmt->bindValue(':fecha_inicio', $filtros['fecha_inicio']);
            $stmt->bindValue(':fecha_fin', $filtros['fecha_fin']);
        }

        if (!empty($filtros['categoria'])) {
            $stmt->bindValue(':categoria', $filtros['categoria']);
        }

        if (!empty($filtros['proveedor_id'])) {
            $stmt->bindValue(':proveedor_id', $filtros['proveedor_id'], PDO::PARAM_INT);
        }

        $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
