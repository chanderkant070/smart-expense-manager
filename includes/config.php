<?php
// Database settings. Priority: config.local.php > environment variables > defaults.
// Defaults match a fresh XAMPP / WAMP install (user root, empty password).
if (is_file(__DIR__ . '/config.local.php')) require __DIR__ . '/config.local.php';

function cfg($key, $default) {
    $v = getenv($key);
    return ($v !== false && $v !== '') ? $v : $default;
}

defined('DB_HOST') || define('DB_HOST', cfg('DB_HOST', '127.0.0.1'));
defined('DB_PORT') || define('DB_PORT', cfg('DB_PORT', '3306'));
defined('DB_NAME') || define('DB_NAME', cfg('DB_NAME', 'smart_expense_manager'));
defined('DB_USER') || define('DB_USER', cfg('DB_USER', 'root'));
defined('DB_PASS') || define('DB_PASS', cfg('DB_PASS', ''));
// Set DB_SSL=true for cloud MySQL providers (Aiven, TiDB Cloud, etc.)
defined('DB_SSL')  || define('DB_SSL', filter_var(cfg('DB_SSL', 'false'), FILTER_VALIDATE_BOOLEAN));
