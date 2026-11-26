<?php
/**
 * Home: phase 1 shell to exercise the layout and components. Full long-scroll page is built in phase 2.
 *
 * @var array $site
 */
?>
<section class="hero">
    <div class="wrap hero__inner">
        <p class="eyebrow">Hawke's Bay · Aotearoa New Zealand · Since <?= e($site['founded']) ?></p>
        <h1 class="display">Where Indian Classical Heritage Meets Contemporary Aotearoa Theatre</h1>
        <p class="lead">Classical dance classes, original dance dramas and an annual charity showcase in Napier, Hastings and Havelock North.</p>
        <div class="button-row">
            <a class="button" href="/academy#enrol">Enrol in Classes</a>
            <a class="button button--outline" href="/events">Upcoming Events</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">What we do</p>
            <h2>Three pillars</h2>
        </header>
        <div class="grid grid--3">
            <article class="card">
                <h3>Classical Academy</h3>
                <p class="text-muted">Indian classical dance training for young dancers and adults.</p>
            </article>
            <article class="card">
                <h3>Krishni Productions</h3>
                <p class="text-muted">Indian epic dance dramas staged with Hawke's Bay theatre makers.</p>
            </article>
            <article class="card">
                <h3>M.A.D.E for a Cause</h3>
                <p class="text-muted">Our annual Music and Dance Event, supporting a local charity every year since 2017.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section--light">
    <div class="wrap prose">
        <p class="eyebrow">Phase 1 preview</p>
        <h2>Light editorial section</h2>
        <p>This page is a temporary shell for checking the new layout, type and colours. The full home page arrives in phase 2. <a href="/about">Read about us</a>.</p>
    </div>
</section>
