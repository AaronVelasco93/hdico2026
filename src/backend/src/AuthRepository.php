<?php
declare(strict_types=1);

namespace App;

use mysqli;

final class AuthRepository
{
    /**
     * Conexion mysqli compartida.
     *
     * @var mysqli
     */
    private $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Busca un usuario por su username para autenticar.
     *
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT id, username, password_hash, display_name FROM users WHERE username = ? LIMIT 1';

        $statement = $this->connection->prepare($sql);
        $statement->bind_param('s', $username);
        $statement->execute();
        $statement->bind_result($id, $dbUsername, $passwordHash, $displayName);

        if (!$statement->fetch()) {
            return null;
        }

        return [
            'id' => (int) $id,
            'username' => (string) $dbUsername,
            'password_hash' => (string) $passwordHash,
            'display_name' => (string) $displayName,
        ];
    }
}
