<div class="page-head">
    <div>
        <h1 class="page-title"><?= $user ? 'Edit User' : 'New User' ?></h1>
        <p class="page-desc"><?= $user ? e($user['email']) : 'Create an application account' ?></p>
    </div>
    <div class="page-actions"><a class="btn btn-outline" href="<?= url('/users') ?>"><?= svg_icon('arrow-left') ?> Back</a></div>
</div>

<form method="post" action="<?= url($user ? '/users/' . $user['id'] : '/users') ?>">
    <?= csrf_field() ?>
    <div class="card card-pad">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input class="input" name="name" value="<?= e(old('name', $user['name'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input class="input" type="email" name="email" value="<?= e(old('email', $user['email'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select class="select" name="role" required>
                    <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator — full access</option>
                    <option value="staff" <?= ($user['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>IT Staff — inventory &amp; transactions</option>
                    <option value="employee" <?= ($user['role'] ?? '') === 'employee' ? 'selected' : '' ?>>Employee — view own equipment</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Linked Employee</label>
                <select class="select" name="employee_id">
                    <option value="">—</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= old('employee_id', (string)($user['employee_id'] ?? '')) == $emp['id'] ? 'selected' : '' ?>><?= e($emp['name']) ?> (<?= e($emp['employee_number']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Required for the Employee role — links the account to the employee's asset records.</div>
            </div>
            <div class="form-group">
                <label class="form-label"><?= $user ? 'New Password (leave blank to keep)' : 'Password *' ?></label>
                <input class="input" type="password" name="password" <?= $user ? '' : 'required' ?> autocomplete="new-password">
            </div>
            <?php if ($user): ?>
            <div class="form-group">
                <label class="form-label">Password Confirmation</label>
                <input class="input" type="password" name="password_confirmation" autocomplete="new-password">
            </div>
            <div class="form-group col-span">
                <div class="check-row">
                    <input type="checkbox" id="active" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
                    <label for="active">Account is active (allowed to sign in)</label>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary"><?= svg_icon('check') ?> <?= $user ? 'Save Changes' : 'Create User' ?></button>
        <a class="btn btn-outline" href="<?= url('/users') ?>">Cancel</a>
    </div>
</form>