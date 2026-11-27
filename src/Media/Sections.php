<?php

declare(strict_types=1);

namespace App\Media;

use App\Content;

/**
 * Where an image can be placed on the public site. This whitelist is the only source of valid
 * section/item values: anything submitted from the admin is checked against it.
 *
 * 'single'  sections hold one image (uploading replaces it); 'gallery' sections hold many, in order.
 * 'items'   names the content list an image belongs to (e.g. which production), or null.
 */
final class Sections
{
    /** @return array<string, array{label:string, group:string, kind:'single'|'gallery', items:?string, fit:'cover'|'contain', help:string}> */
    public static function all(): array
    {
        return [
            'home-hero'          => ['label' => 'Home page hero',     'group' => 'Page heroes', 'kind' => 'single',  'items' => null,          'fit' => 'cover',   'help' => 'Full-width background behind the home page heading. Landscape, at least 1600px wide; it is darkened so text stays readable.'],
            'academy-hero'       => ['label' => 'Academy hero',       'group' => 'Page heroes', 'kind' => 'single',  'items' => null,          'fit' => 'cover',   'help' => 'Beside the Academy page heading, cropped to 3:2 landscape.'],
            'music-hero'         => ['label' => 'Live Music hero',    'group' => 'Page heroes', 'kind' => 'single',  'items' => null,          'fit' => 'cover',   'help' => 'Beside the Live Music page heading, cropped to 3:2 landscape.'],
            'team-group'         => ['label' => 'Team group photo',   'group' => 'Team',        'kind' => 'single',  'items' => null,          'fit' => 'contain', 'help' => 'Above the team cards on the About page. Shown whole (not cropped).'],
            'team-photo'         => ['label' => 'Team member photo',  'group' => 'Team',        'kind' => 'single',  'items' => 'team',        'fit' => 'cover',   'help' => 'Round portrait on the team card and in the bio pop-up. Face centred, square-ish works best.'],
            'production-cover'   => ['label' => 'Production cover',   'group' => 'Productions', 'kind' => 'single',  'items' => 'productions', 'fit' => 'contain', 'help' => 'Poster or key image on the Productions page and the home page showcase (4:5 portrait slot).'],
            'production-gallery' => ['label' => 'Production gallery', 'group' => 'Productions', 'kind' => 'gallery', 'items' => 'productions', 'fit' => 'cover',   'help' => 'Photo gallery under the production. The first 8 show straight away; the rest behind "Show more".'],
            'made-gallery'       => ['label' => 'M.A.D.E gallery',    'group' => 'Events',      'kind' => 'gallery', 'items' => 'made',        'fit' => 'cover',   'help' => 'Photos for that year in the Events archive. The first photo is the card cover.'],
            'band-gallery'       => ['label' => 'Band photos',        'group' => 'Live Music',  'kind' => 'gallery', 'items' => null,          'fit' => 'cover',   'help' => 'Photo gallery on the Live Music page. The first photo is also the band card on the home page.'],
        ];
    }

    /** @return array{label:string, group:string, kind:string, items:?string, fit:string, help:string}|null */
    public static function get(string $section): ?array
    {
        return self::all()[$section] ?? null;
    }

    /**
     * The choices for a section's item dropdown (key => label), from the content files.
     *
     * @return array<string, string>
     */
    public static function items(?string $list): array
    {
        return match ($list) {
            'team'        => array_column(Content::get('team'), 'name', 'slug'),
            'productions' => array_column(Content::get('productions'), 'title', 'slug'),
            'made'        => array_combine(
                array_map('strval', array_column(Content::get('events')['archive'], 'year')),
                array_column(Content::get('events')['archive'], 'title')
            ),
            default       => [],
        };
    }

    /** True when $section exists and $item is valid for it (null for sections without items). */
    public static function isValid(string $section, ?string $item): bool
    {
        $s = self::get($section);
        if ($s === null) {
            return false;
        }
        if ($s['items'] === null) {
            return $item === null;
        }

        return $item !== null && array_key_exists($item, self::items($s['items']));
    }

    /**
     * Every placement as one flat list for the admin dropdown: value "section" or "section:item".
     *
     * @return array<string, list<array{value:string, label:string}>> grouped by 'group'
     */
    public static function placements(): array
    {
        $out = [];
        foreach (self::all() as $key => $s) {
            if ($s['items'] === null) {
                $out[$s['group']][] = ['value' => $key, 'label' => $s['label']];
                continue;
            }
            foreach (self::items($s['items']) as $itemKey => $itemLabel) {
                $out[$s['group']][] = ['value' => $key . ':' . $itemKey, 'label' => $s['label'] . ' — ' . $itemLabel];
            }
        }

        return $out;
    }

    /** "section:item" → [section, item|null], or null if not a valid placement. */
    public static function parse(mixed $value): ?array
    {
        if (!is_string($value) || strlen($value) > 120) {
            return null;
        }
        [$section, $item] = array_pad(explode(':', $value, 2), 2, null);

        return self::isValid($section, $item) ? [$section, $item] : null;
    }
}
