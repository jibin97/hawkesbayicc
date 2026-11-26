<?php

/**
 * Events & M.A.D.E for a Cause.
 *
 * 'upcoming': add real events here. Each needs at least title + date (Y-m-d). When the list is empty,
 * pages show a "next show to be announced" state. Past dates are hidden automatically.
 *   ['title' => '', 'date' => '2027-06-12', 'time' => '7:00pm', 'venue' => '', 'town' => 'Hastings',
 *    'charity' => '', 'summary' => '', 'ticket_url' => 'https://…']
 *
 * 'archive': dates and venues for 2019–2025 are from the event posters. CONFIRM the charity for 2017–2021 and 2025. Photos: content/images.php group 'made-{year}'.
 */

declare(strict_types=1);

return [
    'upcoming' => [],

    'charities' => ['Cancer Society', 'Kidney Kids', 'Heart Kids', 'Resource HB'],

    'archive' => [
        ['year' => 2025, 'title' => 'M.A.D.E for a Cause 2025', 'featuring' => 'Wairua Paani', 'charity' => null, 'venue' => "Toitoi, Hawke's Bay Arts & Events Centre", 'note' => "Celebrating 10 years of HBICC and raising awareness of cybersecurity, with Dr. Wuts & The Bindaas Bollywood Band. Saturday 26 April 2025."],
        ['year' => 2023, 'title' => 'M.A.D.E for a Cause 2023', 'featuring' => 'Takshaka – King of the Snakes', 'charity' => 'Resource HB', 'venue' => "Toitoi, Hawke's Bay Arts & Events Centre", 'note' => 'Saturday 5 August 2023, with Dr. Wuts & The Bindaas Bollywood Band.'],
        ['year' => 2021, 'title' => 'M.A.D.E for a Cause 2021', 'featuring' => 'Sita Unsung', 'charity' => null, 'venue' => "Toitoi, Hawke's Bay Arts & Events Centre", 'note' => 'Saturday 10 April 2021: a matinee and an evening show.'],
        ['year' => 2019, 'title' => 'M.A.D.E for a Cause 2019', 'featuring' => 'Shakuntala', 'charity' => null, 'venue' => 'Napier Municipal Theatre', 'note' => 'Saturday 9 March 2019: the debut of Shakuntala.'],
        ['year' => 2018, 'title' => 'M.A.D.E for a Cause 2018', 'featuring' => null, 'charity' => null, 'venue' => null],
        ['year' => 2017, 'title' => 'M.A.D.E for a Cause 2017', 'featuring' => null, 'charity' => null, 'venue' => null, 'note' => 'The first M.A.D.E for a Cause.'],
    ],
];
