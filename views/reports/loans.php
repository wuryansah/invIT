<div class="page-head">
    <div>
        <h1 class="page-title">Loan Report</h1>
        <p class="page-desc"><?= count($rows) ?> loan records</p>
    </div>
    <div class="page-actions">
        <?php
        $exportMap = ['Active' => 'loans-active', 'Overdue' => 'loans-overdue', 'Returned' => 'loans-returned', '' => 'loans-history'];
        $exportKey = $exportMap[$statusFilter] ?? 'loans-history';
        ?>
        <a class="btn btn-outline" href="<?= url('/reports/export/' . $exportKey) ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-outline" href="<?= url('/reports') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="filter-bar">
    <?php $base = url('/reports/loans'); ?>
    <a class="btn btn-sm <?= $statusFilter ? 'btn-outline' : 'btn-soft' ?>" href="<?= $base ?>">All</a>
    <a class="btn btn-sm <?= $statusFilter === 'Active' ? 'btn-soft' : 'btn-outline' ?>" href="<?= $base ?>?status=Active">Active</a>
    <a class="btn btn-sm <?= $statusFilter === 'Overdue' ? 'btn-soft' : 'btn-outline' ?>" href="<?= $base ?>?status=Overdue">Overdue</a>
    <a class="btn btn-sm <?= $statusFilter === 'Returned' ? 'btn-soft' : 'btn-outline' ?>" href="<?= $base ?>?status=Returned">Returned</a>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Loan No</th><th>Employee</th><th>Department</th><th>Asset</th><th>Loan Date</th><th>Due</th><th>Returned</th><th>Purpose</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="9" class="table-empty">No loans.</td></tr><?php endif; ?>
            <?php foreach ($rows as $l): ?>
                <tr>
                    <td><a class="td-main" href="<?= url('/loans/' . $l['id']) ?>"><?= e($l['loan_no']) ?></a></td>
                    <td><?= e($l['employee_name']) ?></td>
                    <td><?= e($l['department_name'] ?? '—') ?></td>
                    <td><?= e($l['asset_code']) ?></td>
                    <td><?= e(format_date($l['loan_date'])) ?></td>
                    <td><?= e(format_date($l['expected_return_date'])) ?></td>
                    <td><?= e(format_date($l['actual_return_date'])) ?></td>
                    <td class="muted"><?= e($l['purpose'] ?: '—') ?></td>
                    <td><?= status_badge($l['report_status'] ?? $l['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>