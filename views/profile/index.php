<?php $user = \App\Core\Auth::user(); $employee = \App\Core\Auth::employee(); ?>
<div class="page-head">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-desc"><?= e($user['email']) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/my-assets') ?>"><?= svg_icon('package-check') ?> My Equipment</a>
    </div>
</div>

<div class="grid-2">
    <div class="card card-pad">
        <div class="section-label" style="margin-top:0;">Account</div>
        <ul class="detail-list">
            <li><span class="d-label">Name</span><span class="d-value"><?= e($user['name']) ?></span></li>
            <li><span class="d-label">Email</span><span class="d-value"><?= e($user['email']) ?></span></li>
            <li><span class="d-label">Role</span><span class="d-value"><?= e(ucfirst($user['role'])) ?></span></li>
            <li><span class="d-label">Linked Employee</span><span class="d-value"><?= e($employee['name'] ?? '—') ?></span></li>
            <li><span class="d-label">Last Login</span><span class="d-value"><?= e(format_datetime($user['last_login_at'])) ?></span></li>
            <li><span class="d-label">Member Since</span><span class="d-value"><?= e(format_date($user['created_at'])) ?></span></li>
        </ul>
    </div>

    <div class="card card-pad">
        <div class="section-label" style="margin-top:0;">Change Password</div>
        <form method="post" action="<?= url('/profile/password') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Current Password</label>
                <input class="input" type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label class="form-label">New Password (min. 8 chars)</label>
                <input class="input" type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input class="input" type="password" name="new_password_confirmation" required>
            </div>
            <button class="btn btn-primary"><?= svg_icon('check') ?> Update Password</button>
        </form>
    </div>
</div>