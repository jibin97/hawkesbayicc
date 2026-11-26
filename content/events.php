<?php

/**
 * Events & M.A.D.E for a Cause.
 *
 * 'upcoming': add real events here. Each needs at least title + date (Y-m-d). When the list is empty,
 * pages show a "next show to be announced" state. Past dates are hidden automatically.
 *   ['title' => '', 'date' => '2027-06-12', 'time' => '7:00pm', 'venue' => '', 'town' => 'Hastings',
 *    'charity' => '', 'summary' => '', 'ticket_url' => 'https://…']
 *
 * 'archive': CONFIRM charity and venue per year (the old site doesn't say). gallery_count = old site's photos.
 */

declare(strict_types=1);

return [
    'upcoming' => [],

    'charities' => ['Cancer Society', 'Kidney Kids', 'Heart Kids', 'Resource HB'],

    'archive' => [
        ['year' => 2025, 'title' => 'M.A.D.E for a Cause 2025', 'featuring' => 'Wairua Paani', 'charity' => null, 'venue' => null, 'gallery_count' => 10],
        ['year' => 2023, 'title' => 'M.A.D.E for a Cause 2023', 'featuring' => null, 'charity' => null, 'venue' => null, 'gallery_count' => 7],
        ['year' => 2021, 'title' => 'M.A.D.E for a Cause 2021', 'featuring' => null, 'charity' => null, 'venue' => null, 'gallery_count' => 17],
        ['year' => 2019, 'title' => 'M.A.D.E for a Cause 2019', 'featuring' => null, 'charity' => null, 'venue' => null, 'gallery_count' => 18],
        ['year' => 2018, 'title' => 'M.A.D.E for a Cause 2018', 'featuring' => null, 'charity' => null, 'venue' => null, 'gallery_count' => 24],
        ['year' => 2017, 'title' => 'M.A.D.E for a Cause 2017', 'featuring' => null, 'charity' => null, 'venue' => null, 'gallery_count' => 24, 'note' => 'The first M.A.D.E for a Cause.'],
    ],
];
