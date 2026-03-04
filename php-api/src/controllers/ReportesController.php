<?php

namespace App\Controllers;

use App\Services\ReportesService;
use App\Utils\Response;
use App\Middleware\AuthMiddleware;

/**
 * Controller: ReportesController
 * Maneja todos los requests HTTP relacionados con reportes
 */
class ReportesController
{
    private $reportesService;
    private $auth;

    public function __construct()
    {
        $this->reportesService = new ReportesService();
        $this->auth = new AuthMiddleware();
    }

    /**
     * GET /reportes
     * Obtener lista de reportes disponibles
     */
    public function listarReportes()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor', 'vendedor']);

            $reportes = $this->reportesService->obtenerReportesDisponibles();

            Response::success($reportes, 'Reportes disponibles');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/ventas-por-fecha
     * Reporte: Ventas por fecha
     * 
     * Parámetros:
     * ?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function ventasPorFecha()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            if (!$fechaInicio || !$fechaFin) {
                throw new \Exception('fecha_inicio y fecha_fin son requeridos');
            }

            $reporte = $this->reportesService->reporteVentasPorFecha($fechaInicio, $fechaFin);

            Response::success($reporte, 'Reporte de ventas por fecha');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/productos-mas-vendidos
     * Reporte: Productos más vendidos
     * 
     * Parámetros:
     * ?limite=10&fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function productosMasVendidos()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $limite = $_GET['limite'] ?? 10;
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = $this->reportesService->reporteProductosMasVendidos(
                $limite,
                $fechaInicio,
                $fechaFin
            );

            Response::success($reporte, 'Reporte de productos más vendidos');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/ventas-por-categoria
     * Reporte: Ventas por categoría
     * 
     * Parámetros:
     * ?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function ventasPorCategoria()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = $this->reportesService->reporteVentasPorCategoria($fechaInicio, $fechaFin);

            Response::success($reporte, 'Reporte de ventas por categoría');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/ventas-por-proveedor
     * Reporte: Ventas por proveedor
     * 
     * Parámetros:
     * ?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function ventasPorProveedor()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = $this->reportesService->reporteVentasPorProveedor($fechaInicio, $fechaFin);

            Response::success($reporte, 'Reporte de ventas por proveedor');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/ventas-por-usuario
     * Reporte: Ventas por usuario (desempeño de vendedores)
     * 
     * Parámetros:
     * ?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function ventasPorUsuario()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = $this->reportesService->reporteVentasPorUsuario($fechaInicio, $fechaFin);

            Response::success($reporte, 'Reporte de ventas por usuario');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/resumen-general
     * Reporte: Resumen general de ventas (KPIs)
     * 
     * Parámetros:
     * ?fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function resumenGeneral()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = $this->reportesService->reporteResumenGeneral($fechaInicio, $fechaFin);

            Response::success($reporte, 'Resumen general de ventas');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * GET /reportes/ranking-productos-ingresos
     * Reporte: Ranking de productos por ingresos
     * 
     * Parámetros:
     * ?limite=10&fecha_inicio=2024-01-01&fecha_fin=2024-12-31
     */
    public function rankingProductosPorIngresos()
    {
        try {
            $this->auth->authenticate();
            $this->auth->authorize(['admin', 'consultor']);

            $limite = $_GET['limite'] ?? 10;
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = $this->reportesService->reporteProductosPorIngresos(
                $limite,
                $fechaInicio,
                $fechaFin
            );

            Response::success($reporte, 'Ranking de productos por ingresos');
        } catch (\Exception $e) {
            http_response_code(400);
            Response::error($e->getMessage(), 400);
        }
    }
}
