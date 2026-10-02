<div class="page-head">
    <div>
        <h1 class="page-title"><?= $employee ? 'Edit ' . e($employee['name']) : 'New Employee' ?></h1>
        <p class="page-desc"><?= $employee ? 'Employee number ' . e($employee['employee_number']) : 'Add an employee to the directory' ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/employees') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<form method="post" action="<?= url($employee ? '/employees/' . $employee['id'] : '/employees') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card card-pad">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Employee Number *</label>
                <input class="input" name="employee_number" value="<?= e(old('employee_number', $employee['employee_number'] ?? '')) ?>" placeholder="EMP-0001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input class="input" name="name" value="<?= e(old('name', $employee['name'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Department</label>
                <select class="select" name="department_id">
                    <option value="">—</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= old('department_id', (string)($employee['department_id'] ?? '')) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Position / Job Title</label>
                <input class="input" name="position" value="<?= e(old('position', $employee['position'] ?? '')) ?>" placeholder="Finance Staff">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input class="input" type="email" name="email" value="<?= e(old('email', $employee['email'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input class="input" name="phone" value="<?= e(old('phone', $employee['phone'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Office Location</label>
                <input class="input" name="office_location" value="<?= e(old('office_location', $employee['office_location'] ?? '')) ?>" placeholder="Head Office, 3rd Floor">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="select" name="status">
                    <option value="Active" <?= ($employee['status'] ?? 'Active') === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= ($employee['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Photo</label>
                <?php if ($employee && $employee['photo']): ?>
                    <img src="<?= asset_url($employee['photo']) ?>" class="asset-photo" style="height:80px;width:80px;object-fit:cover;margin-bottom:8px;">
                <?php endif; ?>
                <input class="input" type="file" name="photo" accept="image/*">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Notes</label>
                <textarea class="textarea" name="notes"><?= e(old('notes', $employee['notes'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary"><?= svg_icon('check') ?> <?= $employee ? 'Save Changes' : 'Create Employee' ?></button>
        <a class="btn btn-outline" href="<?= url('/employees') ?>">Cancel</a>
    </div>
</form>