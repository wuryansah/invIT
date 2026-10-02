<?php if (empty($pagination['total'])): ?>
    <tr><td colspan="99" class="table-empty"><span class="muted">No records found.</span></td></tr>
<?php else: ?>
    <tr>
        <td colspan="99" class="pagination" style="background:transparent;">
            <div class="pagination">
                <?php
                $page = $pagination['page'];
                $last = $pagination['lastPage'];

                $urlFor = function (int $p) use ($pagination) {
                    if ($p < 1 || $p > $pagination['lastPage']) {
                        return '#';
                    }
                    $query = $pagination['query'] ?? '';
                    $q = [];
                    if ($query !== '') {
                        parse_str($query, $q);
                    }
                    if ($p <= 1) {
                        unset($q['page']);
                    } else {
                        $q['page'] = $p;
                    }
                    return $pagination['base'] . '?' . http_build_query($q);
                };
                ?>
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
                <span class="pagination-info">Showing <?= $pagination['total'] ? min(($page - 1) * $pagination['perPage'] + 1, $pagination['total']) : 0 ?>–<?= min($page * $pagination['perPage'], $pagination['total']) ?> of <?= $pagination['total'] ?></span>
            </div>
        </td>
    </tr>
<?php endif; ?>