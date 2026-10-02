<div class="page-head">
    <div>
        <h1 class="page-title"><?= e($employee['name']) ?></h1>
        <p class="page-desc"><?= e($department['name'] ?? 'No department') ?> · <?= e($employee['position'] ?: '—') ?> · <?= e($employee['employee_number']) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/assign/create') ?>?employee=<?= $employee['id'] ?>"><?= svg_icon('user-check') ?> Assign Asset</a>
        <a class="btn btn-outline" href="<?= url('/loans/create') ?>?employee=<?= $employee['id'] ?>"><?= svg_icon('clock') ?> Loan Out</a>
        <?php if (Auth::isStaff()): ?>
            <a class="btn btn-outline" href="<?= url('/employees/' . $employee['id'] . '/edit') ?>"><?= svg_icon('edit') ?> Edit</a>
        <?php endif; ?>
    </div>
</div>

<div class="detail-grid" style="grid-template-columns: 280px 1fr;">
    <div>
        <div class="card mb-16">
            <?php if ($employee['photo']): ?>
                <img class="asset-photo" src="<?= asset_url($employee['photo']) ?>" alt="<?= e($employee['name']) ?>">
            <?php else: ?>
                <div class="asset-photo empty"><?= svg_icon('camera') ?></div>
            <?php endif; ?>
            <div class="card-pad">
                <?= status_badge($employee['status']) ?>
                <div class="table-wrap" style="margin-top:8px;"></div>
                <ul class="detail-list">
                    <li><span class="d-label">Employee No</span><span class="d-value"><?= e($employee['employee_number']) ?></span></li>
                    <li><span class="d-label">Department</span><span class="d-value"><?= e($department['name'] ?? '—') ?></span></li>
                    <li><span class="d-label">Position</span><span class="d-value"><?= e($employee['position'] ?: '—') ?></span></li>
                    <li><span class="d-label">Email</span><span class="d-value"><?= e($employee['email'] ?: '—') ?></span></li>
                    <li><span class="d-label">Phone</span><span class="d-value"><?= e($employee['phone'] ?: '—') ?></span></li>
                    <li><span class="d-label">Office</span><span class="d-value"><?= e($employee['office_location'] ?: '—') ?></span></li>
                </ul>
            </div>
        </div>
    </div>

    <div>
        <div class="card card-pad mb-16">
            <div class="section-label">Assigned Assets (Permanent)</div>
            <?php if (!$assigned): ?>
                <p class="muted small">No permanently assigned assets.</p>
            <?php else: ?>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>Asset</th><th>Category</th><th>Serial</th><th>Condition</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($assigned as $a): ?>
                        <tr>
                            <td><a class="td-main" href="<?= url('/assets/' . $a['id']) ?>"><?= e($a['asset_code']) ?></a><br><span class="td-sub"><?= e($a['asset_name']) ?></span></td>
                            <td><?= e($a['category_name'] ?? '—') ?></td>
                            <td class="muted"><?= e($a['serial_number'] ?: '—') ?></td>
                            <td><?= status_badge($a['condition']) ?></td>
                            <td><a class="btn btn-outline btn-sm" href="<?= url('/qr/' . urlencode($a['asset_code'])) ?>"><?= svg_icon('qr') ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
        </div>

        <div class="card card-pad mb-16">
            <div class="section-label">Active Loans (Temporary)</div>
            <?php if (!$loans): ?>
                <p class="muted small">No active loans.</p>
            <?php else: ?>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>Loan No</th><th>Asset</th><th>Loan Date</th><th>Due Date</th><th>Purpose</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($loans as $l): $ds = \App\Models\Loan::derivedStatus($l); ?>
                        <tr>
                            <td><a class="td-main" href="<?= url('/loans/' . $l['id']) ?>"><?= e($l['loan_no']) ?></a></td>
                            <td><?= e($l['asset_code']) ?></td>
                            <td><?= e(format_date($l['loan_date'])) ?></td>
                            <td><?= e(format_date($l['expected_return_date'])) ?></td>
                            <td class="muted"><?= e($l['purpose'] ?: '—') ?></td>
                            <td><?= status_badge($ds) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
        </div>

        <?php if ($history): ?>
        <div class="card card-pad">
            <div class="section-label">Recent Asset Activity</div>
            <div class="table-wrap"><table class="table">
                <thead><tr><th>Date</th><th>Asset</th><th>Event</th><th>By</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($history, 0, 20) as $h): ?>
                    <tr>
                        <td class="muted"><?= e(format_datetime($h['created_at'])) ?></td>
                        <td><?= e($h['asset_code'] ?? '—') ?></td>
                        <td><span class="td-main"><?= e($h['title']) ?></span><div class="td-sub"><?= e($h['type']) ?></div></td>
                        <td><?= e($h['user_name'] ?? 'System') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <?php endif; ?>
    </div>
</div>