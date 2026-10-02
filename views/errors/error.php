<div class="page-head">
    <div>
        <h1 class="page-title"><?= e($code ?? 500) ?> — <?= e($message ?? 'Server Error') ?></h1>
        <p class="page-desc">Something went wrong.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-outline" href="<?= url('/') ?>"><?= svg_icon('home') ?> Back to dashboard</a>
    </div>
</div>
<div class="card card-pad">
    <p><?= e($message ?? 'Unexpected error.') ?></p>
</div>