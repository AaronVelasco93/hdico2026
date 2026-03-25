<?php
declare(strict_types=1);

require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/AlumnoRepository.php';
require __DIR__ . '/src/AlumnoValidator.php';

use App\AlumnoRepository;
use App\AlumnoValidator;
use App\Database;

// Solo permitimos operaciones por POST para acciones de escritura.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// Validamos token CSRF antes de procesar cualquier accion.
$csrfToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null;
if (!isValidCsrfToken($csrfToken)) {
    setFlash('danger', 'Token de seguridad invalido. Intenta de nuevo.');
    redirect('index.php');
}

$action = (string) ($_POST['action'] ?? '');
$repository = new AlumnoRepository(Database::getConnection());

// Caso especial: eliminar registro.
if ($action === 'delete') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        setFlash('danger', 'No fue posible identificar el alumno a eliminar.');
        redirect('index.php');
    }

    try {
        $repository->delete((int) $id);
        setFlash('success', 'Alumno eliminado correctamente.');
    } catch (\Throwable) {
        setFlash('danger', 'Ocurrio un error al eliminar el alumno.');
    }

    redirect('index.php');
}

// Para crear/actualizar, validamos los campos de formulario.
$validation = AlumnoValidator::validate($_POST);
$data = $validation['data'];
$errors = $validation['errors'];
$editId = null;

if ($action === 'update') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        $errors['id'] = 'No fue posible identificar el alumno a actualizar.';
    } else {
        $editId = (int) $id;
    }
}

if (!empty($errors)) {
    rememberForm($data, $errors, $editId);
    setFlash('danger', 'Revisa los campos marcados.');
    redirect($editId !== null ? 'index.php?edit=' . $editId : 'index.php');
}

try {
    if ($action === 'update' && $editId !== null) {
        $repository->update($editId, $data);
        setFlash('success', 'Alumno actualizado correctamente.');
    } else {
        $repository->create($data);
        setFlash('success', 'Alumno registrado correctamente.');
    }
} catch (\Throwable $exception) {
    // Conservamos datos para no perder lo capturado por el usuario.
    rememberForm($data, [], $editId);
    $message = $exception->getMessage();
    $isDuplicate = stripos($message, 'duplicate') !== false || stripos($message, '1062') !== false;

    if ($isDuplicate) {
        setFlash('danger', 'Numero de cuenta o correo ya registrado. Usa un valor diferente.');
    } else {
        setFlash('danger', 'No fue posible guardar la informacion.');
    }

    redirect($editId !== null ? 'index.php?edit=' . $editId : 'index.php');
}

redirect('index.php');
