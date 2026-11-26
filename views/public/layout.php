<?php
/**
 * Public site layout.
 *
 * @var string|null $title       Page title (null on home: uses the full site title)
 * @var string      $description Meta description
 * @var string      $path        Current route path, for active nav state
 * @var array       $site        content/site.php
 * @var string      $content     Rendered (already escaped) page HTML
 */
use App\Seo;
use App\View;

$is404     = $path === '' && $title === 'Page not found';
$fullTitle = $title !== null
    ? $title . ' · ' . $site['short_name']
    : $site['name'] . ' · Indian Classical Dance, Theatre & Music in Hawke\'s Bay';
?>
<!doctype html>
<html lang="en-NZ" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($fullTitle) ?></title>
    <?php if ($description !== ''): ?>
        <meta name="description" content="<?= e($description) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#111114">
    <?php if (!$is404): ?>
        <link rel="canonical" href="<?= e(Seo::canonical($path)) ?>">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="<?= e($site['name']) ?>">
        <meta property="og:locale" content="en_NZ">
        <meta property="og:title" content="<?= e($title ?? $site['name']) ?>">
        <?php if ($description !== ''): ?><meta property="og:description" content="<?= e($description) ?>"><?php endif; ?>
        <meta property="og:url" content="<?= e(Seo::canonical($path)) ?>">
        <meta property="og:image" content="<?= e(Seo::ogImage($path)) ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <?php foreach (Seo::jsonLd($path, $site) as $block): ?>
            <script type="application/ld+json"><?= Seo::encode($block) ?></script>
        <?php endforeach; ?>
    <?php else: ?>
        <meta name="robots" content="noindex">
    <?php endif; ?>
    <script>document.documentElement.classList.replace('no-js', 'js');</script>
    <link rel="preload" href="/assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/cormorant-garamond-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/site.css">
    <script src="/assets/js/site.js" defer></script>
</head>
<body class="public">
    <a class="skip-link" href="#main">Skip to content</a>

    <?= View::partial('public/partials/header', ['site' => $site, 'path' => $path]) ?>

    <main id="main" tabindex="-1">
        <?= $content ?>
    </main>

    <?= View::partial('public/partials/footer', ['site' => $site]) ?>
    <?= View::partial('public/partials/mobile-bar', ['site' => $site]) ?>
</body>
</html>
