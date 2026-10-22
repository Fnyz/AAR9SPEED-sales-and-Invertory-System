<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}

include_once "../Model/Notification.php";

$notify = new Notification();

// A browser reports which outbox messages it posted to Discord.
if (isset($_POST['relay_done'])) {
    csrf_verify();
    require_once "../site/notify.php";
    (new Database())->getConnect(); // loads .env
    notify_mark_sent((string)($_POST['token'] ?? ''), explode(',', (string)($_POST['ids'] ?? '')));
    header('Content-Type: application/json');
    exit(json_encode(['ok' => true]));
}

if (isset($_POST['view'])) {
    csrf_verify();

    $depleted = '';
    if (count($notify->showdepleted()) > 0) {
        $r = $notify->showdepleted();

        foreach ($r as $val) {
            extract($val);

            $depleted .= '<li><a class="dropdown-item" href="Product.php"><strong>' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</strong>&nbsp;<span class="text-secondary">is low on stock</span></a></li>';
        }
    }

    $pending = '';
    if (count($notify->showPending()) > 0) {
        $s = $notify->showPending();

        foreach ($s as $val) {
            extract($val);

            $pending .= '<li><a class="dropdown-item" href="pendingProduct.php"><strong>' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</strong>&nbsp;<span class="text-secondary">is pending</span></a></li>';
        }
    }

    $inactive = '';
    if (count($notify->showInactive()) > 0) {
        $s = $notify->showInactive();

        foreach ($s as $val) {
            extract($val);

            $inactive .= '<li><a class="dropdown-item" href="InactiveProduct.php"><strong>' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</strong>&nbsp;<span class="text-secondary">is out of stock</span></a></li>';
        }
    }

    $count = $notify->count();

    $data = array(
        'depleted' => $depleted,
        'pending' => $pending,
        'inactive' => $inactive,
        'unseen' => $count
    );

    // NOTIFY_VIA_BROWSER: hand waiting Discord messages to this signed-in browser,
    // which posts them (site/layout/scripts.php). Only sent when there is something to post.
    require_once "../site/notify.php";
    $relay = notify_claim_for_browser();
    if ($relay['items']) {
        $data['relay'] = [
            'webhook' => $_ENV['DISCORD_WEBHOOK_URL'],
            'token'   => $relay['token'],
            'items'   => $relay['items'],
        ];
    }
    echo json_encode($data);

    // Fallback scheduler for hosts without cron: staff page loads keep automations
    // running. The response is sent first so the bell never waits on Telegram.
    session_write_close();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    try {
        require_once "../site/automation.php";
        automation_run_due();
    } catch (Throwable $e) {
        error_log('Automation run from notification poll failed: ' . $e->getMessage());
    }
}

?>
