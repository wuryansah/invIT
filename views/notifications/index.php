<div class="page-head">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-desc">Loan due dates, returns, transfers, damage and warranty alerts</p>
    </div>
    <div class="page-actions">
        <form method="post" action="<?= url('/notifications/read-all') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-outline"><?= svg_icon('check') ?> Mark all as read</button>
        </form>
    </div>
</div>

<div class="card">
    <?php if (!$notifications): ?>
        <p class="card-pad muted">No notifications.</p>
    <?php endif; ?>
    <ul class="notif-list">
        <?php foreach ($notifications as $n): ?>
            <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                <span class="n-type bg-<?= $n['is_read'] ? 'secondary' : 'blue' ?>"><?= svg_icon($n['type'] === 'loan' ? 'clock' : ($n['type'] === 'damage' ? 'alert' : ($n['type'] === 'maintenance' ? 'wrench' : 'check'))) ?></span>
                <div>
                    <div class="n-title"><?= e($n['title']) ?></div>
                    <?php if ($n['message']): ?><div class="n-msg"><?= e($n['message']) ?></div><?php endif; ?>
                    <div class="n-time"><?= e(ago($n['created_at'])) ?></div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>