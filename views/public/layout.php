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
use App\View;

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
