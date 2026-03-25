<?php
declare(strict_types=1);

require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/AlumnoRepository.php';

use App\AlumnoRepository;
use App\Database;

/**
 * Exporta el listado completo de alumnos en formato CSV.
 *
 * @param array<int, array<string, mixed>> $alumnos
 */
function exportCsv(array $alumnos): void
{
    // Definimos encabezados para descarga de archivo.
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="alumnos.csv"');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        exit;
    }

    // Escribimos encabezado de columnas.
    fputcsv($output, ['ID', 'Apellido paterno', 'Apellido materno', 'Nombre', 'Numero de cuenta', 'Correo', 'Contacto']);

    // Escribimos cada registro obtenido desde base de datos.
    foreach ($alumnos as $alumno) {
        fputcsv($output, [
            $alumno['id_alumno'] ?? '',
            $alumno['primer_apellido'] ?? '',
            $alumno['segundo_apellido'] ?? '',
            $alumno['nombres'] ?? '',
            $alumno['no_cuenta'] ?? '',
            $alumno['correo'] ?? '',
            $alumno['contacto'] ?? '',
        ]);
    }

    fclose($output);
    exit;
}

$repository = new AlumnoRepository(Database::getConnection());

// Si el usuario pidio exportacion, respondemos con CSV y terminamos.
if (($_GET['action'] ?? '') === 'export') {
    exportCsv($repository->all());
}

$formState = pullFormState();
$old = $formState['old'];
$errors = $formState['errors'];
$flash = pullFlash();
$csrfToken = csrfToken();

$editId = null;
if (isset($_GET['edit'])) {
    $candidate = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($candidate !== false && $candidate > 0) {
        $editId = (int) $candidate;
    }
} elseif (isset($formState['edit_id']) && is_int($formState['edit_id']) && $formState['edit_id'] > 0) {
    $editId = $formState['edit_id'];
}

$editingAlumno = null;
if ($editId !== null) {
    $editingAlumno = $repository->findById($editId);
    if ($editingAlumno === null) {
        setFlash('warning', 'El alumno que intentaste editar ya no existe.');
        redirect('index.php');
    }
}

// Definimos valores base para formulario nuevo.
$values = [
    'primer_apellido' => '',
    'segundo_apellido' => '',
    'nombres' => '',
    'no_cuenta' => '',
    'correo' => '',
    'contacto' => '',
];

// Si estamos editando, precargamos datos desde base de datos.
if (is_array($editingAlumno)) {
    $values = [
        'primer_apellido' => (string) ($editingAlumno['primer_apellido'] ?? ''),
        'segundo_apellido' => (string) ($editingAlumno['segundo_apellido'] ?? ''),
        'nombres' => (string) ($editingAlumno['nombres'] ?? ''),
        'no_cuenta' => (string) ($editingAlumno['no_cuenta'] ?? ''),
        'correo' => (string) ($editingAlumno['correo'] ?? ''),
        'contacto' => (string) ($editingAlumno['contacto'] ?? ''),
    ];
}

// Si hubo error de validacion, priorizamos los valores capturados por el usuario.
foreach ($values as $field => $value) {
    if (isset($old[$field])) {
        $values[$field] = (string) $old[$field];
    }
}

$alumnos = $repository->all();
$isEditing = $editingAlumno !== null;
$submitAction = $isEditing ? 'update' : 'create';
$submitLabel = $isEditing ? 'Actualizar alumno' : 'Registrar alumno';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        thead th {
            position: sticky;
            top: 0;
            background-color: #343a40;
            z-index: 10;
        }
    </style>
</head>

