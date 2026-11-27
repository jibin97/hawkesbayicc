<?php

declare(strict_types=1);

namespace App\Media;

use App\Database\Connection;
use InvalidArgumentException;
use PDO;

/**
 * Data access for site photos in the `images` table. Prepared statements only;
 * section/item values are validated against Sections before they reach SQL.
 */
final class ImageRepository
{
    private const COLUMNS = 'id, section, item_key, sort_order, path, path_sm, original_name, alt_text, fit,
                             mime_type, size_bytes, width, height, uploaded_by, created_at, updated_at';

    /** @var array<string, array<string, list<array<string, mixed>>>>|null per-request cache: [section][item|''] */
    private static ?array $placed = null;

    /**
     * Every placed image, grouped by section and item, in display order. One query per request.
     *
     * @return array<string, array<string, list<array<string, mixed>>>>
     */
    public static function placed(): array
    {
        if (self::$placed !== null) {
            return self::$placed;
        }
        $rows = Connection::fromEnv()
            ->query('SELECT ' . self::COLUMNS . " FROM images WHERE section <> 'unassigned' AND path_sm IS NOT NULL
                     ORDER BY section, item_key, sort_order, id")
            ->fetchAll();

        $out = [];
        foreach ($rows as $row) {
            $out[$row['section']][(string) ($row['item_key'] ?? '')][] = self::cast($row);
        }

        return self::$placed = $out;
    }

    /** @return list<array<string, mixed>> */
    public static function forPlacement(string $section, ?string $item): array
    {
        $stmt = Connection::fromEnv()->prepare(
            'SELECT ' . self::COLUMNS . ' FROM images WHERE section = :s AND item_key <=> :i ORDER BY sort_order, id'
        );
        $stmt->execute(['s' => $section, 'i' => $item]);

        return array_map([self::class, 'cast'], $stmt->fetchAll());
    }

    /** @return array<string, mixed>|null */
    public static function find(int $id): ?array
    {
        $stmt = Connection::fromEnv()->prepare('SELECT ' . self::COLUMNS . ' FROM images WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : self::cast($row);
    }

    /** Count of images per "section" / "section:item" (for the admin overview). @return array<string, int> */
    public static function counts(): array
    {
        $rows = Connection::fromEnv()
            ->query("SELECT section, item_key, COUNT(*) AS n FROM images WHERE section <> 'unassigned' GROUP BY section, item_key")
            ->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['section'] . ($r['item_key'] !== null ? ':' . $r['item_key'] : '')] = (int) $r['n'];
        }

        return $out;
    }

    /**
     * @param array{section:string,item_key:?string,path:string,path_sm:string,original_name:?string,alt_text:string,
     *              fit:string,mime_type:string,size_bytes:int,width:int,height:int,uploaded_by:?int} $d
     */
    public static function create(array $d): int
    {
        if (!Sections::isValid($d['section'], $d['item_key'])) {
            throw new InvalidArgumentException('Invalid image placement.');
        }
        $pdo  = Connection::fromEnv();
        $next = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM images WHERE section = :s AND item_key <=> :i');
        $next->execute(['s' => $d['section'], 'i' => $d['item_key']]);

        $stmt = $pdo->prepare(
            'INSERT INTO images (section, item_key, sort_order, path, path_sm, original_name, alt_text, fit,
                                 mime_type, size_bytes, width, height, uploaded_by)
             VALUES (:section, :item_key, :sort_order, :path, :path_sm, :original_name, :alt_text, :fit,
                     :mime_type, :size_bytes, :width, :height, :uploaded_by)'
        );
        $stmt->execute([
            'section'       => $d['section'],
            'item_key'      => $d['item_key'],
            'sort_order'    => (int) $next->fetchColumn(),
            'path'          => $d['path'],
            'path_sm'       => $d['path_sm'],
            'original_name' => $d['original_name'] !== null ? mb_substr($d['original_name'], 0, 255) : null,
            'alt_text'      => mb_substr($d['alt_text'], 0, 255),
            'fit'           => self::fit($d['fit']),
            'mime_type'     => $d['mime_type'],
            'size_bytes'    => $d['size_bytes'],
            'width'         => $d['width'],
            'height'        => $d['height'],
            'uploaded_by'   => $d['uploaded_by'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function updateDetails(int $id, string $alt, string $fit): void
    {
        $stmt = Connection::fromEnv()->prepare('UPDATE images SET alt_text = :alt, fit = :fit WHERE id = :id');
        $stmt->execute(['alt' => mb_substr($alt, 0, 255), 'fit' => self::fit($fit), 'id' => $id]);
    }

    /** Point an existing row at newly processed files (same place, order and caption). */
    public static function replaceFiles(int $id, array $f, ?int $userId): void
    {
        $stmt = Connection::fromEnv()->prepare(
            'UPDATE images SET path = :path, path_sm = :path_sm, original_name = :original_name, mime_type = :mime_type,
                               size_bytes = :size_bytes, width = :width, height = :height, uploaded_by = :uploaded_by
             WHERE id = :id'
        );
        $stmt->execute([
            'path' => $f['path'], 'path_sm' => $f['path_sm'],
            'original_name' => $f['original_name'] !== null ? mb_substr($f['original_name'], 0, 255) : null,
            'mime_type' => $f['mime_type'], 'size_bytes' => $f['size_bytes'],
            'width' => $f['width'], 'height' => $f['height'], 'uploaded_by' => $userId, 'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Connection::fromEnv()->prepare('DELETE FROM images WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Swap an image with its neighbour in the same section/item. $direction: -1 = earlier, +1 = later. */
    public static function move(int $id, int $direction): bool
    {
        $img = self::find($id);
        if ($img === null) {
            return false;
        }
        $list = self::forPlacement($img['section'], $img['item_key']);
        $ids  = array_column($list, 'id');
        $pos  = array_search($id, $ids, true);
        $to   = $pos + ($direction < 0 ? -1 : 1);
        if ($pos === false || !isset($ids[$to])) {
            return false;
        }
        [$ids[$pos], $ids[$to]] = [$ids[$to], $ids[$pos]];

        // Renumber the whole list 1..n so old gaps/duplicates can't cause odd ordering.
        $pdo  = Connection::fromEnv();
        $stmt = $pdo->prepare('UPDATE images SET sort_order = :o WHERE id = :id');
        $pdo->beginTransaction();
        try {
            foreach ($ids as $i => $rowId) {
                $stmt->execute(['o' => $i + 1, 'id' => $rowId]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return true;
    }

    private static function fit(string $fit): string
    {
        return $fit === 'contain' ? 'contain' : 'cover';
    }

    /** @param array<string, mixed> $row */
    private static function cast(array $row): array
    {
        foreach (['id', 'sort_order', 'size_bytes', 'width', 'height', 'uploaded_by'] as $k) {
            $row[$k] = $row[$k] === null ? null : (int) $row[$k];
        }

        return $row;
    }
}
