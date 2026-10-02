<div class="page-head">
    <div>
        <h1 class="page-title">Employees</h1>
        <p class="page-desc">Company employee directory used for assigning and loaning assets</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/employees/create') ?>"><?= svg_icon('plus') ?> New Employee</a>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/employees') ?>">
    <div class="filter-item" style="flex:1; min-width:220px;">
        <label>Search</label>
        <input class="input" type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Name, employee number, email, position…">
    </div>
    <div class="filter-item">
        <label>Department</label>
        <select class="select" name="department">
            <option value="">All</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= ($filters['department'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Status</label>
        <select class="select" name="status">
            <option value="">All</option>
            <option value="Active" <?= ($filters['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/employees') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Employee</th><th>Employee Number</th><th>Department</th><th>Position</th><th>Email</th><th>Status</th><th>Assigned</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$pagination['items']): ?>
                <tr><td colspan="8" class="table-empty">No employees found.</td></tr>
            <?php endif; ?>
            <?php foreach ($pagination['items'] as $e): ?>
                <?php $assignedCount = \App\Core\Database::first("SELECT COUNT(*) c FROM assets WHERE current_employee_id = ? AND status = 'Assigned'", [$e['id']])['c']; ?>
                <tr>
                    <td class="user-chip">
                        <?php if ($e['photo']): ?><img class="avatar" style="border-radius:50%;object-fit:cover;width:30px;height:30px;" src="<?= asset_url($e['photo']) ?>"><?php else: ?><span class="avatar avatar-sm"><?= e(strtoupper(substr($e['name'], 0, 1))) ?></span><?php endif; ?>
                        <a class="td-main" href="<?= url('/employees/' . $e['id']) ?>"><?= e($e['name']) ?></a>
                    </td>
                    <td class="muted"><?= e($e['employee_number']) ?></td>
                    <td><?= e($e['department_name'] ?? '—') ?></td>
                    <td><?= e($e['position'] ?: '—') ?></td>
                    <td class="muted"><?= e($e['email'] ?: '—') ?></td>
                    <td><?= status_badge($e['status']) ?></td>
                    <td class="strong"><?= $assignedCount ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="icon-btn" title="Open record" href="<?= url('/employees/' . $e['id']) ?>"><?= svg_icon('eye') ?></a>
                            <?php if (Auth::isStaff()): ?><a class="icon-btn" title="Edit" href="<?= url('/employees/' . $e['id'] . '/edit') ?>"><?= svg_icon('edit') ?></a><?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>