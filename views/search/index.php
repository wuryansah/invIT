<div class="page-head">
    <div>
        <h1 class="page-title">Global Search</h1>
        <p class="page-desc">Search assets by code, name, serial, brand, model — or employees by name, number, department</p>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/search') ?>">
    <div class="filter-item" style="flex:1; min-width:240px;">
        <label>Search query</label>
        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Asset code, serial, employee name, department…" autofocus>
    </div>
    <button class="btn btn-primary"><?= svg_icon('search') ?> Search</button>
</form>

<?php if ($q !== ''): ?>
    <div class="card mb-16">
        <div class="card-head"><h3>Assets (<?= count($assets) ?>)</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Asset</th><th>Serial</th><th>Status</th><th>Current User</th><th>Department</th><th>Location</th><th>Condition</th></tr></thead>
                <tbody>
                <?php if (!$assets): ?><tr><td colspan="7" class="table-empty">No matching assets.</td></tr><?php endif; ?>
                <?php foreach ($assets as $a): ?>
                    <tr>
                        <td><a class="td-main" href="<?= url('/assets/' . $a['id']) ?>"><?= e($a['asset_code']) ?></a><br><span class="td-sub"><?= e($a['asset_name']) ?></span></td>
                        <td class="muted"><?= e($a['serial_number'] ?: '—') ?></td>
                        <td><?= status_badge($a['status']) ?></td>
                        <td><?= e($a['employee_name'] ?? '—') ?></td>
                        <td><?= e($a['department_name'] ?? '—') ?></td>
                        <td class="muted"><?= e(trim(implode(' / ', array_filter([$a['building'], $a['floor'], $a['room']])))) ?: '—' ?></td>
                        <td><?= status_badge($a['condition']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3>Employees (<?= count($employees) ?>)</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Employee</th><th>Number</th><th>Department</th><th>Position</th><th>Email</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (!$employees): ?><tr><td colspan="6" class="table-empty">No matching employees.</td></tr><?php endif; ?>
                <?php foreach ($employees as $e): ?>
                    <tr>
                        <td><a class="td-main" href="<?= url('/employees/' . $e['id']) ?>"><?= e($e['name']) ?></a></td>
                        <td><?= e($e['employee_number']) ?></td>
                        <td><?= e($e['department_name'] ?? '—') ?></td>
                        <td><?= e($e['position'] ?: '—') ?></td>
                        <td class="muted"><?= e($e['email'] ?: '—') ?></td>
                        <td><?= status_badge($e['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>