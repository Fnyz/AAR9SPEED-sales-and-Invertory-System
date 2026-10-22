<?php
// Scheduled automations: low-stock alerts and the daily sales report.
//
// Entry points:
//   automation_run_due()  – runs whatever is due; called by cron.php and by the
//                           notification poll (ajax/notification.php) as a fallback
//                           for hosts without cron.
//   automation_send_daily_report() / automation_check_low_stock() – run one task now.
//
// Tables: automation_runs, stock_alerts (migrations/003_automation.sql).

require_once __DIR__ . '/../config/Database/Database.php';
require_once __DIR__ . '/notify.php';

function automation_connect(): ?PDO
{
    $conn = (new Database())->getConnect();
    if (!$conn instanceof PDO) {
        return null;
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Keep PHP and MySQL on the shop's clock so "today" means the same day in both.
    date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
    $conn->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
    return $conn;
}

function automation_low_stock_threshold(): int
{
    return (int)($_ENV['LOW_STOCK_THRESHOLD'] ?? 5);
}

function automation_peso(float $amount): string
{
    return '₱' . number_format($amount, 2);
}

function automation_last_run(PDO $conn, string $task): ?DateTime
{
    $stmt = $conn->prepare("SELECT last_run_at FROM automation_runs WHERE task = :task");
    $stmt->execute([':task' => $task]);
    $value = $stmt->fetchColumn();
    return $value ? new DateTime($value) : null;
}

function automation_mark_run(PDO $conn, string $task): void
{
    $stmt = $conn->prepare(
        "INSERT INTO automation_runs (task, last_run_at) VALUES (:task, :now)
         ON DUPLICATE KEY UPDATE last_run_at = VALUES(last_run_at)"
    );
    $stmt->execute([':task' => $task, ':now' => date('Y-m-d H:i:s')]);
}

function automation_report_is_due(PDO $conn): bool
{
    $reportTime = $_ENV['DAILY_REPORT_TIME'] ?? '21:00';
    if (date('H:i') < $reportTime) {
        return false;
    }
    $last = automation_last_run($conn, 'daily_report');
    return $last === null || $last->format('Y-m-d') !== date('Y-m-d');
}

/**
 * Run every task that is due. Safe to call often: it does nothing until
 * SCHEDULER_INTERVAL_MINUTES have passed since the last run, and a MySQL lock
 * stops two requests from running it at the same time.
 */
function automation_run_due(bool $force = false): array
{
    if (!notify_configured()) {
        return ['status' => 'disabled', 'reason' => 'Set DISCORD_WEBHOOK_URL, or TELEGRAM_BOT_TOKEN and TELEGRAM_CHAT_ID, in .env'];
    }
    $conn = automation_connect();
    if ($conn === null) {
        return ['status' => 'error', 'reason' => 'Database connection failed'];
    }

    if ((int)$conn->query("SELECT GET_LOCK('aar9speed_automation', 0)")->fetchColumn() !== 1) {
        return ['status' => 'skipped', 'reason' => 'Another run is in progress'];
    }

    try {
        $interval = max(1, (int)($_ENV['SCHEDULER_INTERVAL_MINUTES'] ?? 5));
        $last = automation_last_run($conn, 'scheduler');
        if (!$force && $last !== null && (time() - $last->getTimestamp()) < $interval * 60) {
            return ['status' => 'skipped', 'reason' => 'Not due yet'];
        }
        automation_mark_run($conn, 'scheduler');

        $result = ['status' => 'ok', 'low_stock_alerted' => automation_check_low_stock($conn)];

        if (automation_report_is_due($conn)) {
            $result['daily_report_sent'] = automation_send_daily_report($conn);
        }
        return $result;
    } finally {
        $conn->query("SELECT RELEASE_LOCK('aar9speed_automation')");
    }
}

/**
 * Alert once when an item drops to the threshold, and once more if it then runs out.
 * Restocking above the threshold clears the item so it can alert again later.
 * Returns the number of items included in the alert.
 */
function automation_check_low_stock(PDO $conn): int
{
    $threshold = automation_low_stock_threshold();

    $conn->prepare(
        "DELETE sa FROM stock_alerts sa JOIN item i ON i.ITEM_ID = sa.item_id
         WHERE i.ITEM_QUANTITY > :threshold"
    )->execute([':threshold' => $threshold]);

    $stmt = $conn->prepare(
        "SELECT i.ITEM_ID, i.ITEM_NAME, i.ITEM_CODE, i.ITEM_QUANTITY, sa.alerted_qty
         FROM item i LEFT JOIN stock_alerts sa ON sa.item_id = i.ITEM_ID
         WHERE i.ITEM_QUANTITY <= :threshold
           AND (sa.item_id IS NULL OR (i.ITEM_QUANTITY <= 0 AND sa.alerted_qty > 0))
         ORDER BY i.ITEM_QUANTITY ASC, i.ITEM_NAME ASC"
    );
    $stmt->execute([':threshold' => $threshold]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$items) {
        return 0;
    }

    $lines = [];
    foreach (array_slice($items, 0, 20) as $item) {
        $qty = (int)$item['ITEM_QUANTITY'];
        $lines[] = '• ' . notify_escape($item['ITEM_NAME']) . ' <code>#' . notify_escape((string)$item['ITEM_CODE']) . '</code> — '
            . ($qty <= 0 ? '<b>OUT OF STOCK</b>' : $qty . ' left');
    }
    if (count($items) > 20) {
        $lines[] = '…and ' . (count($items) - 20) . ' more';
    }

    $message = "<b>⚠️ Low stock alert</b>\n"
        . "These items are at or below {$threshold} units:\n\n"
        . implode("\n", $lines)
        . "\n\nRestock them from Purchase Orders.";

    if (!notify_send($message)) {
        return 0;
    }

    $save = $conn->prepare(
        "INSERT INTO stock_alerts (item_id, alerted_qty, alerted_at) VALUES (:id, :qty, :now)
         ON DUPLICATE KEY UPDATE alerted_qty = VALUES(alerted_qty), alerted_at = VALUES(alerted_at)"
    );
    foreach ($items as $item) {
        $save->execute([':id' => $item['ITEM_ID'], ':qty' => (int)$item['ITEM_QUANTITY'], ':now' => date('Y-m-d H:i:s')]);
    }
    return count($items);
}

function automation_sales_between(PDO $conn, string $from, string $to): array
{
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(TRANS_PRICE), 0) AS total,
                COUNT(DISTINCT trans_code)    AS transactions,
                COALESCE(SUM(TRANS_QUANTITY), 0) AS items
         FROM transactions
         WHERE TRANS_CREATED_AT >= :from AND TRANS_CREATED_AT < :to"
    );
    $stmt->execute([':from' => $from, ':to' => $to]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/** Build and send today's sales summary. Returns true when Telegram accepted it. */
function automation_send_daily_report(PDO $conn): bool
{
    $today     = date('Y-m-d');
    $tomorrow  = date('Y-m-d', strtotime('+1 day'));
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $sales = automation_sales_between($conn, $today, $tomorrow);
    $prev  = automation_sales_between($conn, $yesterday, $today);

    $total = (float)$sales['total'];
    $prevTotal = (float)$prev['total'];
    $trend = '';
    if ($prevTotal > 0) {
        $change = ($total - $prevTotal) / $prevTotal * 100;
        $trend = ' (' . ($change >= 0 ? '▲' : '▼') . ' ' . number_format(abs($change), 0) . '% vs yesterday)';
    }

    $best = $conn->prepare(
        "SELECT i.ITEM_NAME, SUM(t.TRANS_QUANTITY) AS qty, SUM(t.TRANS_PRICE) AS total
         FROM transactions t JOIN item i ON i.ITEM_ID = t.ITEM_ID
         WHERE t.TRANS_CREATED_AT >= :from AND t.TRANS_CREATED_AT < :to
         GROUP BY i.ITEM_ID, i.ITEM_NAME
         ORDER BY total DESC LIMIT 3"
    );
    $best->execute([':from' => $today, ':to' => $tomorrow]);
    $bestSellers = $best->fetchAll(PDO::FETCH_ASSOC);

    $refundLine = '';
    try {
        $ref = $conn->prepare(
            "SELECT COUNT(*) AS cnt, COALESCE(SUM(REFUND_QUANTITY), 0) AS qty
             FROM refunds WHERE REFUND_DATE >= :from AND REFUND_DATE < :to"
        );
        $ref->execute([':from' => $today, ':to' => $tomorrow]);
        $refunds = $ref->fetch(PDO::FETCH_ASSOC);
        $refundLine = 'Refunds: ' . (int)$refunds['cnt'] . ' (' . (int)$refunds['qty'] . " items)\n";
    } catch (PDOException $e) {
        // refunds table only exists after migrations/002_improvements.sql
    }

    $low = $conn->prepare(
        "SELECT ITEM_NAME, ITEM_QUANTITY FROM item
         WHERE ITEM_QUANTITY <= :threshold ORDER BY ITEM_QUANTITY ASC, ITEM_NAME ASC"
    );
    $low->execute([':threshold' => automation_low_stock_threshold()]);
    $lowItems = $low->fetchAll(PDO::FETCH_ASSOC);

    $message = "<b>📊 AAR9SPEED daily report</b>\n" . date('D, j M Y') . "\n\n"
        . 'Sales: <b>' . automation_peso($total) . '</b>' . $trend . "\n"
        . 'Transactions: ' . (int)$sales['transactions'] . ' · Items sold: ' . (int)$sales['items'] . "\n"
        . $refundLine;

    if ($bestSellers) {
        $message .= "\n<b>Best sellers today</b>\n";
        foreach ($bestSellers as $n => $row) {
            $message .= ($n + 1) . '. ' . notify_escape($row['ITEM_NAME']) . ' — ' . (int)$row['qty'] . ' sold · ' . automation_peso((float)$row['total']) . "\n";
        }
    } else {
        $message .= "\nNo sales recorded today.\n";
    }

    if ($lowItems) {
        $message .= "\n<b>Needs restocking (" . count($lowItems) . ")</b>\n";
        foreach (array_slice($lowItems, 0, 10) as $row) {
            $qty = (int)$row['ITEM_QUANTITY'];
            $message .= '• ' . notify_escape($row['ITEM_NAME']) . ' — ' . ($qty <= 0 ? 'out of stock' : $qty . ' left') . "\n";
        }
        if (count($lowItems) > 10) {
            $message .= '…and ' . (count($lowItems) - 10) . " more\n";
        }
    }

    if (!notify_send($message)) {
        return false;
    }
    automation_mark_run($conn, 'daily_report');
    return true;
}
