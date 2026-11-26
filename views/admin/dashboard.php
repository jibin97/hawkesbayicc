<?php
/** @var array{id:int,email:string,name:string,role:string} $user */
?>
<section>
    <h1>Dashboard</h1>
    <p>Welcome, <?= e($user['name']) ?>.</p>
    <dl class="details">
        <dt>Email</dt>
        <dd><?= e($user['email']) ?></dd>
        <dt>Role</dt>
        <dd><?= e(ucfirst($user['role'])) ?></dd>
    </dl>
    <p>Content management is coming soon.</p>
</section>
