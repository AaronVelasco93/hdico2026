<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Escapa texto para imprimirlo de forma segura en HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirige a otra ruta y termina la ejecucion.
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * Guarda un mensaje flash para mostrarlo en la siguiente carga.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Recupera y elimina el flash actual.
 *
 * @return array{type: string, message: string}|null
 */
function pullFlash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return [
        'type' => (string) ($flash['type'] ?? 'info'),
        'message' => (string) ($flash['message'] ?? ''),
    ];
}

/**
 * Genera (si no existe) y devuelve el token CSRF de sesion.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

/**
 * Verifica si el token recibido coincide con el de la sesion.
 */
function isValidCsrfToken(?string $token): bool
{
    if ($token === null || $token === '') {
        return false;
    }

    $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
    return $sessionToken !== '' && hash_equals($sessionToken, $token);
}

/**
 * Conserva datos del formulario y errores para reconstruir la vista tras redireccion.
 *
 * @param array<string, mixed> $old
 * @param array<string, string> $errors
 */
function rememberForm(array $old, array $errors, ?int $editId = null): void
{
    $_SESSION['form_state'] = [
        'old' => $old,
        'errors' => $errors,
        'edit_id' => $editId,
    ];
}

/**
 * Recupera y limpia el estado temporal del formulario.
 *
 * @return array{old: array<string, mixed>, errors: array<string, string>, edit_id: int|null}
 */
function pullFormState(): array
{
    $empty = ['old' => [], 'errors' => [], 'edit_id' => null];

    if (!isset($_SESSION['form_state']) || !is_array($_SESSION['form_state'])) {
        return $empty;
    }

    $state = $_SESSION['form_state'];
    unset($_SESSION['form_state']);

    return [
        'old' => is_array($state['old'] ?? null) ? $state['old'] : [],
        'errors' => is_array($state['errors'] ?? null) ? $state['errors'] : [],
        'edit_id' => isset($state['edit_id']) ? (int) $state['edit_id'] : null,
    ];
}
