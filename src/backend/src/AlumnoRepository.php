<?php
declare(strict_types=1);

namespace App;

use mysqli;
use mysqli_result;

final class AlumnoRepository
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
     * Obtiene todos los alumnos ordenados por id descendente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $sql = 'SELECT id_alumno, no_cuenta, primer_apellido, segundo_apellido, nombres, correo, contacto
                FROM registro
                ORDER BY id_alumno DESC';

        $result = $this->connection->query($sql);
        if (!$result instanceof mysqli_result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Busca un alumno por su id.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT id_alumno, no_cuenta, primer_apellido, segundo_apellido, nombres, correo, contacto
                FROM registro WHERE id_alumno = ?';

        $statement = $this->connection->prepare($sql);
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->bind_result(
            $idAlumno,
            $noCuenta,
            $primerApellido,
            $segundoApellido,
            $nombres,
            $correo,
            $contacto
        );

        if (!$statement->fetch()) {
            return null;
        }

        return [
            'id_alumno' => $idAlumno,
            'no_cuenta' => $noCuenta,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'nombres' => $nombres,
            'correo' => $correo,
            'contacto' => $contacto,
        ];
    }

    /**
     * Inserta un nuevo alumno y devuelve el id generado.
     *
     * @param array<string, string> $alumno
     */
    public function create(array $alumno): int
    {
        $sql = 'INSERT INTO registro (no_cuenta, primer_apellido, segundo_apellido, nombres, correo, contacto)
                VALUES (?, ?, ?, ?, ?, ?)';

        $noCuenta = (string) $alumno['no_cuenta'];
        $primerApellido = (string) $alumno['primer_apellido'];
        $segundoApellido = $alumno['segundo_apellido'] !== '' ? (string) $alumno['segundo_apellido'] : null;
        $nombres = (string) $alumno['nombres'];
        $correo = (string) $alumno['correo'];
        $contacto = (string) $alumno['contacto'];

        $statement = $this->connection->prepare($sql);
        $statement->bind_param('ssssss', $noCuenta, $primerApellido, $segundoApellido, $nombres, $correo, $contacto);
        $statement->execute();

        return (int) $this->connection->insert_id;
    }

    /**
     * Actualiza la informacion de un alumno existente.
     *
     * @param array<string, string> $alumno
     */
    public function update(int $id, array $alumno): void
    {
        $sql = 'UPDATE registro
                SET no_cuenta = ?,
                    primer_apellido = ?,
                    segundo_apellido = ?,
                    nombres = ?,
                    correo = ?,
                    contacto = ?
                WHERE id_alumno = ?';

        $noCuenta = (string) $alumno['no_cuenta'];
        $primerApellido = (string) $alumno['primer_apellido'];
        $segundoApellido = $alumno['segundo_apellido'] !== '' ? (string) $alumno['segundo_apellido'] : null;
        $nombres = (string) $alumno['nombres'];
        $correo = (string) $alumno['correo'];
        $contacto = (string) $alumno['contacto'];

        $statement = $this->connection->prepare($sql);
        $statement->bind_param('ssssssi', $noCuenta, $primerApellido, $segundoApellido, $nombres, $correo, $contacto, $id);
        $statement->execute();
    }

    /**
     * Elimina un alumno por id.
     */
    public function delete(int $id): void
    {
        $sql = 'DELETE FROM registro WHERE id_alumno = ?';
        $statement = $this->connection->prepare($sql);
        $statement->bind_param('i', $id);
        $statement->execute();
    }
}
