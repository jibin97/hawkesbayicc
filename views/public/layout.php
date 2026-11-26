<?php
/**
 * @var string $title
 * @var string $content Rendered (already escaped) page HTML
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · <?= e(site_name()) ?></title>
    <meta name="theme-color" content="#0A0809">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="wrap">
            <a class="brand" href="/"><?= e(site_name()) ?></a>
        </div>
    </header>
    <main id="main" class="wrap">
        <?= $content ?>
    </main>
    <footer class="site-footer">
        <div class="wrap">
            <p>&copy; <?= e(date('Y')) ?> <?= e(site_name()) ?></p>
        </div>
    </footer>
</body>
</html>
