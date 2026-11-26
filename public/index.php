<?php

/**
 * Front controller: every request that isn't a real file under public/ comes here.
 */

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/bootstrap.php';

use App\View;

/** path => [allowed methods, handler file relative to APP_ROOT, or null for the public home page] */
$routes = [
    '/'             => [['GET', 'HEAD'], null],
    '/admin'        => [['GET', 'HEAD'], 'admin/index.php'],
    '/admin/login'  => [['GET', 'HEAD', 'POST'], 'admin/login.php'],
    '/admin/logout' => [['POST'], 'admin/logout.php'],
];

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$path   = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path   = is_string($path) ? (rtrim($path, '/') ?: '/') : '/';

try {
    if (!isset($routes[$path])) {
        http_response_code(404);
        echo View::render('public/404', ['title' => 'Page not found'], 'public/layout');
        return;
    }

    [$methods, $handler] = $routes[$path];
    if (!in_array($method, $methods, true)) {
        http_response_code(405);
        header('Allow: ' . implode(', ', $methods));
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Method Not Allowed\n";
        return;
    }

    if ($handler === null) {
        header('Cache-Control: public, max-age=300');
        echo View::render('public/home', ['title' => 'Home'], 'public/layout');
        return;
    }

    require APP_ROOT . '/' . $handler;
} catch (Throwable $e) {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    echo "Sorry, something went wrong. Please try again later.\n";
}
