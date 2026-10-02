<div class="page-head">
    <div>
        <h1 class="page-title"><?= e($loan['loan_no']) ?></h1>
        <?= status_badge($loan['derived_status']) ?>
    </div>
    <div class="page-actions">
        <?php if ($loan['status'] === 'Active'): ?>
            <a class="btn btn-outline" target="_blank" href="<?= url('/print/handover-loan/' . $loan['id']) ?>"><?= svg_icon('printer') ?> Loan Form</a>
            <a class="btn btn-primary" href="#return"><?= svg_icon('arrow-left') ?> Process Return</a>
        <?php else: ?>
            <a class="btn btn-outline" target="_blank" href="<?= url('/print/return/' . $loan['id']) ?>"><?= svg_icon('printer') ?> Return Form</a>
        <?php endif; ?>
        <a class="btn btn-outline" href="<?= url('/loans') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="detail-grid" style="grid-template-columns: 1fr 1fr;">
    <div class="card card-pad">
        <div class="section-label">Loan Details</div>
        <ul class="detail-list">
            <li><span class="d-label">Loan Number</span><span class="d-value"><?= e($loan['loan_no']) ?></span></li>
            <li><span class="d-label">Employee</span><span class="d-value"><?= e($loan['employee_name']) ?> (<?= e($loan['employee_number']) ?>)</span></li>
            <li><span class="d-label">Department</span><span class="d-value"><?= e($loan['department_name'] ?? '—') ?></span></li>
            <li><span class="d-label">Purpose</span><span class="d-value"><?= e($loan['purpose'] ?: '—') ?></span></li>
            <li><span class="d-label">Loan Date</span><span class="d-value"><?= e(format_date($loan['loan_date'])) ?></span></li>
            <li><span class="d-label">Expected Return</span><span class="d-value"><?= e(format_date($loan['expected_return_date'])) ?></span></li>
            <li><span class="d-label">Actual Return</span><span class="d-value"><?= e(format_date($loan['actual_return_date'])) ?></span></li>
            <li><span class="d-label">Condition Before</span><span class="d-value"><?= e($loan['condition_before'] ?: '—') ?></span></li>
            <li><span class="d-label">Condition After</span><span class="d-value"><?= e($loan['condition_after_return'] ?: '—') ?></span></li>
            <li><span class="d-label">Issued By</span><span class="d-value"><?= e($loan['issued_by_name'] ?? '—') ?></span></li>
            <li><span class="d-label">Approved By</span><span class="d-value"><?= e($loan['approved_by_name'] ?? '—') ?></span></li>
            <?php if ($loan['status'] === 'Returned'): ?>
                <li><span class="d-label">Returned To</span><span class="d-value"><?= e($loan['returned_to_name'] ?? '—') ?></span></li>
                <li><span class="d-label">Received By</span><span class="d-value"><?= e($loan['received_by_name'] ?? '—') ?></span></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card card-pad">
        <div class="section-label">Asset</div>
        <p class="td-main strong" style="font-size:15px;"><?= e($loan['asset_code']) ?> — <?= e($loan['asset_name']) ?></p>
        <ul class="detail-list">
            <li><span class="d-label">Category</span><span class="d-value"><?= e($loan['category_name'] ?? '—') ?></span></li>
            <li><span class="d-label">Serial Number</span><span class="d-value"><?= e($loan['serial_number'] ?: '—') ?></span></li>
            <li><span class="d-label">Accessories Included</span><span class="d-value"><?= e($loan['accessories_included'] ?: '—') ?></span></li>
            <li><span class="d-label">Accessories Returned</span><span class="d-value"><?= e($loan['accessories_returned'] ?: '—') ?></span></li>
            <li><span class="d-label">Missing Accessories</span><span class="d-value" style="color:<?= $loan['missing_accessories'] ? 'var(--danger)' : '' ?>;"><?= e($loan['missing_accessories'] ?: '—') ?></span></li>
            <li><span class="d-label">Damage Description</span><span class="d-value" style="color:<?= $loan['damage_description'] ? 'var(--danger)' : '' ?>;"><?= e($loan['damage_description'] ?: '—') ?></span></li>
            <li><span class="d-label">Employee Acknowledged</span><span class="d-value"><?= $loan['employee_acknowledged'] ? 'Yes' : 'No' ?></span></li>
        </ul>
        <?php if ($loan['notes']): ?>
            <div class="section-label">Notes</div><p class="muted small"><?= nl2br(e($loan['notes'])) ?></p>
        <?php endif; ?>
        <div class="section-label">Acknowledgement</div>
        <div style="margin-top:26px;" class="print-only-after"></div>
    </div>
</div>

<?php if ($loan['status'] === 'Active'): ?>
<div class="card mt-16" id="return">
    <div class="card-head"><h3>Process Return</h3></div>
    <div class="card-pad">
        <form method="post" action="<?= url('/loans/' . $loan['id'] . '/return') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Return Date *</label>
                    <input class="input" type="date" name="return_date" value="<?= today() ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Asset Condition After Return *</label>
                    <select class="select" name="condition_after">
                        <?php foreach (\App\Models\Asset::CONDITIONS as $c): ?>
                            <option value="<?= e($c) ?>" <?= $c === 'Good' ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Damaged or Critical will move the asset to Under Maintenance.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Accessories Returned</label>
                    <input class="input" name="accessories_returned" placeholder="What was returned with the asset">
                </div>
                <div class="form-group">
                    <label class="form-label">Missing Accessories</label>
                    <input class="input" name="missing_accessories" placeholder="List anything not returned">
                </div>
                <div class="form-group col-span">
                    <label class="form-label">Damage Description</label>
                    <textarea class="textarea" name="damage_description" placeholder="Describe any damage (if any)"></textarea>
                </div>
                <div class="form-group col-span">
                    <label class="form-label">Notes</label>
                    <textarea class="textarea" name="notes"></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" data-confirm="Confirm this return?"><?= svg_icon('check') ?> Confirm Return</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>