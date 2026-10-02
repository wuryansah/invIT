<div class="page-head">
    <div>
        <h1 class="page-title">Employee Asset Report</h1>
        <p class="page-desc">All active employees and their equipment</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/reports/export/employee-assets') ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-outline" href="<?= url('/reports') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Employee</th><th>Employee No</th><th>Department</th><th>Assigned Assets</th><th>Active Loans</th></tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="5" class="table-empty">No active employees.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a class="td-main" href="<?= url('/employees/' . $r['employee_id']) ?>"><?= e($r['employee_name']) ?></a></td>
                    <td><?= e($r['employee_number']) ?></td>
                    <td><?= e($r['department_name'] ?? '—') ?></td>
                    <td class="muted small"><?= e($r['assigned_assets'] ?: '—') ?></td>
                    <td><?= status_badge((int)$r['active_loans'] > 0 ? 'Active' : 'Returned') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>