<?php $isLogin = ($mode === 'login'); ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $isLogin ? 'Log in' : 'Create account' ?> · Smart Expense Manager</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<form class="auth-card" method="post" autocomplete="on">
  <div class="logo"><span class="logo-mark">S</span> Smart Expense Manager</div>
  <h2><?= $isLogin ? 'Log in to your account' : 'Create your account' ?></h2>
  <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <?php if (!$isLogin): ?>
    <label>Full name<input type="text" name="name" required maxlength="80" value="<?= e($_POST['name'] ?? '') ?>"></label>
  <?php endif; ?>
  <label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
  <label>Password<input type="password" name="password" required minlength="<?= $isLogin ? 1 : 6 ?>"></label>
  <?php if (!$isLogin): ?>
    <label>Confirm password<input type="password" name="confirm" required minlength="6"></label>
  <?php endif; ?>
  <button type="submit" class="btn block"><?= $isLogin ? 'Log in' : 'Create account' ?></button>
  <p class="auth-switch">
    <?= $isLogin ? 'New here? <a href="register.php">Create an account</a>' : 'Already registered? <a href="login.php">Log in</a>' ?>
  </p>
</form>
</body>
</html>
