<div class="page-head">
    <div>
        <h1 class="page-title">Transfers</h1>
        <p class="page-desc">Complete asset transfer history between employees</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/transfers/create') ?>"><?= svg_icon('arrow-swap') ?> Transfer Asset</a>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/transfers') ?>">
    <div class="filter-item" style="flex:1; min-width:220px;">
        <label>Search</label>
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Transfer no, asset, employee…">
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/transfers') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Transfer No</th><th>Asset</th><th>From</th><th>To</th><th>Date</th><th>Reason</th><th>Condition</th></tr>
            </thead>
            <tbody>
            <?php if (!$pagination['items']): ?>
                <tr><td colspan="7" class="table-empty">No transfer records.</td></tr>
            <?php endif; ?>
            <?php foreach ($pagination['items'] as $t): ?>
                <tr>
                    <td class="td-main"><?= e($t['transfer_no']) ?></td>
                    <td><a href="<?= url('/assets/' . $t['asset_id']) ?>"><?= e($t['asset_code']) ?></a><br><span class="td-sub"><?= e($t['asset_name']) ?></span></td>
                    <td><?= e($t['previous_employee_name'] ?? '—') ?><br><span class="td-sub"><?= e($t['previous_department_name'] ?? '') ?></span></td>
                    <td class="strong"><?= e($t['new_employee_name'] ?? '—') ?><br><span class="td-sub"><?= e($t['new_department_name'] ?? '') ?></span></td>
                    <td><?= e(format_date($t['transfer_date'])) ?></td>
                    <td class="muted"><?= e($t['reason'] ?: '—') ?></td>
                    <td><?= status_badge($t['current_condition'] ?? $t['previous_condition'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>