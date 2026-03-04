<?php

namespace App\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtHandler {
    private $secret;
    private $algorithm;
    private $expire;

    public function __construct() {
        $config = require __DIR__ . '/../config/app.php';
        $this->secret = $config['jwt']['secret'];
        $this->algorithm = $config['jwt']['algorithm'];
        $this->expire = $config['jwt']['expire'];
    }

    /**
     * Genera un token JWT
     */
    public function generateToken($userId, $email, $rol) {
        $issuedAt = time();
        $expire = $issuedAt + $this->expire;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'userId' => $userId,
            'email' => $email,
            'rol' => $rol
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Valida y decodifica un token JWT
     */
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            throw new Exception("Token inválido o expirado: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el token del header Authorization
     */
    public static function getTokenFromHeader() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            throw new Exception("Token no proporcionado");
        }

        $authHeader = $headers['Authorization'];
        
        // Formato: Bearer <token>
        if (strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7);
        }
        
        throw new Exception("Formato de token inválido");
    }
}
