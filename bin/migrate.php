<?php

/**
 * Applies database/migrations/*.sql in filename order, once each, and records them in `schema_migrations`.
 * Safe to run repeatedly: already-applied files are skipped.
 *
 *   php bin/migrate.php            apply pending migrations
 *   php bin/migrate.php --status   list applied / pending
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Database\Connection;

try {
    $pdo = Connection::fromEnv();
} catch (Throwable $e) {
    fwrite(STDERR, "Could not connect to the database: {$e->getMessage()}\nCheck DB_* in .env.\n");
    exit(1);
}

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
    filename   VARCHAR(191) NOT NULL,
    applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$applied = array_flip($pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));
$files   = glob(dirname(__DIR__) . '/database/migrations/*.sql') ?: [];
sort($files, SORT_STRING);

if (in_array('--status', $argv, true)) {
    foreach ($files as $file) {
        printf("%-9s %s\n", isset($applied[basename($file)]) ? 'applied' : 'PENDING', basename($file));
    }
    exit(0);
}

$record = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:f)');
$count  = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        continue;
    }
    echo "Applying {$name} … ";
    try {
        // MySQL DDL commits implicitly, so a failed file can't be rolled back: fix it, then re-run.
        $pdo->exec((string) file_get_contents($file));
    } catch (PDOException $e) {
        echo "FAILED\n";
        fwrite(STDERR, $e->getMessage() . "\nStopped; later migrations were not run.\n");
        exit(1);
    }
    $record->execute(['f' => $name]);
    echo "done\n";
    $count++;
}
echo $count === 0 ? "Nothing to do: database is up to date.\n" : "Applied {$count} migration(s).\n";
