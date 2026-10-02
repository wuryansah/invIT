<div class="page-head">
    <div>
        <h1 class="page-title">System Users</h1>
        <p class="page-desc">Manage application accounts and role-based access</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/users/create') ?>"><?= svg_icon('plus') ?> New User</a>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/users') ?>">
    <div class="filter-item" style="flex:1; min-width:220px;">
        <label>Search</label>
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Name or email…">
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/users') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Employee</th><th>Last Login</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            <?php if (!$pagination['items']): ?><tr><td colspan="7" class="table-empty">No users.</td></tr><?php endif; ?>
            <?php foreach ($pagination['items'] as $u): ?>
                <tr>
                    <td class="user-chip"><span class="avatar avatar-sm"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></span> <?= e($u['name']) ?></td>
                    <td class="muted"><?= e($u['email']) ?></td>
                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'red' : ($u['role'] === 'staff' ? 'info' : 'secondary') ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                    <td><?= e($u['employee_name'] ?? '—') ?></td>
                    <td class="muted"><?= e(format_datetime($u['last_login_at'])) ?></td>
                    <td><?= status_badge($u['is_active'] ? 'Active' : 'Inactive') ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="icon-btn" href="<?= url('/users/' . $u['id'] . '/edit') ?>"><?= svg_icon('edit') ?></a>
                            <form method="post" action="<?= url('/users/' . $u['id'] . '/toggle') ?>" data-confirm="<?= $u['is_active'] ? 'Deactivate this user?' : 'Activate this user?' ?>">
                                <?= csrf_field() ?>
                                <button class="icon-btn" title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>" <?= $u['id'] === (auth()['id'] ?? 0) ? 'disabled' : '' ?>><?= svg_icon($u['is_active'] ? 'x' : 'check') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>