<?php

/** POST /admin/inquiry/status: change an inquiry's status (new / read / archived). */

declare(strict_types=1);

use App\Auth\Auth;
use App\Http\Session;
use App\Inquiries\InquiryRepository;

if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

Session::start();
admin_headers();
Auth::requireLogin();

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::setFlash('Your session expired. Please try again.');
    redirect('/admin/inquiries');
}

$id     = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';

if ($id === false || $id === null || !in_array($status, InquiryRepository::STATUSES, true)
    || InquiryRepository::find($id) === null) {
    http_response_code(400);
    Session::setFlash('That change could not be made.');
    redirect('/admin/inquiries');
}

InquiryRepository::setStatus($id, $status);

$labels = ['new' => 'marked as unread', 'read' => 'marked as read', 'archived' => 'archived'];
Session::setFlash("Inquiry #{$id} {$labels[$status]}.");

redirect($status === 'archived' ? '/admin/inquiries' : '/admin/inquiry?id=' . $id);
