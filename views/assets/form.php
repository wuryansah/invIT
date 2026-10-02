<?php $asset_code = $asset['asset_code'] ?? ''; ?>
<div class="page-head">
    <div>
        <h1 class="page-title"><?= $asset ? 'Edit ' . e($asset['asset_code']) : 'New Asset' ?></h1>
        <p class="page-desc"><?= $asset ? e($asset['asset_name']) : 'Register new equipment' ?></p>
    </div>
    <div class="page-actions">
        <?php if ($asset): ?>
            <a class="btn btn-outline" href="<?= url('/assets/' . $asset['id']) ?>"><?= svg_icon('eye') ?> View</a>
        <?php endif; ?>
        <a class="btn btn-outline" href="<?= url('/assets') ?>"><?= svg_icon('arrow-left') ?> Back</a>
    </div>
</div>

<form method="post" action="<?= url($asset ? '/assets/' . $asset['id'] : '/assets') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card card-pad mb-16">
        <div class="form-section" style="margin-top:0; padding-top:0; border:0;">
            <h4>Basic Information</h4>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Asset Code *</label>
                <input class="input" name="asset_code" value="<?= e(old('asset_code', $asset_code)) ?>" placeholder="IT-LAP-0001" required>
            </div>
            <div class="form-group">
                <label class="form-label">Asset Name *</label>
                <input class="input" name="asset_name" value="<?= e(old('asset_name', $asset['asset_name'] ?? '')) ?>" placeholder="Dell Latitude 5420" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select class="select" name="category_id" required>
                    <option value="">Select category…</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= old('category_id', (string)($asset['category_id'] ?? '')) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Brand</label>
                <input class="input" name="brand" value="<?= e(old('brand', $asset['brand'] ?? '')) ?>" placeholder="Dell">
            </div>
            <div class="form-group">
                <label class="form-label">Model</label>
                <input class="input" name="model" value="<?= e(old('model', $asset['model'] ?? '')) ?>" placeholder="Latitude 5420">
            </div>
            <div class="form-group">
                <label class="form-label">Serial Number</label>
                <input class="input" name="serial_number" value="<?= e(old('serial_number', $asset['serial_number'] ?? '')) ?>" placeholder="Unique serial / part">
            </div>
            <div class="form-group">
                <label class="form-label">Product Number</label>
                <input class="input" name="product_number" value="<?= e(old('product_number', $asset['product_number'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">IP Address</label>
                <input class="input" name="ip_address" value="<?= e(old('ip_address', $asset['ip_address'] ?? '')) ?>" placeholder="192.168.1.10">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Specification</label>
                <textarea class="textarea" name="specification"><?= e(old('specification', $asset['specification'] ?? '')) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Purchase Date</label>
                <input class="input" type="date" name="purchase_date" value="<?= e(old('purchase_date', $asset['purchase_date'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Purchase Price</label>
                <input class="input" type="number" step="0.01" min="0" name="purchase_price" value="<?= e(old('purchase_price', $asset['purchase_price'] ?? 0)) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Warranty Expiration</label>
                <input class="input" type="date" name="warranty_expiration" value="<?= e(old('warranty_expiration', $asset['warranty_expiration'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Supplier</label>
                <input class="input" name="supplier" value="<?= e(old('supplier', $asset['supplier'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Invoice Number</label>
                <input class="input" name="invoice_number" value="<?= e(old('invoice_number', $asset['invoice_number'] ?? '')) ?>">
            </div>
        </div>

        <div class="form-section"><h4>Location</h4></div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Department</label>
                <select class="select" name="department_id">
                    <option value="">—</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= old('department_id', (string)($asset['department_id'] ?? '')) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Building</label>
                <input class="input" name="building" value="<?= e(old('building', $asset['building'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Floor</label>
                <input class="input" name="floor" value="<?= e(old('floor', $asset['floor'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Room</label>
                <input class="input" name="room" value="<?= e(old('room', $asset['room'] ?? '')) ?>">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Storage Location</label>
                <input class="input" name="storage_location" value="<?= e(old('storage_location', $asset['storage_location'] ?? '')) ?>" placeholder="e.g. IT Warehouse, Rack 3">
            </div>
        </div>

        <div class="form-section"><h4>Status &amp; Additional</h4></div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Status</label>
                <?php if ($asset && in_array($asset['status'], ['Assigned', 'On Loan'], true)): ?>
                    <input class="input" value="<?= e($asset['status']) ?>" disabled>
                    <div class="form-hint">Status is fixed while the asset is assigned/on loan.</div>
                <?php else: ?>
                    <select class="select" name="status">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= e($s) ?>" <?= ($asset['status'] ?? 'Available') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Condition</label>
                <select class="select" name="condition">
                    <?php foreach ($conditions as $c): ?>
                        <option value="<?= e($c) ?>" <?= ($asset['condition'] ?? 'Good') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-span">
                <label class="form-label">Photo</label>
                <?php if ($asset && $asset['photo']): ?>
                    <img src="<?= asset_url($asset['photo']) ?>" class="asset-photo" style="height:120px;width:160px;object-fit:cover;margin-bottom:8px;">
                <?php endif; ?>
                <input class="input" type="file" name="photo" accept="image/*">
            </div>
            <div class="form-group col-span">
                <label class="form-label">Notes</label>
                <textarea class="textarea" name="notes"><?= e(old('notes', $asset['notes'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary"><?= svg_icon('check') ?> <?= $asset ? 'Save Changes' : 'Create Asset' ?></button>
        <a class="btn btn-outline" href="<?= url('/assets') ?>">Cancel</a>
    </div>
</form>