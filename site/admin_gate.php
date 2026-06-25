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
        $authenticated = false;

        if (password_verify($password, $admin['staff_pass'])) {
            $authenticated = true;
        } elseif ($admin['staff_pass'] === $password) {
            // Plaintext password — rehash transparently
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE staff SET staff_pass = :hash WHERE STAFF_ID = :id");
            $upd->execute([':hash' => $hash, ':id' => $admin['STAFF_ID']]);
            $authenticated = true;
        }

        if ($authenticated) {
            $_SESSION['adminId'] = (int) $admin['role_id'];
            header('location: ' . $destination);
            exit;
        }
    }

    $gate_message = 'Invalid Credentials';
}
