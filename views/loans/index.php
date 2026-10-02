<div class="page-head">
    <div>
        <h1 class="page-title">Loan Monitoring</h1>
        <p class="page-desc">Track active, due, overdue and returned equipment loans</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('/loans/create') ?>"><?= svg_icon('plus') ?> New Loan</a>
    </div>
</div>

<form class="filter-bar" method="get" action="<?= url('/loans') ?>">
    <div class="filter-item" style="flex:1; min-width:180px;">
        <label>Search</label>
        <input class="input" type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Loan no, asset, employee…">
    </div>
    <div class="filter-item">
        <label>Department</label>
        <select class="select" name="department">
            <option value="">All departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= ($filters['department'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Asset Category</label>
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
            <option value="Active" <?= ($filters['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Due Today" <?= ($filters['status'] ?? '') === 'Due Today' ? 'selected' : '' ?>>Due Today</option>
            <option value="Overdue" <?= ($filters['status'] ?? '') === 'Overdue' ? 'selected' : '' ?>>Overdue</option>
            <option value="Returned" <?= ($filters['status'] ?? '') === 'Returned' ? 'selected' : '' ?>>Returned</option>
        </select>
    </div>
    <div class="filter-item">
        <label>Loan From</label>
        <input class="input" type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
    </div>
    <div class="filter-item">
        <label>Loan To</label>
        <input class="input" type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
    </div>
    <button class="btn btn-soft"><?= svg_icon('search') ?> Filter</button>
    <a class="btn btn-sm btn-outline" href="<?= url('/loans') ?>">Reset</a>
</form>

<div class="card table-card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Loan No</th><th>Employee</th><th>Asset</th><th>Loan Date</th><th>Due Date</th><th>Purpose</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (!$list): ?>
                <tr><td colspan="8" class="table-empty">No loans match your filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($list as $l): ?>
                <tr class="<?= $l['derived_status'] === 'Overdue' ? 'overdue' : ($l['derived_status'] === 'Due Today' ? 'due-today' : '') ?>">
                    <td><a class="td-main" href="<?= url('/loans/' . $l['id']) ?>"><?= e($l['loan_no']) ?></a></td>
                    <td class="user-chip"><span class="avatar avatar-sm"><?= e(strtoupper(substr($l['employee_name'] ?? '?', 0, 1))) ?></span> <?= e($l['employee_name'] ?? '—') ?></td>
                    <td><a href="<?= url('/assets/' . $l['asset_id']) ?>"><?= e($l['asset_code']) ?></a><br><span class="td-sub"><?= e($l['asset_name']) ?></span></td>
                    <td><?= e(format_date($l['loan_date'])) ?></td>
                    <td><?= e(format_date($l['expected_return_date'])) ?></td>
                    <td class="muted"><?= e(truncate($l['purpose'] ?? '', 24)) ?></td>
                    <td><?= status_badge($l['derived_status']) ?></td>
                    <td>
                        <div class="table-actions">
                            <a class="icon-btn" title="Open loan" href="<?= url('/loans/' . $l['id']) ?>"><?= svg_icon('eye') ?></a>
                            <?php if ($l['status'] === 'Active'): ?>
                                <a class="icon-btn" title="Process return" href="<?= url('/loans/' . $l['id']) ?>#return"><?= svg_icon('arrow-left') ?></a>
                                <a class="icon-btn" title="Print loan form" target="_blank" href="<?= url('/print/handover-loan/' . $l['id']) ?>"><?= svg_icon('printer') ?></a>
                            <?php else: ?>
                                <a class="icon-btn" title="Print return form" target="_blank" href="<?= url('/print/return/' . $l['id']) ?>"><?= svg_icon('printer') ?></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <?php
        $page = (int)$pagination['page']; $last = (int)$pagination['lastPage'];
        $mk = function (int $p) {
            $q = $_GET; unset($q['page']);
            if ($p > 1) $q['page'] = $p;
            return url('/loans') . ($q ? '?' . http_build_query($q) : '');
        };
        if ($last > 1): ?>
            <a class="page-link <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($mk($page - 1)) ?>">‹</a>
            <?php for ($i = max(1, $page - 2); $i <= min($last, $page + 2); $i++): ?>
                <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= e($mk($i)) ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a class="page-link <?= $page >= $last ? 'disabled' : '' ?>" href="<?= e($mk($page + 1)) ?>">›</a>
            <span class="pagination-info">Page <?= $page ?> of <?= $last ?> · <?= $pagination['total'] ?> loans</span>
        <?php else: ?>
            <span class="pagination-info"><?= $pagination['total'] ?> loans</span>
        <?php endif; ?>
    </div>
</div>