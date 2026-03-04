<?php

namespace App;

use Exception;

class Router {
    private $routes = [];
    private $params = [];

    /**
     * Registra una ruta
     */
    public function route($method, $path, $callback) {
        $this->routes[$method][$path] = $callback;
    }

    /**
     * Maneja una solicitud HTTP
     */
    public function dispatch($method, $path) {
        // Eliminar query string
        $path = strtok($path, '?');
        
        // Buscar coincidencia de ruta
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $callback) {
                if ($this->matchRoute($routePath, $path)) {
                    call_user_func_array($callback, $this->params);
                    return;
                }
            }
        }

        // Buscar también en GET si es HEAD
        if ($method === 'HEAD' && isset($this->routes['GET'])) {
            foreach ($this->routes['GET'] as $routePath => $callback) {
                if ($this->matchRoute($routePath, $path)) {
                    call_user_func_array($callback, $this->params);
                    return;
                }
            }
        }

        throw new Exception("Ruta no encontrada: $method $path", 404);
    }

    /**
     * Comprueba si una ruta coincide con el patrón
     */
    private function matchRoute($pattern, $path) {
        $pattern = preg_replace_callback('/:\w+/', function($matches) {
            return '([^/]+)';
        }, $pattern);

        $pattern = '^' . $pattern . '$';

        if (preg_match("/$pattern/", $path, $matches)) {
            array_shift($matches); // Eliminar el primer elemento (la ruta completa)
            $this->params = $matches;
            return true;
        }

        return false;
    }

    /**
     * Convierte el método HTTP para GET
     */
    public static function get($path, $callback) {
        return ['GET', $path, $callback];
    }

    /**
     * Convierte el método HTTP para POST
     */
    public static function post($path, $callback) {
        return ['POST', $path, $callback];
    }

    /**
     * Convierte el método HTTP para PUT
     */
    public static function put($path, $callback) {
        return ['PUT', $path, $callback];
    }

    /**
     * Convierte el método HTTP para DELETE
     */
    public static function delete($path, $callback) {
        return ['DELETE', $path, $callback];
    }
}
