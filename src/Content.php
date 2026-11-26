<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use RuntimeException;

/**
 * Loads static content arrays from content/{name}.php (cached per request).
 */
final class Content
{
    /** @var array<string, array<mixed>> */
    private static array $cache = [];

    /** @return array<mixed> */
    public static function get(string $name): array
    {
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }
        if (preg_match('/^[a-z0-9_-]+$/', $name) !== 1) {
            throw new InvalidArgumentException('Invalid content name.');
        }

        $file = dirname(__DIR__) . '/content/' . $name . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Content file not found: {$name}");
        }

        $data = require $file;
        if (!is_array($data)) {
            throw new RuntimeException("Content file must return an array: {$name}");
        }

        return self::$cache[$name] = $data;
    }
}
