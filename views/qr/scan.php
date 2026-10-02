<div class="page-head">
    <div>
        <h1 class="page-title">Scan QR / Barcode</h1>
        <p class="page-desc">Point a phone camera at an asset QR sticker or scan it from the asset page</p>
    </div>
</div>

<div class="grid-2">
    <div class="card card-pad">
        <div class="section-label" style="margin-top:0;">Manual lookup</div>
        <p class="muted small">Use your phone's camera app use the "scan QR" built-in feature, or type the asset code below.</p>
        <form class="flex wrap" onsubmit="event.preventDefault(); navToScan(document.getElementById('codeInput').value);">
            <input class="input" id="codeInput" placeholder="IT-LAP-0001" style="flex:1; min-width:180px; text-transform:uppercase;">
            <button class="btn btn-primary"><?= svg_icon('search') ?> Lookup</button>
        </form>
        <div class="section-label">Or upload the QR image</div>
        <p class="muted small">The asset detail page renders the QR code pointing to <code>/qr/&lt;CODE&gt;</code>. Scanning it (or opening <code>/qr/CODE</code>) shows the asset information below.</p>
    </div>
    <div class="card card-pad">
        <div class="section-label" style="margin-top:0;">Example QR</div>
        <div class="qr-wrap">
            <div id="exampleQr"></div>
            <div class="qr-tag">IT-LAP-0001</div>
            <div class="muted small">Every asset has its own sticker-ready QR code.</div>
        </div>
    </div>
</div>
<script src="<?= asset_url('js/qrcode.js') ?>"></script>
<script>renderQR('exampleQr', <?= json_encode(qr_url('IT-LAP-0001')) ?>);</script>