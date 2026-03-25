<?php
declare(strict_types=1);

namespace App;

use mysqli;
use mysqli_sql_exception;
use RuntimeException;

final class Database
{
    /**
     * Crea una sola conexion mysqli reutilizable para toda la peticion.
     */
    public static function getConnection(): mysqli
    {
        static $connection = null;

        if ($connection instanceof mysqli) {
            return $connection;
        }

        /** @var array<string, mixed> $config */
        $config = require __DIR__ . '/../config/database.php';

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $connection = new mysqli(
                (string) $config['host'],
                (string) $config['username'],
                (string) $config['password'],
                (string) $config['dbname'],
                (int) $config['port']
            );
            $connection->set_charset((string) $config['charset']);
        } catch (mysqli_sql_exception $exception) {
            // Ocultamos detalles sensibles y enviamos un mensaje manejable.
            throw new RuntimeException('No fue posible conectar con la base de datos.', 0, $exception);
        }

        return $connection;
    }
}
