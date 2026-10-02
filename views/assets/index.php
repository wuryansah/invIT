<div class="page-head">
    <div>
        <h1 class="page-title">Assets</h1>
        <p class="page-desc">All registered IT equipment</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/assets/export') ?>"><?= svg_icon('download') ?> Export Excel</a>
        <a class="btn btn-primary" href="<?= url('/assets/create') ?>"><?= svg_icon('plus') ?> New Asset</a>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/assets') ?>">
    <div class="filter-item" style="flex:1; min-width:200px;">
        <label>Search</label>
        <input class="input" type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Code, name, serial, brand, model…">
    </div>
    <div class="filter-item">
        <label>Category</label>
        <select class="select" name="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($filters['category'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Status</label>
        <select class="select" name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Condition</label>
        <select class="select" name="condition">
            <option value="">All</option>
            <?php foreach ($conditions as $c): ?>
                <option value="<?= e($c) ?>" <?= ($filters['condition'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Department</label>
        <select class="select" name="department">
            <option value="">All</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= ($filters['department'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/assets') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Asset</th><th>Category</th><th>Serial</th><th>Status</th>
                    <th>Condition</th><th>Current Holder</th><th>Department</th><th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$pagination['items']): ?>
                <tr><td colspan="8" class="table-empty">No assets found.</td></tr>
            <?php endif; ?>
            <?php foreach ($pagination['items'] as $a): ?>
                <tr>
                    <td>
                        <a class="td-main" href="<?= url('/assets/' . $a['id']) ?>"><?= e($a['asset_code']) ?></a>
                        <div class="td-sub"><?= e($a['asset_name']) ?></div>
                    </td>
                    <td><?= e($a['category_name'] ?? '—') ?></td>
                    <td class="muted"><?= e($a['serial_number'] ?: '—') ?></td>
                    <td><?= status_badge($a['status']) ?></td>
                    <td><?= status_badge($a['condition']) ?></td>
                    <td><?= e($a['employee_name'] ?? '—') ?></td>
                    <td><?= e($a['department_name'] ?? '—') ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="icon-btn" title="Open record" href="<?= url('/assets/' . $a['id']) ?>"><?= svg_icon('eye') ?></a>
                            <?php if (Auth::isStaff()): ?>
                                <a class="icon-btn" title="Edit" href="<?= url('/assets/' . $a['id'] . '/edit') ?>"><?= svg_icon('edit') ?></a>
                                <a class="icon-btn" title="QR" href="<?= url('/qr/' . urlencode($a['asset_code'])) ?>"><?= svg_icon('qr') ?></a>
                                <?php if (Auth::isAdmin()): ?>
                                <form method="post" action="<?= url('/assets/' . $a['id'] . '/delete') ?>" style="display:inline;" data-confirm="Delete <?= e($a['asset_code']) ?> permanently? This cannot be undone.">
                                    <?= csrf_field() ?>
                                    <button class="icon-btn" title="Delete"><?= svg_icon('trash') ?></button>
                                </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php require config('paths.views') . '/partials/pagination.php'; ?>
</div>