<?php
// Over HTTPS, the session cookie is only ever sent over HTTPS.
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $isHttps]);
session_start();
require_once __DIR__ . '/csrf.php';
csrf_init();

if (!isset($_SESSION['userId'])) {
    header('location:login.php');
    exit;
}
