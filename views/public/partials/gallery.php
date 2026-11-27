<?php
/**
 * Photo gallery: a masonry grid of thumbnails, each linking to the large image.
 * Without JavaScript the links simply open the photo; site.js turns them into a lightbox
 * (keyboard, swipe, prev/next) covering every photo in the same [data-gallery].
 *
 * @var list<array>          $images  From images(): w, h, sm, lg, alt
 * @var string               $title   Gallery name, used in alt text and the lightbox caption
 * @var int                  $visible How many thumbnails show before "Show all" (0 = all hidden behind the button)
 * @var string               $id      Unique id prefix for this gallery
 */
$visible ??= 6;
$images   = array_values($images);
$total    = count($images);
if ($total === 0) {
    return;
}
$shown  = array_slice($images, 0, $visible);
$hidden = array_slice($images, $visible);

$thumb = static function (array $img, int $n) use ($title, $total): string {
    $alt = ($img['alt'] !== '' ? $img['alt'] : $title) . ", photo {$n} of {$total}";
    $w   = min(640, (int) $img['w']);
    $h   = (int) round($img['h'] * $w / $img['w']);
    return '<li><a class="gallery__link" href="' . e($img['lg']) . '" data-lightbox'
        . ' data-w="' . (int) $img['w'] . '" data-h="' . (int) $img['h'] . '">'
        . '<img src="' . e($img['sm']) . '" alt="' . e($alt) . '" width="' . $w . '" height="' . $h . '" loading="lazy" decoding="async">'
        . '</a></li>';
};
?>
<div class="gallery" data-gallery="<?= e($title) ?>" id="<?= e($id) ?>">
    <?php if ($shown !== []): ?>
        <ul class="gallery__grid">
            <?php foreach ($shown as $i => $img): ?><?= $thumb($img, $i + 1) ?><?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($hidden !== []): ?>
        <details class="gallery__more">
            <summary><?= $shown === [] ? 'View all ' . $total . ' photos' : 'Show ' . count($hidden) . ' more photos' ?></summary>
            <ul class="gallery__grid">
                <?php foreach ($hidden as $i => $img): ?><?= $thumb($img, $visible + $i + 1) ?><?php endforeach; ?>
            </ul>
        </details>
    <?php endif; ?>
</div>
