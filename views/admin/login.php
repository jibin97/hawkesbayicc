<?php
/**
 * @var string $csrf
 * @var string $email
 * @var string|null $error
 * @var string|null $notice
 */
?>
<section class="card login">
    <h1>Admin log in</h1>

    <?php if ($notice !== null): ?>
        <p class="notice" role="status"><?= e($notice) ?></p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p class="error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="/admin/login" novalidate>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="username" maxlength="191"
               value="<?= e($email) ?>" required autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button type="submit">Log in</button>
    </form>
</section>
