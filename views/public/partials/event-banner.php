<?php
/**
 * Featured event banner: the next upcoming event, or an "announced soon" state with the latest M.A.D.E.
 *
 * @var array $events content/events.php
 * @var array $site
 */
$next   = upcoming_events($events['upcoming'])[0] ?? null;
$latest = $events['archive'][0] ?? null;
?>
<section class="section section--tight" aria-labelledby="featured-event">
    <div class="wrap">
        <div class="event-banner">
            <?php if ($next !== null): ?>
                <div class="event-banner__date" aria-hidden="true">
                    <span class="event-banner__day"><?= e(date('j', strtotime($next['date']))) ?></span>
                    <span class="event-banner__month"><?= e(date('M Y', strtotime($next['date']))) ?></span>
                </div>
                <div class="event-banner__body">
                    <p class="eyebrow">Next show</p>
                    <h2 id="featured-event"><?= e($next['title']) ?></h2>
                    <ul class="meta-list">
                        <li><?= icon('calendar', 18) ?> <?= e(format_date($next['date'])) ?><?= !empty($next['time']) ? ', ' . e($next['time']) : '' ?></li>
                        <?php if (!empty($next['venue'])): ?>
                            <li><?= icon('pin', 18) ?> <?= e($next['venue']) ?><?= !empty($next['town']) ? ', ' . e($next['town']) : '' ?></li>
                        <?php endif; ?>
                    </ul>
                    <?php if (!empty($next['charity'])): ?>
                        <p class="event-banner__charity">Supporting <strong><?= e($next['charity']) ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="event-banner__cta">
                    <?php if (!empty($next['ticket_url'])): ?>
                        <a class="button" href="<?= e($next['ticket_url']) ?>" rel="noopener" target="_blank">
                            <?= icon('ticket', 18) ?> Book tickets<span class="visually-hidden"> (opens in a new tab)</span>
                        </a>
                    <?php endif; ?>
                    <a class="button button--outline" href="/events#upcoming">Event details</a>
                </div>
            <?php else: ?>
                <div class="event-banner__body">
                    <p class="eyebrow">Upcoming events</p>
                    <h2 id="featured-event">Our next show will be announced soon</h2>
                    <p class="text-muted">
                        <?php if ($latest !== null): ?>
                            Our most recent showcase was <?= e($latest['title']) ?><?= $latest['featuring'] ? ', featuring ' . e($latest['featuring']) : '' ?>.
                        <?php endif; ?>
                        Follow us on social media or get in touch to hear about tickets first.
                    </p>
                </div>
                <div class="event-banner__cta">
                    <a class="button" href="/contact">Get event updates</a>
                    <a class="button button--outline" href="/events#made">See past events</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
