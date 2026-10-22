<?php
// Who is signed in, and what they may do. Needs session_start() first.
//
// is_admin(): the signed-in account has the ADMIN role, or this session passed
// the admin password page (site/admin_gate.php). Admin-only *actions* (refunds,
// price and cost changes, deletes) use this. Admin *areas* (Staff, Purchase
// orders, Reports, Activity log) still ask for the admin password first; see
// site/require_admin.php.

require_once __DIR__ . '/../config/Database/Database.php';

const ROLE_ADMIN = 1;

function current_role_id(): int
{
    if (!isset($_SESSION['roleId']) && isset($_SESSION['getId'])) {
        // Sessions started before roles were stored: look it up once.
        try {
            $conn = (new Database())->getConnect();
            $stmt = $conn ? $conn->prepare("SELECT role_id FROM staff WHERE STAFF_ID = :id") : null;
            if ($stmt && $stmt->execute([':id' => $_SESSION['getId']])) {
                $_SESSION['roleId'] = (int)$stmt->fetchColumn();
            }
        } catch (PDOException $e) {
            error_log('current_role_id failed: ' . $e->getMessage());
        }
    }
    return (int)($_SESSION['roleId'] ?? 0);
}

function is_admin(): bool
{
    return current_role_id() === ROLE_ADMIN || ($_SESSION['adminId'] ?? 0) === 1;
}

/** For AJAX handlers: stop with a JSON 403 unless the user is an admin. */
function require_admin_json(string $action): void
{
    if (!is_admin()) {
        http_response_code(403);
        header('Content-Type: application/json');
        exit(json_encode(['error' => 'Only an admin can ' . $action . '. Sign in with an admin account, or ask an admin.']));
    }
}

/** Minutes of inactivity before staff are signed out (SESSION_IDLE_MINUTES, default 30). */
function session_idle_minutes(): int
{
    Database::loadEnv();
    return max(5, (int)($_ENV['SESSION_IDLE_MINUTES'] ?? 30));
}

/**
 * Sign out a session that has been idle too long. Returns true when it did.
 * Call on every page load (site/protected.php) and from the keep-alive ping.
 */
function session_expire_if_idle(): bool
{
    if (!isset($_SESSION['userId'])) {
        return false;
    }
    $last = (int)($_SESSION['last_activity'] ?? time());
    if (time() - $last > session_idle_minutes() * 60) {
        $_SESSION = [];
        session_destroy();
        return true;
    }
    $_SESSION['last_activity'] = time();
    return false;
}
