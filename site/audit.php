<?php
function log_action(string $action, string $target = ''): void
{
    try {
        $db   = new Database();
        $conn = $db->getConnect();
        $user = $_SESSION['userId'] ?? 'unknown';
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $conn->prepare(
            "INSERT INTO logs (staff_user, action, target, ip_address) VALUES (:u, :a, :t, :ip)"
        );
        $stmt->execute([':u' => $user, ':a' => $action, ':t' => $target, ':ip' => $ip]);
    } catch (Exception $e) {
        // Audit failures must never break the main flow
    }
}
