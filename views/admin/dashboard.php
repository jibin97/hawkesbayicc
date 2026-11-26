<?php
/**
 * @var array{id:int,email:string,name:string,role:string} $user
 * @var array<string,int>|null $newCounts null if the inquiries table isn't available yet
 */
$labels = ['enrolment' => 'Enrolments', 'booking' => 'Band bookings', 'contact' => 'Contact messages'];
?>
<section>
    <h1>Dashboard</h1>
    <p>Welcome, <?= e($user['name']) ?>.</p>

    <h2 class="admin-subhead">New inquiries</h2>
    <?php if ($newCounts === null): ?>
        <p class="error">The inquiries table isn't set up yet. Run migration 004 (see INSTALL.md).</p>
    <?php else: ?>
        <ul class="admin-stats">
            <?php foreach ($labels as $type => $label): ?>
                <li>
                    <a class="card card--link" href="/admin/inquiries?type=<?= e($type) ?>&amp;status=new">
                        <span class="admin-stats__n"><?= (int) $newCounts[$type] ?></span>
                        <span class="text-muted"><?= e($label) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><a class="button" href="/admin/inquiries">Open inbox</a></p>
    <?php endif; ?>

    <h2 class="admin-subhead">Your account</h2>
    <dl class="details">
        <dt>Email</dt>
        <dd><?= e($user['email']) ?></dd>
        <dt>Role</dt>
        <dd><?= e(ucfirst($user['role'])) ?></dd>
    </dl>
</section>
