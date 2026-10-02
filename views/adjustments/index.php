<div class="page-head">
    <div>
        <h1 class="page-title">Inventory Adjustments</h1>
        <p class="page-desc">Stock opname, found/missing assets, relocation and data corrections — every change is audited</p>
    </div>
</div>

<div class="card card-pad mb-16">
    <div class="section-label" style="margin-top:0;">Record Adjustment</div>
    <form method="post" action="<?= url('/adjustments') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Asset *</label>
                <select class="select" name="asset_id" required>
                    <option value="">Select asset…</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name']) ?> (<?= e($a['status']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Adjustment Type *</label>
                <select class="select" name="type" required>
                    <option value="Stock Opname">Stock Opname</option>
                    <option value="Found">Found Asset</option>
                    <option value="Missing">Missing Asset</option>
                    <option value="Quantity Correction">Quantity Correction</option>
                    <option value="Relocation">Relocation</option>
                    <option value="Data Correction">Data Correction</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">New Status (optional)</label>
                <select class="select" name="new_status">
                    <option value="">Keep current</option>
                    <?php foreach (\App\Models\Asset::STATUSES as $s): ?>
                        <option value="<?= e($s) ?>"><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">New Location</label>
                <input class="input" name="new_location" placeholder="Building / Floor / Room" title="Shorthand supported: Building / Floor / Room">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Reason *</label>
                <input class="input" name="reason" placeholder="Reason for this adjustment" required>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Notes</label>
                <textarea class="textarea" name="notes"></textarea>
            </div>
        </div>
        <button class="btn btn-primary"><?= svg_icon('check') ?> Save Adjustment</button>
    </form>
</div>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>No</th><th>Asset</th><th>Type</th><th>Old Status</th><th>New Status</th><th>Old Location</th><th>New Location</th><th>Reason</th><th>By</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php if (!$pagination['items']): ?><tr><td colspan="10" class="table-empty">No adjustments yet.</td></tr><?php endif; ?>
            <?php foreach ($pagination['items'] as $g): ?>
                <tr>
                    <td class="td-main"><?= e($g['adjustment_no']) ?></td>
                    <td><?= e($g['asset_code'] ?? '—') ?></td>
                    <td><span class="badge bg-primary"><?= e($g['type']) ?></span></td>
                    <td><?= e($g['old_status']) ?></td>
                    <td><?= e($g['new_status']) ?></td>
                    <td class="muted small"><?= e($g['old_location'] ?: '—') ?></td>
                    <td class="muted small"><?= e($g['new_location'] ?: '—') ?></td>
                    <td class="muted small"><?= e($g['reason']) ?></td>
                    <td><?= e($g['performed_by_name'] ?? '—') ?></td>
                    <td class="muted"><?= e(format_datetime($g['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>