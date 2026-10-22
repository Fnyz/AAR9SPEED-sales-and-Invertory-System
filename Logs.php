<?php
$page = "logs";
$pageTitle = "Activity log";
include 'site/protected.php';
$adminGatePage = 'AdminUnlock.php?next=Logs.php';
include 'site/require_admin.php';
require_once './Model/Log.php';

$log = new Log();
$log->getConnect(); // loads .env
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');

$validDate = fn($v) => ($d = DateTime::createFromFormat('!Y-m-d', (string)$v)) && $d->format('Y-m-d') === $v ? $v : '';
$filters = [
    'user' => trim((string)($_GET['user'] ?? '')),
    'type' => array_key_exists($_GET['type'] ?? '', Log::TYPES) ? $_GET['type'] : '',
    'from' => $validDate($_GET['from'] ?? ''),
    'to'   => $validDate($_GET['to'] ?? ''),
    'q'    => trim((string)($_GET['q'] ?? '')),
];
$perPage = 50;
$pageNo = max(1, (int)($_GET['page'] ?? 1));
[$entries, $total] = $log->search($filters, $pageNo, $perPage);
$pages = max(1, (int)ceil($total / $perPage));
$users = $log->users();
$filtered = array_filter($filters) !== [];

// Plain words for each action; anything else is shown with underscores as spaces.
$labels = [
    'login' => ['Signed in', 'ti-login', 'green'],
    'login_failed' => ['Wrong password at sign-in', 'ti-alert-triangle', 'red'],
    'logout' => ['Signed out', 'ti-logout', 'secondary'],
    'logout_idle' => ['Signed out after inactivity', 'ti-clock', 'secondary'],
    'admin_unlock' => ['Entered the admin password', 'ti-lock-open', 'blue'],
    'admin_unlock_failed' => ['Wrong admin password', 'ti-lock-exclamation', 'red'],
    'sale' => ['Made a sale', 'ti-shopping-cart', 'green'],
    'refund_process' => ['Recorded a refund', 'ti-receipt-refund', 'orange'],
    'report_export' => ['Downloaded a sales report', 'ti-file-spreadsheet', 'blue'],
    'product_insert' => ['Added a product', 'ti-package', 'blue'],
    'product_update' => ['Edited a product', 'ti-pencil', 'secondary'],
    'product_restock' => ['Restocked', 'ti-truck-delivery', 'blue'],
    'price_change' => ['Changed a price', 'ti-currency-peso', 'orange'],
    'cost_change' => ['Changed a cost', 'ti-currency-peso', 'orange'],
    'order_restock' => ['Purchase order: restock', 'ti-truck-delivery', 'blue'],
    'order_activate' => ['Purchase order: activated product', 'ti-truck-delivery', 'blue'],
    'order_reactivate' => ['Purchase order: back in stock', 'ti-truck-delivery', 'blue'],
    'profile_update' => ['Updated own account', 'ti-user-edit', 'secondary'],
];
$describe = function (string $action) use ($labels): array {
    if (isset($labels[$action])) {
        return $labels[$action];
    }
    [$thing, $verb] = array_pad(explode('_', $action, 2), 2, '');
    $verbs = ['insert' => 'Added', 'update' => 'Edited', 'delete' => 'Deleted'];
    if (isset($verbs[$verb])) {
        return [$verbs[$verb] . ' a ' . $thing, $verb === 'delete' ? 'ti-trash' : ($verb === 'insert' ? 'ti-plus' : 'ti-pencil'), $verb === 'delete' ? 'red' : 'secondary'];
    }
    return [ucfirst(str_replace('_', ' ', $action)), 'ti-point', 'secondary'];
};

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$url = fn(array $change) => 'Logs.php?' . http_build_query(array_filter(array_merge($filters, ['page' => $pageNo], $change), fn($v) => $v !== '' && $v !== 1));
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'site/layout/head.php' ?>
</head>

