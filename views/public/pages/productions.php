<?php
/**
 * Krishni Productions: each production as an editorial case study.
 *
 * @var array $site
 */
use App\Content;
use App\View;

$productions = Content::get('productions');
$youtube     = null;
foreach ($site['social'] as $s) {
    if ($s['icon'] === 'youtube') {
        $youtube = $s['url'];
    }
}
?>
<section class="page-hero" aria-labelledby="page-title">
    <div class="wrap prose">
        <p class="eyebrow">Krishni Productions · Since 2019</p>
        <h1 id="page-title">Indian epic dance drama, made in Hawke's Bay</h1>
        <p class="lead">Founded by Deepthi Krishna in 2019, Krishni Productions brings Indian classical dance together with local theatre makers to stage original dance dramas.</p>
        <nav class="chip-nav" aria-label="Productions on this page">
            <ul>
                <?php foreach ($productions as $p): ?>
                    <li><a href="#<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</section>

<?php foreach ($productions as $i => $p): ?>
    <article class="section production <?= $i % 2 === 1 ? 'section--surface production--flip' : '' ?>" id="<?= e($p['slug']) ?>" aria-labelledby="<?= e($p['slug']) ?>-title">
        <div class="wrap production__grid">
            <div class="production__media reveal">
                <?= View::partial('public/partials/media', [
                    'image' => image('production-cover', $p['slug']),
                    'ratio' => '4x5', 'sizes' => '(min-width: 48em) 420px, 100vw',
                    'label' => $p['title'] . ' poster', 'eager' => $i === 0,
                ]) ?>
            </div>
            <div class="production__body">
                <p class="eyebrow"><?= $p['year'] ? e($p['year']) . ' · ' : '' ?><?= e($p['kicker']) ?></p>
                <h2 id="<?= e($p['slug']) ?>-title"><?= e($p['title']) ?></h2>

                <h3 class="label-heading">Synopsis</h3>
                <?php foreach ($p['synopsis'] as $para): ?>
                    <p><?= e($para) ?></p>
                <?php endforeach; ?>

                <?php if ($p['credits'] !== []): ?>
                    <h3 class="label-heading">Creative team</h3>
                    <dl class="credits">
                        <?php foreach ($p['credits'] as $credit): ?>
                            <div><dt><?= e($credit['role']) ?></dt><dd><?= e($credit['name']) ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>

                <?php if ($p['partners'] !== []): ?>
                    <p class="text-muted text-small">In collaboration with <?= e(implode(' and ', $p['partners'])) ?>.</p>
                <?php endif; ?>

                <div class="production__extras">
                    <div class="trailer">
                        <h3 class="label-heading">Trailer</h3>
                        <?php if ($p['youtube_id'] !== null): ?>
                            <div class="video">
                                <iframe src="https://www.youtube-nocookie.com/embed/<?= e($p['youtube_id']) ?>" title="<?= e($p['title']) ?> trailer"
                                        loading="lazy" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-small">Trailer coming soon.<?php if ($youtube): ?> Meanwhile, <a href="<?= e($youtube) ?>" rel="noopener" target="_blank">watch on our YouTube channel<span class="visually-hidden"> (opens in a new tab)</span></a>.<?php endif; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $photos = images('production-gallery', $p['slug']); if ($photos !== []): ?>
            <div class="wrap production__gallery">
                <h3 class="label-heading">Gallery · <?= count($photos) ?> photos</h3>
                <?= View::partial('public/partials/gallery', ['images' => $photos, 'title' => $p['title'], 'id' => 'gallery-' . $p['slug'], 'visible' => 8]) ?>
            </div>
        <?php endif; ?>
    </article>
<?php endforeach; ?>

<section class="section section--light cta-band" aria-labelledby="collab-title">
    <div class="wrap cta-band__inner">
        <div>
            <p class="eyebrow">Collaborate with us</p>
            <h2 id="collab-title">Bring a Krishni production to your stage</h2>
            <p class="lead">Venues, festivals and theatre companies: talk to us about performances and creative collaborations.</p>
        </div>
        <div class="button-row">
            <a class="button" href="/contact">Get in touch</a>
        </div>
    </div>
</section>
