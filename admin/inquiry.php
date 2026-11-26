<?php

/** GET /admin/inquiry?id=N: one inquiry in full. Opening a new inquiry marks it as read. */

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

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$inquiry = $id !== false && $id !== null ? InquiryRepository::find($id) : null;

if ($inquiry === null) {
    http_response_code(404);
    echo View::render('admin/not-found', [
        'title' => 'Not found', 'user' => $user, 'csrf' => Session::csrfToken(), 'active' => 'inquiries',
    ], 'admin/layout');
    return;
}

if ($inquiry['status'] === 'new') {
    InquiryRepository::setStatus((int) $inquiry['id'], 'read');
    $inquiry['status'] = 'read';
}

echo View::render('admin/inquiry', [
    'title'   => 'Inquiry #' . $inquiry['id'],
    'user'    => $user,
    'csrf'    => Session::csrfToken(),
    'active'  => 'inquiries',
    'inquiry' => $inquiry,
    'notice'  => Session::pullFlash(),
], 'admin/layout');
