<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\UsuarioService;
use App\Utils\Response;
use Exception;

class AuthController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    /**
     * POST /auth/register
     */
    public function register() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $nombre = $input['nombre'] ?? null;
            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;
            $confirmPassword = $input['confirmPassword'] ?? null;

            $usuario = $this->authService->register($nombre, $email, $password, $confirmPassword);

            Response::success($usuario->toArray(), "Usuario registrado exitosamente", 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    /**
     * POST /auth/login
     */
    public function login() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;

            $result = $this->authService->login($email, $password);

            Response::success($result, "Sesión iniciada exitosamente", 200);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 401);
        }
    }

    /**
     * POST /auth/change-password
     */
    public function changePassword() {
        try {
            $user = AuthMiddleware::authenticate();
            $input = json_decode(file_get_contents('php://input'), true);

            $oldPassword = $input['oldPassword'] ?? null;
            $newPassword = $input['newPassword'] ?? null;
            $confirmPassword = $input['confirmPassword'] ?? null;

            $this->authService->changePassword($user['userId'], $oldPassword, $newPassword, $confirmPassword);

            Response::success(null, "Contraseña cambiada exitosamente", 200);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
