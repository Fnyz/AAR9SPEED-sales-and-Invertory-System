<?php
// Slows down password guessing (table: login_attempts, migrations/008_login_attempts.sql).
//
// Within LOGIN_WINDOW_MINUTES, a username is locked after LOGIN_MAX_PER_USER failed
// attempts, and an internet address after LOGIN_MAX_PER_IP (higher, because a shop's
// computers may share one address). Attempts are counted in the database, so clearing
// cookies doesn't reset them. Blocked attempts never check the password.

const LOGIN_MAX_PER_USER    = 5;
const LOGIN_MAX_PER_IP      = 20;
const LOGIN_WINDOW_MINUTES  = 15;

function login_limit_now(): string
{
    date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
    return date('Y-m-d H:i:s');
}

function login_limit_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/**
 * Minutes until this username (or this address) may try again; 0 when allowed.
 * If the table is missing, it allows the attempt and logs the problem, so a
 * forgotten migration can't lock everyone out.
 */
function login_blocked_minutes(PDO $conn, string $scope, string $username): int
{
    try {
        $now = login_limit_now();
        $since = date('Y-m-d H:i:s', strtotime("-" . LOGIN_WINDOW_MINUTES . " minutes"));
        $wait = 0;
        foreach ([['username', strtolower($username), LOGIN_MAX_PER_USER], ['ip', login_limit_ip(), LOGIN_MAX_PER_IP]] as [$column, $value, $max]) {
            if ($value === '') {
                continue;
            }
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS failures, MIN(attempted_at) AS oldest FROM login_attempts
                 WHERE scope = :scope AND $column = :value AND attempted_at >= :since"
            );
            $stmt->execute([':scope' => $scope, ':value' => $value, ':since' => $since]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ((int)$row['failures'] >= $max) {
                // Allowed again once the oldest failure in the window has expired.
                $seconds = strtotime($row['oldest']) + LOGIN_WINDOW_MINUTES * 60 - strtotime($now);
                $wait = max($wait, (int)ceil(max(60, $seconds) / 60));
            }
        }
        return $wait;
    } catch (PDOException $e) {
        error_log('login_blocked_minutes failed (run migrations/008_login_attempts.sql): ' . $e->getMessage());
        return 0;
    }
}

function login_record_failure(PDO $conn, string $scope, string $username): void
{
    try {
        $now = login_limit_now();
        $conn->prepare(
            "INSERT INTO login_attempts (scope, username, ip, attempted_at) VALUES (:scope, :username, :ip, :now)"
        )->execute([':scope' => $scope, ':username' => substr(strtolower($username), 0, 50), ':ip' => login_limit_ip(), ':now' => $now]);
        // Housekeeping: nothing older than a day is needed.
        $conn->prepare("DELETE FROM login_attempts WHERE attempted_at < :old")
            ->execute([':old' => date('Y-m-d H:i:s', strtotime('-1 day'))]);
    } catch (PDOException $e) {
        error_log('login_record_failure failed (run migrations/008_login_attempts.sql): ' . $e->getMessage());
    }
}

/** After a correct password: forget this username's failures. */
function login_clear_failures(PDO $conn, string $scope, string $username): void
{
    try {
        $conn->prepare("DELETE FROM login_attempts WHERE scope = :scope AND username = :username")
            ->execute([':scope' => $scope, ':username' => strtolower($username)]);
    } catch (PDOException $e) {
        error_log('login_clear_failures failed: ' . $e->getMessage());
    }
}

function login_blocked_message(int $minutes): string
{
    return 'Too many failed attempts. For your security, try again in ' . $minutes . ($minutes === 1 ? ' minute.' : ' minutes.');
}
