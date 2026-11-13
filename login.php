<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $st = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email=?');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email']];
        redirect('dashboard.php');
    }
    $error = 'Email or password is incorrect.';
}
$mode = 'login';
require __DIR__ . '/includes/auth_form.php';
