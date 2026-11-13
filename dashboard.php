<?php
$page = 'dashboard'; $title = 'Dashboard';
require __DIR__ . '/includes/header.php';

$uid = (int)$user['id'];
[$ym, $mStart, $mEnd] = month_range(date('Y-m'));
[, $pStart, $pEnd]    = month_range(date('Y-m', strtotime('first day of last month')));

$allIncome   = total('income', 'received_on', $uid);
$allExpenses = total('expenses', 'spent_on', $uid);
$balance     = $allIncome - $allExpenses;
$expNow  = total('expenses', 'spent_on', $uid, $mStart, $mEnd);
$expPrev = total('expenses', 'spent_on', $uid, $pStart, $pEnd);
$incNow  = total('income', 'received_on', $uid, $mStart, $mEnd);
$expChange = pct_change($expNow, $expPrev);

$series = monthly_series($uid, 6);
$cats6  = category_breakdown($uid, $mStart, $mEnd);
$catTotal = array_sum(array_column($cats6, 'total'));

$st = db()->prepare('SELECT e.id, e.amount, e.spent_on, e.mode, e.sub_category, c.name cat, c.color
    FROM expenses e JOIN categories c ON c.id=e.category_id WHERE e.user_id=? ORDER BY e.spent_on DESC, e.id DESC LIMIT 5');
$st->execute([$uid]);
$recent = $st->fetchAll();

$g = db()->prepare('SELECT title, target, saved FROM goals WHERE user_id=?');
$g->execute([$uid]);
$goal = $g->fetch();
$goalPct = $goal ? min(100, (int)round($goal['saved'] / $goal['target'] * 100)) : 0;
$empty = ($allIncome + $allExpenses) == 0;
?>
<?php if ($empty): ?>
  <section class="card empty-banner">
    <div>
      <h3>Start tracking</h3>
      <p>Add your first expense or income from the sidebar. Want to see the charts working first? Load sample data for the last 6 months.</p>
    </div>
    <form action="actions.php" method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="demo_data"><input type="hidden" name="redirect" value="dashboard.php">
      <button class="btn">Load demo data</button>
    </form>
  </section>
<?php endif; ?>

<section class="grid grid-4">
  <article class="card stat tint-blue">
    <h4>Account balance</h4>
    <div class="big"><?= inr($balance) ?></div>
    <p class="sub">All income minus all expenses</p>
  </article>
  <article class="card stat tint-red">
    <h4>Expenses this month</h4>
    <div class="big"><?= inr($expNow) ?></div>
    <?php if ($expChange === null): ?>
      <p class="sub">No spending last month to compare</p>
    <?php else: ?>
      <p class="sub <?= $expChange > 0 ? 'bad' : 'good' ?>"><?= abs($expChange) ?>% <?= $expChange > 0 ? 'more' : 'less' ?> than last month</p>
    <?php endif; ?>
  </article>
  <article class="card stat tint-green">
    <h4>Income this month</h4>
    <div class="big"><?= inr($incNow) ?></div>
    <p class="sub <?= $incNow - $expNow >= 0 ? 'good' : 'bad' ?>">Saved <?= inr($incNow - $expNow) ?> so far</p>
  </article>
  <article class="card stat tint-amber goal">
    <div class="goal-ring" style="--p: <?= $goalPct ?>"><span><?= $goalPct ?>%</span></div>
    <div class="goal-info">
      <h4>Goal <button type="button" class="link" data-open="modal-goal">Edit</button></h4>
      <?php if ($goal): ?>
        <strong><?= e($goal['title']) ?></strong>
        <p class="sub">Target <?= inr($goal['target'], 0) ?><br>Saved <?= inr($goal['saved'], 0) ?></p>
      <?php else: ?>
        <p class="sub">Set a savings goal to track it here.</p>
      <?php endif; ?>
    </div>
  </article>
</section>

<section class="grid grid-2">
  <article class="card">
    <div class="card-head"><h3>Monthly expenses</h3><span class="muted">Last 6 months</span></div>
    <div class="chart-box"><canvas id="chartMonthly"></canvas></div>
  </article>
  <article class="card">
    <div class="card-head"><h3>Top categories</h3><span class="muted"><?= date('F') ?></span></div>
    <?php if ($cats6): ?>
      <div class="donut-wrap">
        <div class="donut-box"><canvas id="chartCategory"></canvas></div>
        <ul class="legend">
          <?php foreach (array_slice($cats6, 0, 6) as $c): ?>
            <li><i style="background:<?= e($c['color']) ?>"></i><span><?= e($c['name']) ?></span><b><?= inr($c['total'], 0) ?></b></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php else: ?>
      <p class="muted pad">No expenses recorded this month yet.</p>
    <?php endif; ?>
  </article>
</section>

<section class="card">
  <div class="card-head"><h3>Income vs expenses</h3><span class="muted">Last 6 months</span></div>
  <div class="chart-box tall"><canvas id="chartIncomeExpense"></canvas></div>
</section>

<section class="card">
  <div class="card-head"><h3>Recent expenses</h3><a class="link" href="expenses.php">View all</a></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Amount</th><th>Category</th><th>Sub category</th><th>Date</th><th>Mode</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><b><?= inr($r['amount']) ?></b></td>
          <td><span class="dot" style="background:<?= e($r['color']) ?>"></span><?= e($r['cat']) ?></td>
          <td><?= e($r['sub_category'] ?: '—') ?></td>
          <td><?= date('d M Y', strtotime($r['spent_on'])) ?></td>
          <td><span class="pill"><?= e($r['mode']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="5" class="muted">Nothing here yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<div class="modal" id="modal-goal" aria-hidden="true">
  <form class="modal-card" action="actions.php" method="post">
    <h3>Savings goal</h3>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="set_goal"><input type="hidden" name="redirect" value="dashboard.php">
    <div class="form-grid">
      <label class="span2">Goal name<input type="text" name="title" maxlength="80" required value="<?= e($goal['title'] ?? '') ?>" placeholder="e.g. New laptop"></label>
      <label>Target (₹)<input type="number" name="target" step="1" min="1" required value="<?= e($goal['target'] ?? '') ?>"></label>
      <label>Saved so far (₹)<input type="number" name="saved" step="1" min="0" required value="<?= e($goal['saved'] ?? 0) ?>"></label>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn ghost" data-close>Cancel</button>
      <button type="submit" class="btn">Save goal</button>
    </div>
  </form>
</div>

<script id="chart-data" type="application/json"><?= json_for_script([
    'series' => $series,
    'cats'   => ['labels' => array_column($cats6, 'name'), 'values' => array_map('floatval', array_column($cats6, 'total')), 'colors' => array_column($cats6, 'color')],
]) ?></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
