<?php

/** POST /admin/logout */

declare(strict_types=1);

use App\Auth\Auth;
use App\Http\Session;

if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

Session::start();
admin_headers();
Auth::requireLogin();

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    redirect('/admin');
}

Auth::logout();

// Fresh, empty session just to carry the confirmation message.
Session::start();
Session::setFlash('You have been logged out.');

redirect('/admin/login');
