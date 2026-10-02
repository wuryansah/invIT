<div class="page-head">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-desc">Inventory overview at a glance · <?= e(format_datetime(date('Y-m-d H:i:s'))) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/assets/create') ?>"><?= svg_icon('plus') ?> New Asset</a>
        <a class="btn btn-outline" href="<?= url('/loans/create') ?>"><?= svg_icon('clock') ?> New Loan</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon bg-blue"><?= svg_icon('box') ?></span>
        <div><div class="stat-value"><?= array_sum($statusCounts) ?></div><div class="stat-label">Total Assets</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-green"><?= svg_icon('check') ?></span>
        <div><div class="stat-value"><?= $statusCounts['Available'] ?? 0 ?></div><div class="stat-label">Available</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-cyan"><?= svg_icon('user-check') ?></span>
        <div><div class="stat-value"><?= $statusCounts['Assigned'] ?? 0 ?></div><div class="stat-label">Assigned</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-amber"><?= svg_icon('clock') ?></span>
        <div><div class="stat-value"><?= $statusCounts['On Loan'] ?? 0 ?></div><div class="stat-label">On Loan</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-slate"><?= svg_icon('wrench') ?></span>
        <div><div class="stat-value"><?= $statusCounts['Under Maintenance'] ?? 0 ?></div><div class="stat-label">Under Maintenance</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-red"><?= svg_icon('alert') ?></span>
        <div><div class="stat-value"><?= $statusCounts['Damaged'] ?? 0 ?></div><div class="stat-label">Damaged</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-violet"><?= svg_icon('search') ?></span>
        <div><div class="stat-value"><?= $statusCounts['Lost'] ?? 0 ?></div><div class="stat-label">Lost</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon bg-slate"><?= svg_icon('box') ?></span>
        <div><div class="stat-value"><?= ($statusCounts['Retired'] ?? 0) + ($statusCounts['Disposed'] ?? 0) ?></div><div class="stat-label">Retired / Disposed</div></div>
    </div>
</div>

<div class="dash-row mb-24">
    <div class="card card-pad">
        <div class="stat-value"><?= $employeeTotal ?></div>
        <div class="stat-label">Total Employees</div>
    </div>
    <div class="card card-pad">
        <div class="stat-value"><?= $withAssigned ?></div>
        <div class="stat-label">Employees with Assigned Assets</div>
    </div>
    <div class="card card-pad">
        <div class="stat-value"><?= $activeLoans ?></div>
        <div class="stat-label">Active Loans</div>
    </div>
    <div class="card card-pad">
        <div class="stat-value <?= $overdue > 0 ? 'text-danger' : '' ?>" style="<?= $overdue > 0 ? 'color:var(--danger);' : '' ?>"><?= $overdue ?></div>
        <div class="stat-label">Overdue Loans</div>
    </div>
</div>

<div class="chart-grid">
    <div class="card">
        <div class="card-head"><h3>Assets by Status</h3></div>
        <div class="card-pad chart-box"><canvas id="chartStatus" height="120"></canvas></div>
    </div>
    <div class="card">
        <div class="card-head"><h3>Assets by Category</h3></div>
        <div class="card-pad chart-box"><canvas id="chartCategory" height="120"></canvas></div>
    </div>
    <div class="card">
        <div class="card-head"><h3>Monthly Loan Transactions</h3></div>
        <div class="card-pad chart-box"><canvas id="chartLoans" height="120"></canvas></div>
    </div>
    <div class="card">
        <div class="card-head"><h3>Monthly Asset Assignments</h3></div>
        <div class="card-pad chart-box"><canvas id="chartAssignments" height="120"></canvas></div>
    </div>
</div>

<div class="grid-2 mb-24">
    <div class="card">
        <div class="card-head"><h3>Recent Active Loans</h3><a class="small" href="<?= url('/loans') ?>">View all →</a></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Loan No</th><th>Employee</th><th>Asset</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$recentLoans): ?>
                    <tr><td colspan="5" class="table-empty">No active loans</td></tr>
                <?php endif; ?>
                <?php foreach ($recentLoans as $l): $l['derived_status'] = \App\Models\Loan::derivedStatus($l); ?>
                    <tr class="<?= $l['derived_status'] === 'Overdue' ? 'overdue' : ($l['derived_status'] === 'Due Today' ? 'due-today' : '') ?>">
                        <td><a href="<?= url('/loans/' . $l['id']) ?>" class="td-main"><?= e($l['loan_no']) ?></a></td>
                        <td><?= e($l['employee_name']) ?></td>
                        <td><?= e($l['asset_code']) ?></td>
                        <td><?= e(format_date($l['expected_return_date'])) ?></td>
                        <td><?= status_badge($l['derived_status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><h3>Assets by Department</h3></div>
        <div class="card-pad">
            <?php foreach ($byDepartment as $d): ?>
                <div class="flex-between" style="padding:7px 0;">
                    <span><?= e($d['name']) ?></span>
                    <span class="badge bg-soft bg-primary"><?= e($d['c']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
var statusData = {
    labels: <?= json_encode(array_keys(array_filter($statusCounts, fn($v) => $v > 0))) ?>,
    values: <?= json_encode(array_values(array_filter($statusCounts, fn($v) => $v > 0))) ?>
};
var categoryData = {
    labels: <?= json_encode(array_column($byCategory, 'name')) ?>,
    values: <?= json_encode(array_map(fn($r) => (int)$r['c'], $byCategory)) ?>
};
var loanChart = { labels: <?= json_encode($loanLabels) ?>, values: <?= json_encode($loanCounts) ?> };
var assignChart = { labels: <?= json_encode($assignLabels) ?>, values: <?= json_encode($assignCounts) ?> };
</script>
<script src="<?= asset_url('js/chart.umd.min.js') ?>"></script>
<script>
(function () {
    var palette = ['#2563eb','#16a34a','#d97706','#dc2626','#0891b2','#7c3aed','#64748b','#f59e0b'];
    function doughnut(ctx, data, colors) {
        new Chart(ctx, {
            type: 'doughnut',
            data: { labels: data.labels, datasets: [{ data: data.values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
            options: { plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }, maintainAspectRatio: false }
        });
    }
    function bars(ctx, data) {
        new Chart(ctx, {
            type: 'bar',
            data: { labels: data.labels, datasets: [{ data: data.values, backgroundColor: '#2563eb', borderRadius: 5, maxBarThickness: 34 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, maintainAspectRatio: false }
        });
    }
    doughnut(document.getElementById('chartStatus'), statusData, palette);
    doughnut(document.getElementById('chartCategory'), categoryData, palette);
    bars(document.getElementById('chartLoans'), loanChart);
    bars(document.getElementById('chartAssignments'), assignChart);
})();
</script>