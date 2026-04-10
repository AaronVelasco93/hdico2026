<?php
declare(strict_types=1);

namespace App;

final class AuthService
{
    private AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Intenta iniciar sesion con username y password.
     */
    public function login(string $username, string $password): bool
    {
        $user = $this->authRepository->findByUsername($username);
        if ($user === null) {
            return false;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'display_name' => (string) $user['display_name'],
        ];

        return true;
    }

    /**
     * Cierra la sesion activa y elimina la cookie de sesion.
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Devuelve el usuario autenticado en sesion o null si no existe.
     *
     * @return array{id: int, username: string, display_name: string}|null
     */
    public function currentUser(): ?array
    {
        $user = $_SESSION['auth_user'] ?? null;
        if (!is_array($user)) {
            return null;
        }

        return [
            'id' => (int) ($user['id'] ?? 0),
            'username' => (string) ($user['username'] ?? ''),
            'display_name' => (string) ($user['display_name'] ?? ''),
        ];
    }

    /**
     * Exige sesion activa, si no existe lanza error HTTP 401.
     *
     * @return array{id: int, username: string, display_name: string}
     */
    public function requireAuth(): array
    {
        $user = $this->currentUser();
        if ($user === null || $user['id'] <= 0) {
            throw new ApiException(401, 'Debes iniciar sesion para continuar.');
        }

        return $user;
    }
}
