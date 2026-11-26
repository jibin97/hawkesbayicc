<?php
/**
 * Sticky bottom quick-action bar, phones only (hidden by CSS on wider screens).
 *
 * @var array $site
 */
?>
<nav class="mobile-bar" aria-label="Quick actions">
    <a class="mobile-bar__item mobile-bar__item--primary" href="<?= e($site['cta']['path']) ?>">
        <?= icon('enrol', 22) ?><span>Enrol</span>
    </a>
    <a class="mobile-bar__item" href="/events">
        <?= icon('ticket', 22) ?><span>Events</span>
    </a>
    <a class="mobile-bar__item" href="tel:<?= e($site['contact']['phone_tel']) ?>">
        <?= icon('phone', 22) ?><span>Call</span>
    </a>
</nav>
