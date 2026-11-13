<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dashboard.php');
csrf_check();

$pdo  = db();
$uid  = (int)current_user()['id'];
$back = in_array($_POST['redirect'] ?? '', ['dashboard.php', 'expenses.php', 'income.php', 'report.php'], true)
        ? $_POST['redirect'] : 'dashboard.php';
$act  = $_POST['action'] ?? '';
$text = fn($k, $max) => mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max);

switch ($act) {
    case 'add_expense':
        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $cat    = (int)($_POST['category_id'] ?? 0);
        $date   = $_POST['date'] ?? '';
        $mode   = $_POST['mode'] ?? '';
        $ok = $pdo->prepare('SELECT 1 FROM categories WHERE id=?');
        $ok->execute([$cat]);
        if ($amount <= 0 || $amount > 99999999 || !valid_date($date) || !in_array($mode, MODES, true) || !$ok->fetch()) {
            flash('Enter a valid amount, date, category and payment mode.', 'err'); break;
        }
        $pdo->prepare('INSERT INTO expenses (user_id, category_id, sub_category, amount, spent_on, mode, note) VALUES (?,?,?,?,?,?,?)')
            ->execute([$uid, $cat, $text('sub_category', 80) ?: null, $amount, $date, $mode, $text('note', 255) ?: null]);
        flash('Expense saved.');
        break;

    case 'add_income':
        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $date   = $_POST['date'] ?? '';
        $source = $text('source', 80);
        if ($amount <= 0 || $amount > 99999999 || !valid_date($date) || $source === '') {
            flash('Enter a valid amount, date and income source.', 'err'); break;
        }
        $pdo->prepare('INSERT INTO income (user_id, source, amount, received_on, note) VALUES (?,?,?,?,?)')
            ->execute([$uid, $source, $amount, $date, $text('note', 255) ?: null]);
        flash('Income saved.');
        break;

    case 'delete_expense':
        $pdo->prepare('DELETE FROM expenses WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $uid]);
        flash('Expense deleted.');
        break;

    case 'delete_income':
        $pdo->prepare('DELETE FROM income WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $uid]);
        flash('Income deleted.');
        break;

    case 'set_goal':
        $title  = $text('title', 80);
        $target = round((float)($_POST['target'] ?? 0), 2);
        $saved  = round((float)($_POST['saved'] ?? 0), 2);
        if ($title === '' || $target <= 0 || $saved < 0) { flash('Enter a goal name, a target above 0 and the amount saved.', 'err'); break; }
        $pdo->prepare('INSERT INTO goals (user_id, title, target, saved) VALUES (?,?,?,?)
                       ON DUPLICATE KEY UPDATE title=VALUES(title), target=VALUES(target), saved=VALUES(saved)')
            ->execute([$uid, $title, $target, $saved]);
        flash('Goal updated.');
        break;

    case 'demo_data':
        $has = $pdo->prepare('SELECT COUNT(*) FROM expenses WHERE user_id=?');
        $has->execute([$uid]);
        if ((int)$has->fetchColumn() > 0) { flash('Demo data only loads into an empty account.', 'err'); break; }
        $ids = $pdo->query('SELECT name, id FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
        $subs = [
            'Food & Grocery' => ['Swiggy', 'Zomato', 'BigBasket', 'Blinkit'],
            'Shopping' => ['Amazon', 'Flipkart', 'Myntra'],
            'Travelling' => ['IRCTC', 'Uber', 'Ola', 'Metro'],
            'Bill & Subscription' => ['Netflix', 'Spotify', 'Electricity', 'WiFi'],
            'Entertainment' => ['PVR', 'BookMyShow'],
            'Health' => ['Pharmacy', 'Clinic'],
            'Education' => ['Udemy', 'Books'],
            'Investment' => ['Groww', 'Zerodha'],
            'Miscellaneous' => ['Gift', 'Repairs'],
        ];
        $insE = $pdo->prepare('INSERT INTO expenses (user_id, category_id, sub_category, amount, spent_on, mode) VALUES (?,?,?,?,?,?)');
        $insI = $pdo->prepare('INSERT INTO income (user_id, source, amount, received_on) VALUES (?,?,?,?)');
        $pdo->beginTransaction();
        for ($i = 5; $i >= 0; $i--) {
            $ts  = strtotime($i === 0 ? 'first day of this month' : "first day of -$i month");
            $max = $i === 0 ? (int)date('j') : (int)date('t', $ts);
            $ym  = date('Y-m', $ts);
            $insI->execute([$uid, 'Salary', mt_rand(38000, 46000), $ym . '-01']);
            if (mt_rand(0, 1)) $insI->execute([$uid, 'Freelance', mt_rand(3000, 12000), sprintf('%s-%02d', $ym, mt_rand(1, $max))]);
            for ($k = 0; $k < 14; $k++) {
                $name = array_rand($subs);
                $insE->execute([$uid, $ids[$name], $subs[$name][array_rand($subs[$name])],
                    mt_rand(80, 3200), sprintf('%s-%02d', $ym, mt_rand(1, $max)), MODES[array_rand(MODES)]]);
            }
        }
        $pdo->commit();
        flash('Demo data added for the last 6 months.');
        break;

    default:
        flash('Unknown action.', 'err');
}
redirect($back);
