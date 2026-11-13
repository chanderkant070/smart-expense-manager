<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name  = mb_substr(trim($_POST['name'] ?? ''), 0, 80);
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter your name and a valid email.';
    elseif (strlen($pass) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($pass !== ($_POST['confirm'] ?? '')) $error = 'Passwords do not match.';
    else {
        $st = db()->prepare('SELECT 1 FROM users WHERE email=?');
        $st->execute([$email]);
        if ($st->fetch()) $error = 'An account with this email already exists.';
        else {
            db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)db()->lastInsertId(), 'name' => $name, 'email' => $email];
            flash('Welcome to Smart Expense Manager! Add your first expense, or load demo data from the dashboard.');
            redirect('dashboard.php');
        }
    }
}
$mode = 'register';
require __DIR__ . '/includes/auth_form.php';
