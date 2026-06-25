<?php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once __DIR__ . '/csrf.php';
csrf_init();

if (!isset($_SESSION['userId'])) {
    header('location:login.php');
    exit;
}
