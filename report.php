<?php
$page = 'report'; $title = 'Monthly Report';
require __DIR__ . '/includes/header.php';
$uid = (int)$user['id'];

[$ym, $from, $to] = month_range($_GET['month'] ?? date('Y-m'));
[, $pFrom, $pTo]  = month_range(date('Y-m', strtotime($from . ' -1 month')));

$inc  = total('income', 'received_on', $uid, $from, $to);
$exp  = total('expenses', 'spent_on', $uid, $from, $to);
$prev = total('expenses', 'spent_on', $uid, $pFrom, $pTo);
$net  = $inc - $exp;
$rate = $inc > 0 ? round($net / $inc * 100) : null;
$chg  = pct_change($exp, $prev);
$cats = category_breakdown($uid, $from, $to);

// Daily spending for the line chart
$days = (int)date('t', strtotime($from));
$daily = array_fill(1, $days, 0.0);
$st = db()->prepare('SELECT DAY(spent_on) d, SUM(amount) s FROM expenses WHERE user_id=? AND spent_on BETWEEN ? AND ? GROUP BY d');
$st->execute([$uid, $from, $to]);
foreach ($st->fetchAll() as $r) $daily[(int)$r['d']] = (float)$r['s'];

$st = db()->prepare('SELECT e.amount, e.spent_on, e.sub_category, e.mode, c.name cat FROM expenses e JOIN categories c ON c.id=e.category_id
    WHERE e.user_id=? AND e.spent_on BETWEEN ? AND ? ORDER BY e.amount DESC LIMIT 10');
$st->execute([$uid, $from, $to]);
$top = $st->fetchAll();
?>
<section class="card no-print">
  <form class="filters" method="get">
    <label>Report month<input type="month" name="month" value="<?= e($ym) ?>" max="<?= date('Y-m') ?>"></label>
    <button class="btn small">Show report</button>
    <a class="btn small ghost" href="export.php?month=<?= e($ym) ?>">Download CSV</a>
    <button type="button" class="btn small ghost" onclick="window.print()">Print / Save PDF</button>
  </form>
</section>

<h2 class="report-title">Report for <?= date('F Y', strtotime($from)) ?></h2>

<section class="grid grid-4">
  <article class="card stat tint-green"><h4>Income</h4><div class="big"><?= inr($inc) ?></div></article>
  <article class="card stat tint-red"><h4>Expenses</h4><div class="big"><?= inr($exp) ?></div>
    <?php if ($chg !== null): ?><p class="sub <?= $chg > 0 ? 'bad' : 'good' ?>"><?= abs($chg) ?>% <?= $chg > 0 ? 'more' : 'less' ?> than previous month</p><?php endif; ?></article>
  <article class="card stat tint-blue"><h4>Net savings</h4><div class="big <?= $net >= 0 ? 'good' : 'bad' ?>"><?= inr($net) ?></div></article>
  <article class="card stat tint-amber"><h4>Savings rate</h4><div class="big"><?= $rate === null ? '—' : $rate . '%' ?></div><p class="sub">Of income this month</p></article>
</section>

<section class="grid grid-2">
  <article class="card">
    <div class="card-head"><h3>Spending by category</h3></div>
    <?php if ($cats): foreach ($cats as $c): $w = $exp > 0 ? round($c['total'] / $exp * 100) : 0; ?>
      <div class="bar-row">
        <div class="bar-label"><span><span class="dot" style="background:<?= e($c['color']) ?>"></span><?= e($c['name']) ?></span><b><?= inr($c['total']) ?> · <?= $w ?>%</b></div>
        <div class="bar"><i style="width:<?= $w ?>%;background:<?= e($c['color']) ?>"></i></div>
      </div>
    <?php endforeach; else: ?><p class="muted pad">No expenses in this month.</p><?php endif; ?>
  </article>
  <article class="card">
    <div class="card-head"><h3>Daily spending</h3></div>
    <div class="chart-box"><canvas id="chartDaily"></canvas></div>
  </article>
</section>

<section class="card">
  <div class="card-head"><h3>10 biggest expenses</h3></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Category</th><th>Sub category</th><th>Mode</th><th class="r">Amount</th></tr></thead>
      <tbody>
      <?php foreach ($top as $r): ?>
        <tr><td><?= date('d M', strtotime($r['spent_on'])) ?></td><td><?= e($r['cat']) ?></td><td><?= e($r['sub_category'] ?: '—') ?></td>
            <td><span class="pill"><?= e($r['mode']) ?></span></td><td class="r"><b><?= inr($r['amount']) ?></b></td></tr>
      <?php endforeach; ?>
      <?php if (!$top): ?><tr><td colspan="5" class="muted">Nothing to show.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script id="chart-data" type="application/json"><?= json_for_script([
    'daily' => ['labels' => array_keys($daily), 'values' => array_values($daily)],
]) ?></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
