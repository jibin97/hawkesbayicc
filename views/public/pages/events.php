<?php
/**
 * Events: upcoming events and the M.A.D.E for a Cause archive.
 *
 * @var array $site
 */
use App\Content;
use App\View;

$events   = Content::get('events');
$upcoming = upcoming_events($events['upcoming']);
?>
<section class="page-hero" aria-labelledby="page-title">
    <div class="wrap prose">
        <p class="eyebrow">Events &amp; charity</p>
        <h1 id="page-title">Shows that give back</h1>
        <p class="lead">Every year since 2017, our Music and Dance Event (M.A.D.E) for a Cause has brought students, artists and audiences together, with part of the proceeds going to a local charity.</p>
        <div class="button-row">
            <a class="button" href="#upcoming">Upcoming events</a>
            <a class="button button--outline" href="#made">M.A.D.E archive</a>
        </div>
    </div>
</section>

<section class="section" id="upcoming" aria-labelledby="upcoming-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">What's on</p>
            <h2 id="upcoming-title">Upcoming events</h2>
        </header>

        <?php if ($upcoming === []): ?>
            <div class="card empty-state">
                <h3>Our next show will be announced soon</h3>
                <p class="text-muted">Follow us on social media or get in touch to hear about tickets first.</p>
                <ul class="social-links" aria-label="Social media">
                    <?php foreach ($site['social'] as $social): ?>
                        <li><a href="<?= e($social['url']) ?>" rel="noopener" target="_blank"><?= icon($social['icon'], 20) ?><span class="visually-hidden"><?= e($social['label']) ?> (opens in a new tab)</span></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <ul class="event-list">
                <?php foreach ($upcoming as $ev): ?>
                    <li class="card event-card">
                        <div class="event-banner__date" aria-hidden="true">
                            <span class="event-banner__day"><?= e(date('j', strtotime($ev['date']))) ?></span>
                            <span class="event-banner__month"><?= e(date('M Y', strtotime($ev['date']))) ?></span>
                        </div>
                        <div>
                            <h3><?= e($ev['title']) ?></h3>
                            <ul class="meta-list">
                                <li><?= icon('calendar', 18) ?> <?= e(format_date($ev['date'])) ?><?= !empty($ev['time']) ? ', ' . e($ev['time']) : '' ?></li>
                                <?php if (!empty($ev['venue'])): ?><li><?= icon('pin', 18) ?> <?= e($ev['venue']) ?><?= !empty($ev['town']) ? ', ' . e($ev['town']) : '' ?></li><?php endif; ?>
                            </ul>
                            <?php if (!empty($ev['summary'])): ?><p class="text-muted"><?= e($ev['summary']) ?></p><?php endif; ?>
                            <?php if (!empty($ev['charity'])): ?><p class="event-banner__charity">Supporting <strong><?= e($ev['charity']) ?></strong></p><?php endif; ?>
                        </div>
                        <?php if (!empty($ev['ticket_url'])): ?>
                            <a class="button" href="<?= e($ev['ticket_url']) ?>" rel="noopener" target="_blank"><?= icon('ticket', 18) ?> Book tickets<span class="visually-hidden"> for <?= e($ev['title']) ?> (opens in a new tab)</span></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="section section--surface" id="made" aria-labelledby="made-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">M.A.D.E for a Cause · 2017 to today</p>
            <h2 id="made-title">The M.A.D.E archive</h2>
            <p class="text-muted">Our students' annual showcase, which has supported:</p>
            <ul class="tag-list">
                <?php foreach ($events['charities'] as $charity): ?><li class="badge"><?= e($charity) ?></li><?php endforeach; ?>
            </ul>
        </header>

        <ol class="timeline">
            <?php foreach ($events['archive'] as $item): ?>
                <li class="timeline__item reveal" id="made-<?= e($item['year']) ?>">
                    <span class="timeline__year"><?= e($item['year']) ?></span>
                    <article class="card card--media timeline__card">
                        <?php $photos = images('made-gallery', (string) $item['year']); ?>
                        <?= View::partial('public/partials/media', ['image' => $photos[0] ?? null, 'ratio' => '3x2', 'sizes' => '(min-width: 48em) 600px, 100vw', 'label' => 'M.A.D.E ' . $item['year']]) ?>
                        <div class="card__body">
                            <h3><?= e($item['title']) ?></h3>
                            <?php if (!empty($item['featuring'])): ?><p>Featuring <em><?= e($item['featuring']) ?></em></p><?php endif; ?>
                            <?php if (!empty($item['venue'])): ?><p class="text-muted text-small"><?= icon('pin', 16) ?> <?= e($item['venue']) ?></p><?php endif; ?>
                            <?php if (!empty($item['note'])): ?><p class="text-muted"><?= e($item['note']) ?></p><?php endif; ?>
                            <?php if (!empty($item['charity'])): ?><p class="event-banner__charity">Supporting <strong><?= e($item['charity']) ?></strong></p><?php endif; ?>
                            <?= View::partial('public/partials/gallery', ['images' => $photos, 'title' => $item['title'], 'id' => 'gallery-made-' . $item['year'], 'visible' => 0]) ?>
                        </div>
                    </article>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section--light cta-band" aria-labelledby="sponsor-title">
    <div class="wrap cta-band__inner">
        <div>
            <p class="eyebrow">Sponsors &amp; media</p>
            <h2 id="sponsor-title">Support the next M.A.D.E for a Cause</h2>
            <p class="lead">Businesses, charities and media: talk to us about sponsorship, partnerships and coverage.</p>
        </div>
        <div class="button-row">
            <a class="button" href="/contact">Contact us</a>
        </div>
    </div>
</section>
