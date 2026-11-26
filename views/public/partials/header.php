<?php
/**
 * Sticky site header. Desktop: inline nav + Enrol CTA. Mobile: <details> menu (works without JS).
 *
 * @var array  $site
 * @var string $path
 */
$navList = static function (array $items, string $path): string {
    $html = '';
    foreach ($items as $item) {
        $current = is_current($item['path'], $path) ? ' aria-current="page"' : '';
        $html .= '<li><a href="' . e($item['path']) . '"' . $current . '>' . e($item['label']) . '</a></li>';
    }
    return $html;
};
?>
<header class="masthead">
    <div class="wrap masthead__row">
        <a class="brand" href="/">
            <span class="brand__mark" aria-hidden="true"><?= e($site['short_name']) ?></span>
            <span class="brand__name"><?= e($site['name']) ?></span>
        </a>

        <nav class="nav-desktop" aria-label="Main">
            <ul><?= $navList($site['nav'], $path) ?></ul>
        </nav>

        <a class="button button--sm masthead__cta" href="<?= e($site['cta']['path']) ?>"><?= e($site['cta']['label']) ?></a>

        <details class="nav-mobile" data-menu>
            <summary class="nav-mobile__toggle">
                <span class="nav-mobile__open"><?= icon('menu', 22) ?></span>
                <span class="nav-mobile__close"><?= icon('close', 22) ?></span>
                <span class="nav-mobile__label">Menu</span>
            </summary>
            <nav class="nav-mobile__panel" aria-label="Main">
                <ul><?= $navList($site['nav'], $path) ?></ul>
                <a class="button button--block" href="<?= e($site['cta']['path']) ?>"><?= e($site['cta']['label']) ?></a>
                <a class="nav-mobile__call" href="tel:<?= e($site['contact']['phone_tel']) ?>">
                    <?= icon('phone', 18) ?> <?= e($site['contact']['phone_display']) ?>
                </a>
            </nav>
        </details>
    </div>
</header>
