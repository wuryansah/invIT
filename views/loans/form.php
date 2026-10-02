<div class="page-head">
    <div>
        <h1 class="page-title">New Loan</h1>
        <p class="page-desc">Temporarily loan equipment to an employee</p>
    </div>
    <div class="page-actions"><a class="btn btn-outline" href="<?= url('/loans') ?>"><?= svg_icon('arrow-left') ?> Back</a></div>
</div>
<?php $qAsset = (int)(($_GET['asset'] ?? 0)); $qEmployee = (int)(($_GET['employee'] ?? 0)); ?>
<form method="post" action="<?= url('/loans') ?>">
    <?= csrf_field() ?>
    <div class="card card-pad">
        <div class="form-grid">
            <div class="form-group col-span">
                <label class="form-label">Asset *</label>
                <?php if ($qAsset): $asset = null; foreach ($assets as $a): if ((int)$a['id'] === $qAsset) { $asset = $a; break; } endforeach; ?>
                    <input class="input" value="<?= e(($asset['asset_code'] ?? '?') . ' — ' . ($asset['asset_name'] ?? '')) ?>" disabled>
                    <input type="hidden" name="asset_id" value="<?= $qAsset ?>">
                <?php else: ?>
                    <select class="select" name="asset_id" required>
                        <option value="">Select an available asset…</option>
                        <?php foreach ($assets as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['asset_code'] . ' — ' . $a['asset_name']) ?> (<?= e($a['category_name']) ?>)</option>
                        <?php endforeach; ?>
                        <?php if (!$assets): ?>
                            <option value="" disabled>No available assets — release assets first.</option>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Employee *</label>
                <?php if ($qEmployee): $emp = null; foreach ($employees as $em): if ((int)$em['id'] === $qEmployee) { $emp = $em; break; } endforeach; ?>
                    <input class="input" value="<?= e(($emp['name'] ?? '?') . ' — ' . ($emp['employee_number'] ?? '')) ?>" disabled>
                    <input type="hidden" name="employee_id" value="<?= $qEmployee ?>">
                <?php else: ?>
                    <select class="select" name="employee_id" required>
                        <option value="">Select an active employee…</option>
                        <?php foreach ($employees as $em): ?>
                            <option value="<?= $em['id'] ?>"><?= e($em['name']) ?> (<?= e($em['employee_number']) ?>) — <?= e($em['position'] ?: '') ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Loan Date *</label>
                <input class="input" type="date" name="loan_date" value="<?= today() ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Expected Return *</label>
                <input class="input" type="date" name="expected_return_date" value="<?= e(date('Y-m-d', strtotime('+7 days'))) ?>" required>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Purpose</label>
                <input class="input" name="purpose" placeholder="e.g. Business trip, project, event">
            </div>
            <div class="form-group">
                <label class="form-label">Condition Before Loan</label>
                <select class="select" name="condition_before">
                    <?php foreach (\App\Models\Asset::CONDITIONS as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-span">
                <div class="check-row" style="margin-top:22px;">
                    <input type="checkbox" id="ack" name="employee_acknowledged" value="1" checked>
                    <label for="ack">Employee acknowledges receipt of the equipment.</label>
                </div>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Accessories Included</label>
                <input class="input" name="accessories_included" placeholder="e.g. Charger, case, bag">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Notes</label>
                <textarea class="textarea" name="notes"></textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-success"><?= svg_icon('check') ?> Create Loan</button>
        <a class="btn btn-outline" href="<?= url('/loans') ?>">Cancel</a>
    </div>
</form>