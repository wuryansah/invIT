<div class="page-head">
    <div>
        <h1 class="page-title">Transfer Asset</h1>
        <p class="page-desc">Move an assigned asset to a different employee</p>
    </div>
    <div class="page-actions"><a class="btn btn-outline" href="<?= url('/transfers') ?>"><?= svg_icon('arrow-left') ?> Back</a></div>
</div>

<form method="post" action="<?= url('/transfers') ?>">
    <?= csrf_field() ?>
    <div class="card card-pad">
        <div class="form-grid">
            <div class="form-group col-span">
                <label class="form-label">Asset (currently assigned) *</label>
                <select class="select" name="asset_id" required>
                    <option value="">Select an assigned asset…</option>
                    <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name'] . ' (with ' . $a['employee_name'] . ')') ?></option>
                    <?php endforeach; ?>
                    <?php if (!$assets): ?>
                        <option value="" disabled>No assets are currently assigned to anyone.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group col-span">
                <label class="form-label">New Employee</label>
                <select class="select" name="employee_id" required>
                    <option value="">Select the new custodian…</option>
                    <?php foreach ($employees as $em): ?>
                        <option value="<?= $em['id'] ?>"><?= e($em['name']) ?> (<?= e($em['employee_number']) ?>) — <?= e($em['position'] ?: '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Transfer Date *</label>
                <input class="input" type="date" name="transfer_date" value="<?= today() ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Current Condition</label>
                <select class="select" name="current_condition">
                    <?php foreach (\App\Models\Asset::CONDITIONS as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Reason *</label>
                <input class="input" name="reason" placeholder="e.g. Staff rotation, job transfer" required>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Notes</label>
                <textarea class="textarea" name="notes"></textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary"><?= svg_icon('arrow-swap') ?> Transfer</button>
        <a class="btn btn-outline" href="<?= url('/transfers') ?>">Cancel</a>
    </div>
</form>