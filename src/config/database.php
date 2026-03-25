<?php
declare(strict_types=1);

/**
 * Configuracion central de base de datos.
 * Puedes sobreescribir valores con variables de entorno:
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS.
 */
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    'dbname' => getenv('DB_NAME') ?: 'registro_alumnos',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '123456',
    'charset' => 'utf8mb4',
];
