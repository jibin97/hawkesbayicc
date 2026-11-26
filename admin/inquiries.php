<?php

/** GET /admin/inquiries: inbox list with type/status filters and pagination. */

declare(strict_types=1);

use App\Auth\Auth;
use App\Http\Session;
use App\Inquiries\InquiryRepository;
use App\View;

if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

Session::start();
admin_headers();
$user = Auth::requireLogin();

// Whitelisted filters: anything else is rejected (falls back to "all").
$type   = is_string($_GET['type'] ?? null) && in_array($_GET['type'], InquiryRepository::TYPES, true) ? $_GET['type'] : null;
$status = is_string($_GET['status'] ?? null) && in_array($_GET['status'], InquiryRepository::STATUSES, true) ? $_GET['status'] : null;
$page   = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]) ?: 1;

$perPage = 25;
$result  = InquiryRepository::paginate($type, $status, $page, $perPage);

echo View::render('admin/inquiries', [
    'title'     => 'Inquiries',
    'user'      => $user,
    'csrf'      => Session::csrfToken(),
    'active'    => 'inquiries',
    'rows'      => $result['rows'],
    'total'     => $result['total'],
    'page'      => $page,
    'pages'     => max(1, (int) ceil($result['total'] / $perPage)),
    'type'      => $type,
    'status'    => $status,
    'newCounts' => InquiryRepository::newCounts(),
    'notice'    => Session::pullFlash(),
], 'admin/layout');
