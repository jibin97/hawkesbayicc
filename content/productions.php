<?php

/**
 * Krishni Productions. Facts from the old About/Team pages; anything unknown is null and the
 * template shows a graceful "coming soon" state instead.
 *
 * Years, venues and partners below are from the productions' own posters (old site galleries, checked 2026-11).
 * CONFIRM for each: full synopsis, full credits, YouTube trailer id.
 * Cover and gallery photos are managed in the admin (/admin/images), keyed by slug.
 */

declare(strict_types=1);

return [
    [
        'slug'      => 'shakuntala',
        'title'     => 'Shakuntala',
        'year'      => 2019,
        'kicker'    => 'Our first Indian epic dance drama',
        'synopsis'  => [
            "Krishni Productions began with Shakuntala, HBICC's first Indian epic dance drama, staged in 2019 in collaboration with Cook Place Productions.",
            // CONFIRM: add the production's own synopsis here.
        ],
        'credits'   => [
            ['role' => 'Director',   'name' => 'Ken Keys'],
            ['role' => 'Playwright', 'name' => 'Pauline Ellen Hayes'],
            ['role' => 'Producer & dancer', 'name' => 'Deepthi Krishna'],
        ],
        'partners'      => ['Cook Place Productions'],
        'youtube_id'    => null,
    ],
    [
        'slug'      => 'sita-unsung',
        'title'     => 'Sita Unsung',
        'year'      => 2021,
        'kicker'    => 'Epic Indian dance drama with aerial theatre',
        'synopsis'  => [
            'Sita Unsung followed Shakuntala as a Krishni Productions theatrical drama, produced by Deepthi Krishna and directed by Ken Keys.',
        ],
        'credits'   => [
            ['role' => 'Director', 'name' => 'Ken Keys'],
            ['role' => 'Producer', 'name' => 'Deepthi Krishna'],
        ],
        'partners'      => ["Hawke's Bay Flying Trapeze"],
        'youtube_id'    => null,
    ],
    [
        'slug'      => 'takshaka',
        'title'     => 'Takshaka – King of the Snakes',
        'year'      => 2023,
        'kicker'    => "With Hawke's Bay Flying Trapeze",
        'synopsis'  => [
            "Takshaka – King of the Snakes was staged by Krishni Productions in collaboration with Hawke's Bay Flying Trapeze, directed by Ken Keys with music in part by Anton Wuts.",
        ],
        'credits'   => [
            ['role' => 'Director', 'name' => 'Ken Keys'],
            ['role' => 'Producer', 'name' => 'Deepthi Krishna'],
            ['role' => 'Music (in part)', 'name' => 'Anton Wuts'],
        ],
        'partners'      => ["Hawke's Bay Flying Trapeze"],
        'youtube_id'    => null,
    ],
    [
        'slug'      => 'wairua-paani',
        'title'     => 'Wairua Paani',
        'year'      => 2025,
        'kicker'    => 'Featured at M.A.D.E for a Cause 2025',
        'synopsis'  => [
            'Wairua Paani was the featured production at M.A.D.E for a Cause 2025.',
        ],
        'credits'       => [],   // CONFIRM
        'partners'      => ["Hawke's Bay Flying Trapeze", 'Wahanui Productions'],
        'youtube_id'    => null,
    ],
];
