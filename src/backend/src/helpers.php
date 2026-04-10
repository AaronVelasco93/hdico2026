<?php
declare(strict_types=1);

use App\ApiException;

/**
 * Inicia sesion PHP solo una vez por peticion.
 */
function bootSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Configura encabezados CORS para permitir al frontend consumir la API.
 */
function applyCors(): void
{
    $allowedOrigin = getenv('FRONTEND_ORIGIN') ?: 'http://localhost:8080';
    $requestOrigin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    $originToSend = $requestOrigin !== '' && $requestOrigin === $allowedOrigin ? $requestOrigin : $allowedOrigin;

    header('Access-Control-Allow-Origin: ' . $originToSend);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Vary: Origin');
}

/**
 * Responde JSON con codigo HTTP y finaliza la ejecucion.
 *
 * @param array<string, mixed> $payload
 */
function jsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Obtiene cuerpo JSON de la solicitud o, si no hay JSON, usa $_POST.
 *
 * @return array<string, mixed>
 */
function readInput(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return is_array($_POST) ? $_POST : [];
    }

    $decoded = json_decode($rawBody, true);
    if (!is_array($decoded)) {
        throw new ApiException(400, 'El cuerpo de la solicitud no es JSON valido.');
    }

    return $decoded;
}

/**
 * Verifica que el metodo HTTP usado sea uno de los permitidos.
 *
 * @param array<int, string> $allowedMethods
 */
function ensureMethod(string $actualMethod, array $allowedMethods): void
{
    $normalizedMethod = strtoupper($actualMethod);
    $normalizedAllowed = array_map('strtoupper', $allowedMethods);

    if (!in_array($normalizedMethod, $normalizedAllowed, true)) {
        throw new ApiException(405, 'Metodo HTTP no permitido para este endpoint.');
    }
}

/**
 * Convierte un valor en entero positivo o lanza error de validacion.
 */
function positiveIntOrFail(mixed $value, string $fieldName): int
{
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        throw new ApiException(422, sprintf('El campo %s debe ser un entero positivo.', $fieldName));
    }

    return (int) $id;
}

/**
 * Detecta si la excepcion corresponde a una violacion de llave unica.
 */
function isDuplicateError(Throwable $exception): bool
{
    $message = $exception->getMessage();
    return stripos($message, 'duplicate') !== false || stripos($message, '1062') !== false;
}

/**
 * Exporta una coleccion de alumnos en formato CSV y finaliza la respuesta.
 *
 * @param array<int, array<string, mixed>> $alumnos
 */
function exportAlumnosCsv(array $alumnos): void
{
    http_response_code(200);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="alumnos.csv"');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        exit;
    }

    fputcsv($output, ['ID', 'Apellido paterno', 'Apellido materno', 'Nombre', 'Numero de cuenta', 'Correo', 'Contacto']);

    foreach ($alumnos as $alumno) {
        fputcsv($output, [
            (string) ($alumno['id_alumno'] ?? ''),
            (string) ($alumno['primer_apellido'] ?? ''),
            (string) ($alumno['segundo_apellido'] ?? ''),
            (string) ($alumno['nombres'] ?? ''),
            (string) ($alumno['no_cuenta'] ?? ''),
            (string) ($alumno['correo'] ?? ''),
            (string) ($alumno['contacto'] ?? ''),
        ]);
    }

    fclose($output);
    exit;
}
