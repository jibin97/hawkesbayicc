<?php
/**
 * Contact.
 *
 * @var array $site
 */
use App\View;

$c = $site['contact'];
?>
<section class="page-hero" aria-labelledby="page-title">
    <div class="wrap prose">
        <p class="eyebrow">Contact</p>
        <h1 id="page-title">Get in touch</h1>
        <p class="lead">Questions about classes, tickets, bookings or collaborations? We'd love to hear from you.</p>
    </div>
</section>

<section class="section" aria-labelledby="ways-title">
    <div class="wrap">
        <h2 id="ways-title" class="visually-hidden">Ways to contact us</h2>
        <div class="grid grid--3">
            <a class="card card--link contact-card" href="tel:<?= e($c['phone_tel']) ?>">
                <span class="contact-card__icon"><?= icon('phone', 24) ?></span>
                <span class="eyebrow">Call or text</span>
                <span class="contact-card__value"><?= e($c['phone_display']) ?></span>
            </a>
            <a class="card card--link contact-card" href="mailto:<?= e($c['email']) ?>">
                <span class="contact-card__icon"><?= icon('mail', 24) ?></span>
                <span class="eyebrow">Email</span>
                <span class="contact-card__value"><?= e($c['email']) ?></span>
            </a>
            <div class="card contact-card">
                <span class="contact-card__icon"><?= icon('pin', 24) ?></span>
                <span class="eyebrow">Based in</span>
                <span class="contact-card__value"><?= e($c['location']) ?></span>
                <span class="text-muted text-small">Serving <?= e(implode(', ', array_slice($c['service_areas'], 0, -1))) ?> and <?= e(end($c['service_areas'])) ?>.</span>
            </div>
        </div>
    </div>
</section>

<section class="section section--light" id="message" aria-labelledby="message-title">
    <div class="wrap split">
        <header class="section-heading">
            <p class="eyebrow">Send a message</p>
            <h2 id="message-title">Quick enquiry</h2>
            <p class="text-muted">Looking to enrol? Use the <a href="/academy#enrol">enrolment form</a>. Booking the band? Use the <a href="/music#book">booking form</a>.</p>
            <h3 class="label-heading">Follow us</h3>
            <ul class="social-links" aria-label="Social media">
                <?php foreach ($site['social'] as $social): ?>
                    <li><a href="<?= e($social['url']) ?>" rel="noopener" target="_blank"><?= icon($social['icon'], 20) ?><span class="visually-hidden"><?= e($social['label']) ?> (opens in a new tab)</span></a></li>
                <?php endforeach; ?>
            </ul>
        </header>
        <?= View::partial('public/partials/form-slot', [
            'site'    => $site,
            'heading' => 'Online enquiry form opens soon',
            'text'    => "In the meantime, call or email us and we'll get back to you.",
        ]) ?>
    </div>
</section>
