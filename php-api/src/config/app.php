<?php

return [
    'database' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 5432,
        'dbname' => $_ENV['DB_NAME'] ?? 'proyectoweb',
        'user' => $_ENV['DB_USER'] ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8',
    ],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'tu-clave-secreta-super-segura-aqui',
        'algorithm' => 'HS256',
        'expire' => 86400, // 24 horas en segundos
    ],
    'app' => [
        'name' => 'ProyectoWeb API',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'development',
    ]
];
