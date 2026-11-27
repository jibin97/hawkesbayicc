<?php

/**
 * Loads the starter photos (database/seed-images/) into the `images` table and public/uploads/.
 * Run once after the migrations. Placements that already have photos are skipped, so it is safe to re-run
 * and never overwrites what an admin has uploaded.
 *
 *   php bin/seed-images.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Media\ImageRepository;
use App\Media\ImageStore;
use App\Media\Sections;
use App\Media\UploadException;

$dir      = dirname(__DIR__) . '/database/seed-images';
$manifest = require $dir . '/manifest.php';

try {
    $existing = ImageRepository::counts();
} catch (PDOException $e) {
    fwrite(STDERR, "Database error: {$e->getMessage()}\nRun `php bin/migrate.php` first.\n");
    exit(1);
}

$added = $skipped = $failed = 0;
$skipPlacement = [];
foreach ($manifest as $entry) {
    $placement = $entry['section'] . ($entry['item'] !== null ? ':' . $entry['item'] : '');
    if (!Sections::isValid($entry['section'], $entry['item'])) {
        fwrite(STDERR, "Skipping {$entry['file']}: {$placement} is not a valid section/item.\n");
        $failed++;
        continue;
    }
    // Leave placements alone if they already had photos before this run.
    if (($existing[$placement] ?? 0) > 0) {
        $skipPlacement[$placement] = true;
        $skipped++;
        continue;
    }
    try {
        $files = ImageStore::fromPath($dir . '/' . $entry['file'], $entry['section'], $entry['file']);
        ImageRepository::create($files + [
            'section'     => $entry['section'],
            'item_key'    => $entry['item'],
            'alt_text'    => $entry['alt'],
            'fit'         => $entry['fit'],
            'uploaded_by' => null,
        ]);
        $added++;
    } catch (UploadException | PDOException $e) {
        fwrite(STDERR, "Failed {$entry['file']}: {$e->getMessage()}\n");
        $failed++;
    }
}

echo "Added {$added} photo(s).";
if ($skipped > 0) {
    echo " Skipped {$skipped} in " . count($skipPlacement) . ' place(s) that already have photos.';
}
echo "\n";
exit($failed > 0 ? 1 : 0);
