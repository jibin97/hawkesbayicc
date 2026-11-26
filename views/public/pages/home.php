<?php
/**
 * Home: long-scroll editorial landing page.
 *
 * @var array $site
 */
use App\Content;
use App\View;

$productions = Content::get('productions');
$events      = Content::get('events');
$music       = Content::get('music');

$pillars = [
    [
        'eyebrow' => 'Learn',
        'title'   => 'Classical Academy',
        'text'    => 'Indian classical dance classes for young dancers and adults, from first steps to advanced practice.',
        'href'    => '/academy',
        'cta'     => 'Explore classes',
    ],
    [
        'eyebrow' => 'Watch',
        'title'   => 'Krishni Productions',
        'text'    => "Indian epic dance dramas created with Hawke's Bay theatre makers, from Shakuntala to Wairua Paani.",
        'href'    => '/productions',
        'cta'     => 'See our productions',
    ],
    [
        'eyebrow' => 'Give',
        'title'   => 'M.A.D.E for a Cause',
        'text'    => 'Our annual Music and Dance Event. Since 2017, part of the proceeds has gone to a local charity each year.',
        'href'    => '/events',
        'cta'     => 'Upcoming & past events',
    ],
];
?>
<section class="hero" aria-labelledby="hero-title">
    <?php if ($hero = image('site', 'dance')): ?>
        <div class="hero__media"><?= View::partial('public/partials/img', ['image' => $hero, 'alt' => '', 'sizes' => '100vw', 'eager' => true]) ?></div>
    <?php endif; ?>
    <div class="wrap hero__inner">
        <p class="eyebrow">Napier · Hastings · Havelock North · Since <?= e($site['founded']) ?></p>
        <h1 id="hero-title" class="display">Where Indian Classical Heritage Meets Contemporary Aotearoa Theatre</h1>
        <p class="lead">Classical dance classes, original dance dramas and an annual charity showcase from the <?= e($site['name']) ?>.</p>
        <div class="button-row">
            <a class="button" href="/academy#enrol">Enrol in Classes</a>
            <a class="button button--outline" href="/events">Upcoming Events</a>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="pillars-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">What we do</p>
            <h2 id="pillars-title">Dance, drama and giving back</h2>
        </header>
        <div class="grid grid--3">
            <?php foreach ($pillars as $i => $pillar): ?>
                <article class="card card--link card--pillar reveal">
                    <span class="card__number" aria-hidden="true">0<?= $i + 1 ?></span>
                    <p class="eyebrow"><?= e($pillar['eyebrow']) ?></p>
                    <h3><a class="card__link" href="<?= e($pillar['href']) ?>"><?= e($pillar['title']) ?></a></h3>
                    <p class="text-muted"><?= e($pillar['text']) ?></p>
                    <span class="card__more" aria-hidden="true"><?= e($pillar['cta']) ?> <?= icon('arrow', 16) ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= View::partial('public/partials/event-banner', ['events' => $events, 'site' => $site]) ?>

<section class="section section--surface" aria-labelledby="impact-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">Since 2015</p>
            <h2 id="impact-title">A decade on Hawke's Bay stages</h2>
        </header>
        <dl class="stats">
            <div class="stats__item reveal"><dt>Founded</dt><dd><?= e($site['founded']) ?></dd></div>
            <div class="stats__item reveal"><dt>First M.A.D.E for a Cause</dt><dd>2017</dd></div>
            <div class="stats__item reveal"><dt>Krishni Productions dance dramas</dt><dd><?= count($productions) ?></dd></div>
            <div class="stats__item reveal"><dt>Stage performances produced by our Artistic Director</dt><dd>~60</dd></div>
        </dl>
        <ul class="highlights">
            <li class="reveal"><span class="badge">Parliament</span> Our Artistic Director, Deepthi Krishna, has performed at Parliament House in Wellington.</li>
            <li class="reveal"><span class="badge">Local Hero</span> Our Chairperson, Ken Keys, received a Kiwibank Local Hero Award in 2017.</li>
            <li class="reveal"><span class="badge">On screen</span> Deepthi competed on TVNZ 3's The Great NZ Dance Masala in 2018.</li>
        </ul>
        <div class="charities">
            <h3 class="charities__title">Charities supported by M.A.D.E for a Cause</h3>
            <ul class="charities__list">
                <?php foreach ($events['charities'] as $charity): ?>
                    <li><?= e($charity) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="showcase-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">On stage</p>
            <h2 id="showcase-title">Productions &amp; live music</h2>
        </header>
    </div>
    <div class="scroller" role="region" aria-label="Productions and band, scrolls sideways" tabindex="0">
        <ul class="scroller__track">
            <?php foreach ($productions as $p): ?>
                <li class="scroller__item">
                    <article class="card card--link card--media">
                        <?= View::partial('public/partials/media', ['image' => image($p['slug'], $p['cover']), 'alt' => '', 'ratio' => '4x5', 'fit' => $p['cover_fit'], 'sizes' => '300px', 'label' => $p['title']]) ?>
                        <div class="card__body">
                            <p class="eyebrow"><?= $p['year'] ? e($p['year']) . ' · ' : '' ?>Krishni Productions</p>
                            <h3><a class="card__link" href="/productions#<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
                            <p class="text-muted text-small"><?= e($p['kicker']) ?></p>
                        </div>
                    </article>
                </li>
            <?php endforeach; ?>
            <li class="scroller__item">
                <article class="card card--link card--media card--band">
                    <?= View::partial('public/partials/media', ['image' => image('music', '04'), 'alt' => '', 'ratio' => '4x5', 'sizes' => '300px', 'label' => 'Live music']) ?>
                    <div class="card__body">
                        <p class="eyebrow">Live music · Est. <?= e($music['founded']) ?></p>
                        <h3><a class="card__link" href="/music"><?= e($music['name']) ?></a></h3>
                        <p class="text-muted text-small">Book the band for your event.</p>
                    </div>
                </article>
            </li>
        </ul>
    </div>
</section>

<section class="section section--light cta-band" aria-labelledby="cta-title">
    <div class="wrap cta-band__inner">
        <div>
            <p class="eyebrow">Classes for children &amp; adults</p>
            <h2 id="cta-title">Begin your classical dance journey</h2>
            <p class="lead">Enrol online in a few minutes, and we'll be in touch about the right class and your start date.</p>
        </div>
        <div class="button-row">
            <a class="button" href="/academy#enrol">Enrol now</a>
            <a class="button button--outline" href="tel:<?= e($site['contact']['phone_tel']) ?>"><?= icon('phone', 18) ?> <?= e($site['contact']['phone_display']) ?></a>
        </div>
    </div>
</section>
