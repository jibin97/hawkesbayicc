<?php
/**
 * @var array<string,mixed> $inquiry
 * @var string $csrf
 * @var string|null $notice
 */
use App\Forms\Schema;

$typeLabels = ['enrolment' => 'Enrolment', 'booking' => 'Band booking', 'contact' => 'Contact'];
$details    = $inquiry['details'];

// Label each stored value using the form's schema; unknown keys (older forms) fall back to the key name.
$labelled = [];
$known    = [];
foreach (Schema::fields((string) $inquiry['type']) as $f) {
    $known[$f['name']] = true;
    if (!array_key_exists($f['name'], $details)) {
        continue;
    }
    $labelled[] = [$f['report'] ?? $f['label'], $details[$f['name']]];
}
foreach ($details as $key => $value) {
    if (!isset($known[$key])) {
        $labelled[] = [ucfirst(str_replace('_', ' ', (string) $key)), $value];
    }
}
$format = static function (mixed $v): string {
    if (is_bool($v)) {
        return $v ? 'Yes' : 'No';
    }
    if (is_array($v)) {
        return $v === [] ? '—' : implode(', ', array_map('strval', $v));
    }
    return (string) $v === '' ? '—' : (string) $v;
};
?>
<section>
    <p><a href="/admin/inquiries">← All inquiries</a></p>

    <?php if ($notice !== null): ?>
        <p class="notice" role="status"><?= e($notice) ?></p>
    <?php endif; ?>

    <header class="inquiry-head">
        <span class="badge"><?= e($typeLabels[$inquiry['type']] ?? $inquiry['type']) ?></span>
        <h1><?= e($inquiry['subject']) ?></h1>
        <p class="text-muted">
            Received <?= e(date('l j F Y, g:ia', strtotime((string) $inquiry['created_at']))) ?> ·
            Status: <?= e(ucfirst((string) $inquiry['status'])) ?>
        </p>
    </header>

    <div class="inquiry-actions">
        <a class="button" href="mailto:<?= e($inquiry['email']) ?>?subject=<?= rawurlencode('Re: ' . $inquiry['subject']) ?>">Reply by email</a>
        <?php if (!empty($inquiry['phone'])): ?>
            <a class="button button--outline" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $inquiry['phone'])) ?>">Call <?= e($inquiry['phone']) ?></a>
        <?php endif; ?>
        <?php foreach (['new' => 'Mark as unread', 'archived' => 'Archive'] as $status => $label):
            if ($inquiry['status'] === $status) continue; ?>
            <form method="post" action="/admin/inquiry/status">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                <input type="hidden" name="status" value="<?= e($status) ?>">
                <button type="submit" class="button--outline"><?= e($label) ?></button>
            </form>
        <?php endforeach; ?>
        <?php if ($inquiry['status'] === 'archived'): ?>
            <form method="post" action="/admin/inquiry/status">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                <input type="hidden" name="status" value="read">
                <button type="submit" class="button--outline">Move back to inbox</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <dl class="details details--wide">
            <dt>From</dt><dd><?= e($inquiry['name']) ?></dd>
            <dt>Email</dt><dd><a href="mailto:<?= e($inquiry['email']) ?>"><?= e($inquiry['email']) ?></a></dd>
            <?php foreach ($labelled as [$label, $value]): ?>
                <dt><?= e($label) ?></dt>
                <dd class="pre-wrap"><?= e($format($value)) ?></dd>
            <?php endforeach; ?>
        </dl>
    </div>

    <p class="text-muted text-small">
        Email alert:
        <?php if ($inquiry['email_sent_at'] !== null): ?>
            sent <?= e(date('j M Y, g:ia', strtotime((string) $inquiry['email_sent_at']))) ?>.
        <?php else: ?>
            not sent<?= $inquiry['email_error'] !== null ? ' (' . e($inquiry['email_error']) . ')' : '' ?>.
        <?php endif; ?>
    </p>
</section>