<body class="p-4 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Registro de Alumnos</h1>
            <a href="index.php?action=export" class="btn btn-primary">Exportar CSV</a>
        </div>

        <?php if ($flash !== null): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form action="process.php" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="action" value="<?= e($submitAction) ?>">
                    <?php if ($isEditing): ?>
                        <input type="hidden" name="id" value="<?= e((string) $editId) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="primer_apellido">Apellido Paterno</label>
                            <input type="text" id="primer_apellido" name="primer_apellido"
                                class="form-control <?= isset($errors['primer_apellido']) ? 'is-invalid' : '' ?>"
                                value="<?= e($values['primer_apellido']) ?>" required>
                            <?php if (isset($errors['primer_apellido'])): ?>
                                <div class="invalid-feedback"><?= e($errors['primer_apellido']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="segundo_apellido">Apellido Materno</label>
                            <input type="text" id="segundo_apellido" name="segundo_apellido"
                                class="form-control <?= isset($errors['segundo_apellido']) ? 'is-invalid' : '' ?>"
                                value="<?= e($values['segundo_apellido']) ?>">
                            <?php if (isset($errors['segundo_apellido'])): ?>
                                <div class="invalid-feedback"><?= e($errors['segundo_apellido']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="nombres">Nombre</label>
                            <input type="text" id="nombres" name="nombres"
                                class="form-control <?= isset($errors['nombres']) ? 'is-invalid' : '' ?>"
                                value="<?= e($values['nombres']) ?>" required>
                            <?php if (isset($errors['nombres'])): ?>
                                <div class="invalid-feedback"><?= e($errors['nombres']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="no_cuenta">Numero de Cuenta</label>
                            <input type="text" id="no_cuenta" name="no_cuenta"
                                class="form-control <?= isset($errors['no_cuenta']) ? 'is-invalid' : '' ?>"
                                value="<?= e($values['no_cuenta']) ?>" inputmode="numeric" maxlength="9"
                                pattern="[0-9]{8,9}" required>
                            <?php if (isset($errors['no_cuenta'])): ?>
                                <div class="invalid-feedback"><?= e($errors['no_cuenta']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="correo">Correo</label>
                            <input type="email" id="correo" name="correo"
                                class="form-control <?= isset($errors['correo']) ? 'is-invalid' : '' ?>"
                                value="<?= e($values['correo']) ?>" required>
                            <?php if (isset($errors['correo'])): ?>
                                <div class="invalid-feedback"><?= e($errors['correo']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="contacto">Contacto</label>
                            <input type="text" id="contacto" name="contacto"
                                class="form-control <?= isset($errors['contacto']) ? 'is-invalid' : '' ?>"
                                value="<?= e($values['contacto']) ?>" inputmode="numeric" maxlength="10"
                                pattern="[0-9]{10}" required>
                            <?php if (isset($errors['contacto'])): ?>
                                <div class="invalid-feedback"><?= e($errors['contacto']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success"><?= e($submitLabel) ?></button>
                        <?php if ($isEditing): ?>
                            <a href="index.php" class="btn btn-outline-secondary">Cancelar edicion</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
            <table class="table table-striped table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Nombre</th>
                        <th>Numero de Cuenta</th>
                        <th>Correo</th>
                        <th>Contacto</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alumnos)): ?>
                        <tr>
                            <td colspan="8">No hay alumnos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?= e((string) $alumno['id_alumno']) ?></td>
                                <td><?= e((string) $alumno['primer_apellido']) ?></td>
                                <td><?= e((string) ($alumno['segundo_apellido'] ?? '')) ?></td>
                                <td><?= e((string) $alumno['nombres']) ?></td>
                                <td><?= e((string) $alumno['no_cuenta']) ?></td>
                                <td><?= e((string) $alumno['correo']) ?></td>
                                <td><?= e((string) $alumno['contacto']) ?></td>
                                <td class="text-nowrap">
                                    <a href="index.php?edit=<?= e((string) $alumno['id_alumno']) ?>"
                                        class="btn btn-sm btn-warning">
                                        Modificar
                                    </a>
                                    <form class="d-inline" action="process.php" method="POST"
                                        onsubmit="return confirm('Seguro que deseas eliminar este alumno?');">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= e((string) $alumno['id_alumno']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>