<body>
    <div class="page">
        <?php include 'site/layout/navbar.php' ?>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="page-pretitle">Admin only</div>
                    <h1 class="page-title">Activity log</h1>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="card">
                        <div class="card-body border-bottom">
                            <form class="row g-2 align-items-end" method="get" action="Logs.php">
                                <div class="col-sm-6 col-lg-2">
                                    <label class="form-label" for="log-user">Staff</label>
                                    <select class="form-select" id="log-user" name="user">
                                        <option value="">Everyone</option>
                                        <?php foreach ($users as $u) : ?>
                                            <option value="<?php echo $e($u) ?>"<?php echo $u === $filters['user'] ? ' selected' : '' ?>><?php echo $e($u) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <label class="form-label" for="log-type">Type</label>
                                    <select class="form-select" id="log-type" name="type">
                                        <option value="">Everything</option>
                                        <?php foreach (Log::TYPES as $key => [$label]) : ?>
                                            <option value="<?php echo $e($key) ?>"<?php echo $key === $filters['type'] ? ' selected' : '' ?>><?php echo $e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-lg-2">
                                    <label class="form-label" for="log-from">From</label>
                                    <input type="date" class="form-control" id="log-from" name="from" value="<?php echo $e($filters['from']) ?>">
                                </div>
                                <div class="col-6 col-lg-2">
                                    <label class="form-label" for="log-to">To</label>
                                    <input type="date" class="form-control" id="log-to" name="to" value="<?php echo $e($filters['to']) ?>">
                                </div>
                                <div class="col-sm-8 col-lg-2">
                                    <label class="form-label" for="log-q">Details contain</label>
                                    <input type="search" class="form-control" id="log-q" name="q" value="<?php echo $e($filters['q']) ?>" placeholder="e.g. invoice code">
                                </div>
                                <div class="col-sm-4 col-lg-1 d-flex gap-1">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </form>
                            <?php if ($filtered) : ?>
                                <div class="mt-2 small"><a href="Logs.php">Clear filters</a></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($entries) : ?>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>When</th>
                                            <th>Staff</th>
                                            <th>What happened</th>
                                            <th>Details</th>
                                            <th>IP address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($entries as $row) : [$label, $icon, $tone] = $describe((string)$row['action']); ?>
                                            <tr>
                                                <td class="text-secondary num text-nowrap" title="<?php echo $e($row['created_at']) ?>"><?php echo $e(date('j M Y, g:i A', strtotime($row['created_at']))) ?></td>
                                                <td><?php echo $e($row['staff_user']) ?></td>
                                                <td class="text-nowrap"><span class="text-<?php echo $tone ?> me-1"><i class="ti <?php echo $icon ?>"></i></span><?php echo $e($label) ?></td>
                                                <td class="text-secondary text-break" style="max-width: 24rem"><?php echo $row['target'] !== '' ? $e($row['target']) : '—' ?></td>
                                                <td class="text-secondary code"><?php echo $e($row['ip_address'] ?: '—') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer d-flex flex-wrap align-items-center gap-2">
                                <span class="text-secondary small me-auto">
                                    <?php echo number_format(($pageNo - 1) * $perPage + 1) ?>–<?php echo number_format(min($total, $pageNo * $perPage)) ?> of <?php echo number_format($total) ?> entries
                                </span>
                                <?php if ($pages > 1) : ?>
                                    <a class="btn btn-sm<?php echo $pageNo <= 1 ? ' disabled' : '' ?>" href="<?php echo $e($url(['page' => $pageNo - 1])) ?>"<?php echo $pageNo <= 1 ? ' aria-disabled="true" tabindex="-1"' : '' ?>><i class="ti ti-chevron-left"></i> Newer</a>
                                    <span class="small text-secondary">Page <?php echo $pageNo ?> of <?php echo $pages ?></span>
                                    <a class="btn btn-sm<?php echo $pageNo >= $pages ? ' disabled' : '' ?>" href="<?php echo $e($url(['page' => $pageNo + 1])) ?>"<?php echo $pageNo >= $pages ? ' aria-disabled="true" tabindex="-1"' : '' ?>>Older <i class="ti ti-chevron-right"></i></a>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <div class="card-body text-center text-secondary py-5"><?php echo $filtered ? 'No activity matches these filters.' : 'Nothing has been recorded yet.' ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'site/layout/scripts.php' ?>
</body>

</html>
