<?php

namespace App\Services;

use App\Repositories\ReporteVentaRepository;

/**
 * Service: ReportesService
 * Lógica de negocio para reportes
 * 
 * Responsabilidades:
 * - Generar reportes de ventas
 * - Consultas analíticas
 * - Agregaciones
 * - Filtros
 */
class ReportesService
{
    private $reporteRepo;

    public function __construct()
    {
        $this->reporteRepo = new ReporteVentaRepository();
    }

    /**
     * Obtener reporte de ventas por fecha
     */
    public function reporteVentasPorFecha($fechaInicio, $fechaFin)
    {
        try {
            if (empty($fechaInicio) || empty($fechaFin)) {
                throw new \Exception('Las fechas son requeridas');
            }

            // Validar formato de fecha
            if (!$this->validarFecha($fechaInicio) || !$this->validarFecha($fechaFin)) {
                throw new \Exception('Formato de fecha inválido (usar YYYY-MM-DD)');
            }

            $ventas = $this->reporteRepo->getVentasPorFecha($fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Ventas por Fecha',
                'rango_fechas' => [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ],
                'datos' => $ventas,
                'total_registros' => count($ventas),
                'total_ventas' => array_sum(array_column($ventas, 'total_venta') ?? [])
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener reporte de productos más vendidos
     */
    public function reporteProductosMasVendidos($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        try {
            if ($limite <= 0 || $limite > 1000) {
                $limite = 10;
            }

            $producto = $this->reporteRepo->getProductosMasVendidos($limite, $fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Productos Más Vendidos',
                'rango_fechas' => $fechaInicio && $fechaFin ? [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ] : 'Todo el período',
                'limite' => $limite,
                'datos' => $productos,
                'total_registros' => count($productos)
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener reporte de ventas por categoría
     */
    public function reporteVentasPorCategoria($fechaInicio = null, $fechaFin = null)
    {
        try {
            $categorias = $this->reporteRepo->getVentasPorCategoria($fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Ventas por Categoría',
                'rango_fechas' => $fechaInicio && $fechaFin ? [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ] : 'Todo el período',
                'datos' => $categorias,
                'total_registros' => count($categorias),
                'total_ventas' => array_sum(array_column($categorias, 'total_venta') ?? [])
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener reporte de ventas por proveedor
     */
    public function reporteVentasPorProveedor($fechaInicio = null, $fechaFin = null)
    {
        try {
            $proveedores = $this->reporteRepo->getVentasPorProveedor($fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Ventas por Proveedor',
                'rango_fechas' => $fechaInicio && $fechaFin ? [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ] : 'Todo el período',
                'datos' => $proveedores,
                'total_registros' => count($proveedores)
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener reporte de ventas por usuario (vendedor)
     */
    public function reporteVentasPorUsuario($fechaInicio = null, $fechaFin = null)
    {
        try {
            $usuarios = $this->reporteRepo->getVentasPorUsuario($fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Ventas por Usuario',
                'rango_fechas' => $fechaInicio && $fechaFin ? [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ] : 'Todo el período',
                'datos' => $usuarios,
                'total_registros' => count($usuarios)
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener resumen general de ventas
     */
    public function reporteResumenGeneral($fechaInicio = null, $fechaFin = null)
    {
        try {
            $resumen = $this->reporteRepo->getResumenGeneral($fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Resumen General de Ventas',
                'rango_fechas' => $fechaInicio && $fechaFin ? [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ] : 'Todo el período',
                'resumen' => [
                    'total_registros' => $resumen['total_registros'] ?? 0,
                    'cantidad_total_vendida' => $resumen['cantidad_total_vendida'] ?? 0,
                    'total_ventas' => $resumen['total_ventas'] ?? 0,
                    'venta_promedio' => $resumen['venta_promedio'] ?? 0,
                    'venta_maxima' => $resumen['venta_maxima'] ?? 0,
                    'venta_minima' => $resumen['venta_minima'] ?? 0,
                    'dias_con_ventas' => $resumen['dias_con_ventas'] ?? 0
                ]
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener ranking de productos por ingresos
     */
    public function reporteProductosPorIngresos($limite = 10, $fechaInicio = null, $fechaFin = null)
    {
        try {
            if ($limite <= 0 || $limite > 1000) {
                $limite = 10;
            }

            $productos = $this->reporteRepo->getRankingProductosPorIngresos($limite, $fechaInicio, $fechaFin);

            return [
                'tipo_reporte' => 'Ranking de Productos por Ingresos',
                'rango_fechas' => $fechaInicio && $fechaFin ? [
                    'desde' => $fechaInicio,
                    'hasta' => $fechaFin
                ] : 'Todo el período',
                'limite' => $limite,
                'datos' => $productos,
                'total_registros' => count($productos),
                'ingreso_total' => array_sum(array_column($productos, 'ingreso_total') ?? [])
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Obtener todos los reportes disponibles
     */
    public function obtenerReportesDisponibles()
    {
        return [
            'reportes' => [
                [
                    'nombre' => 'Ventas por Fecha',
                    'endpoint' => '/reportes/ventas-por-fecha',
                    'parametros' => ['fecha_inicio', 'fecha_fin'],
                    'descripcion' => 'Resumen de ventas agrupadas por fecha'
                ],
                [
                    'nombre' => 'Productos Más Vendidos',
                    'endpoint' => '/reportes/productos-mas-vendidos',
                    'parametros' => ['limite', 'fecha_inicio?', 'fecha_fin?'],
                    'descripcion' => 'Top N productos ordenados por cantidad vendida'
                ],
                [
                    'nombre' => 'Ventas por Categoría',
                    'endpoint' => '/reportes/ventas-por-categoria',
                    'parametros' => ['fecha_inicio?', 'fecha_fin?'],
                    'descripcion' => 'Análisis de ventas por categoría de productos'
                ],
                [
                    'nombre' => 'Ventas por Proveedor',
                    'endpoint' => '/reportes/ventas-por-proveedor',
                    'parametros' => ['fecha_inicio?', 'fecha_fin?'],
                    'descripcion' => 'Ventas agrupadas por proveedor'
                ],
                [
                    'nombre' => 'Ventas por Usuario',
                    'endpoint' => '/reportes/ventas-por-usuario',
                    'parametros' => ['fecha_inicio?', 'fecha_fin?'],
                    'descripcion' => 'Desempeño de vendedores'
                ],
                [
                    'nombre' => 'Resumen General',
                    'endpoint' => '/reportes/resumen-general',
                    'parametros' => ['fecha_inicio?', 'fecha_fin?'],
                    'descripcion' => 'KPIs y métricas generales'
                ],
                [
                    'nombre' => 'Ranking por Ingresos',
                    'endpoint' => '/reportes/ranking-productos-ingresos',
                    'parametros' => ['limite', 'fecha_inicio?', 'fecha_fin?'],
                    'descripcion' => 'Productos ordenados por ingresos totales'
                ]
            ]
        ];
    }

    /**
     * Validar formato de fecha
     */
    private function validarFecha($fecha)
    {
        $formato = 'Y-m-d';
        $fechaObj = \DateTime::createFromFormat($formato, $fecha);
        return $fechaObj && $fechaObj->format($formato) === $fecha;
    }
}
