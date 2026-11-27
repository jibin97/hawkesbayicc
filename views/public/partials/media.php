<?php
/**
 * Image slot with a fixed aspect ratio (no layout shift). Shows a decorative placeholder when no image is given.
 *
 * @var array|null  $image  From image(): w, h, sm, lg, alt, fit (admin-managed)
 * @var string|null $alt    Override the image's own alt text; '' = decorative
 * @var string      $ratio  One of: 16x9, 3x2, 4x5, 1x1
 * @var string      $sizes  <img sizes>: how wide the slot is on screen
 * @var string|null $fit    Override: 'cover' (crop to the ratio) or 'contain' (whole image); default from the image
 * @var string      $label  Short caption shown on the placeholder only
 * @var bool        $eager  Load immediately (above-the-fold images)
 */
use App\View;

$image ??= null;
$alt   ??= $image['alt'] ?? '';
$ratio ??= '16x9';
$sizes ??= '(min-width: 64em) 40vw, 100vw';
$fit   ??= $image['fit'] ?? 'cover';
$label ??= '';
$eager ??= false;
?>
<?php if ($image !== null): ?>
    <figure class="media media--<?= e($ratio) ?><?= $fit === 'contain' ? ' media--contain' : '' ?>">
        <?= View::partial('public/partials/img', ['image' => $image, 'alt' => $alt, 'sizes' => $sizes, 'eager' => $eager]) ?>
    </figure>
<?php else: ?>
    <div class="media media--<?= e($ratio) ?> media--placeholder" aria-hidden="true">
        <span class="media__ornament"></span>
        <?php if ($label !== ''): ?><span class="media__label"><?= e($label) ?></span><?php endif; ?>
    </div>
<?php endif; ?>
