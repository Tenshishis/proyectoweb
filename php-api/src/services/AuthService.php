<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Models\Usuario;
use App\Utils\JwtHandler;
use App\Utils\Validator;
use Exception;

class AuthService {
    private $usuarioRepository;
    private $jwtHandler;

    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
        $this->jwtHandler = new JwtHandler();
    }

    /**
     * Registra un nuevo usuario
     */
    public function register($nombre, $email, $password, $confirmPassword, $rol = 'consultor') {
        // Validar
        Validator::validateRequired($nombre, 'nombre');
        Validator::validateRequired($email, 'email');
        Validator::validateRequired($password, 'password');
        Validator::validateRequired($confirmPassword, 'confirmPassword');
        
        if (!Validator::validateEmail($email)) {
            throw new Exception("Email inválido");
        }
        
        if ($password !== $confirmPassword) {
            throw new Exception("Las contraseñas no coinciden");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres");
        }

        Validator::validateRole($rol);

        // Verificar que el email no exista
        $existe = $this->usuarioRepository->getByEmail($email);
        if ($existe) {
            throw new Exception("El email ya está registrado");
        }

        // Crear usuario
        $usuario = $this->usuarioRepository->create($nombre, $email, $password, $rol);
        
        return $usuario;
    }

    /**
     * Inicia sesión
     */
    public function login($email, $password) {
        Validator::validateRequired($email, 'email');
        Validator::validateRequired($password, 'password');

        // Obtener usuario
        $usuario = $this->usuarioRepository->getByEmail($email);
        if (!$usuario) {
            throw new Exception("Credenciales inválidas");
        }

        // Verificar activo
        if (!$usuario->activo) {
            throw new Exception("El usuario está inactivo");
        }

        // Verificar contraseña
        if (!$usuario->verifyPassword($password)) {
            throw new Exception("Credenciales inválidas");
        }

        // Generar token JWT
        $token = $this->jwtHandler->generateToken($usuario->id, $usuario->email, $usuario->rol);

        return [
            'token' => $token,
            'user' => $usuario->toArray()
        ];
    }

    /**
     * Valida un token JWT
     */
    public function validateToken($token) {
        return $this->jwtHandler->validateToken($token);
    }

    /**
     * Obtiene el usuario autenticado
     */
    public function getCurrentUser($decoded) {
        $usuario = $this->usuarioRepository->getById($decoded['userId']);
        if (!$usuario || !$usuario->activo) {
            throw new Exception("Usuario no válido");
        }

        return $usuario;
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $oldPassword, $newPassword, $confirmPassword) {
        Validator::validateRequired($oldPassword, 'oldPassword');
        Validator::validateRequired($newPassword, 'newPassword');
        Validator::validateRequired($confirmPassword, 'confirmPassword');

        if ($newPassword !== $confirmPassword) {
            throw new Exception("Las contraseñas no coinciden");
        }

        if (strlen($newPassword) < 8) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres");
        }

        // Obtener usuario
        $usuario = $this->usuarioRepository->getById($userId);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        // Verificar contraseña actual
        if (!$usuario->verifyPassword($oldPassword)) {
            throw new Exception("La contraseña actual es incorrecta");
        }

        // Cambiar contraseña
        $changed = $this->usuarioRepository->changePassword($userId, $newPassword);
        if (!$changed) {
            throw new Exception("No se pudo cambiar la contraseña");
        }

        return true;
    }
}
