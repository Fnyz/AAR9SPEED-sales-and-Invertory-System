<?php
// $destination must be set by caller. session_start() + csrf.php already loaded via protected.php.

include_once __DIR__ . '/../config/Database/Database.php';
require_once __DIR__ . '/login_limit.php';
require_once __DIR__ . '/audit.php';
$dt = new Database();
$conn = $dt->getConnect();

// Message shown on the admin password page, or null.
$gate_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $password = $_POST['password'] ?? '';
    // Failed admin-password attempts are counted per signed-in staff member.
    $gateUser = (string)($_SESSION['userId'] ?? '');

    // Only check against actual ADMIN staff (role_id = 1), never trust POST for role
    $stmt = $conn->prepare("SELECT * FROM staff WHERE role_id = 1 LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (($wait = login_blocked_minutes($conn, 'admin', $gateUser)) > 0) {
        $gate_message = login_blocked_message($wait);
    } elseif ($admin) {
        // Plain-text passwords are no longer accepted (see migrations/004_hash_passwords.php).
        if (password_verify($password, $admin['staff_pass'])) {
            login_clear_failures($conn, 'admin', $gateUser);
            $_SESSION['adminId'] = (int) $admin['role_id'];
            log_action('admin_unlock', $destination);
            // Return to the exact page asked for (e.g. Order.php?item=5&mode=activate),
            // but only if it is this gate's own destination page.
            $return = $_SESSION['admin_return'] ?? '';
            unset($_SESSION['admin_return']);
            $target = strpos($return, $destination . '?') === 0 ? $return : $destination;
            header('location: ' . $target);
            exit;
        }
    }

    if ($gate_message === null) {
        login_record_failure($conn, 'admin', $gateUser);
        log_action('admin_unlock_failed', $destination);
        $gate_message = 'wrong';
    }
}
