<?php
/**
 * Temporary stand-in for the online forms (built in phase 3). Gives visitors a working way to reach us.
 *
 * @var array  $site
 * @var string $heading
 * @var string $text
 */
$c = $site['contact'];
?>
<div class="card form-slot">
    <h3><?= e($heading) ?></h3>
    <p class="text-muted"><?= e($text) ?></p>
    <div class="button-row">
        <a class="button" href="tel:<?= e($c['phone_tel']) ?>"><?= icon('phone', 18) ?> Call <?= e($c['phone_display']) ?></a>
        <a class="button button--outline" href="mailto:<?= e($c['email']) ?>"><?= icon('mail', 18) ?> Email us</a>
    </div>
</div>
