<?php
declare(strict_types=1);

require __DIR__ . '/../src/ApiException.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/AlumnoRepository.php';
require __DIR__ . '/../src/AlumnoValidator.php';
require __DIR__ . '/../src/AuthRepository.php';
require __DIR__ . '/../src/AuthService.php';

use App\AlumnoRepository;
use App\AlumnoValidator;
use App\ApiException;
use App\AuthRepository;
use App\AuthService;
use App\Database;

bootSession();
applyCors();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action = (string) ($_GET['action'] ?? '');

$connection = Database::getConnection();
$alumnoRepository = new AlumnoRepository($connection);
$authService = new AuthService(new AuthRepository($connection));

try {
    switch ($action) {
        case 'login':
            ensureMethod($method, ['POST']);
            $input = readInput();
            $username = trim((string) ($input['username'] ?? ''));
            $password = (string) ($input['password'] ?? '');

            if ($username === '' || $password === '') {
                throw new ApiException(422, 'Usuario y password son obligatorios.');
            }

            if (!$authService->login($username, $password)) {
                throw new ApiException(401, 'Credenciales invalidas.');
            }

            jsonResponse(200, [
                'ok' => true,
                'message' => 'Sesion iniciada correctamente.',
                'user' => $authService->currentUser(),
            ]);
            break;

        case 'logout':
            ensureMethod($method, ['POST']);
            $authService->logout();
            jsonResponse(200, [
                'ok' => true,
                'message' => 'Sesion cerrada.',
            ]);
            break;

        case 'me':
            ensureMethod($method, ['GET']);
            $user = $authService->currentUser();
            if ($user === null) {
                throw new ApiException(401, 'No hay sesion activa.');
            }

            jsonResponse(200, [
                'ok' => true,
                'user' => $user,
            ]);
            break;

        case 'alumnos':
            ensureMethod($method, ['GET']);
            $authService->requireAuth();
            jsonResponse(200, [
                'ok' => true,
                'data' => $alumnoRepository->all(),
            ]);
            break;

        case 'export':
        case 'alumnos_export':
            ensureMethod($method, ['GET']);
            $authService->requireAuth();
            exportAlumnosCsv($alumnoRepository->all());
            break;

        case 'alumnos_create':
            ensureMethod($method, ['POST']);
            $authService->requireAuth();
            $input = readInput();
            $validation = AlumnoValidator::validate($input);

            if (!empty($validation['errors'])) {
                jsonResponse(422, [
                    'ok' => false,
                    'message' => 'Hay campos invalidos.',
                    'errors' => $validation['errors'],
                ]);
            }

            $newId = $alumnoRepository->create($validation['data']);
            jsonResponse(201, [
                'ok' => true,
                'message' => 'Alumno registrado correctamente.',
                'id' => $newId,
            ]);
            break;

        case 'alumnos_update':
            ensureMethod($method, ['PUT', 'PATCH', 'POST']);
            $authService->requireAuth();
            $id = positiveIntOrFail($_GET['id'] ?? null, 'id');
            $input = readInput();
            $validation = AlumnoValidator::validate($input);

            if (!empty($validation['errors'])) {
                jsonResponse(422, [
                    'ok' => false,
                    'message' => 'Hay campos invalidos.',
                    'errors' => $validation['errors'],
                ]);
            }

            $existing = $alumnoRepository->findById($id);
            if ($existing === null) {
                throw new ApiException(404, 'El alumno indicado no existe.');
            }

            $alumnoRepository->update($id, $validation['data']);
            jsonResponse(200, [
                'ok' => true,
                'message' => 'Alumno actualizado correctamente.',
            ]);
            break;

        case 'alumnos_delete':
            ensureMethod($method, ['DELETE', 'POST']);
            $authService->requireAuth();
            $id = positiveIntOrFail($_GET['id'] ?? null, 'id');
            $existing = $alumnoRepository->findById($id);
            if ($existing === null) {
                throw new ApiException(404, 'El alumno indicado no existe.');
            }

            $alumnoRepository->delete($id);
            jsonResponse(200, [
                'ok' => true,
                'message' => 'Alumno eliminado correctamente.',
            ]);
            break;

        default:
            throw new ApiException(404, 'Accion no encontrada.');
    }
} catch (ApiException $exception) {
    jsonResponse($exception->statusCode(), [
        'ok' => false,
        'message' => $exception->getMessage(),
    ]);
} catch (Throwable $exception) {
    if (isDuplicateError($exception)) {
        jsonResponse(409, [
            'ok' => false,
            'message' => 'Numero de cuenta o correo ya registrado.',
        ]);
    }

    jsonResponse(500, [
        'ok' => false,
        'message' => 'Ocurrio un error interno en el servidor.',
    ]);
}
