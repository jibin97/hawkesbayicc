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
</head>
<body class="admin">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="wrap header-row">
            <span class="brand"><?= e(site_name()) ?> <small>Admin</small></span>
            <?php if ($user !== null): ?>
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
