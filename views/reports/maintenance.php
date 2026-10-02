<div class="page-head">
    <div>
        <h1 class="page-title">Maintenance Report</h1>
        <p class="page-desc">All maintenance records (<?= count($rows) ?>)</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/reports/export/maintenance') ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-outline" href="<?= url('/reports') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No</th><th>Asset</th><th>Category</th><th>Type</th><th>Started</th><th>Completed</th><th>Cost</th><th>Performed By</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="9" class="table-empty">No maintenance records.</td></tr><?php endif; ?>
            <?php foreach ($rows as $m): ?>
                <tr>
                    <td class="td-main"><?= e($m['maintenance_no']) ?></td>
                    <td><a href="<?= url('/assets/' . $m['asset_id']) ?>"><?= e($m['asset_code']) ?></a><br><span class="td-sub"><?= e($m['asset_name']) ?></span></td>
                    <td><?= e($m['category_name'] ?? '—') ?></td>
                    <td><?= e($m['type']) ?></td>
                    <td><?= e(format_date($m['started_at'])) ?></td>
                    <td><?= e(format_date($m['completed_at'])) ?></td>
                    <td><?= e(format_money($m['cost'])) ?></td>
                    <td><?= e($m['performed_by'] ?: '—') ?></td>
                    <td><?= status_badge($m['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>