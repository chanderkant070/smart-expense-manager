<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$uid = (int)current_user()['id'];
[$ym, $from, $to] = month_range($_GET['month'] ?? date('Y-m'));

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="smart-expense-manager-' . $ym . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly
fputcsv($out, ['Type', 'Date', 'Category / Source', 'Sub category', 'Mode', 'Note', 'Amount']);

$st = db()->prepare('SELECT e.spent_on d, c.name cat, e.sub_category, e.mode, e.note, e.amount
    FROM expenses e JOIN categories c ON c.id=e.category_id WHERE e.user_id=? AND e.spent_on BETWEEN ? AND ? ORDER BY e.spent_on');
$st->execute([$uid, $from, $to]);
foreach ($st as $r) fputcsv($out, ['Expense', $r['d'], $r['cat'], $r['sub_category'], $r['mode'], $r['note'], $r['amount']]);

$st = db()->prepare('SELECT received_on d, source, note, amount FROM income WHERE user_id=? AND received_on BETWEEN ? AND ? ORDER BY received_on');
$st->execute([$uid, $from, $to]);
foreach ($st as $r) fputcsv($out, ['Income', $r['d'], $r['source'], '', '', $r['note'], $r['amount']]);
fclose($out);
