<?php

namespace App\Middleware;

use App\Utils\JwtHandler;
use App\Utils\Response;
use Exception;

class AuthMiddleware {
    /**
     * Verifica que el usuario esté autenticado
     */
    public static function authenticate() {
        try {
            $token = JwtHandler::getTokenFromHeader();
            $jwtHandler = new JwtHandler();
            $decoded = $jwtHandler->validateToken($token);
            
            // Almacena los datos del usuario en una variable global
            $GLOBALS['user'] = $decoded;
            
            return $decoded;
        } catch (Exception $e) {
            Response::error("No autorizado: " . $e->getMessage(), 401);
        }
    }

    /**
     * Verifica que el usuario tenga un rol específico
     */
    public static function authorize($allowedRoles) {
        $user = self::authenticate();
        
        if (!in_array($user['rol'], $allowedRoles)) {
            Response::error("No tiene permisos para realizar esta acción", 403);
        }
        
        return $user;
    }

    /**
     * Verifica que solo administradores accedan
     */
    public static function adminOnly() {
        return self::authorize(['admin']);
    }

    /**
     * Verifica que administradores y vendedores accedan
     */
    public static function adminOrVendedor() {
        return self::authorize(['admin', 'vendedor']);
    }

    /**
     * Obtiene el usuario autenticado actual
     */
    public static function getCurrentUser() {
        return $GLOBALS['user'] ?? null;
    }
}
