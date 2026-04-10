<?php
declare(strict_types=1);

/**
 * Configuracion de conexion a base de datos para el backend API.
 * Todos los valores se pueden sobreescribir por variables de entorno.
 */
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => (int) (getenv('DB_PORT') ?: 3306),
    'dbname' => getenv('DB_NAME') ?: 'registro_alumnos',
    'username' => getenv('DB_USER') ?: 'app_user',
    'password' => getenv('DB_PASS') ?: 'app_secret',
    'charset' => 'utf8mb4',
];
