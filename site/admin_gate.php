<?php
// $destination must be set by caller. session_start() + csrf.php already loaded via protected.php.

include_once __DIR__ . '/../config/Database/Database.php';
$dt = new Database();
$conn = $dt->getConnect();

$gate_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $password = $_POST['password'] ?? '';

    // Only check against actual ADMIN staff (role_id = 1), never trust POST for role
    $stmt = $conn->prepare("SELECT * FROM staff WHERE role_id = 1 LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Plain-text passwords are no longer accepted (see migrations/004_hash_passwords.php).
        if (password_verify($password, $admin['staff_pass'])) {
            $_SESSION['adminId'] = (int) $admin['role_id'];
            // Return to the exact page asked for (e.g. Order.php?item=5&mode=activate),
            // but only if it is this gate's own destination page.
            $return = $_SESSION['admin_return'] ?? '';
            unset($_SESSION['admin_return']);
            $target = strpos($return, $destination . '?') === 0 ? $return : $destination;
            header('location: ' . $target);
            exit;
        }
    }

    $gate_message = 'Invalid Credentials';
}
