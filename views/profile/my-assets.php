<?php if (!$employee): ?>
    <div class="page-head">
        <div><h1 class="page-title">My Equipment</h1></div>
    </div>
    <div class="card card-pad">
        <div class="alert alert-warning mb-0">Your account is not linked to an employee record. Please contact the IT Administrator
            to link this account (User account → Linked Employee).</div>
    </div>
    <?php return; ?>
<?php endif; ?>

<div class="page-head">
    <div>
        <h1 class="page-title"><?= e($employee['name']) ?></h1>
        <p class="page-desc"><?= e(\App\Models\Department::find((int)$employee['department_id'])['name'] ?? 'No department') ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/profile') ?>"><?= svg_icon('settings') ?> My Profile</a>
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
                <ul class="detail-list">
                    <li><span class="d-label">Employee No</span><span class="d-value"><?= e($employee['employee_number']) ?></span></li>
                    <li><span class="d-label">Position</span><span class="d-value"><?= e($employee['position'] ?: '—') ?></span></li>
                    <li><span class="d-label">Email</span><span class="d-value"><?= e($employee['email'] ?: '—') ?></span></li>
                    <li><span class="d-label">Phone</span><span class="d-value"><?= e($employee['phone'] ?: '—') ?></span></li>
                </ul>
            </div>
        </div>
    </div>

    <div>
        <div class="card card-pad mb-16">
            <div class="section-label" style="margin-top:0;">Permanently Assigned</div>
            <?php if (!$assigned): ?>
                <p class="muted small">No permanently assigned equipment.</p>
            <?php else: ?>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>Asset</th><th>Serial</th><th>Condition</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($assigned as $a): ?>
                        <tr>
                            <td style="white-space:nowrap;"><span class="td-main"><?= e($a['asset_code']) ?></span><br><span class="td-sub"><?= e($a['asset_name']) ?></span></td>
                            <td class="muted"><?= e($a['serial_number'] ?: '—') ?></td>
                            <td><?= status_badge($a['condition']) ?></td>
                            <td><?= status_badge($a['status']) ?></td>
                            <td><a class="btn btn-outline btn-sm" href="<?= url('/assets/' . $a['id']) ?>"><?= svg_icon('eye') ?> Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
            <?php endif; ?>
        </div>

        <div class="card card-pad mb-16">
            <div class="section-label" style="margin-top:0;">Temporarily Borrowed (Active Loans)</div>
            <?php if (!$loans): ?>
                <p class="muted small">No active loans.</p>
            <?php else: ?>
                <div class="table-wrap"><table class="table">
                    <thead><tr><th>Loan No</th><th>Asset</th><th>Loan Date</th><th>Due Date</th><th>Purpose</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($loans as $l): $ds = \App\Models\Loan::derivedStatus($l); ?>
                        <tr class="<?= $ds === 'Overdue' ? 'overdue' : '' ?>">
                            <td class="td-main"><?= e($l['loan_no']) ?></td>
                            <td><?= e(\App\Models\Asset::find((int)$l['asset_id'])['asset_code'] ?? '') ?></td>
                            <td><?= e(format_date($l['loan_date'])) ?></td>
                            <td><?= e(format_date($l['expected_return_date'])) ?></td>
                            <td class="muted"><?= e($l['purpose'] ?: '—') ?></td>
                            <td><?= status_badge($ds) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php $overdue = array_filter($loans, fn($l) => \App\Models\Loan::derivedStatus($l) === 'Overdue'); ?>
                <?php if ($overdue): ?>
                    <div class="alert alert-danger mb-0 mt-16"><?= count($overdue) ?> overdue loan(s) — please return the equipment as soon as possible.</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($history): ?>
        <div class="card card-pad">
            <div class="section-label" style="margin-top:0;">My Recent Asset Activity</div>
            <div class="table-wrap"><table class="table">
                <thead><tr><th>Date</th><th>Asset</th><th>Event</th><th>By</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($history, 0, 20) as $h): ?>
                    <tr>
                        <td class="muted"><?= e(format_date($h['created_at'])) ?></td>
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