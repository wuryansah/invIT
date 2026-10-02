<div class="page-head">
    <div>
        <h1 class="page-title"><?= e($asset['asset_code']) ?></h1>
        <p class="page-desc">Scanned via QR code · <?= e($asset['asset_name']) ?></p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/assets/' . $asset['id']) ?>"><?= svg_icon('eye') ?> Full record</a>
        <a class="btn btn-outline" href="<?= url('/scan') ?>"><?= svg_icon('qr') ?> Scan again</a>
    </div>
</div>

<div class="detail-grid" style="grid-template-columns: 300px 1fr;">
    <div class="card card-pad">
        <?php if ($asset['photo']): ?>
            <img class="asset-photo" src="<?= asset_url($asset['photo']) ?>" alt="<?= e($asset['asset_code']) ?>">
        <?php else: ?>
            <div class="asset-photo empty"><?= svg_icon('camera') ?></div>
        <?php endif; ?>
    </div>
    <div class="card card-pad">
        <div class="flex wrap mb-16"><?= status_badge($asset['status']) ?> <?= status_badge($asset['condition']) ?></div>
        <ul class="detail-list">
            <li><span class="d-label">Asset Code</span><span class="d-value"><?= e($asset['asset_code']) ?></span></li>
            <li><span class="d-label">Asset Name</span><span class="d-value"><?= e($asset['asset_name']) ?></span></li>
            <li><span class="d-label">Category</span><span class="d-value"><?= e($asset['category_name'] ?? '—') ?></span></li>
            <li><span class="d-label">Serial Number</span><span class="d-value"><?= e($asset['serial_number'] ?: '—') ?></span></li>
            <li><span class="d-label">Status</span><span class="d-value"><?= e($asset['status']) ?></span></li>
            <li><span class="d-label">Current Employee</span><span class="d-value"><?= e($asset['employee_name'] ?? '—') ?></span></li>
            <li><span class="d-label">Department</span><span class="d-value"><?= e($asset['department_name'] ?? '—') ?></span></li>
            <li><span class="d-label">Location</span><span class="d-value"><?php
                $parts = array_filter([$asset['building'], $asset['floor'], $asset['room']]);
                foreach ($parts as &$p) { $p = (string)$p; }
                $loc = implode(' / ', $parts);
                if ($asset['storage_location']) { $loc .= ' (' . $asset['storage_location'] . ')'; }
                echo e($loc ?: '—');
            ?></span></li>
        </ul>
    </div>
</div>