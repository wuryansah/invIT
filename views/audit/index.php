<div class="page-head">
    <div>
        <h1 class="page-title">Audit Log</h1>
        <p class="page-desc">Every important action is recorded with user, date/time and IP address</p>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/audit') ?>">
    <div class="filter-item">
        <label>User</label>
        <select class="select" name="user">
            <option value="">All users</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($filters['user'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item" style="flex:1; min-width:180px;">
        <label>Action contains</label>
        <input class="input" name="action" value="<?= e($filters['action'] ?? '') ?>" placeholder="e.g. Asset, Loan, Login…">
    </div>
    <div class="filter-item">
        <label>From</label>
        <input class="input" type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
    </div>
    <div class="filter-item">
        <label>To</label>
        <input class="input" type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/audit') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Date / Time</th><th>User</th><th>Role</th><th>Action</th><th>Description</th><th>IP Address</th></tr></thead>
            <tbody>
            <?php if (!$pagination['items']): ?><tr><td colspan="6" class="table-empty">No audit entries.</td></tr><?php endif; ?>
            <?php foreach ($pagination['items'] as $log): ?>
                <tr>
                    <td class="muted"><?= e(format_datetime($log['created_at'])) ?></td>
                    <td><?= e($log['user_name'] ?? 'System') ?></td>
                    <td><?= e($log['role'] ?? '—') ?></td>
                    <td><span class="badge bg-primary"><?= e($log['action']) ?></span></td>
                    <td class="muted small"><?= e($log['description']) ?></td>
                    <td class="muted"><?= e($log['ip_address'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>