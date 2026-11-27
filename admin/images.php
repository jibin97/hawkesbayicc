<?php

/** GET /admin/images?place=section[:item] — manage the photos in one place on the site. */

declare(strict_types=1);

use App\Auth\Auth;
use App\Http\Session;
use App\Media\ImageRepository;
use App\Media\ImageStore;
use App\Media\Sections;
use App\View;

if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

Session::start();
admin_headers();
$user = Auth::requireLogin();

$placements = Sections::placements();
$first      = $placements[array_key_first($placements)][0]['value'];
$placeValue = is_string($_GET['place'] ?? null) ? $_GET['place'] : $first;
$place      = Sections::parse($placeValue);
if ($place === null) {
    $placeValue = $first;
    $place      = Sections::parse($first);
}
[$section, $item] = $place;

$dbError = null;
try {
    $images = ImageRepository::forPlacement($section, $item);
    $counts = ImageRepository::counts();
} catch (PDOException $e) {
    error_log('Admin images: ' . $e->getMessage());
    $images  = [];
    $counts  = [];
    $dbError = 'The images table is not ready. Run `php bin/migrate.php` (see INSTALL.md).';
}

$itemLabel = $item !== null ? (Sections::items(Sections::get($section)['items'])[$item] ?? $item) : null;

echo View::render('admin/images', [
    'title'      => 'Images',
    'user'       => $user,
    'csrf'       => Session::csrfToken(),
    'active'     => 'images',
    'notice'     => Session::pullFlash(),
    'placements' => $placements,
    'placeValue' => $placeValue,
    'section'    => $section,
    'sectionDef' => Sections::get($section),
    'itemLabel'  => $itemLabel,
    'images'     => $images,
    'counts'     => $counts,
    'dbError'    => $dbError,
    'maxUpload'  => ImageStore::serverLimit(),
], 'admin/layout');
