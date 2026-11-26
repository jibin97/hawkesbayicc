<?php
/**
 * Academy & enrolment.
 *
 * @var array $site
 */
use App\Content;
use App\View;

$academy   = Content::get('academy');
$timetable = $academy['timetable'];
?>
<section class="page-hero epk-hero" aria-labelledby="page-title">
    <div class="wrap epk-hero__grid">
        <div>
        <p class="eyebrow">Classical Academy</p>
        <h1 id="page-title">Indian classical dance classes in Hawke's Bay</h1>
        <p class="lead">Learn the dance traditions of India with HBICC: technique, rhythm and storytelling, with a stage to perform on every year.</p>
        <div class="button-row">
            <a class="button" href="#enrol">Enrol online</a>
            <a class="button button--outline" href="#faq">Questions?</a>
        </div>
        </div>
        <div class="reveal">
            <?= View::partial('public/partials/media', ['image' => image('site', 'duet'), 'alt' => 'Two HBICC students performing classical Indian dance on stage', 'ratio' => '3x2', 'sizes' => '(min-width: 64em) 560px, 100vw', 'eager' => true]) ?>
        </div>
    </div>
</section>

<section class="section section--light" aria-labelledby="disciplines-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">Disciplines</p>
            <h2 id="disciplines-title">What you can learn</h2>
        </header>
        <div class="grid grid--2">
            <?php foreach ($academy['disciplines'] as $d): ?>
                <article class="card discipline reveal">
                    <p class="eyebrow"><?= e($d['origin']) ?></p>
                    <h3><?= e($d['name']) ?></h3>
                    <p class="text-muted"><?= e($d['summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--light section--tight" aria-labelledby="timetable-title">
    <div class="wrap">
        <div class="split">
            <div>
                <header class="section-heading">
                    <p class="eyebrow">Classes &amp; age groups</p>
                    <h2 id="timetable-title">Timetable</h2>
                </header>
                <?php if ($timetable === null): ?>
                    <div class="alert alert--info" role="note">
                        <p><strong>Our class timetable is being finalised.</strong> Tell us the student's age and experience on the enrolment form and we'll recommend the right class, or call <a href="tel:<?= e($site['contact']['phone_tel']) ?>"><?= e($site['contact']['phone_display']) ?></a>.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap" role="region" aria-labelledby="timetable-title" tabindex="0">
                        <table class="table">
                            <thead><tr><th scope="col">Class</th><th scope="col">Ages</th><th scope="col">Day</th><th scope="col">Time</th></tr></thead>
                            <tbody>
                                <?php foreach ($timetable['classes'] as $class): ?>
                                    <tr>
                                        <th scope="row"><?= e($class['name']) ?></th>
                                        <td><?= e($class['ages']) ?></td>
                                        <td><?= e($class['day']) ?></td>
                                        <td><?= e($class['time']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($timetable['venue'])): ?><p class="text-muted"><?= icon('pin', 16) ?> <?= e($timetable['venue']) ?></p><?php endif; ?>
                    <?php if (!empty($timetable['fees'])): ?><p class="text-muted"><?= e($timetable['fees']) ?></p><?php endif; ?>
                <?php endif; ?>
            </div>
            <div>
                <header class="section-heading">
                    <p class="eyebrow">What to expect</p>
                    <h2>Classes for every goal</h2>
                </header>
                <ul class="check-list">
                    <?php foreach ($academy['expectations'] as $item): ?>
                        <li><?= e($item) ?></li>
                    <?php endforeach; ?>
                    <li>Performing on stage at our annual M.A.D.E for a Cause showcase</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="section section--light" aria-labelledby="policies-title">
    <div class="wrap">
        <header class="section-heading">
            <p class="eyebrow">Term structure</p>
            <h2 id="policies-title">How classes work</h2>
        </header>
        <ol class="steps">
            <?php foreach ($academy['policies'] as $policy): ?>
                <li class="steps__item reveal">
                    <h3><?= e($policy['title']) ?></h3>
                    <p class="text-muted"><?= e($policy['text']) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section section--light" id="faq" aria-labelledby="faq-title">
    <div class="wrap">
        <div class="split split--wide-right">
            <header class="section-heading">
                <p class="eyebrow">FAQ</p>
                <h2 id="faq-title">Questions from parents &amp; students</h2>
                <p class="text-muted">Can't find your answer? Call <a href="tel:<?= e($site['contact']['phone_tel']) ?>"><?= e($site['contact']['phone_display']) ?></a> or <a href="/contact">send us a message</a>.</p>
            </header>
            <div class="accordion">
                <?php foreach ($academy['faqs'] as $faq): ?>
                    <details class="accordion__item">
                        <summary><?= e($faq['q']) ?></summary>
                        <div class="accordion__body"><p><?= e($faq['a']) ?></p></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="section section--light" id="enrol" aria-labelledby="enrol-title">
    <div class="wrap">
        <div class="split split--wide-right">
            <header class="section-heading">
                <p class="eyebrow">Enrolment</p>
                <h2 id="enrol-title">Enrol a student</h2>
                <p class="text-muted">It takes a few minutes. We'll contact you to confirm the class and start date.</p>
                <h3 class="label-heading" id="terms">Terms and conditions</h3>
                <ol class="terms-list">
                    <?php foreach ($academy['policies'] as $policy): ?>
                        <li><?= e($policy['text']) ?></li>
                    <?php endforeach; ?>
                </ol>
            </header>
            <?= View::partial('public/partials/form', [
                'form'   => 'enrolment',
                'action' => $formAction,
                'state'  => $formState,
                'site'   => $site,
            ]) ?>
        </div>
    </div>
</section>
