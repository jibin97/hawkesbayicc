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

foreach ($pages as $path => [$template, $title, $description]) {
    $router->add($path, ['GET', 'HEAD'], static function () use ($template, $title, $description, $path): void {
        public_headers();
        header('Cache-Control: public, max-age=300');
        echo View::page($template, [
            'title'       => $title,
            'description' => $description,
            'path'        => $path,
        ]);
    });
}

// ---------- Admin ----------
$admin = static fn (string $file): callable => static function () use ($file): void {
    require APP_ROOT . '/admin/' . $file;
};
$router->add('/admin',        ['GET', 'HEAD'],         $admin('index.php'));
$router->add('/admin/login',  ['GET', 'HEAD', 'POST'], $admin('login.php'));
$router->add('/admin/logout', ['POST'],                $admin('logout.php'));

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
