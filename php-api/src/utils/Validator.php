<?php

namespace App\Utils;

use Exception;

class Validator {
    /**
     * Valida un email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida que un campo no esté vacío
     */
    public static function validateRequired($value, $fieldName) {
        if (empty(trim($value))) {
            throw new Exception("El campo '$fieldName' es requerido");
        }
        return true;
    }

    /**
     * Valida longitud mínima
     */
    public static function validateMinLength($value, $minLength, $fieldName) {
        if (strlen($value) < $minLength) {
            throw new Exception("El campo '$fieldName' debe tener al menos $minLength caracteres");
        }
        return true;
    }

    /**
     * Valida longitud máxima
     */
    public static function validateMaxLength($value, $maxLength, $fieldName) {
        if (strlen($value) > $maxLength) {
            throw new Exception("El campo '$fieldName' no puede exceder $maxLength caracteres");
        }
        return true;
    }

    /**
     * Valida que un valor sea numérico
     */
    public static function validateNumeric($value, $fieldName) {
        if (!is_numeric($value)) {
            throw new Exception("El campo '$fieldName' debe ser un número");
        }
        return true;
    }

    /**
     * Valida que un valor sea positivo
     */
    public static function validatePositive($value, $fieldName) {
        self::validateNumeric($value, $fieldName);
        if ($value <= 0) {
            throw new Exception("El campo '$fieldName' debe ser un número positivo");
        }
        return true;
    }

    /**
     * Valida que un valor sea un rol válido
     */
    public static function validateRole($role) {
        $validRoles = ['admin', 'vendedor', 'consultor'];
        if (!in_array($role, $validRoles)) {
            throw new Exception("Rol inválido. Roles válidos: " . implode(', ', $validRoles));
        }
        return true;
    }
}
