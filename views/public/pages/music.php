<?php
/**
 * Live Music: electronic press kit (EPK) for Dr. Wuts & The Bindaas Bollywood Band.
 *
 * @var array $site
 */
use App\Content;
use App\View;

$band    = Content::get('music');
$youtube = null;
foreach ($site['social'] as $s) {
    if ($s['icon'] === 'youtube') {
        $youtube = $s['url'];
    }
}
?>
<section class="page-hero epk-hero" aria-labelledby="page-title">
    <div class="wrap epk-hero__grid">
        <div>
            <p class="eyebrow">Live music · Est. <?= e($band['founded']) ?></p>
            <h1 id="page-title"><?= e($band['name']) ?></h1>
            <p class="lead"><?= e($band['intro']) ?></p>
            <div class="button-row">
                <a class="button" href="#book">Book the band</a>
                <?php if ($youtube): ?>
                    <a class="button button--outline" href="<?= e($youtube) ?>" rel="noopener" target="_blank"><?= icon('play', 16) ?> Watch on YouTube<span class="visually-hidden"> (opens in a new tab)</span></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="reveal">
            <?= View::partial('public/partials/media', ['image' => image('site', 'band'), 'alt' => 'Dr. Wuts & The Bindaas Bollywood Band playing live on stage', 'ratio' => '3x2', 'sizes' => '(min-width: 64em) 560px, 100vw', 'label' => 'The band live', 'eager' => true]) ?>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="bio-title">
    <div class="wrap split split--wide-left">
        <div class="prose">
            <p class="eyebrow">Band bio</p>
            <h2 id="bio-title">Jazz roots, Bollywood heart</h2>
            <?php foreach ($band['bio'] as $para): ?>
                <p><?= e($para) ?></p>
            <?php endforeach; ?>
        </div>
        <aside class="card facts" aria-label="Quick facts">
            <dl class="details">
                <dt>Founded</dt><dd><?= e($band['founded']) ?></dd>
                <dt>Bandleader</dt><dd><a href="/about#anton-wuts"><?= e($band['leader']) ?></a></dd>
                <dt>Based in</dt><dd>Hawke's Bay, New Zealand</dd>
                <dt>Part of</dt><dd><?= e($site['name']) ?></dd>
            </dl>
            <?php if ($band['lineup'] !== []): ?>
                <h3 class="label-heading">Line-up</h3>
                <ul class="plain-list">
                    <?php foreach ($band['lineup'] as $m): ?><li><?= e($m['name']) ?>: <?= e($m['instrument']) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>
    </div>
</section>

<section class="section section--surface" aria-labelledby="media-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">Watch &amp; listen</p>
            <h2 id="media-title">Showreel</h2>
        </header>
        <?php if ($band['videos'] !== []): ?>
            <div class="grid grid--2">
                <?php foreach ($band['videos'] as $v): ?>
                    <div class="video">
                        <iframe src="https://www.youtube-nocookie.com/embed/<?= e($v['youtube_id']) ?>" title="<?= e($v['title']) ?>"
                                loading="lazy" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="grid grid--2">
                <?= View::partial('public/partials/media', ['ratio' => '16x9', 'label' => 'Live performance video coming soon']) ?>
                <?= View::partial('public/partials/media', ['ratio' => '16x9', 'label' => 'Audio showcase coming soon']) ?>
            </div>
        <?php endif; ?>
        <h3 class="label-heading">Photos</h3>
        <?= View::partial('public/partials/gallery', ['images' => images('music'), 'title' => $band['name'] . ' live', 'id' => 'gallery-band', 'visible' => 4]) ?>

        <?php if ($band['repertoire'] !== []): ?>
            <h3 class="label-heading">Repertoire</h3>
            <ul class="tag-list">
                <?php foreach ($band['repertoire'] as $r): ?><li class="badge"><?= e($r) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="section section--light" id="book" aria-labelledby="book-title">
    <div class="wrap split split--wide-right">
        <header class="section-heading">
            <p class="eyebrow">Bookings</p>
            <h2 id="book-title">Book the band for your event</h2>
            <p class="text-muted">Tell us the date, venue and type of event and we'll get back to you with availability.</p>
        </header>
        <?= View::partial('public/partials/form', [
            'form'   => 'booking',
            'action' => $formAction,
            'state'  => $formState,
            'site'   => $site,
        ]) ?>
    </div>
</section>
