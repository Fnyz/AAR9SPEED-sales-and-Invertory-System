<?php
session_start();
require_once __DIR__ . '/../config/Database/Database.php';
require_once __DIR__ . '/../site/audit.php';

if (isset($_SESSION['userId'])) {
    log_action(isset($_GET['timeout']) ? 'logout_idle' : 'logout');
}
$_SESSION = [];
session_destroy();

header('location:../login.php' . (isset($_GET['timeout']) ? '?timeout=1' : ''));
