<?php $isStaff = Auth::isStaff(); ?>
<div class="page-head">
    <div class="flex wrap">
        <h1 class="page-title"><?= e($asset['asset_code']) ?></h1>
        <?= status_badge($asset['status']) ?>
        <?= status_badge($asset['condition']) ?>
    </div>
    <div class="page-actions">
        <?php if ($isStaff): ?>
            <?php if ($asset['status'] === 'Available'): ?>
                <a class="btn btn-soft" href="<?= url('/assign/create') ?>?asset=<?= $asset['id'] ?>"><?= svg_icon('user-check') ?> Assign</a>
                <a class="btn btn-soft" href="<?= url('/loans/create') ?>?asset=<?= $asset['id'] ?>"><?= svg_icon('clock') ?> Loan Out</a>
            <?php endif; ?>
            <?php if ($asset['status'] === 'Under Maintenance'): ?>
                <a class="btn btn-soft" href="<?= url('/maintenance') ?>"><?= svg_icon('wrench') ?> Maintenance</a>
            <?php endif; ?>
            <a class="btn btn-outline" href="<?= url('/qr/' . urlencode($asset['asset_code'])) ?>"><?= svg_icon('qr') ?> QR</a>
            <a class="btn btn-outline" href="<?= url('/reports/asset-history/' . $asset['id']) ?>"><?= svg_icon('file-text') ?> History</a>
            <a class="btn btn-outline" href="<?= url('/assets/' . $asset['id'] . '/edit') ?>"><?= svg_icon('edit') ?> Edit</a>
            <form method="post" action="<?= url('/assets/' . $asset['id'] . '/clone') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button class="btn btn-outline" title="Duplicate this asset as a new record"><?= svg_icon('plus') ?> Clone</button>
            </form>
            <?php if (Auth::isAdmin()): ?>
                <form method="post" action="<?= url('/assets/' . $asset['id'] . '/delete') ?>" style="display:inline;" data-confirm="Delete this asset permanently? This cannot be undone.">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger"><?= svg_icon('trash') ?> Delete</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <a class="btn btn-outline" href="<?= url('/assets') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<div class="detail-grid">
    <div>
        <div class="card mb-16">
            <?php if ($asset['photo']): ?>
                <img class="asset-photo" src="<?= asset_url($asset['photo']) ?>" alt="<?= e($asset['asset_code']) ?>">
            <?php else: ?>
                <div class="asset-photo empty"><?= svg_icon('camera') ?></div>
            <?php endif; ?>
            <div class="qr-wrap">
                <div id="assetQr"></div>
                <div class="qr-tag"><?= e($asset['asset_code']) ?></div>
                <div class="muted small">Scan with a phone camera or the QR scanner to open this asset</div>
            </div>
        </div>

        <div class="card card-pad mb-16">
            <div class="section-label">Current Status</div>
            <ul class="detail-list">
                <li><span class="d-label">Status</span><span class="d-value"><?= e($asset['status']) ?></span></li>
                <li><span class="d-label">Condition</span><span class="d-value"><?= e($asset['condition']) ?></span></li>
                <li><span class="d-label">Current Employee</span><span class="d-value"><?= e($employee['name'] ?? '—') ?></span></li>
                <li><span class="d-label">Department</span><span class="d-value"><?= e($department['name'] ?? '—') ?></span></li>
                <li><span class="d-label">Location</span><span class="d-value"><?= e(trim(implode(' / ', array_filter([$asset['building'], $asset['floor'], $asset['room']])))) ?: '—' ?></span></li>
            </ul>
        </div>

        <?php if ($asset['warranty_expiration'] && $asset['warranty_expiration'] >= date('Y-m-d', strtotime('-30 days'))): ?>
            <div class="alert alert-<?= $asset['warranty_expiration'] < today() ? 'danger' : 'warning' ?>">
                <?= $asset['warranty_expiration'] < today() ? 'Warranty expired on ' . format_date($asset['warranty_expiration']) : 'Warranty expires ' . format_date($asset['warranty_expiration']) . ' — soon.' ?>
            </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="card mb-16">
            <div class="card-head"><div class="flex" style="justify-content:flex-start;"><?= svg_icon('box') ?><h3 style="margin-left:6px;">Asset Information</h3></div></div>
            <div class="card-pad">
                <ul class="detail-list" style="columns:2; column-gap:24px;">
                    <li><span class="d-label">Asset Code</span><span class="d-value"><?= e($asset['asset_code']) ?></span></li>
                    <li><span class="d-label">Name</span><span class="d-value"><?= e($asset['asset_name']) ?></span></li>
                    <li><span class="d-label">Category</span><span class="d-value"><?= e($category['name'] ?? '—') ?></span></li>
                    <li><span class="d-label">Brand</span><span class="d-value"><?= e($asset['brand'] ?: '—') ?></span></li>
                    <li><span class="d-label">Model</span><span class="d-value"><?= e($asset['model'] ?: '—') ?></span></li>
                    <li><span class="d-label">Serial Number</span><span class="d-value"><?= e($asset['serial_number'] ?: '—') ?></span></li>
                    <li><span class="d-label">IP Address</span><span class="d-value"><?= e($asset['ip_address'] ?: '—') ?></span></li>
                    <li><span class="d-label">Product Number</span><span class="d-value"><?= e($asset['product_number'] ?: '—') ?></span></li>
                    <li><span class="d-label">Purchase Date</span><span class="d-value"><?= e(format_date($asset['purchase_date'])) ?></span></li>
                    <li><span class="d-label">Purchase Price</span><span class="d-value"><?= e(format_money($asset['purchase_price'])) ?></span></li>
                    <li><span class="d-label">Warranty Expires</span><span class="d-value"><?= e(format_date($asset['warranty_expiration'])) ?></span></li>
                    <li><span class="d-label">Supplier</span><span class="d-value"><?= e($asset['supplier'] ?: '—') ?></span></li>
                    <li><span class="d-label">Invoice</span><span class="d-value"><?= e($asset['invoice_number'] ?: '—') ?></span></li>
                    <li><span class="d-label">Storage Location</span><span class="d-value"><?= e($asset['storage_location'] ?: '—') ?></span></li>
                    <li><span class="d-label">Created</span><span class="d-value"><?= e(format_datetime($asset['created_at'])) ?></span></li>
                </ul>
                <?php if ($asset['specification']): ?>
                    <div class="section-label">Specification</div>
                    <p class="muted small"><?= nl2br(e($asset['specification'])) ?></p>
                <?php endif; ?>
                <?php if ($asset['notes']): ?>
                    <div class="section-label">Notes</div>
                    <p class="muted small"><?= nl2br(e($asset['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-16">
            <div class="card-head"><div class="flex" style="justify-content:flex-start;"><?= svg_icon('clock') ?><h3 style="margin-left:6px;">Transaction History</h3></div></div>
            <div class="card-pad">
                <?php if (!$history): ?>
                    <p class="muted">No transaction history yet.</p>
                <?php else: ?>
                    <ol class="timeline">
                        <?php foreach (array_reverse($history) as $h): ?>
                            <li class="timeline-item">
                                <div class="t-date"><?= e(format_datetime($h['created_at'])) ?> · <?= e($h['type']) ?> · by <?= e($h['user_name'] ?? 'System') ?></div>
                                <div class="t-title"><?= e($h['title']) ?></div>
                                <?php if ($h['description']): ?><div class="t-desc"><?= e($h['description']) ?></div><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isStaff): ?>
        <div class="card mb-16">
            <div class="card-head"><div class="flex" style="justify-content:flex-start;"><?= svg_icon('sliders') ?><h3 style="margin-left:6px;">Change Status</h3></div></div>
            <div class="card-pad">
                <form class="flex wrap" method="post" action="<?= url('/assets/' . $asset['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <select class="select" name="status" style="max-width:220px;">
                        <?php foreach ($statuses as $s): ?>
                            <?php if (!in_array($s, ['On Loan'], true)): ?>
                                <option value="<?= e($s) ?>" <?= $asset['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <input class="input" name="note" placeholder="Optional note" style="flex:1; min-width:160px;">
                    <button class="btn btn-outline"><?= svg_icon('check') ?> Update</button>
                </form>
            </div>
        </div>

        <div class="grid-2 mb-16">
            <?php if ($assignments): ?>
            <div class="card">
                <div class="card-head"><h3>Assignments</h3></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>No</th><th>Employee</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($assignments as $as): ?>
                        <tr>
                            <td class="td-main"><?= e($as['assignment_no']) ?></td>
                            <td><?= e(\App\Models\Employee::find((int)$as['employee_id'])['name'] ?? '') ?></td>
                            <td><?= e(format_date($as['assignment_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
            <?php endif; ?>

            <?php if ($loans): ?>
            <div class="card">
                <div class="card-head"><h3>Loans</h3></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>No</th><th>Borrower</th><th>Loan</th><th>Due</th></tr></thead>
                    <tbody>
                    <?php foreach ($loans as $ln): ?>
                        <tr>
                            <td><a class="td-main" href="<?= url('/loans/' . $ln['id']) ?>"><?= e($ln['loan_no']) ?></a></td>
                            <td><?= e(\App\Models\Employee::find((int)$ln['employee_id'])['name'] ?? '') ?></td>
                            <td><?= e(format_date($ln['loan_date'])) ?></td>
                            <td><?= e(format_date($ln['expected_return_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid-2 mb-16">
            <?php if ($transfers): ?>
            <div class="card">
                <div class="card-head"><h3>Transfers</h3></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>Date</th><th>From</th><th>To</th></tr></thead>
                    <tbody>
                    <?php foreach ($transfers as $t): ?>
                        <tr>
                            <td><?= e(format_date($t['transfer_date'])) ?></td>
                            <td><?= e(\App\Models\Employee::find((int)$t['previous_employee_id'])['name'] ?? '') ?></td>
                            <td><?= e(\App\Models\Employee::find((int)$t['new_employee_id'])['name'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
            <?php endif; ?>

            <?php if ($maintenance): ?>
            <div class="card">
                <div class="card-head"><h3>Maintenance</h3></div>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>No</th><th>Type</th><th>Start</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($maintenance as $m): ?>
                        <tr>
                            <td class="td-main"><?= e($m['maintenance_no']) ?></td>
                            <td><?= e($m['type']) ?></td>
                            <td><?= e(format_date($m['started_at'])) ?></td>
                            <td><?= status_badge($m['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= asset_url('js/qrcode.js') ?>"></script>
<script>renderQR('assetQr', <?= json_encode(qr_url($asset['asset_code'])) ?>);</script>