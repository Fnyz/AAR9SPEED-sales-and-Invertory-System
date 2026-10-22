<?php
$page = "dashboard";
$pageTitle = "Dashboard";
include 'site/protected.php';
require_once './Model/Dashboard.php';
require_once './site/layout/chart.php';

$dash = new Dashboard();
$dash->getConnect(); // loads .env
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');

$today     = date('Y-m-d');
$tomorrow  = date('Y-m-d', strtotime('+1 day'));
$yesterday = date('Y-m-d', strtotime('-1 day'));

$salesToday     = $dash->salesBetween($today, $tomorrow);
$salesYesterday = $dash->salesBetween($yesterday, $today);
$allTime        = $dash->salesBetween('1970-01-02', $tomorrow);
$days7          = $dash->dailySales(7);
$days30         = $dash->dailySales(30);
$low            = $dash->lowStockItems();
$lowCounts      = $dash->lowStockCounts();
$pending        = (int)($dash->pending()[0]['pending'] ?? 0);
$recent         = $dash->recentTransactions(6);
$best           = $dash->bestSellersSince(date('Y-m-d', strtotime('-29 days')));
$automation     = $dash->automationStatus();
$notices        = $dash->showDeplated();

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);

$todayTotal = (float)$salesToday['total'];
$prevTotal  = (float)$salesYesterday['total'];
if ($prevTotal > 0) {
    $change = ($todayTotal - $prevTotal) / $prevTotal * 100;
    $trend = ['text' => ($change >= 0 ? '▲ ' : '▼ ') . number_format(abs($change), 0) . '% vs yesterday', 'class' => $change >= 0 ? 'text-green' : 'text-red'];
} else {
    $trend = ['text' => 'No sales yesterday', 'class' => 'text-secondary'];
}
$todayCount = (int)$salesToday['transactions'];
$avgSale = $todayCount > 0 ? $todayTotal / $todayCount : 0;

$stockChip = function (int $qty): array {
    if ($qty <= 1) {
        return ['bg-red-lt', $qty <= 0 ? 'Out of stock' : '1 left'];
    }
    return ['bg-yellow-lt', $qty . ' left'];
};

