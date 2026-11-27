<?php
/**
 * @var string $title
 * @var string $content Rendered (already escaped) page HTML
 * @var array{name:string}|null $user
 * @var string $csrf
 */
$user ??= null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · Admin · <?= e(site_name()) ?></title>
    <meta name="theme-color" content="#0A0809">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <link rel="stylesheet" href="/assets/css/site.css">
    <script src="/assets/js/admin.js" defer></script>
</head>
<body class="admin">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="wrap header-row">
            <span class="brand"><?= e(site_name()) ?> <small>Admin</small></span>
            <?php if ($user !== null): ?>
                <nav class="admin-nav" aria-label="Admin">
                    <a href="/admin" <?= ($active ?? 'dashboard') === 'dashboard' ? 'aria-current="page"' : '' ?>>Dashboard</a>
                    <a href="/admin/inquiries" <?= ($active ?? '') === 'inquiries' ? 'aria-current="page"' : '' ?>>Inquiries</a>
                    <a href="/admin/images" <?= ($active ?? '') === 'images' ? 'aria-current="page"' : '' ?>>Images</a>
                    <a href="/" target="_blank" rel="noopener">View site<span class="visually-hidden"> (opens in a new tab)</span></a>
                </nav>
                <form method="post" action="/admin/logout" class="logout">
                    <span class="who"><?= e($user['name']) ?></span>
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <button type="submit">Log out</button>
                </form>
            <?php endif; ?>
        </div>
    </header>
    <main id="main" class="wrap">
        <?= $content ?>
    </main>
</body>
</html>
