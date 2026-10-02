<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · <?= e(setting('company_name', config('app.name'))) ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body class="auth-body">
<div class="auth-card">
    <div class="auth-logo">
        <div class="illustration"><?= svg_icon('box') ?></div>
    </div>
    <div class="auth-brand">
        <div class="auth-brand-text">
            <strong>IT Inventory Management</strong>
        </div>
    </div>
    <p class="auth-sub">Sign in to manage IT assets, equipment loans and assignments.</p>

    <?php if ($msg = ($_SESSION['_flash']['error'] ?? null)): unset($_SESSION['_flash']['error']); ?>
        <div class="alert alert-danger"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['_errors'])): ?>
        <div class="alert alert-danger">
            <ul><?php foreach (array_unique($_SESSION['_errors']) as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            <?php unset($_SESSION['_errors']); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= url('/login') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label class="form-label" for="email">Email address</label>
            <input class="input" id="email" type="email" name="email" value="<?= e(old('email')) ?>" required autofocus autocomplete="email">
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="input" id="password" type="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary" style="width:100%; justify-content:center; padding:11px;">Sign in</button>
    </form>

</div>
</body>
</html>