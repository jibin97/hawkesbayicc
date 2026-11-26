<?php

declare(strict_types=1);

namespace App\Auth;

use App\Database\Connection;
use App\Http\Session;

/**
 * Session-based authentication for admin users. Call Session::start() first.
 */
final class Auth
{
    private const SESSION_KEY = '_user_id';

    /**
     * Hash of a random throwaway value. Verified against when the email is unknown, so a
     * failed login takes the same time whether or not the account exists.
     */
    private const DUMMY_HASH = '$2y$12$igKCMzwxM7TONBMD3vc91O6UuCvQoO9cEg4jH4wGlLpQwgk8IOXQW';

    /** @var array{id:int,email:string,name:string,role:string}|null */
    private static ?array $user = null;
    private static bool $loaded = false;

    /** Check credentials and log the user in. Returns false on any failure (no reason given). */
    public static function attempt(string $email, string $password): bool
    {
        $pdo  = Connection::fromEnv();
        $stmt = $pdo->prepare('SELECT id, password_hash, is_active FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row === false) {
            password_verify($password, self::DUMMY_HASH);
            return false;
        }

        if (!password_verify($password, $row['password_hash']) || (int) $row['is_active'] !== 1) {
            return false;
        }

        $id = (int) $row['id'];

        if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
            $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')
                ->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
        }
        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $id]);

        Session::regenerate();
        $_SESSION[self::SESSION_KEY] = $id;
        self::$loaded = false;

        return true;
    }

    /**
     * The logged-in, still-active user, or null.
     *
     * @return array{id:int,email:string,name:string,role:string}|null
     */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        self::$user   = null;

        $id = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_int($id)) {
            return null;
        }

        $stmt = Connection::fromEnv()->prepare(
            'SELECT id, email, name, role FROM users WHERE id = :id AND is_active = 1 LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            // Account deleted or deactivated since login: drop the stale session.
            unset($_SESSION[self::SESSION_KEY]);
            return null;
        }

        self::$user = [
            'id'    => (int) $row['id'],
            'email' => (string) $row['email'],
            'name'  => (string) $row['name'],
            'role'  => (string) $row['role'],
        ];

        return self::$user;
    }

    /**
     * Return the logged-in user, or redirect to the login page and stop.
     *
     * @return array{id:int,email:string,name:string,role:string}
     */
    public static function requireLogin(): array
    {
        $user = self::user();
        if ($user === null) {
            redirect('/admin/login');
            exit; // redirect() exits; keeps static analysers happy
        }

        return $user;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user   = null;
        self::$loaded = true;
    }
}
