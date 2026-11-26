<?php

/**
 * Site-wide content: organisation details, contact, social links and navigation.
 * Edit here; templates read it via App\Content::get('site').
 *
 * Values marked CONFIRM came from the revamp spec but could not be verified on the old site.
 */

declare(strict_types=1);

return [
    'name'       => "Hawke's Bay Indian Cultural Centre",
    'short_name' => 'HBICC',
    'founded'    => 2015,
    'tagline'    => 'Art has the power to Transform, Illuminate, Educate, Inspire and Motivate!',
    'summary'    => "Indian classical dance, theatre and music in Hawke's Bay, Aotearoa New Zealand, since 2015.",
    'url'        => 'https://hawkesbayicc.nz',   // canonical base for SEO links (no trailing slash)

    'contact' => [
        'phone_display' => '021 1642979',
        'phone_tel'     => '+64211642979',
        'email'         => 'info@hawkesbayicc.nz',   // CONFIRM: obfuscated on the old site
        'location'      => "Hastings, Hawke's Bay, New Zealand",
        'service_areas' => ['Napier', 'Hastings', 'Havelock North'],
    ],

    'social' => [
        ['label' => 'Facebook',  'icon' => 'facebook',  'url' => 'https://www.facebook.com/hawkesbayiccnz'],
        ['label' => 'Instagram', 'icon' => 'instagram', 'url' => 'https://www.instagram.com/hawkesbayicc/'],
        ['label' => 'YouTube',   'icon' => 'youtube',   'url' => 'https://www.youtube.com/channel/UCoHyl4kt1_OtW8QnMsTt7CA'],
    ],

    // Main navigation, in order. 'path' must match a route in public/index.php.
    'nav' => [
        ['label' => 'Home',        'path' => '/'],
        ['label' => 'Academy',     'path' => '/academy'],
        ['label' => 'Productions', 'path' => '/productions'],
        ['label' => 'Live Music',  'path' => '/music'],
        ['label' => 'Events',      'path' => '/events'],
        ['label' => 'About',       'path' => '/about'],
        ['label' => 'Contact',     'path' => '/contact'],
    ],

    'cta' => ['label' => 'Enrol Now', 'path' => '/academy#enrol'],
];
