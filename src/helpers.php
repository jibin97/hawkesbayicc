<?php

declare(strict_types=1);

use App\Config\Env;

/** Escape a value for HTML output (text and attribute contexts). */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function site_name(): string
{
    return Env::get('APP_NAME') ?: "Hawke's Bay ICC";
}

/** Redirect to a local path and stop. Only same-site paths are accepted. */
function redirect(string $path, int $status = 303): void
{
    if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
        $path = '/';
    }
    header('Location: ' . $path, true, $status);
    exit;
}

/** Response headers for every admin page. */
function admin_headers(): void
{
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'none'");
}
