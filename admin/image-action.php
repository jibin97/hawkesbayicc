<?php

/**
 * POST /admin/images/action — every change to site photos.
 * action: upload | save | up | down | replace | delete. Always: login, CSRF, whitelisted values.
 */

declare(strict_types=1);

use App\Auth\Auth;
use App\Http\Session;
use App\Media\ImageRepository;
use App\Media\ImageStore;
use App\Media\Sections;
use App\Media\UploadException;

if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

// Development only (display_errors on): PHP already printed its "POST too large" warning, so no session or
// redirect is possible. Show a fixed message; nothing is processed.
if (headers_sent() && ($_POST === []) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > ImageStore::iniBytes('post_max_size')) {
    echo '<p>Those files are too large to upload together. Try fewer or smaller photos.</p><p><a href="/admin/images">Back to Images</a></p>';
    exit;
}

Session::start();
admin_headers();
$user = Auth::requireLogin();

$back = static function (?string $place, ?int $id = null): never {
    redirect('/admin/images' . ($place !== null ? '?place=' . rawurlencode($place) : '') . ($id !== null ? '#image-' . $id : ''));
};

// A POST bigger than post_max_size arrives with $_POST and $_FILES empty: explain instead of "session expired".
$length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($_POST === [] && $length > ImageStore::iniBytes('post_max_size')) {
    Session::setFlash('Those files are too large to upload together (server limit ' . ImageStore::serverLimit() . '). Try fewer or smaller photos.');
    $back(null);
}

$placeValue = is_string($_POST['place'] ?? null) ? $_POST['place'] : null;
$place      = Sections::parse($placeValue);

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::setFlash('Your session expired. Please try again.');
    $back($place !== null ? $placeValue : null);
}

$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
if (!in_array($action, ['upload', 'save', 'up', 'down', 'replace', 'delete'], true) || $place === null) {
    http_response_code(400);
    Session::setFlash('That change could not be made.');
    $back(null);
}
[$section, $item] = $place;
$def = Sections::get($section);

$alt = is_string($_POST['alt'] ?? null) ? trim((string) preg_replace('/\s+/u', ' ', $_POST['alt'])) : '';
$fit = ($_POST['fit'] ?? '') === 'contain' ? 'contain' : 'cover';
$altOk = static fn (string $a): bool => $a !== '' && mb_strlen($a) <= 255 && preg_match('/[\x00-\x1F\x7F]/', $a) !== 1;

// ---------- Upload new photo(s) ----------
if ($action === 'upload') {
    if (!$altOk($alt)) {
        Session::setFlash('Please describe the photo (alt text, up to 255 characters). It is read aloud to blind visitors and used by search engines.');
        $back($placeValue);
    }
    // Normalise photos[] into a list of single-file arrays.
    $raw   = $_FILES['photos'] ?? null;
    $files = [];
    if (is_array($raw) && is_array($raw['name'] ?? null)) {
        foreach (array_keys($raw['name']) as $i) {
            if (($raw['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = ['name' => $raw['name'][$i], 'tmp_name' => $raw['tmp_name'][$i], 'error' => $raw['error'][$i], 'size' => $raw['size'][$i]];
        }
    }
    if ($files === []) {
        Session::setFlash('Please choose a photo to upload.');
        $back($placeValue);
    }
    if ($def['kind'] === 'single') {
        $files = array_slice($files, 0, 1);
    }
    if (count($files) > 20) {
        Session::setFlash('Please upload up to 20 photos at a time.');
        $back($placeValue);
    }

    $existing = $def['kind'] === 'single' ? (ImageRepository::forPlacement($section, $item)[0] ?? null) : null;
    $done     = 0;
    $errors   = [];
    $lastId   = null;
    foreach ($files as $file) {
        $label = basename((string) $file['name']);
        try {
            $stored = ImageStore::fromUpload($file, $section);
        } catch (UploadException $e) {
            $errors[] = $label . ': ' . $e->getMessage();
            continue;
        }
        try {
            if ($existing !== null) {
                // Single-image places: replace in place, then remove the old files.
                ImageRepository::replaceFiles($existing['id'], $stored, $user['id']);
                ImageRepository::updateDetails($existing['id'], $alt, $fit);
                ImageStore::deleteFiles($existing['path'], $existing['path_sm']);
                $lastId = $existing['id'];
            } else {
                $lastId = ImageRepository::create($stored + [
                    'section' => $section, 'item_key' => $item, 'alt_text' => $alt, 'fit' => $fit, 'uploaded_by' => $user['id'],
                ]);
            }
            $done++;
        } catch (PDOException $e) {
            ImageStore::deleteFiles($stored['path'], $stored['path_sm']);   // don't leave orphans
            error_log('Image save failed: ' . $e->getMessage());
            $errors[] = $label . ': could not be saved. Please try again.';
        }
    }

    $msg = $done > 0
        ? ($existing !== null ? 'Photo replaced.' : ($done === 1 ? 'Photo uploaded.' : "{$done} photos uploaded."))
        : 'No photos were uploaded.';
    if ($errors !== []) {
        $msg .= ' ' . implode(' ', $errors);
    }
    Session::setFlash($msg);
    $back($placeValue, $lastId);
}

// ---------- Actions on one existing photo ----------
$id  = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$img = is_int($id) ? ImageRepository::find($id) : null;
// The photo must belong to the place the form came from (stops edits to an unexpected id).
if ($img === null || $img['section'] !== $section || $img['item_key'] !== $item) {
    http_response_code(400);
    Session::setFlash('That photo was not found. It may have been deleted already.');
    $back($placeValue);
}

switch ($action) {
    case 'save':
        if (!$altOk($alt)) {
            Session::setFlash('Please enter alt text (up to 255 characters).');
            $back($placeValue, $id);
        }
        ImageRepository::updateDetails($id, $alt, $fit);
        Session::setFlash('Photo details saved.');
        break;

    case 'up':
    case 'down':
        ImageRepository::move($id, $action === 'up' ? -1 : 1);
        Session::setFlash($action === 'up' ? 'Moved earlier.' : 'Moved later.');
        break;

    case 'replace':
        $file = $_FILES['photo'] ?? null;
        try {
            $stored = ImageStore::fromUpload(is_array($file) && !is_array($file['name'] ?? null) ? $file : [], $section);
        } catch (UploadException $e) {
            Session::setFlash('Not replaced: ' . $e->getMessage());
            $back($placeValue, $id);
        }
        try {
            ImageRepository::replaceFiles($id, $stored, $user['id']);
        } catch (PDOException $e) {
            ImageStore::deleteFiles($stored['path'], $stored['path_sm']);
            error_log('Image replace failed: ' . $e->getMessage());
            Session::setFlash('The photo could not be replaced. Please try again.');
            $back($placeValue, $id);
        }
        ImageStore::deleteFiles($img['path'], $img['path_sm']);
        Session::setFlash('Photo replaced. Its alt text was kept: check it still describes the new photo.');
        break;

    case 'delete':
        ImageRepository::delete($id);
        ImageStore::deleteFiles($img['path'], $img['path_sm']);
        Session::setFlash('Photo deleted.');
        $back($placeValue);
}

$back($placeValue, $id);
