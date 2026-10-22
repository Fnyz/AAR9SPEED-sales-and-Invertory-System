<?php
// Keeps the session alive while staff are actively using a page without
// reloading it (sent by site/layout/scripts.php after keyboard/mouse activity).
session_start();
require_once "../site/csrf.php";
require_once "../site/auth.php";
header('Content-Type: application/json');

if (session_expire_if_idle() || !isset($_SESSION['userId'])) {
    http_response_code(401);
    exit(json_encode(['signedIn' => false]));
}
csrf_verify();
echo json_encode(['signedIn' => true, 'idleMinutes' => session_idle_minutes()]);
