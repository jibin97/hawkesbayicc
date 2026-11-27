<?php

/**
 * Front controller: every request that isn't a real file under public/ comes here.
 */

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/bootstrap.php';

use App\Forms\FormHandler;
use App\Http\Router;
use App\View;

$router = new Router();

// ---------- Public pages: path => [template in views/public/pages, <title>, meta description] ----------
$pages = [
    '/'            => ['home', null, "Indian classical dance classes, theatre productions and the annual M.A.D.E for a Cause charity showcase in Hawke's Bay, New Zealand."],
    '/academy'     => ['academy', 'Academy & Enrolment', "Indian classical dance classes for children and adults in Hastings, Napier and Havelock North. Enrol online."],
    '/productions' => ['productions', 'Krishni Productions', "Krishni Productions: Indian epic dance dramas staged in Hawke's Bay, including Shakuntala, Sita Unsung, Takshaka and Wairua Paani."],
    '/music'       => ['music', 'Dr. Wuts & The Bindaas Bollywood Band', "Book Dr. Wuts & The Bindaas Bollywood Band, Hawke's Bay's live Bollywood band led by saxophonist and composer Anton Wuts."],
    '/events'      => ['events', 'Events & M.A.D.E for a Cause', "Upcoming HBICC shows and the M.A.D.E (Music and Dance Event) for a Cause charity showcase archive, 2017 to today."],
    '/about'       => ['about', 'About & Team', "The story and people behind the Hawke's Bay Indian Cultural Centre, founded in 2015 by Deepthi Krishna and Sreejith Nair."],
    '/contact'     => ['contact', 'Contact', "Contact the Hawke's Bay Indian Cultural Centre: call 021 1642979 or send us a message."],
];

// Pages that post a form back to themselves: path => [form name, section anchor]
$forms = [
    '/academy' => ['enrolment', 'enrol'],
    '/music'   => ['booking', 'book'],
    '/contact' => ['contact', 'message'],
];

foreach ($pages as $path => [$template, $title, $description]) {
    $form    = $forms[$path][0] ?? null;
    $anchor  = $forms[$path][1] ?? null;
    $methods = $form !== null ? ['GET', 'HEAD', 'POST'] : ['GET', 'HEAD'];

    $router->add($path, $methods, static function () use ($template, $title, $description, $path, $form, $anchor): void {
        public_headers();
        $formState = null;

        if ($form !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = FormHandler::handle($form, $_POST, (string) ($_SERVER['REMOTE_ADDR'] ?? ''));

            if ($result['status'] === 'sent') {
                // Post/Redirect/Get, then send the email alert after the visitor has their response.
                header('Location: ' . $path . '?sent=' . $form . '#' . $anchor, true, 303);
                header('Content-Length: 0');
                header('Connection: close');
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                } else {
                    ignore_user_abort(true);
                    flush();
                }
                if ($result['after'] !== null) {
                    ($result['after'])();
                }
                return;
            }

            http_response_code(422);
            header('Cache-Control: no-store');
            $formState = $result;
        } else {
            header('Cache-Control: public, max-age=300');
            if ($form !== null && ($_GET['sent'] ?? null) === $form) {
                $formState = ['status' => 'sent'];
                header('Cache-Control: no-store');
            }
        }

        echo View::page($template, [
            'title'       => $title,
            'description' => $description,
            'path'        => $path,
            'formState'   => $formState,
            'formAction'  => $form !== null ? $path . '#' . $anchor : null,
        ]);
    });
}

// ---------- Permanent redirects from the old WordPress site's URLs (keeps search rankings & links) ----------
$router->redirect('/team', '/about#team');
$router->redirect('/enrolment', '/academy#enrol');
$router->redirect('/upcoming-events', '/events#upcoming');
foreach (['shakuntala', 'sita-unsung', 'takshaka', 'wairua-paani'] as $slug) {
    $router->redirect('/productions/gallery/' . $slug, '/productions#' . $slug);
}
foreach ([2017, 2018, 2019, 2021, 2023, 2025] as $year) {
    $router->redirect('/events/gallery/made-' . $year, '/events#made-' . $year);
}

// ---------- Admin ----------
$admin = static fn (string $file): callable => static function () use ($file): void {
    require APP_ROOT . '/admin/' . $file;
};
$router->add('/admin',        ['GET', 'HEAD'],         $admin('index.php'));
$router->add('/admin/login',  ['GET', 'HEAD', 'POST'], $admin('login.php'));
$router->add('/admin/logout', ['POST'],                $admin('logout.php'));
$router->add('/admin/inquiries',      ['GET', 'HEAD'], $admin('inquiries.php'));
$router->add('/admin/inquiry',        ['GET', 'HEAD'], $admin('inquiry.php'));
$router->add('/admin/inquiry/status', ['POST'],        $admin('inquiry-status.php'));
$router->add('/admin/images',         ['GET', 'HEAD'], $admin('images.php'));
$router->add('/admin/images/action',  ['POST'],        $admin('image-action.php'));

// ---------- Dispatch ----------
try {
    $router->dispatch(
        strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
        (string) ($_SERVER['REQUEST_URI'] ?? '/'),
        static function (): void {
            public_headers();
            echo View::page('404', ['title' => 'Page not found', 'description' => '', 'path' => '']);
        }
    );
} catch (Throwable $e) {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    echo "Sorry, something went wrong. Please try again later.\n";
}
