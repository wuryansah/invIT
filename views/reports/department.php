<div class="page-head">
    <div>
        <h1 class="page-title">Department Report</h1>
        <p class="page-desc">IT equipment by department</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/reports/export/department') ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-outline" href="<?= url('/reports') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Department</th><th>Total Assets</th><th>Available</th><th>Assigned</th><th>On Loan</th><th>Maintenance</th><th>Other</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="7" class="table-empty">No data.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="td-main"><?= e($r['department_name']) ?></td>
                    <td class="strong"><?= (int)$r['total_assets'] ?></td>
                    <td><?= (int)$r['available'] ?></td>
                    <td><?= (int)$r['assigned'] ?></td>
                    <td><?= (int)$r['on_loan'] ?></td>
                    <td><?= (int)$r['maintenance'] ?></td>
                    <td><?= (int)$r['other'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>