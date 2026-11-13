<?php
require_once __DIR__ . '/auth.php';
require_login();
$user  = current_user();
$cats  = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$flash = flash();
$page  = $page ?? 'dashboard';
$title = $title ?? 'Smart Expense Manager';
$self  = basename($_SERVER['PHP_SELF']);
$nav = [
    'dashboard' => ['dashboard.php', 'Dashboard',      '▦'],
    'expenses'  => ['expenses.php',  'All Expenses',   '⇄'],
    'income'    => ['income.php',    'Income',         '＋'],
    'report'    => ['report.php',    'Monthly Report', '▤'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · Smart Expense Manager</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="logo"><span class="logo-mark">S</span> Smart Expense Manager</div>
    <nav>
      <p class="nav-label">General</p>
      <?php foreach ($nav as $key => [$href, $label, $icon]): ?>
        <a href="<?= $href ?>" class="<?= $page === $key ? 'active' : '' ?>"><span class="ico"><?= $icon ?></span><?= $label ?></a>
      <?php endforeach; ?>
      <p class="nav-label">Quick add</p>
      <button type="button" class="nav-btn" data-open="modal-expense"><span class="ico">−</span>Add expense</button>
      <button type="button" class="nav-btn" data-open="modal-income"><span class="ico">+</span>Add income</button>
    </nav>
    <div class="sidebar-foot">
      <a href="logout.php" class="logout"><span class="ico">⎋</span>Logout</a>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <button type="button" class="menu-btn" id="menuBtn" aria-label="Open menu">☰</button>
      <div class="greet">
        <h1>Hi, <?= e(explode(' ', $user['name'])[0]) ?> 👋</h1>
        <p>Track all your expenses and transactions</p>
      </div>
      <form class="search" action="expenses.php" method="get">
        <input type="search" name="q" placeholder="Search expenses…" value="<?= e($_GET['q'] ?? '') ?>">
      </form>
      <div class="date-chip"><?= date('d M Y') ?></div>
      <div class="avatar" title="<?= e($user['email']) ?>"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div>
    </header>

    <?php if ($flash): ?>
      <div class="flash <?= $flash[1] === 'err' ? 'err' : 'ok' ?>" role="status"><?= e($flash[0]) ?></div>
    <?php endif; ?>
