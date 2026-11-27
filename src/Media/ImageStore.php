<?php

declare(strict_types=1);

namespace App\Media;

use finfo;
use GdImage;

/**
 * Turns an uploaded (or seed) photo into the site's two WebP files and removes old ones.
 *
 * Every image is decoded and re-encoded with GD: this fixes phone rotation (EXIF), strips metadata
 * such as GPS location, and guarantees that what's stored is a plain image, whatever was uploaded.
 * Files go to public/uploads/{section}/{random}-{sm|lg}.webp.
 */
final class ImageStore
{
    public const MAX_BYTES  = 10 * 1024 * 1024;   // per file
    public const MAX_PIXELS = 40_000_000;         // ~40 megapixels; guards against "decompression bombs"
    private const TYPES     = ['image/jpeg' => 'JPEG', 'image/png' => 'PNG', 'image/webp' => 'WebP'];
    private const SIZES     = ['lg' => 1600, 'sm' => 640];

    public static function uploadsDir(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads';
    }

    /**
     * Validate one entry of $_FILES and process it.
     *
     * @param array<string, mixed> $file
     * @return array{path:string,path_sm:string,original_name:?string,mime_type:string,size_bytes:int,width:int,height:int}
     * @throws UploadException with a message safe to show the admin
     */
    public static function fromUpload(array $file, string $section): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new UploadException(match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'This file is larger than the server allows (' . self::serverLimit() . ').',
                UPLOAD_ERR_PARTIAL  => 'The upload was interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE  => 'Please choose a photo to upload.',
                default             => 'The server could not receive this file.',
            });
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($tmp)) {
            throw new UploadException('The server could not receive this file.');
        }
        $name = is_string($file['name'] ?? null) ? basename($file['name']) : null;

        return self::fromPath($tmp, $section, $name);
    }

    /**
     * @return array{path:string,path_sm:string,original_name:?string,mime_type:string,size_bytes:int,width:int,height:int}
     * @throws UploadException
     */
    public static function fromPath(string $src, string $section, ?string $originalName): array
    {
        if (Sections::get($section) === null) {
            throw new UploadException('Unknown section.');
        }
        $size = (int) @filesize($src);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new UploadException('Photos must be under ' . (self::MAX_BYTES / 1024 / 1024) . ' MB.');
        }

        // Trust the file's bytes, not its name or the browser's claimed type.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($src) ?: '';
        if (!isset(self::TYPES[$mime])) {
            throw new UploadException('Only JPEG, PNG or WebP photos can be uploaded.');
        }
        $info = @getimagesize($src);
        if ($info === false || $info[0] < 1 || $info[1] < 1) {
            throw new UploadException('This file could not be read as an image.');
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new UploadException('This photo is too large in pixels. Please resize it to under 40 megapixels.');
        }
        if ($info[0] < 320 || $info[1] < 200) {
            throw new UploadException('This photo is too small; please use one at least 320 pixels wide.');
        }

        $previousLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');   // decoding a large phone photo needs room
        try {
            $img = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($src),
                'image/png'  => @imagecreatefrompng($src),
                'image/webp' => @imagecreatefromwebp($src),
            };
            if (!$img instanceof GdImage) {
                throw new UploadException('This file could not be read as an image.');
            }
            $img = self::orient($img, $src, $mime);

            $dir = self::uploadsDir() . '/' . $section;
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new UploadException('The server could not save the photo (uploads folder is not writable).');
            }

            $base  = bin2hex(random_bytes(8));
            $paths = [];
            $dims  = [];
            foreach (self::SIZES as $tag => $maxW) {
                $w   = imagesx($img);
                $h   = imagesy($img);
                $tw  = min($maxW, $w);
                $th  = max(1, (int) round($h * $tw / $w));
                $dst = imagecreatetruecolor($tw, $th);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
                $file = "{$dir}/{$base}-{$tag}.webp";
                if (!imagewebp($dst, $file, $tag === 'sm' ? 70 : 76)) {
                    foreach ($paths as $p) {
                        @unlink(self::uploadsDir() . substr($p, strlen('/uploads')));
                    }
                    throw new UploadException('The server could not save the photo.');
                }
                $paths[$tag] = "/uploads/{$section}/{$base}-{$tag}.webp";
                $dims[$tag]  = [$tw, $th];
            }
        } finally {
            ini_set('memory_limit', (string) $previousLimit);
        }

        return [
            'path'          => $paths['lg'],
            'path_sm'       => $paths['sm'],
            'original_name' => $originalName,
            'mime_type'     => 'image/webp',
            'size_bytes'    => (int) filesize(self::uploadsDir() . substr($paths['lg'], strlen('/uploads'))),
            'width'         => $dims['lg'][0],
            'height'        => $dims['lg'][1],
        ];
    }

    /** Delete an image's files. Only ever touches files inside public/uploads. */
    public static function deleteFiles(?string ...$paths): void
    {
        $root = realpath(self::uploadsDir());
        if ($root === false) {
            return;
        }
        foreach ($paths as $p) {
            if ($p === null || !str_starts_with($p, '/uploads/') || str_contains($p, '..')) {
                continue;
            }
            $real = realpath($root . substr($p, strlen('/uploads')));
            if ($real !== false && str_starts_with($real, $root . DIRECTORY_SEPARATOR) && is_file($real)) {
                @unlink($real);
            }
        }
    }

    public static function serverLimit(): string
    {
        $bytes = min(self::iniBytes('upload_max_filesize'), self::iniBytes('post_max_size'), self::MAX_BYTES);

        return round($bytes / 1024 / 1024, 1) . ' MB';
    }

    public static function iniBytes(string $key): int
    {
        $v    = trim((string) ini_get($key));
        $num  = (int) $v;
        $unit = strtolower(substr($v, -1));

        return match ($unit) {
            'g' => $num * 1024 ** 3,
            'm' => $num * 1024 ** 2,
            'k' => $num * 1024,
            default => $num,
        } ?: PHP_INT_MAX;
    }

    /** Rotate according to the camera's EXIF orientation (JPEG only), so portraits stand upright. */
    private static function orient(GdImage $img, string $src, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $img;
        }
        $exif = @exif_read_data($src);
        $deg  = [3 => 180, 6 => -90, 8 => 90][is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1] ?? 0;
        if ($deg === 0) {
            return $img;
        }
        $rotated = imagerotate($img, $deg, 0);

        return $rotated instanceof GdImage ? $rotated : $img;
    }
}
