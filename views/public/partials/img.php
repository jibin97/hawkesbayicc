<?php
/**
 * Responsive <img> for an entry from content/images.php: the browser picks the 640px or 1600px WebP.
 * width/height are the real proportions, so space is reserved before the image loads.
 *
 * @var array  $image  w, h, sm, lg
 * @var string $alt
 * @var string $sizes
 * @var bool   $eager
 * @var string $class
 */
$alt   ??= '';
$sizes ??= '100vw';
$eager ??= false;
$class ??= '';
$lgW = min(1600, (int) $image['w']);
$h   = (int) round($image['h'] * $lgW / $image['w']);
?>
<img src="<?= e($image['sm']) ?>"
     <?php if ($lgW > 640): ?>srcset="<?= e($image['sm']) ?> 640w, <?= e($image['lg']) ?> <?= $lgW ?>w" sizes="<?= e($sizes) ?>"<?php endif; ?>
     alt="<?= e($alt) ?>" width="<?= $lgW ?>" height="<?= $h ?>"<?= $class !== '' ? ' class="' . e($class) . '"' : '' ?>
     loading="<?= $eager ? 'eager' : 'lazy' ?>" decoding="async"<?= $eager ? ' fetchpriority="high"' : '' ?>>
