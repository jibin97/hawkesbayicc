<?php
/**
 * Image slot with a fixed aspect ratio (no layout shift). Shows a decorative placeholder until a
 * real image path is supplied (phase 4 imports images into /uploads).
 *
 * @var string|null $src
 * @var string      $alt   Required when $src is set; describe the scene, not "image of"
 * @var string      $ratio One of: 16x9, 3x2, 4x5, 1x1
 * @var string      $label Short caption shown on the placeholder only
 * @var bool        $eager Load immediately (above-the-fold images)
 */
$src   ??= null;
$alt   ??= '';
$ratio ??= '16x9';
$label ??= '';
$eager ??= false;
[$w, $h] = match ($ratio) {
    '3x2'   => [1200, 800],
    '4x5'   => [800, 1000],
    '1x1'   => [800, 800],
    default => [1600, 900],
};
?>
<?php if ($src !== null): ?>
    <figure class="media media--<?= e($ratio) ?>">
        <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" width="<?= $w ?>" height="<?= $h ?>"
             loading="<?= $eager ? 'eager' : 'lazy' ?>" decoding="async">
    </figure>
<?php else: ?>
    <div class="media media--<?= e($ratio) ?> media--placeholder" aria-hidden="true">
        <span class="media__ornament"></span>
        <?php if ($label !== ''): ?><span class="media__label"><?= e($label) ?></span><?php endif; ?>
    </div>
<?php endif; ?>
