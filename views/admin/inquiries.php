<?php
/**
 * @var list<array<string,mixed>> $rows
 * @var int $total
 * @var int $page
 * @var int $pages
 * @var string|null $type
 * @var string|null $status
 * @var array<string,int> $newCounts
 * @var string|null $notice
 */
$typeLabels   = ['enrolment' => 'Enrolment', 'booking' => 'Band booking', 'contact' => 'Contact'];
$statusLabels = ['new' => 'New', 'read' => 'Read', 'archived' => 'Archived'];

$url = static function (array $params) use ($type, $status): string {
    $q = array_filter(['type' => $type, 'status' => $status] + [], static fn ($v) => $v !== null);
    $q = array_filter(array_merge($q, $params), static fn ($v) => $v !== null && $v !== 1);
    return '/admin/inquiries' . ($q === [] ? '' : '?' . http_build_query($q));
};
?>
<section>
    <h1>Inquiries</h1>

    <?php if ($notice !== null): ?>
        <p class="notice" role="status"><?= e($notice) ?></p>
    <?php endif; ?>

    <nav class="admin-filters" aria-label="Filter by type">
        <a href="<?= e($url(['type' => null, 'page' => null])) ?>" <?= $type === null ? 'aria-current="page"' : '' ?>>All</a>
        <?php foreach ($typeLabels as $key => $label): ?>
            <a href="<?= e($url(['type' => $key, 'page' => null])) ?>" <?= $type === $key ? 'aria-current="page"' : '' ?>>
                <?= e($label) ?><?php if ($newCounts[$key] > 0): ?> <span class="count"><?= (int) $newCounts[$key] ?> new</span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <nav class="admin-filters admin-filters--status" aria-label="Filter by status">
        <a href="<?= e($url(['status' => null, 'page' => null])) ?>" <?= $status === null ? 'aria-current="page"' : '' ?>>Inbox</a>
        <?php foreach ($statusLabels as $key => $label): ?>
            <a href="<?= e($url(['status' => $key, 'page' => null])) ?>" <?= $status === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($rows === []): ?>
        <p class="card empty">No inquiries here yet.</p>
    <?php else: ?>
        <p class="text-muted text-small"><?= (int) $total ?> inquir<?= $total === 1 ? 'y' : 'ies' ?></p>
        <ul class="inbox">
            <?php foreach ($rows as $r): ?>
                <li class="inbox__item<?= $r['status'] === 'new' ? ' inbox__item--new' : '' ?>">
                    <a class="inbox__link" href="/admin/inquiry?id=<?= (int) $r['id'] ?>">
                        <span class="inbox__top">
                            <span class="badge"><?= e($typeLabels[$r['type']] ?? $r['type']) ?></span>
                            <?php if ($r['status'] === 'new'): ?><span class="badge badge--new">New</span><?php endif; ?>
                            <time class="inbox__time" datetime="<?= e(str_replace(' ', 'T', (string) $r['created_at'])) ?>"><?= e(date('j M Y, g:ia', strtotime((string) $r['created_at']))) ?></time>
                        </span>
                        <span class="inbox__subject"><?= e($r['subject']) ?></span>
                        <span class="inbox__from"><?= e($r['name']) ?> · <?= e($r['email']) ?></span>
                        <?php if ($r['email_sent_at'] === null && $r['email_error'] !== null): ?>
                            <span class="inbox__warn">Email alert not sent</span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($pages > 1): ?>
            <nav class="pager" aria-label="Pages">
                <?php if ($page > 1): ?><a href="<?= e($url(['page' => $page - 1])) ?>">← Newer</a><?php endif; ?>
                <span>Page <?= (int) $page ?> of <?= (int) $pages ?></span>
                <?php if ($page < $pages): ?><a href="<?= e($url(['page' => $page + 1])) ?>">Older →</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
