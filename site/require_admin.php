<?php
// Include after site/protected.php on admin-only pages. Sends anyone who hasn't
// passed the admin password page (site/admin_gate.php) back to it, remembering
// the page and query they asked for so the gate can return them there.
// Set $adminGatePage to that page, e.g. 'SecurityCheck.php'.
if (($_SESSION['adminId'] ?? 0) !== 1) {
    $_SESSION['admin_return'] = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '')
        . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
    header('location: ' . ($adminGatePage ?? 'Dashboard.php'));
    exit;
}
