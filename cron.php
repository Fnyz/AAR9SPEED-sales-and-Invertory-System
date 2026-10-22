<?php
// Scheduler endpoint for automations (low-stock alerts, daily sales report).
//
// Web:  cron.php?key=CRON_SECRET              run whatever is due
//       cron.php?key=CRON_SECRET&task=report  send today's report now
//       cron.php?key=CRON_SECRET&task=test    send a test Telegram message
// CLI:  php cron.php [run|report|test]
//
// Call it every 5–15 minutes from cron-job.org, a server cron, or
// .github/workflows/scheduler.yml.

require_once __DIR__ . '/site/automation.php';

header('Content-Type: application/json');

$isCli = PHP_SAPI === 'cli';
$task  = $isCli ? ($argv[1] ?? 'run') : ($_GET['task'] ?? 'run');

// Loads .env as a side effect, so CRON_SECRET is available below.
if (automation_connect() === null) {
    http_response_code(500);
    exit(json_encode(['status' => 'error', 'reason' => 'Database connection failed']));
}

if (!$isCli) {
    $secret = $_ENV['CRON_SECRET'] ?? '';
    if ($secret === '') {
        http_response_code(503);
        exit(json_encode(['status' => 'disabled', 'reason' => 'Set CRON_SECRET in .env to enable this endpoint']));
    }
    if (!hash_equals($secret, (string)($_GET['key'] ?? ''))) {
        http_response_code(403);
        exit(json_encode(['status' => 'forbidden']));
    }
}

try {
    switch ($task) {
        case 'run':
            $result = automation_run_due($isCli);
            break;
        case 'report':
            $result = ['status' => automation_send_daily_report(automation_connect()) ? 'sent' : 'failed'];
            break;
        case 'test':
            $sent = notify_send("<b>✅ AAR9SPEED automations are connected.</b>\nLow-stock alerts and the daily report will arrive here.");
            $result = ['status' => $sent ? (notify_via_browser() ? 'queued' : 'sent') : 'failed', 'channels' => notify_channels()];
            if ($sent && notify_via_browser()) {
                $result['reason'] = 'Queued for Discord. It is posted the next time a signed-in staff member opens or reloads a page.';
            }
            if (!$sent) {
                $result['reason'] = notify_channels()
                    ? 'The message was not accepted. See errors.'
                    : 'No alert channel is set in this server\'s .env (DISCORD_WEBHOOK_URL or TELEGRAM_*).';
                $result['errors'] = notify_errors();
            }
            break;
        default:
            http_response_code(400);
            $result = ['status' => 'error', 'reason' => 'Unknown task. Use run, report or test.'];
    }
} catch (Throwable $e) {
    error_log('cron.php failed: ' . $e->getMessage());
    http_response_code(500);
    $result = ['status' => 'error', 'reason' => 'Automation failed, check the server error log'];
}

echo json_encode($result);
if ($isCli) {
    echo PHP_EOL;
}
