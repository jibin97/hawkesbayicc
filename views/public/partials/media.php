<?php
/**
 * Image slot with a fixed aspect ratio (no layout shift). Shows a decorative placeholder when no image is given.
 *
 * @var array|null  $image  Entry from content/images.php (via image()): w, h, sm, lg
 * @var string      $alt    Required with an image; describe the scene, not "image of". '' = decorative
 * @var string      $ratio  One of: 16x9, 3x2, 4x5, 1x1
 * @var string      $sizes  <img sizes>: how wide the slot is on screen
 * @var string      $fit    'cover' (crop to the ratio) or 'contain' (show the whole image, e.g. posters)
 * @var string      $label  Short caption shown on the placeholder only
 * @var bool        $eager  Load immediately (above-the-fold images)
 */
use App\View;

$image ??= null;
$alt   ??= '';
$ratio ??= '16x9';
$sizes ??= '(min-width: 64em) 40vw, 100vw';
$fit   ??= 'cover';
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