$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
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
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="page-pretitle">Overview · <?php echo $e(date('l, j F')) ?></div>
                            <h1 class="page-title"><?php echo $e($greeting . ', ' . $displayName) ?></h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <a href="invoice.php" class="btn"><i class="ti ti-file-invoice me-2"></i>Invoices</a>
                            <a href="Sale.php" class="btn btn-primary ms-1"><i class="ti ti-cash-register me-2"></i>New sale</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-deck row-cards">

                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="subheader">Sales today</div>
                                    <div class="d-flex align-items-baseline flex-wrap gap-2 mt-1">
                                        <div class="h1 mb-0 num"><?php echo $e($peso($todayTotal)) ?></div>
                                        <span class="small fw-semibold <?php echo $trend['class'] ?>"><?php echo $e($trend['text']) ?></span>
                                    </div>
                                    <div class="text-secondary small mt-2">Yesterday: <span class="num"><?php echo $e($peso($prevTotal)) ?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="subheader">Transactions today</div>
                                    <div class="h1 mb-0 mt-1 num"><?php echo $todayCount ?></div>
                                    <div class="text-secondary small mt-2"><?php echo $todayCount > 0 ? 'Average sale <span class="num">' . $e($peso($avgSale)) . '</span>' : 'No sales yet today' ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="subheader">Low stock</div>
                                    <div class="d-flex align-items-baseline flex-wrap gap-2 mt-1">
                                        <div class="h1 mb-0 num"><?php echo (int)$lowCounts['low'] ?> <?php echo (int)$lowCounts['low'] === 1 ? 'item' : 'items' ?></div>
                                        <?php if ((int)$lowCounts['critical'] > 0) : ?>
                                            <span class="small fw-semibold text-red"><?php echo (int)$lowCounts['critical'] ?> at 1 or 0</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-secondary small mt-2"><?php echo (int)$lowCounts['low'] ?> of <?php echo (int)$lowCounts['products'] ?> products at or below their reorder level</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="subheader">Pending items</div>
                                    <div class="h1 mb-0 mt-1 num"><?php echo $pending ?></div>
                                    <div class="text-secondary small mt-2"><a href="pendingProduct.php">View pending products</a></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title" id="sales-title">Sales · last 7 days</h2>
                                    <div class="card-actions">
                                        <div class="btn-group" role="group" aria-label="Chart range">
                                            <button type="button" class="btn btn-sm active" data-range="7" aria-pressed="true">7 days</button>
                                            <button type="button" class="btn btn-sm" data-range="30" aria-pressed="false">30 days</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div data-chart="7"><?php echo render_sales_chart($days7) ?></div>
                                    <div data-chart="30" hidden><?php echo render_sales_chart($days30) ?></div>
                                </div>
                                <div class="card-footer text-secondary small">
                                    All-time sales <span class="num fw-semibold text-body"><?php echo $e($peso($allTime['total'])) ?></span>
                                    from <span class="num"><?php echo (int)$allTime['transactions'] ?></span> transactions
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Needs restocking</h2>
                                    <div class="card-actions"><a href="Product.php">View all</a></div>
                                </div>
                                <?php if ($low) : ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($low as $item) : [$chipClass, $chipText] = $stockChip((int)$item['ITEM_QUANTITY']); ?>
                                            <div class="list-group-item">
                                                <div class="d-flex align-items-center justify-content-between gap-3">
                                                    <div class="text-truncate">
                                                        <div class="fw-medium text-truncate"><?php echo $e($item['ITEM_NAME']) ?></div>
                                                        <div class="code text-secondary">#<?php echo $e($item['ITEM_CODE']) ?><?php echo $item['SUPPLIER_COMPANY'] ? ' · ' . $e($item['SUPPLIER_COMPANY']) : '' ?> · reorder at <?php echo (int)$item['reorder_level'] ?></div>
                                                    </div>
                                                    <span class="badge <?php echo $chipClass ?> stock-chip"><?php echo $e($chipText) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="card-footer">
                                        <a href="Securitylogin.php" class="btn btn-outline-primary w-100">Create purchase orders</a>
                                    </div>
                                <?php else : ?>
                                    <div class="card-body text-secondary">Every product is above its reorder level.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Recent transactions</h2>
                                    <div class="card-actions"><a href="invoice.php">All invoices</a></div>
                                </div>
                                <?php if ($recent) : ?>
                                    <div class="table-responsive">
                                        <table class="table card-table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Invoice</th>
                                                    <th>Customer</th>
                                                    <th class="text-end">Items</th>
                                                    <th class="text-end">Total</th>
                                                    <th class="text-end">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent as $t) : ?>
                                                    <tr>
                                                        <td>
                                                            <a href="transaction.php?code=<?php echo urlencode($t['trans_code']) ?>" class="code"><?php echo $e($t['trans_code']) ?></a>
                                                        </td>
                                                        <td><?php echo $e(in_array(strtoupper(trim($t['customer'])), ['', 'WALK IN'], true) ? 'Walk-in' : $t['customer']) ?></td>
                                                        <td class="text-end num"><?php echo (int)$t['items'] ?></td>
                                                        <td class="text-end num fw-semibold"><?php echo $e($peso($t['total'])) ?></td>
                                                        <td class="text-end text-secondary num"><?php echo $e(date('j M, g:i A', strtotime($t['created_at']))) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else : ?>
                                    <div class="card-body text-secondary">No transactions recorded yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="row row-cards">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h2 class="card-title">Best sellers · 30 days</h2>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($best) : $top = max(1, (float)$best[0]['total']); ?>
                                                <div class="d-flex flex-column gap-3">
                                                    <?php foreach ($best as $b) : ?>
                                                        <div>
                                                            <div class="d-flex justify-content-between gap-2 mb-1">
                                                                <span class="fw-medium"><?php echo $e($b['ITEM_NAME']) ?></span>
                                                                <span class="text-secondary num"><?php echo $e($peso($b['total'])) ?></span>
                                                            </div>
                                                            <div class="progress progress-sm">
                                                                <div class="progress-bar bg-primary" style="width: <?php echo round((float)$b['total'] / $top * 100) ?>%" role="progressbar" aria-label="<?php echo $e($b['ITEM_NAME']) ?>, <?php echo (int)$b['qty'] ?> sold"></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else : ?>
                                                <span class="text-secondary">No sales in the last 30 days.</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h2 class="card-title">Automations</h2>
                                        </div>
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                                <span>Alerts</span>
                                                <?php if ($automation['channels']) : ?>
                                                    <span class="badge bg-green-lt"><?php echo $e(implode(' + ', $automation['channels'])) ?></span>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary-lt">Not set up</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                                <span>Daily sales report</span>
                                                <span class="text-secondary num"><?php echo $e(date('g:i A', strtotime($automation['report_time']))) ?></span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                                <span>Last low-stock alert</span>
                                                <span class="text-secondary num"><?php echo $automation['last_alert'] ? $e(date('j M, g:i A', strtotime($automation['last_alert']))) : 'None yet' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($notices) : ?>
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h2 class="card-title">Notices</h2>
                                            </div>
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($notices as $n) : ?>
                                                    <div class="list-group-item">
                                                        <div class="fw-semibold"><?php echo $e($n['subject']) ?></div>
                                                        <div class="text-secondary small"><?php echo $e($n['message']) ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl text-secondary small d-flex justify-content-between flex-wrap gap-2">
                    <span>AAR9SPEED Sales &amp; Inventory · Cebu City</span>
                    <span>Built with Tabler</span>
                </div>
            </footer>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        $('[data-range]').on('click', function () {
            var range = $(this).attr('data-range');
            $('[data-range]').removeClass('active').attr('aria-pressed', 'false');
            $(this).addClass('active').attr('aria-pressed', 'true');
            $('[data-chart]').prop('hidden', true);
            $('[data-chart="' + range + '"]').prop('hidden', false);
            $('#sales-title').text('Sales · last ' + range + ' days');
        });
    </script>
    <?php if (isset($_SESSION['success'])) : unset($_SESSION['success']); ?>
        <script>$(function () { showToast('Your account was updated.', 'success'); });</script>
    <?php endif; ?>
</body>

</html>
