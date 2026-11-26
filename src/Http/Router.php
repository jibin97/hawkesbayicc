<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Minimal exact-path router with permanent redirects.
 */
final class Router
{
    /** @var array<string, array{methods: list<string>, handler: callable}> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $redirects = [];

    /** @param list<string> $methods */
    public function add(string $path, array $methods, callable $handler): void
    {
        $this->routes[self::normalise($path)] = ['methods' => $methods, 'handler' => $handler];
    }

    /** 301 from an old path to a local target (which may include a #fragment). */
    public function redirect(string $from, string $to): void
    {
        $this->redirects[self::normalise($from)] = $to;
    }

    /**
     * @param callable(): void $notFound
     */
    public function dispatch(string $method, string $uri, callable $notFound): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = self::normalise(is_string($path) ? $path : '/');

        if (isset($this->redirects[$path])) {
            header('Location: ' . $this->redirects[$path], true, 301);
            return;
        }

        if (!isset($this->routes[$path])) {
            http_response_code(404);
            $notFound();
            return;
        }

        $route = $this->routes[$path];
        if (!in_array($method, $route['methods'], true)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $route['methods']));
            header('Content-Type: text/plain; charset=UTF-8');
            echo "Method Not Allowed\n";
            return;
        }

        ($route['handler'])();
    }

    /** Lower-case, no trailing slash ("/About/" and "/about" are the same page). */
    private static function normalise(string $path): string
    {
        $path = strtolower(rtrim($path, '/'));

        return $path === '' ? '/' : $path;
    }
}
