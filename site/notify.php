<?php
// One place to send automation messages to every configured channel
// (Telegram and/or Discord). Messages are written in Telegram's small HTML
// subset — <b>, <code> and escaped text — and converted for Discord.
//
// Hosts that block outgoing connections (InfinityFree's free plan): set
// NOTIFY_VIA_BROWSER=1. Discord messages then go into the notify_outbox table
// and a signed-in staff browser posts them (ajax/notification.php + scripts.php).

require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/discord.php';
require_once __DIR__ . '/../config/Database/Database.php';

/** Why each channel failed during this request, e.g. ['Discord' => 'HTTP 403: …']. */
function notify_record_error(string $channel, string $detail): void
{
    // Keep it short and readable: strip HTML error pages down to text.
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($detail)));
    $GLOBALS['notify_errors'][$channel] = mb_substr($text, 0, 300);
}

function notify_errors(): array
{
    return $GLOBALS['notify_errors'] ?? [];
}

function notify_configured(): bool
{
    return telegram_configured() || discord_configured();
}

/** True when Discord messages are handed to staff browsers instead of sent by the server. */
function notify_via_browser(): bool
{
    return discord_configured() && filter_var($_ENV['NOTIFY_VIA_BROWSER'] ?? '', FILTER_VALIDATE_BOOLEAN);
}

/** Names of the channels that are set up, e.g. ['Telegram', 'Discord']. */
function notify_channels(): array
{
    return array_values(array_filter([
        telegram_configured() ? 'Telegram' : null,
        discord_configured() ? (notify_via_browser() ? 'Discord (via browser)' : 'Discord') : null,
    ]));
}

/** Escape text (product names, etc.) before putting it in a message. */
function notify_escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Turn the message HTML into Discord Markdown. */
function notify_html_to_markdown(string $html): string
{
    $md = preg_replace(['~</?b>~i', '~</?code>~i'], ['**', '`'], $html);
    $md = strip_tags($md);
    return html_entity_decode($md, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Send to every configured channel. True when at least one accepted it
 * (or, with NOTIFY_VIA_BROWSER, when it was queued for a staff browser).
 */
function notify_send(string $html): bool
{
    $sent = false;
    if (telegram_configured()) {
        $sent = telegram_send($html) || $sent;
    }
    if (discord_configured()) {
        $markdown = notify_html_to_markdown($html);
        $sent = (notify_via_browser() ? notify_queue($markdown) : discord_send($markdown)) || $sent;
    }
    return $sent;
}

/* ---------- Outbox for NOTIFY_VIA_BROWSER (migrations/007_notify_outbox.sql) ---------- */

function notify_outbox_conn(): ?PDO
{
    $conn = (new Database())->getConnect();
    if (!$conn instanceof PDO) {
        return null;
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
    return $conn;
}

/** Queue a Discord message for the next staff browser. */
function notify_queue(string $markdown): bool
{
    try {
        $conn = notify_outbox_conn();
        if (!$conn) {
            notify_record_error('Discord', 'Could not queue the message: no database connection.');
            return false;
        }
        $conn->prepare("INSERT INTO notify_outbox (content, created_at) VALUES (:content, :now)")
            ->execute([':content' => $markdown, ':now' => date('Y-m-d H:i:s')]);
        return true;
    } catch (PDOException $e) {
        error_log('notify_queue failed: ' . $e->getMessage());
        notify_record_error('Discord', 'Could not queue the message. Import migrations/007_notify_outbox.sql.');
        return false;
    }
}

/**
 * Claim up to $limit waiting messages for one browser, so a second open page
 * doesn't send them too. Claims expire after 2 minutes in case that browser
 * closes before sending. Messages older than 2 days are skipped.
 * Returns ['token' => …, 'items' => [['id' => …, 'content' => …], …]].
 */
function notify_claim_for_browser(int $limit = 5): array
{
    if (!notify_via_browser()) {
        return ['token' => '', 'items' => []];
    }
    try {
        $conn = notify_outbox_conn();
        if (!$conn) {
            return ['token' => '', 'items' => []];
        }
        $token = bin2hex(random_bytes(16));
        $now = date('Y-m-d H:i:s');
        $conn->prepare(
            "UPDATE notify_outbox SET claim_token = :token, claimed_at = :now
             WHERE sent_at IS NULL
               AND (claimed_at IS NULL OR claimed_at < :stale)
               AND created_at >= :oldest
             ORDER BY id LIMIT " . (int)$limit
        )->execute([
            ':token'  => $token,
            ':now'    => $now,
            ':stale'  => date('Y-m-d H:i:s', strtotime('-2 minutes')),
            ':oldest' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ]);
        $read = $conn->prepare("SELECT id, content FROM notify_outbox WHERE claim_token = :token ORDER BY id");
        $read->execute([':token' => $token]);
        $items = array_map(fn($r) => ['id' => (int)$r['id'], 'content' => $r['content']], $read->fetchAll(PDO::FETCH_ASSOC));
        return ['token' => $items ? $token : '', 'items' => $items];
    } catch (PDOException $e) {
        error_log('notify_claim_for_browser failed: ' . $e->getMessage());
        return ['token' => '', 'items' => []];
    }
}

/** A browser reports which claimed messages it posted. */
function notify_mark_sent(string $token, array $ids): void
{
    $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
    if ($token === '' || !$ids) {
        return;
    }
    try {
        $conn = notify_outbox_conn();
        if (!$conn) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $conn->prepare(
            "UPDATE notify_outbox SET sent_at = ? WHERE claim_token = ? AND sent_at IS NULL AND id IN ($placeholders)"
        )->execute(array_merge([date('Y-m-d H:i:s'), $token], $ids));
    } catch (PDOException $e) {
        error_log('notify_mark_sent failed: ' . $e->getMessage());
    }
}
