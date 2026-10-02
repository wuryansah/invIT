<div class="page-head">
    <div>
        <h1 class="page-title">Maintenance</h1>
        <p class="page-desc">Track repairs and assets currently under maintenance</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/reports/maintenance') ?>"><?= svg_icon('file-text') ?> Report</a>
    </div>
</div>

<div class="card card-pad mb-16">
    <div class="section-label" style="margin-top:0;">Start Maintenance</div>
    <form class="filter-bar" method="post" action="<?= url('/maintenance') ?>" style="margin-bottom:0;">
        <?= csrf_field() ?>
        <div class="filter-item">
            <label>Asset</label>
            <select class="select" name="asset_id" required style="min-width:220px;">
                <option value="">Select asset…</option>
                <?php foreach ($assetsInMaintenance as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name']) ?> (already in maintenance)</option>
                <?php endforeach; ?>
                <?php foreach (\App\Models\Asset::where(['status' => 'Available'], 'asset_code') as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name']) ?> (available)</option>
                <?php endforeach; ?>
                <?php foreach (\App\Models\Asset::where(['status' => 'Damaged'], 'asset_code') as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name']) ?> (damaged)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-item">
            <label>Start Date</label>
            <input class="input" type="date" name="started_at" value="<?= today() ?>" required>
        </div>
        <div class="filter-item">
            <label>Type</label>
            <input class="input" name="type" placeholder="Repair / Service" required>
        </div>
        <div class="filter-item">
            <label>Cost</label>
            <input class="input" type="number" name="cost" value="0" step="0.01" min="0">
        </div>
        <div class="filter-item" style="flex:1; min-width:160px;">
            <label>Description</label>
            <input class="input" name="description" placeholder="Describe the issue">
        </div>
        <div class="filter-item">
            <label>Performed By</label>
            <input class="input" name="performed_by" placeholder="Technician">
        </div>
        <button class="btn btn-soft"><?= svg_icon('wrench') ?> Start</button>
    </form>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Maintenance No</th><th>Asset</th><th>Type</th><th>Start</th><th>Completed</th><th>Cost</th><th>Performed By</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$pagination['items']): ?>
                <tr><td colspan="9" class="table-empty">No maintenance records.</td></tr>
            <?php endif; ?>
            <?php foreach ($pagination['items'] as $m): ?>
                <tr>
                    <td class="td-main"><?= e($m['maintenance_no']) ?></td>
                    <td><a href="<?= url('/assets/' . $m['asset_id']) ?>"><?= e($m['asset_code']) ?></a><br><span class="td-sub"><?= e($m['asset_name']) ?></span></td>
                    <td><?= e($m['type']) ?></td>
                    <td><?= e(format_date($m['started_at'])) ?></td>
                    <td><?= e(format_date($m['completed_at'])) ?></td>
                    <td><?= e(format_money($m['cost'])) ?></td>
                    <td><?= e($m['performed_by'] ?: '—') ?></td>
                    <td><?= status_badge($m['status']) ?></td>
                    <td>
                        <?php if ($m['status'] === 'In Progress'): ?>
                            <form method="post" action="<?= url('/maintenance/' . $m['id'] . '/complete') ?>" data-confirm="Complete this maintenance? The asset will become Available.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="completed_at" value="<?= today() ?>">
                                <button class="btn btn-success btn-sm"><?= svg_icon('check') ?> Complete</button>
                            </form>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>