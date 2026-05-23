<?php

declare(strict_types=1);

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'login' => $user['login'],
            'name' => $user['name'],
            'is_admin' => (bool) $user['is_admin'],
            'solicitante_approval' => (bool) $user['solicitante_approval'],
            'admin_approval' => (bool) $user['admin_approval'],
            'financial_approval' => (bool) $user['financial_approval'],
            'purchasing_approval' => (bool) $user['purchasing_approval'],
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }

        $target = (BASE_URL ?: '') . '/auth';
        header('Location: ' . $target);
        exit;
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if ((self::user()['is_admin'] ?? false) === true) {
            return;
        }

        http_response_code(403);
        exit('Acesso negado.');
    }

    public static function hasPermission(string $permission): bool
    {
        $user = self::user();

        if ($user === null) {
            return false;
        }

        if (($user['is_admin'] ?? false) === true) {
            return true;
        }

        return (bool) ($user[$permission] ?? false);
    }
}
