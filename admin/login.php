<?php

/** GET|POST /admin/login — the one admin page that does not require a logged-in user. */

declare(strict_types=1);

use App\Auth\Auth;
use App\Http\Session;
use App\View;

if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

Session::start();
admin_headers();

if (Auth::user() !== null) {
    redirect('/admin');
}

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
        http_response_code(400);
        $error = 'Your session expired. Please try again.';
    } else {
        $email    = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        $wellFormed = $email !== ''
            && strlen($email) <= 191
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && $password !== ''
            && strlen($password) <= 72;

        try {
            if ($wellFormed && Auth::attempt($email, $password)) {
                redirect('/admin');
            }
            http_response_code(401);
            $error = 'Invalid email or password.';
        } catch (PDOException $e) {
            error_log('Admin login DB error: ' . $e->getMessage());
            http_response_code(503);
            $error = 'Login is temporarily unavailable. Please try again later.';
        }
    }
}

echo View::render('admin/login', [
    'title'  => 'Log in',
    'csrf'   => Session::csrfToken(),
    'email'  => $email,
    'error'  => $error,
    'notice' => Session::pullFlash(),
], 'admin/layout');
