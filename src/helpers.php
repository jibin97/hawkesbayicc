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

/**
 * Events from content/events.php whose date is today or later, soonest first.
 *
 * @param list<array<string, mixed>> $events
 * @return list<array<string, mixed>>
 */
function upcoming_events(array $events): array
{
    $today = (new DateTimeImmutable('today', new DateTimeZone('Pacific/Auckland')))->format('Y-m-d');
    $future = array_values(array_filter(
        $events,
        static fn (array $e): bool => isset($e['date']) && is_string($e['date']) && $e['date'] >= $today
    ));
    usort($future, static fn (array $a, array $b): int => strcmp($a['date'], $b['date']));

    return $future;
}

/** "2027-06-12" → "Saturday 12 June 2027" (NZ style). Returns the input unchanged if unparseable. */
function format_date(string $ymd): string
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $ymd);

    return $date === false ? $ymd : $date->format('l j F Y');
}

/** Initials for avatar placeholders: "Pauline Ellen Hayes" → "PH". */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = $parts[0] ?? '';
    $last  = count($parts) > 1 ? $parts[count($parts) - 1] : '';

    return strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
}

/** Response headers for every public page. */
function public_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
}

/** True when $path is the current page (exact match; '/' only matches home). */
function is_current(string $navPath, string $currentPath): bool
{
    return $navPath === $currentPath;
}

/**
 * Inline SVG icon (decorative: aria-hidden). Pair with visible text or an aria-label on the parent.
 * Icons are simple single-colour paths that inherit currentColor.
 */
function icon(string $name, int $size = 20): string
{
    static $paths = [
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>',
        'close'     => '<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>',
        'phone'     => '<path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2a1 1 0 011-.25 11.4 11.4 0 003.6.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1z" fill="currentColor"/>',
        'mail'      => '<path d="M3 5h18v14H3z M3 6l9 7 9-7" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="none"/>',
        'pin'       => '<path d="M12 22s7-6.2 7-12a7 7 0 10-14 0c0 5.8 7 12 7 12z" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="12" cy="10" r="2.5" fill="currentColor"/>',
        'calendar'  => '<path d="M4 6h16v15H4zM4 10h16M8 3v5M16 3v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>',
        'ticket'    => '<path d="M3 8a2 2 0 002-2h14a2 2 0 002 2v2a2 2 0 000 4v2a2 2 0 00-2 2H5a2 2 0 00-2-2v-2a2 2 0 000-4z M14 6v12" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="none"/>',
        'enrol'     => '<path d="M12 4L2 9l10 5 10-5-10-5z M6 11v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5" stroke="currentColor" stroke-width="2" stroke-linejoin="round" fill="none"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        'play'      => '<path d="M8 5v14l11-7z" fill="currentColor"/>',
        'facebook'  => '<path d="M14 8h3V4h-3a4 4 0 00-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8z" fill="currentColor"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/>',
        'youtube'   => '<path d="M22 8.2a3 3 0 00-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 002 8.2 31 31 0 001.6 12 31 31 0 002 15.8a3 3 0 002.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 002.1-2.1 31 31 0 00.4-3.8 31 31 0 00-.4-3.8zM10 15V9l5.2 3z" fill="currentColor"/>',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    return sprintf(
        '<svg class="icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" aria-hidden="true" focusable="false">%2$s</svg>',
        $size,
        $paths[$name]
    );
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

/**
 * Photos placed in a section of the site by the admin (see App\Media\Sections), in display order.
 * Each: id, w, h, sm (640px WebP), lg (up to 1600px WebP), alt, fit. If the database is unavailable,
 * the error is logged once and pages fall back to their placeholders.
 *
 * @return list<array{id:int,w:int,h:int,sm:string,lg:string,alt:string,fit:string}>
 */
function images(string $section, ?string $item = null): array
{
    static $failed = false;
    if ($failed) {
        return [];
    }
    try {
        $rows = \App\Media\ImageRepository::placed()[$section][(string) ($item ?? '')] ?? [];
    } catch (\Throwable $e) {
        $failed = true;
        error_log('Images unavailable: ' . $e->getMessage());
        return [];
    }

    return array_map(static fn (array $r): array => [
        'id' => $r['id'], 'w' => (int) $r['width'], 'h' => (int) $r['height'],
        'sm' => $r['path_sm'], 'lg' => $r['path'], 'alt' => $r['alt_text'], 'fit' => $r['fit'],
    ], $rows);
}

/**
 * The first (or only) photo in a section, or null.
 *
 * @return array{id:int,w:int,h:int,sm:string,lg:string,alt:string,fit:string}|null
 */
function image(string $section, ?string $item = null): ?array
{
    return images($section, $item)[0] ?? null;
}

/** Absolute URL on the canonical site, e.g. abs_url('/academy'). */
function abs_url(string $path): string
{
    return rtrim((string) \App\Content::get('site')['url'], '/') . $path;
}
