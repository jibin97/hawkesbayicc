<?php
/** @var array $site */
$contact = $site['contact'];
?>
<footer class="site-footer">
    <div class="wrap site-footer__grid">
        <section class="site-footer__about" aria-labelledby="footer-about">
            <h2 id="footer-about" class="site-footer__brand"><?= e($site['name']) ?></h2>
            <p class="site-footer__tagline"><?= e($site['tagline']) ?></p>
            <p class="text-muted">
                Serving <?= e(implode(', ', array_slice($contact['service_areas'], 0, -1))) ?>
                and <?= e(end($contact['service_areas'])) ?> since <?= e($site['founded']) ?>.
            </p>
        </section>

        <nav class="site-footer__nav" aria-labelledby="footer-explore">
            <h2 id="footer-explore" class="site-footer__heading">Explore</h2>
            <ul>
                <?php foreach ($site['nav'] as $item): ?>
                    <li><a href="<?= e($item['path']) ?>"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <section aria-labelledby="footer-contact">
            <h2 id="footer-contact" class="site-footer__heading">Get in touch</h2>
            <ul class="contact-list">
                <li><a href="tel:<?= e($contact['phone_tel']) ?>"><?= icon('phone', 18) ?> <?= e($contact['phone_display']) ?></a></li>
                <li><a href="mailto:<?= e($contact['email']) ?>"><?= icon('mail', 18) ?> <?= e($contact['email']) ?></a></li>
                <li><?= icon('pin', 18) ?> <span><?= e($contact['location']) ?></span></li>
            </ul>
            <ul class="social-links" aria-label="Social media">
                <?php foreach ($site['social'] as $social): ?>
                    <li>
                        <a href="<?= e($social['url']) ?>" rel="noopener" target="_blank">
                            <?= icon($social['icon'], 20) ?>
                            <span class="visually-hidden"><?= e($social['label']) ?> (opens in a new tab)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
    <div class="wrap site-footer__legal">
        <p>&copy; <?= e(date('Y')) ?> <?= e($site['name']) ?>. All rights reserved.</p>
    </div>
</footer>
