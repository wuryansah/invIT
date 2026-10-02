<div class="page-head">
    <div>
        <h1 class="page-title">Assign Asset</h1>
        <p class="page-desc">Permanently hand over an available asset to an employee</p>
    </div>
    <div class="page-actions"><a class="btn btn-outline" href="<?= url('/assignments') ?>"><?= svg_icon('arrow-left') ?> Back</a></div>
</div>
<?php $qAsset = (int)(($_GET['asset'] ?? 0)); $qEmployee = (int)(($_GET['employee'] ?? 0)); ?>
<form method="post" action="<?= url('/assign') ?>">
    <?= csrf_field() ?>
    <div class="card card-pad">
        <div class="form-grid">
            <div class="form-group col-span">
                <label class="form-label">Asset *</label>
                <?php if ($qAsset): $asset = null; foreach ($assets as $as): if ((int)$as['id'] === $qAsset) { $asset = $as; break; } endforeach; ?>
                    <input class="input" value="<?= e(($asset['asset_code'] ?? '?') . ' — ' . ($asset['asset_name'] ?? '')) ?>" disabled>
                    <input type="hidden" name="asset_id" value="<?= $qAsset ?>">
                <?php else: ?>
                    <select class="select" name="asset_id" required>
                        <option value="">Select an available asset…</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name']) ?> (<?= e($a['category_name']) ?>)</option>
                        <?php endforeach; ?>
                        <?php if (!$assets): ?>
                            <option value="" disabled>No available assets — release or create assets first.</option>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Employee *</label>
                <?php if ($qEmployee): $employee = null; foreach ($employees as $em): if ((int)$em['id'] === $qEmployee) { $employee = $em; break; } endforeach; ?>
                    <input class="input" value="<?= e(($employee['name'] ?? '?') . ' — ' . ($employee['employee_number'] ?? '')) ?>" disabled>
                    <input type="hidden" name="employee_id" value="<?= $qEmployee ?>">
                <?php else: ?>
                    <select class="select" name="employee_id" required>
                        <option value="">Select an active employee…</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= e($emp['name']) ?> (<?= e($emp['employee_number']) ?>) — <?= e($emp['position'] ?: '') ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Assignment Date *</label>
                <input class="input" type="date" name="assignment_date" value="<?= today() ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Condition at Handover</label>
                <select class="select" name="condition_on_assign">
                    <?php foreach (\App\Models\Asset::CONDITIONS as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Purpose / Reason</label>
                <input class="input" name="purpose" placeholder="e.g. Work from home, onboarding">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Accessories Included</label>
                <input class="input" name="accessories" placeholder="e.g. Charger, Docking station">
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input class="input" name="location" placeholder="Office location">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Notes</label>
                <textarea class="textarea" name="notes"></textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-success"><?= svg_icon('check') ?> Assign Asset</button>
        <a class="btn btn-outline" href="<?= url('/assignments') ?>">Cancel</a>
    </div>
</form>