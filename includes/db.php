<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    if (DB_SSL) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    } catch (PDOException $ex) {
        http_response_code(500);
        exit('<h3>Database connection failed</h3><p>' . htmlspecialchars($ex->getMessage()) . '</p>'
            . '<p>Host: ' . htmlspecialchars(DB_HOST) . ' | Port: ' . htmlspecialchars(DB_PORT)
            . ' | Database: ' . htmlspecialchars(DB_NAME) . ' | User: ' . htmlspecialchars(DB_USER)
            . ' | SSL: ' . (DB_SSL ? 'on' : 'off') . '</p>');
    }

    // First run: create the tables automatically.
    if (!$pdo->query("SHOW TABLES LIKE 'users'")->fetch()) {
        $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    }
    return $pdo;
}
