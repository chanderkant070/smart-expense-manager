<?php
$page = 'expenses'; $title = 'All Expenses';
require __DIR__ . '/includes/header.php';
$uid = (int)$user['id'];

// Default to the current month; an empty month box means "all time".
$month = $_GET['month'] ?? date('Y-m');
$catId = (int)($_GET['category'] ?? 0);
$q     = trim($_GET['q'] ?? '');

$sql = 'SELECT e.id, e.amount, e.spent_on, e.mode, e.sub_category, e.note, c.name cat, c.color
        FROM expenses e JOIN categories c ON c.id=e.category_id WHERE e.user_id=?';
$p = [$uid];
if ($month !== '') { [, $s, $en] = month_range($month); $sql .= ' AND e.spent_on BETWEEN ? AND ?'; array_push($p, $s, $en); }
if ($catId)        { $sql .= ' AND e.category_id=?'; $p[] = $catId; }
if ($q !== '')     { $sql .= ' AND (e.sub_category LIKE ? OR e.note LIKE ? OR c.name LIKE ?)'; $like = '%' . $q . '%'; array_push($p, $like, $like, $like); }
$sql .= ' ORDER BY e.spent_on DESC, e.id DESC LIMIT 500';
$st = db()->prepare($sql); $st->execute($p);
$rows = $st->fetchAll();
$sum  = array_sum(array_column($rows, 'amount'));
?>
<section class="card">
  <div class="card-head">
    <h3>All expenses</h3>
    <button class="btn small" data-open="modal-expense">+ Add expense</button>
  </div>
  <form class="filters" method="get">
    <label>Month<input type="month" name="month" value="<?= e($month) ?>"></label>
    <label>Category
      <select name="category">
        <option value="0">All categories</option>
        <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Search<input type="search" name="q" value="<?= e($q) ?>" placeholder="Sub category or note"></label>
    <button class="btn small">Filter</button>
    <a class="btn small ghost" href="expenses.php">Reset</a>
  </form>

  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Date</th><th>Category</th><th>Sub category</th><th>Mode</th><th>Note</th><th class="r">Amount</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= date('d M Y', strtotime($r['spent_on'])) ?></td>
          <td><span class="dot" style="background:<?= e($r['color']) ?>"></span><?= e($r['cat']) ?></td>
          <td><?= e($r['sub_category'] ?: '—') ?></td>
          <td><span class="pill"><?= e($r['mode']) ?></span></td>
          <td class="muted"><?= e($r['note'] ?: '') ?></td>
          <td class="r"><b><?= inr($r['amount']) ?></b></td>
          <td class="r">
            <form action="actions.php" method="post" data-confirm="Delete this expense?">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_expense"><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="hidden" name="redirect" value="expenses.php">
              <button class="icon-btn" title="Delete" aria-label="Delete expense">✕</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="8" class="muted">No expenses match these filters.</td></tr><?php endif; ?>
      </tbody>
      <?php if ($rows): ?><tfoot><tr><td colspan="6">Total (<?= count($rows) ?> entries)</td><td class="r"><b><?= inr($sum) ?></b></td><td></td></tr></tfoot><?php endif; ?>
    </table>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
