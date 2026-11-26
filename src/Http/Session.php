<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Hardened PHP session wrapper with CSRF token and one-shot flash messages.
 */
final class Session
{
    private const NAME      = 'hbicc_session';
    private const CSRF_KEY  = '_csrf';
    private const FLASH_KEY = '_flash';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        session_name(self::NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    /** New session id (old one invalidated) and a fresh CSRF token, e.g. on login. */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        unset($_SESSION[self::CSRF_KEY]);
    }

    public static function csrfToken(): string
    {
        $token = $_SESSION[self::CSRF_KEY] ?? null;
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::CSRF_KEY] = $token;
        }

        return $token;
    }

    public static function verifyCsrf(mixed $submitted): bool
    {
        $token = $_SESSION[self::CSRF_KEY] ?? null;

        return is_string($token) && is_string($submitted) && hash_equals($token, $submitted);
    }

    public static function setFlash(string $message): void
    {
        $_SESSION[self::FLASH_KEY] = $message;
    }

    /** Return and clear the flash message, if any. */
    public static function pullFlash(): ?string
    {
        $message = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        return is_string($message) ? $message : null;
    }

    /** Clear all session data, expire the cookie and destroy the session. */
    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]);
        session_destroy();
    }
}
