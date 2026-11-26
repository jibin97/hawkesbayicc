<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Env;
use PDO;

/**
 * Shared PDO connection built from DB_* environment settings.
 */
final class Connection
{
    private static ?PDO $pdo = null;

    public static function fromEnv(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = Env::get('DB_HOST', '127.0.0.1');
        // Always connect over TCP: "localhost" would make the MySQL driver use the Unix socket.
        if ($host === null || $host === '' || $host === 'localhost') {
            $host = '127.0.0.1';
        }

        $port = (int) Env::get('DB_PORT', '3306');
        if ($port < 1 || $port > 65535) {
            $port = 3306;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            Env::require('DB_NAME')
        );

        self::$pdo = new PDO($dsn, Env::require('DB_USER'), Env::get('DB_PASS', '') ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
