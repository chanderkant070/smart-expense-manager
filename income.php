<?php
$page = 'income'; $title = 'Income';
require __DIR__ . '/includes/header.php';
$uid = (int)$user['id'];
$month = $_GET['month'] ?? date('Y-m');

$sql = 'SELECT id, source, amount, received_on, note FROM income WHERE user_id=?'; $p = [$uid];
if ($month !== '') { [, $s, $en] = month_range($month); $sql .= ' AND received_on BETWEEN ? AND ?'; array_push($p, $s, $en); }
$sql .= ' ORDER BY received_on DESC, id DESC LIMIT 500';
$st = db()->prepare($sql); $st->execute($p);
$rows = $st->fetchAll();
$sum  = array_sum(array_column($rows, 'amount'));
?>
<section class="card">
  <div class="card-head">
    <h3>Income</h3>
    <button class="btn small" data-open="modal-income">+ Add income</button>
  </div>
  <form class="filters" method="get">
    <label>Month<input type="month" name="month" value="<?= e($month) ?>"></label>
    <button class="btn small">Filter</button>
    <a class="btn small ghost" href="income.php">Reset</a>
  </form>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Date</th><th>Source</th><th>Note</th><th class="r">Amount</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= date('d M Y', strtotime($r['received_on'])) ?></td>
          <td><?= e($r['source']) ?></td>
          <td class="muted"><?= e($r['note'] ?: '') ?></td>
          <td class="r good"><b><?= inr($r['amount']) ?></b></td>
          <td class="r">
            <form action="actions.php" method="post" data-confirm="Delete this income entry?">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_income"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="redirect" value="income.php">
              <button class="icon-btn" title="Delete" aria-label="Delete income">✕</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="muted">No income recorded for this period.</td></tr><?php endif; ?>
      </tbody>
      <?php if ($rows): ?><tfoot><tr><td colspan="4">Total</td><td class="r"><b><?= inr($sum) ?></b></td><td></td></tr></tfoot><?php endif; ?>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
