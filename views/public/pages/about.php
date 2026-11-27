<?php
/**
 * About & Team.
 *
 * @var array $site
 */
use App\Content;
use App\View;

$team = Content::get('team');

$timeline = [
    ['year' => '2015', 'text' => "Deepthi Krishna and Sreejith Nair found the Hawke's Bay Indian Cultural Centre."],
    ['year' => '2017', 'text' => 'The first annual Music and Dance Event (M.A.D.E) for a Cause.'],
    ['year' => '2019', 'text' => 'Krishni Productions launches with Shakuntala, our first Indian epic dance drama.'],
    ['year' => '2021', 'text' => "Sita Unsung, with Hawke's Bay Flying Trapeze, premieres at M.A.D.E for a Cause."],
    ['year' => '2023', 'text' => 'Takshaka – King of the Snakes, and Anton Wuts founds Dr. Wuts & The Bindaas Bollywood Band.'],
    ['year' => '2025', 'text' => 'Wairua Paani is the featured production at M.A.D.E for a Cause 2025.'],
];
?>
<section class="page-hero" aria-labelledby="page-title">
    <div class="wrap prose">
        <p class="eyebrow">About HBICC · Est. <?= e($site['founded']) ?></p>
        <h1 id="page-title">Indian arts and culture, shared in Hawke's Bay</h1>
        <p class="lead"><?= e($site['tagline']) ?></p>
    </div>
</section>

<section class="section section--light" aria-labelledby="story-title">
    <div class="wrap split split--wide-left">
        <div class="prose">
            <p class="eyebrow">Our story</p>
            <h2 id="story-title">How HBICC began</h2>
            <p>The Hawke's Bay Indian Cultural Centre was established in 2015 by Deepthi Krishna and her husband, Sreejith Nair, to promote Indian classical dance, arts and cultural activities, including theatre. From the start we have encouraged local talent and the development of young dancers.</p>
            <p>As the centre grew, we started the annual Music and Dance Event (M.A.D.E) for a Cause in 2017. Every year since, a charitable organisation has received part of the proceeds. M.A.D.E has supported the Cancer Society, Kidney Kids, Heart Kids and Resource HB.</p>
            <p>In 2019 we launched Krishni Productions and staged our first Indian epic dance drama, Shakuntala, with Cook Place Productions. Two more theatrical productions followed: Sita Unsung, and Takshaka – King of the Snakes with Hawke's Bay Flying Trapeze. All three were directed by Ken Keys.</p>
        </div>
        <aside aria-label="Milestones">
            <ol class="milestones">
                <?php foreach ($timeline as $m): ?>
                    <li class="reveal"><span class="milestones__year"><?= e($m['year']) ?></span><span><?= e($m['text']) ?></span></li>
                <?php endforeach; ?>
            </ol>
        </aside>
    </div>
</section>

<section class="section" id="team" aria-labelledby="team-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">Leadership &amp; creative team</p>
            <h2 id="team-title">The people behind HBICC</h2>
        </header>
        <div class="team-photo reveal"><?= View::partial('public/partials/media', ['image' => image('team-group'), 'ratio' => '3x2', 'sizes' => '(min-width: 56em) 860px, 100vw']) ?></div>

        <ul class="team-grid">
            <?php foreach ($team as $person): ?>
                <li class="card team-card reveal" id="<?= e($person['slug']) ?>">
                    <div class="team-card__head">
                        <?php if ($photo = image('team-photo', $person['slug'])): ?>
                            <?= View::partial('public/partials/img', ['image' => $photo, 'alt' => '', 'sizes' => '72px', 'class' => 'avatar']) ?>
                        <?php else: ?>
                            <span class="avatar avatar--initials" aria-hidden="true"><?= e(initials($person['name'])) ?></span>
                        <?php endif; ?>
                        <div>
                            <h3><?= e($person['name']) ?></h3>
                            <span class="badge"><?= e($person['role']) ?></span>
                        </div>
                    </div>
                    <p class="text-muted"><?= e($person['summary']) ?></p>
                    <details class="bio" data-bio>
                        <summary>Read <?= e(explode(' ', $person['name'])[0]) ?>'s full bio</summary>
                        <div class="bio__body">
                            <?php foreach ($person['bio'] as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
                        </div>
                    </details>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section section--surface section--tight" aria-labelledby="collab-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">Collaborators</p>
            <h2 id="collab-title">Made with our community</h2>
        </header>
        <ul class="tag-list">
            <li class="badge">Cook Place Productions</li>
            <li class="badge">Hawke's Bay Flying Trapeze</li>
            <li class="badge">Cancer Society</li>
            <li class="badge">Kidney Kids</li>
            <li class="badge">Heart Kids</li>
            <li class="badge">Resource HB</li>
        </ul>
    </div>
</section>
