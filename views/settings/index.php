<div class="page-head">
    <div>
        <h1 class="page-title">System Settings</h1>
        <p class="page-desc">Application settings, departments and asset categories</p>
    </div>
</div>

<div class="card card-pad mb-16">
    <div class="section-label" style="margin-top:0;">General</div>
    <form method="post" action="<?= url('/settings') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Company Name *</label>
                <input class="input" name="company_name" value="<?= e(old('company_name', $settings['company_name'])) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Loan Due-Soon Reminder (days) *</label>
                <input class="input" type="number" name="loan_due_soon_days" value="<?= e($settings['loan_due_soon_days']) ?>" min="0">
            </div>
            <div class="form-group col-span">
                <div class="check-row">
                    <input type="checkbox" id="notif" name="notification_enabled" value="1" <?= $settings['notification_enabled'] == '1' ? 'checked' : '' ?>>
                    <label for="notif">Enable in-app notifications</label>
                </div>
            </div>
        </div>
        <button class="btn btn-primary"><?= svg_icon('check') ?> Save Settings</button>
    </form>
</div>

<div class="grid-2">
    <div class="card card-pad">
        <div class="section-label" style="margin-top:0;">Departments</div>
        <form class="flex wrap mb-16" method="post" action="<?= url('/settings/departments') ?>">
            <?= csrf_field() ?>
            <input class="input" name="name" placeholder="Department name" required style="flex:1; min-width:150px;">
            <input class="input" name="code" placeholder="Code" style="width:110px;">
            <button class="btn btn-soft"><?= svg_icon('plus') ?> Add</button>
        </form>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Name</th><th>Code</th><th>Employees</th><th>Assets</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($departments as $d): ?>
                <tr>
                    <td class="td-main"><?= e($d['name']) ?></td>
                    <td class="muted"><?= e($d['code'] ?: '—') ?></td>
                    <td><?= \App\Models\Department::employeeCount($d['id']) ?></td>
                    <td><?= \App\Models\Department::assetCount($d['id']) ?></td>
                    <td>
                        <form method="post" action="<?= url('/settings/departments/' . $d['id'] . '/delete') ?>" data-confirm="Delete this department?">
                            <?= csrf_field() ?>
                            <button class="icon-btn"><?= svg_icon('trash') ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>

    <div class="card card-pad">
        <div class="section-label" style="margin-top:0;">Asset Categories</div>
        <form class="flex wrap mb-16" method="post" action="<?= url('/settings/categories') ?>">
            <?= csrf_field() ?>
            <input class="input" name="name" placeholder="Category name" required style="flex:1; min-width:150px;">
            <input class="input" name="code" placeholder="Code e.g. IT-LAP" required style="width:140px;">
            <button class="btn btn-soft"><?= svg_icon('plus') ?> Add</button>
        </form>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Name</th><th>Code</th><th>Assets</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="td-main"><?= e($c['name']) ?></td>
                    <td class="muted"><?= e($c['code'] ?: '—') ?></td>
                    <td><?= \App\Models\Category::assetCount($c['id']) ?></td>
                    <td>
                        <form method="post" action="<?= url('/settings/categories/' . $c['id'] . '/delete') ?>" data-confirm="Delete this category?">
                            <?= csrf_field() ?>
                            <button class="icon-btn"><?= svg_icon('trash') ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>