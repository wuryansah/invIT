<div class="page-head">
    <div>
        <h1 class="page-title">Assignments</h1>
        <p class="page-desc">Permanent asset handovers to employees</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/assign/create') ?>"><?= svg_icon('user-check') ?> Assign Asset</a>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/assignments') ?>">
    <div class="filter-item" style="flex:1; min-width:220px;">
        <label>Search</label>
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Assignment no, asset, employee…">
    </div>
    <div class="filter-item">
        <label>Status</label>
        <select class="select" name="status">
            <option value="">All</option>
            <option value="Active" <?= $statusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Returned" <?= $statusFilter === 'Returned' ? 'selected' : '' ?>>Returned</option>
        </select>
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/assignments') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Assignment No</th><th>Asset</th><th>Employee</th><th>Department</th><th>Date</th><th>Purpose</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$pagination['items']): ?>
                <tr><td colspan="8" class="table-empty">No assignment records.</td></tr>
            <?php endif; ?>
            <?php foreach ($pagination['items'] as $a): ?>
                <tr>
                    <td class="td-main"><?= e($a['assignment_no']) ?></td>
                    <td><a href="<?= url('/assets/' . $a['asset_id']) ?>"><?= e($a['asset_code']) ?></a><br><span class="td-sub"><?= e($a['asset_name']) ?></span></td>
                    <td class="user-chip"><span class="avatar avatar-sm"><?= e(strtoupper(substr($a['employee_name'] ?? '?', 0, 1))) ?></span> <?= e($a['employee_name'] ?? '—') ?></td>
                    <td><?= e($a['department_name'] ?? '—') ?></td>
                    <td><?= e(format_date($a['assignment_date'])) ?></td>
                    <td class="muted"><?= e(truncate($a['purpose'] ?? '', 30)) ?></td>
                    <td><?= status_badge($a['status']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="icon-btn" title="Print handover" target="_blank" href="<?= url('/print/handover-assignment/' . $a['id']) ?>"><?= svg_icon('printer') ?></a>
                            <?php if ($a['status'] === 'Active'): ?>
                                <form method="post" action="<?= url('/assign/' . $a['id'] . '/unassign') ?>" data-confirm="Release this assignment? The asset will become available.">
                                    <?= csrf_field() ?>
                                    <button class="icon-btn" title="Release"><?= svg_icon('arrow-left') ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>