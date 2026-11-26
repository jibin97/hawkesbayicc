<?php

declare(strict_types=1);

namespace App\Forms;

use App\Config\Env;
use RuntimeException;

/**
 * Stateless, signed form token: proves the form was rendered by this site for this form, and when.
 * Used instead of a session-based CSRF token so public pages stay session-free and cacheable.
 * Format: "<form>.<issued unix time>.<hmac>"
 */
final class FormToken
{
    public const MIN_SECONDS = 3;          // faster than this is almost certainly a bot
    public const MAX_SECONDS = 6 * 3600;   // older than this: ask the visitor to resubmit

    public static function issue(string $form, ?int $now = null): string
    {
        $issued = (string) ($now ?? time());

        return $form . '.' . $issued . '.' . self::sign($form, $issued);
    }

    /** @return 'ok'|'too_fast'|'expired'|'invalid' */
    public static function check(mixed $token, string $form, ?int $now = null): string
    {
        if (!is_string($token) || substr_count($token, '.') !== 2) {
            return 'invalid';
        }
        [$tForm, $issued, $sig] = explode('.', $token);
        if ($tForm !== $form || preg_match('/^\d{10}$/', $issued) !== 1
            || !hash_equals(self::sign($form, $issued), $sig)) {
            return 'invalid';
        }

        $age = ($now ?? time()) - (int) $issued;
        if ($age < self::MIN_SECONDS) {
            return 'too_fast';
        }
        if ($age > self::MAX_SECONDS) {
            return 'expired';
        }

        return 'ok';
    }

    /** Keyed hash of a visitor IP for rate limiting (the raw IP is never stored). */
    public static function hashIp(string $ip): string
    {
        return hash_hmac('sha256', 'ip|' . $ip, self::key());
    }

    private static function sign(string $form, string $issued): string
    {
        return hash_hmac('sha256', $form . '|' . $issued, self::key());
    }

    private static function key(): string
    {
        $key = Env::require('APP_KEY');
        if (strlen($key) < 32) {
            throw new RuntimeException('APP_KEY must be at least 32 characters.');
        }

        return $key;
    }
}
