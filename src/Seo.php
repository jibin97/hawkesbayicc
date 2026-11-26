<?php

declare(strict_types=1);

namespace App;

/**
 * Per-page search and social-sharing data: canonical URL, share image, and schema.org JSON-LD.
 * Everything is built from the content/ files, so it stays in step with what the page shows.
 */
final class Seo
{
    /** Share image (1200×630 JPEG in /assets/img/og/) for each page; others use the default. */
    private const OG_IMAGES = [
        '/academy'     => 'academy',
        '/productions' => 'productions',
        '/music'       => 'music',
        '/events'      => 'events',
        '/about'       => 'about',
    ];

    public static function ogImage(string $path): string
    {
        return abs_url('/assets/img/og/' . (self::OG_IMAGES[$path] ?? 'default') . '.jpg');
    }

    /** Canonical URL: home is "/", other pages have no trailing slash. */
    public static function canonical(string $path): string
    {
        return abs_url($path === '' ? '/' : $path);
    }

    /**
     * JSON-LD blocks for a page.
     *
     * @param array<string, mixed> $site
     * @return list<array<string, mixed>>
     */
    public static function jsonLd(string $path, array $site): array
    {
        $org = [
            '@type'        => 'Organization',
            '@id'          => abs_url('/#organization'),
            'name'         => $site['name'],
            'alternateName' => $site['short_name'],
            'url'          => abs_url('/'),
            'description'  => $site['summary'],
            'foundingDate' => (string) $site['founded'],
            'telephone'    => $site['contact']['phone_tel'],
            'email'        => $site['contact']['email'],
            'address'      => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Hastings',
                'addressRegion'   => "Hawke's Bay",
                'addressCountry'  => 'NZ',
            ],
            'areaServed' => array_map(static fn (string $town): array => ['@type' => 'City', 'name' => $town], $site['contact']['service_areas']),
            'sameAs'     => array_column($site['social'], 'url'),
            'image'      => self::ogImage('/'),
        ];

        $blocks = [];
        if ($path === '/' || $path === '/about' || $path === '/contact') {
            $blocks[] = $org;
        }

        if ($path === '/academy') {
            $faqs = Content::get('academy')['faqs'];
            $blocks[] = [
                '@type'      => 'FAQPage',
                'mainEntity' => array_map(static fn (array $f): array => [
                    '@type'          => 'Question',
                    'name'           => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                ], $faqs),
            ];
        }

        if ($path === '/music') {
            $band = Content::get('music');
            $blocks[] = [
                '@type'        => 'MusicGroup',
                'name'         => $band['name'],
                'url'          => abs_url('/music'),
                'foundingDate' => (string) $band['founded'],
                'genre'        => ['Bollywood', 'Jazz'],
                'description'  => $band['intro'],
                'image'        => self::ogImage('/music'),
                'parentOrganization' => ['@id' => abs_url('/#organization')],
            ];
        }

        if ($path === '/events' || $path === '/') {
            foreach (upcoming_events(Content::get('events')['upcoming']) as $ev) {
                $event = [
                    '@type'       => 'Event',
                    'name'        => $ev['title'],
                    'startDate'   => $ev['date'],
                    'eventStatus' => 'https://schema.org/EventScheduled',
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location'    => [
                        '@type'   => 'Place',
                        'name'    => ($ev['venue'] ?? '') !== '' ? $ev['venue'] : ($ev['town'] ?? "Hawke's Bay"),
                        'address' => ['@type' => 'PostalAddress', 'addressLocality' => $ev['town'] ?? "Hawke's Bay", 'addressCountry' => 'NZ'],
                    ],
                    'organizer'   => ['@id' => abs_url('/#organization')],
                    'image'       => self::ogImage('/events'),
                ];
                if (($ev['summary'] ?? '') !== '') {
                    $event['description'] = $ev['summary'];
                }
                if (($ev['ticket_url'] ?? '') !== '') {
                    $event['offers'] = ['@type' => 'Offer', 'url' => $ev['ticket_url'], 'availability' => 'https://schema.org/InStock'];
                }
                $blocks[] = $event;
            }
        }

        // Org is referenced by @id from other blocks: make sure it's defined on the page once.
        if ($blocks !== [] && !in_array($org, $blocks, true)) {
            foreach ($blocks as $b) {
                if (str_contains((string) json_encode($b), '#organization')) {
                    $blocks[] = $org;
                    break;
                }
            }
        }

        return array_map(static fn (array $b): array => ['@context' => 'https://schema.org'] + $b, $blocks);
    }

    /** Safe to print inside <script type="application/ld+json">: "<" etc. are escaped. */
    public static function encode(array $block): string
    {
        return (string) json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    }
}
