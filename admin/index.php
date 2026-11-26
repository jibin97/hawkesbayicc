<?php

/** GET /admin — admin home (dashboard). */

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

try {
    $newCounts = InquiryRepository::newCounts();
} catch (PDOException $e) {
    error_log('Dashboard: inquiries unavailable: ' . $e->getMessage());
    $newCounts = null;
}

echo View::render('admin/dashboard', [
    'title'     => 'Dashboard',
    'user'      => $user,
    'csrf'      => Session::csrfToken(),
    'active'    => 'dashboard',
    'newCounts' => $newCounts,
], 'admin/layout');
