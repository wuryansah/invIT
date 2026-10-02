<?php if (empty($pagination) || empty($pagination['total'])): return; endif; ?>
<?php
$page = (int)($pagination['page']);
$last = (int)($pagination['lastPage']);

$urlFor = function (int $p) use ($pagination) {
    if ($p < 1 || $p > (int)$pagination['lastPage']) {
        return '#';
    }
    $q = [];
    $query = $pagination['query'] ?? '';
    if ($query !== '') {
        parse_str($query, $q);
    }
    if ($p <= 1) {
        unset($q['page']);
    } else {
        $q['page'] = $p;
    }
    $qs = http_build_query($q);
    return ($pagination['base'] ?? '/') . ($qs !== '' ? '?' . $qs : '');
};
?>
<div class="pagination">
    <a class="page-link <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= e($urlFor($page - 1)) ?>">‹</a>
    <?php
    $start = max(1, $page - 2);
    $end = min($last, $page + 2);
    if ($start > 1): ?><span class="muted small">…</span><?php endif;
    for ($i = $start; $i <= $end; $i++): ?>
        <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= e($urlFor($i)) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($end < $last): ?><span class="muted small">…</span><?php endif; ?>
    <a class="page-link <?= $page >= $last ? 'disabled' : '' ?>" href="<?= e($urlFor($page + 1)) ?>">›</a>
    <span class="pagination-info">
        <?= (($page - 1) * (int)$pagination['perPage'] + 1) ?>–<?= min($page * (int)$pagination['perPage'], (int)$pagination['total']) ?> of <?= (int)$pagination['total'] ?>
    </span>
</div>