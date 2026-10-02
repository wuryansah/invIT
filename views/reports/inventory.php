<div class="page-head">
    <div>
        <h1 class="page-title">Inventory Report</h1>
        <p class="page-desc"><?= count($assets) ?> assets</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/reports/export/inventory') ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-outline" href="<?= url('/reports') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Asset Code</th><th>Name</th><th>Category</th><th>Brand</th><th>Serial</th><th>Status</th><th>Condition</th><th>Employee</th><th>Department</th><th>Location</th></tr>
            </thead>
            <tbody>
            <?php if (!$assets): ?><tr><td colspan="10" class="table-empty">No assets.</td></tr><?php endif; ?>
            <?php foreach ($assets as $a): ?>
                <tr>
                    <td><a href="<?= url('/assets/' . $a['id']) ?>"><?= e($a['asset_code']) ?></a></td>
                    <td><?= e($a['asset_name']) ?></td>
                    <td><?= e($a['category_name'] ?? '—') ?></td>
                    <td><?= e($a['brand'] ?: '—') ?></td>
                    <td class="muted"><?= e($a['serial_number'] ?: '—') ?></td>
                    <td><?= status_badge($a['status']) ?></td>
                    <td><?= status_badge($a['condition']) ?></td>
                    <td><?= e($a['employee_name'] ?? '—') ?></td>
                    <td><?= e($a['department_name'] ?? '—') ?></td>
                    <td class="muted"><?= e(trim(implode(' / ', array_filter([$a['building'], $a['floor'], $a['room']])))) ?: '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>