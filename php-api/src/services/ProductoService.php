<?php

namespace App\Services;

use App\Repositories\ProductoRepository;
use App\Repositories\InventarioRepository;
use App\Repositories\CategoriaRepository;
use App\Utils\Validator;
use Exception;

class ProductoService {
    private $productoRepository;
    private $inventarioRepository;
    private $categoriaRepository;

    public function __construct() {
        $this->productoRepository = new ProductoRepository();
        $this->inventarioRepository = new InventarioRepository();
        $this->categoriaRepository = new CategoriaRepository();
    }

    /**
     * Obtiene todos los productos
     */
    public function getAllProductos($page = 1, $perPage = 20) {
        Validator::validatePositive($page, 'page');
        Validator::validatePositive($perPage, 'per_page');
        
        $productos = $this->productoRepository->getAll($page, $perPage);
        $total = $this->productoRepository->count();

        // Agregar información de inventario a cada producto
        foreach ($productos as $producto) {
            $inventario = $this->inventarioRepository->getByProductoId($producto->id);
            $producto->inventario = $inventario ? $inventario->toArray() : null;
        }

        return [
            'productos' => $productos,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Obtiene un producto por ID
     */
    public function getProductoById($id) {
        Validator::validatePositive($id, 'id');
        
        $producto = $this->productoRepository->getById($id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        // Agregar información de inventario
        $inventario = $this->inventarioRepository->getByProductoId($producto->id);
        $producto->inventario = $inventario ? $inventario->toArray() : null;

        return $producto;
    }

    /**
     * Obtiene un producto por UUID
     */
    public function getProductoByUuid($uuid) {
        $producto = $this->productoRepository->getByUuid($uuid);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        // Agregar información de inventario
        $inventario = $this->inventarioRepository->getByProductoId($producto->id);
        $producto->inventario = $inventario ? $inventario->toArray() : null;

        return $producto;
    }

    /**
     * Crea un nuevo producto
     */
    public function crearProducto($nombre, $descripcion, $categoria_id, $precio_unitario, $sku, $codigo_barras = null, $cantidad_inicial = 0) {
        // Validar entrada
        Validator::validateRequired($nombre, 'nombre');
        Validator::validateMaxLength($nombre, 150, 'nombre');
        Validator::validateRequired($sku, 'sku');
        Validator::validateMaxLength($sku, 50, 'sku');
        Validator::validatePositive($precio_unitario, 'precio_unitario');
        Validator::validatePositive($categoria_id, 'categoria_id');

        // Verificar que la categoría existe
        $categoria = $this->categoriaRepository->getById($categoria_id);
        if (!$categoria) {
            throw new Exception("Categoría no encontrada");
        }

        // Verificar que el SKU sea único
        $existente = $this->productoRepository->getBySku($sku);
        if ($existente) {
            throw new Exception("El SKU ya existe");
        }

        // Crear producto
        $producto = $this->productoRepository->create($nombre, $descripcion, $categoria_id, $precio_unitario, $sku, $codigo_barras);
        
        // Crear inventario
        $inventario = $this->inventarioRepository->create($producto->id, $cantidad_inicial);
        $producto->inventario = $inventario->toArray();

        return $producto;
    }

    /**
     * Actualiza un producto
     */
    public function actualizarProducto($id, $nombre = null, $descripcion = null, $categoria_id = null, $precio_unitario = null, $sku = null, $codigo_barras = null, $activo = null) {
        // Validar
        if ($nombre !== null) {
            Validator::validateMaxLength($nombre, 150, 'nombre');
        }
        if ($precio_unitario !== null) {
            Validator::validatePositive($precio_unitario, 'precio_unitario');
        }
        if ($categoria_id !== null) {
            Validator::validatePositive($categoria_id, 'categoria_id');
            $categoria = $this->categoriaRepository->getById($categoria_id);
            if (!$categoria) {
                throw new Exception("Categoría no encontrada");
            }
        }
        if ($sku !== null) {
            // Verificar unicidad del SKU (excepto el actual)
            $current = $this->productoRepository->getById($id);
            if ($current && $current->sku !== $sku) {
                $existente = $this->productoRepository->getBySku($sku);
                if ($existente) {
                    throw new Exception("El SKU ya existe");
                }
            }
        }

        $producto = $this->productoRepository->update($id, $nombre, $descripcion, $categoria_id, $precio_unitario, $sku, $codigo_barras, $activo);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        // Agregar información de inventario
        $inventario = $this->inventarioRepository->getByProductoId($producto->id);
        $producto->inventario = $inventario ? $inventario->toArray() : null;

        return $producto;
    }

    /**
     * Elimina un producto
     */
    public function eliminarProducto($id) {
        Validator::validatePositive($id, 'id');
        
        $producto = $this->productoRepository->getById($id);
        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        $deleted = $this->productoRepository->delete($id);
        if (!$deleted) {
            throw new Exception("No se pudo eliminar el producto");
        }

        return true;
    }

    /**
     * Busca productos
     */
    public function buscarProductos($keyword, $page = 1, $perPage = 20) {
        Validator::validateRequired($keyword, 'keyword');
        
        $productos = $this->productoRepository->search($keyword, $page, $perPage);
        
        // Agregar información de inventario
        foreach ($productos as $producto) {
            $inventario = $this->inventarioRepository->getByProductoId($producto->id);
            $producto->inventario = $inventario ? $inventario->toArray() : null;
        }

        return $productos;
    }

    /**
     * Obtiene productos por categoría
     */
    public function getProductosByCategoria($categoria_id, $page = 1, $perPage = 20) {
        Validator::validatePositive($categoria_id, 'categoria_id');
        
        $categoria = $this->categoriaRepository->getById($categoria_id);
        if (!$categoria) {
            throw new Exception("Categoría no encontrada");
        }

        $productos = $this->productoRepository->getByCategoria($categoria_id, $page, $perPage);
        
        // Agregar información de inventario
        foreach ($productos as $producto) {
            $inventario = $this->inventarioRepository->getByProductoId($producto->id);
            $producto->inventario = $inventario ? $inventario->toArray() : null;
        }

        return $productos;
    }
}
