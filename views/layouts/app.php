<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle ?? config('app.name')) ?> · <?= e(setting('company_name', config('app.name'))) ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
</head>
<body>
<?php
$nav = [];

if (Auth::role() !== 'employee') {
    $nav[] = ['href' => '/', 'label' => 'Dashboard', 'icon' => 'home', 'module' => 'dashboard'];
} else {
    $nav[] = ['href' => '/my-assets', 'label' => 'My Equipment', 'icon' => 'package-check', 'module' => 'my-assets'];
}

if (Auth::isStaff()) {
    $nav[] = ['href' => '/assets', 'label' => 'Assets', 'icon' => 'box', 'module' => 'assets'];
    $nav[] = ['href' => '/employees', 'label' => 'Employees', 'icon' => 'users', 'module' => 'employees'];
    $nav[] = ['href' => '/assignments', 'label' => 'Assignments', 'icon' => 'user-check', 'module' => 'assignments'];
    $nav[] = ['href' => '/transfers', 'label' => 'Transfers', 'icon' => 'arrow-swap', 'module' => 'transfers'];
    $nav[] = ['href' => '/loans', 'label' => 'Loans', 'icon' => 'clock', 'module' => 'loans'];
    $nav[] = ['href' => '/maintenance', 'label' => 'Maintenance', 'icon' => 'wrench', 'module' => 'maintenance'];
    $nav[] = ['href' => '/reports', 'label' => 'Reports', 'icon' => 'chart', 'module' => 'reports'];
    $nav[] = ['href' => '/search', 'label' => 'Global Search', 'icon' => 'search', 'module' => 'search'];
}

$nav[] = ['href' => '/notifications', 'label' => 'Notifications', 'icon' => 'bell', 'module' => 'notifications'];

if (Auth::isAdmin()) {
    $nav[] = ['href' => '/adjustments', 'label' => 'Adjustments', 'icon' => 'sliders', 'module' => 'adjustments'];
    $nav[] = ['href' => '/audit', 'label' => 'Audit Log', 'icon' => 'shield', 'module' => 'audit'];
    $nav[] = ['href' => '/users', 'label' => 'Users', 'icon' => 'users', 'module' => 'users'];
    $nav[] = ['href' => '/settings', 'label' => 'Settings', 'icon' => 'settings', 'module' => 'settings'];
}

$user = $currentUser ?? Auth::user();
$unread = \App\Models\Notification::unreadCount();
$isStaff = Auth::isStaff();
?>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark"><?= svg_icon('box') ?></span>
            <div>
                <strong>inventory IT</strong>
                <small><?= e(setting('company_name', 'Inventory')) ?></small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($nav as $item): ?>
                <a href="<?= url($item['href']) ?>" class="nav-link<?= ($pageModule ?? '') === $item['module'] ? ' active' : '' ?>">
                    <?= svg_icon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <span class="avatar"><?= e(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?></span>
                <div>
                    <strong><?= e($user['name'] ?? '') ?></strong>
                    <small><?= ucfirst(e($user['role'] ?? '')) ?></small>
                </div>
                <form method="post" action="<?= url('/logout') ?>">
                    <?= csrf_field() ?>
                    <button class="icon-btn" title="Sign out"><?= svg_icon('logout') ?></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn menu-toggle" id="menuToggle" aria-label="Toggle menu"><?= svg_icon('menu') ?></button>

            <?php if ($isStaff): ?>
            <form class="topbar-search" method="get" action="<?= url('/search') ?>">
                <?= svg_icon('search') ?>
                <input type="search" name="q" placeholder="Search assets, serials, employees…" value="<?= e($_GET['q'] ?? '') ?>">
            </form>
            <?php else: ?>
                <span class="topbar-search-empty"></span>
            <?php endif; ?>

            <div class="topbar-right">
                <a href="<?= url('/notifications') ?>" class="icon-btn bell<?= $unread > 0 ? ' has-dot' : '' ?>" title="Notifications">
                    <?= svg_icon('bell') ?>
                    <?php if ($unread > 0): ?><span class="notif-badge"><?= min($unread, 99) ?></span><?php endif; ?>
                </a>
                <div class="user-menu">
                    <span class="avatar"><?= e(strtoupper(substr($user['name'] ?? '?', 0, 1))) ?></span>
                    <div class="user-meta">
                        <strong><?= e($user['name'] ?? '') ?></strong>
                        <small><?= ucfirst(e($user['role'] ?? '')) ?></small>
                    </div>
                    <a class="icon-btn" href="<?= url('/profile') ?>" title="My profile"><?= svg_icon('settings') ?></a>
                </div>
            </div>
        </header>

        <main class="content">
            <?php
            foreach ($flashMessages as $type => $message) {
                echo '<div class="alert alert-' . ($type === 'error' ? 'danger' : $type) . '">'
                    . e($message)
                    . '<button class="alert-close" data-dismiss>×</button></div>';
            }
            if (isset($_SESSION['_errors'])) {
                echo '<div class="alert alert-danger"><strong>Please fix the following:</strong><ul>';
                foreach (array_unique($_SESSION['_errors']) as $error) {
                    echo '<li>' . e($error) . '</li>';
                }
                echo '</ul><button class="alert-close" data-dismiss>×</button></div>';
                unset($_SESSION['_errors']);
            }
            ?>
            <?= $content ?>
        </main>

        <footer class="footer">
            © <?= date('Y') ?> <?= e(setting('company_name', 'invIT')) ?> — IT Inventory Management System by Wuryansah
        </footer>
    </div>
</div>

<script>
    window.APP = {
        baseUrl: <?= json_encode(App::baseUrl()) ?>,
        csrf: <?= json_encode(csrf_token()) ?>
    };
</script>
<script src="<?= asset_url('js/app.js') ?>"></script>
<?php if (isset($extraScripts)) foreach ($extraScripts as $s): ?>
    <script src="<?= asset_url($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>