<?php
require_once __DIR__ . '/db.php';
date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    $https = !empty($_SERVER['HTTPS']) || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $https]);
    session_start();
}

const MODES = ['UPI', 'Card', 'Cash', 'Bank'];

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Format a number as Indian rupees: 898450 -> ₹8,98,450.00 */
function inr($n, int $dec = 2): string {
    $n = (float)$n; $neg = $n < 0; $n = abs($n);
    $s = number_format($n, $dec, '.', '');
    [$int, $frac] = array_pad(explode('.', $s), 2, '');
    $last3 = substr($int, -3);
    $rest  = substr($int, 0, -3);
    if ($rest !== '') $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',';
    return ($neg ? '-' : '') . '₹' . $rest . $last3 . ($dec ? '.' . $frac : '');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

function current_user() { return $_SESSION['user'] ?? null; }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function require_login(): void { if (!current_user()) redirect('login.php'); }

function valid_date($d): bool {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string)$d, $m)) return false;
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
}

/** Returns [ym, first day, last day] for a "YYYY-MM" string (falls back to current month). */
function month_range($ym): array {
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string)$ym)) $ym = date('Y-m');
    $start = $ym . '-01';
    return [$ym, $start, date('Y-m-t', strtotime($start))];
}

function total(string $table, string $col, int $uid, ?string $from = null, ?string $to = null): float {
    $sql = "SELECT COALESCE(SUM(amount),0) FROM $table WHERE user_id=?"; $p = [$uid];
    if ($from) { $sql .= " AND $col BETWEEN ? AND ?"; array_push($p, $from, $to); }
    $st = db()->prepare($sql); $st->execute($p);
    return (float)$st->fetchColumn();
}

function pct_change(float $cur, float $prev): ?int {
    return $prev > 0 ? (int)round(($cur - $prev) / $prev * 100) : null;
}

/** Last $n months of income and expense totals, oldest first. */
function monthly_series(int $uid, int $n = 6): array {
    $months = [];
    for ($i = $n - 1; $i >= 0; $i--) {
        $ts = strtotime($i === 0 ? 'first day of this month' : "first day of -$i month");
        $months[date('Y-m', $ts)] = date('M', $ts);
    }
    $from = array_key_first($months) . '-01';
    $q = function (string $table, string $col) use ($uid, $from) {
        $st = db()->prepare("SELECT DATE_FORMAT($col,'%Y-%m') ym, SUM(amount) s FROM $table WHERE user_id=? AND $col>=? GROUP BY ym");
        $st->execute([$uid, $from]);
        return $st->fetchAll(PDO::FETCH_KEY_PAIR);
    };
    $exp = $q('expenses', 'spent_on'); $inc = $q('income', 'received_on');
    $out = ['labels' => array_values($months), 'expenses' => [], 'income' => []];
    foreach ($months as $k => $_) {
        $out['expenses'][] = round((float)($exp[$k] ?? 0), 2);
        $out['income'][]   = round((float)($inc[$k] ?? 0), 2);
    }
    return $out;
}

function category_breakdown(int $uid, string $from, string $to): array {
    $st = db()->prepare("SELECT c.name, c.color, SUM(e.amount) total, COUNT(*) cnt
        FROM expenses e JOIN categories c ON c.id = e.category_id
        WHERE e.user_id=? AND e.spent_on BETWEEN ? AND ?
        GROUP BY c.id, c.name, c.color ORDER BY total DESC");
    $st->execute([$uid, $from, $to]);
    return $st->fetchAll();
}

function json_for_script(array $data): string {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}
