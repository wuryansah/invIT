<div class="page-head">
    <div>
        <h1 class="page-title">Asset History — <?= e($asset['asset_code']) ?></h1>
        <p class="page-desc"><?= e($asset['asset_name']) ?> · <?= count($rows) ?> records · historical records are never deleted</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/reports/export/asset-history') ?>?asset_id=<?= $asset['id'] ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-outline" href="<?= url('/assets/' . $asset['id']) ?>"><?= svg_icon('eye') ?> View asset</a>
        <a class="btn btn-outline" href="<?= url('/reports') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Date</th><th>Type</th><th>Event</th><th>Description</th><th>By</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="5" class="table-empty">No transaction history for this asset.</td></tr><?php endif; ?>
            <?php foreach ($rows as $h): ?>
                <tr>
                    <td class="muted"><?= e(format_datetime($h['created_at'])) ?></td>
                    <td><span class="badge bg-primary"><?= e($h['type']) ?></span></td>
                    <td class="td-main"><?= e($h['title']) ?></td>
                    <td class="muted small"><?= e($h['description'] ?: '') ?></td>
                    <td><?= e($h['user_name'] ?? 'System') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>