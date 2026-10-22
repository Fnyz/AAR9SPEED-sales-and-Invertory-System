<?php
$page = "reports";
$pageTitle = "Sales reports";
include 'site/protected.php';
$adminGatePage = 'AdminUnlock.php?next=Reports.php';
include 'site/require_admin.php';
require_once './Model/Report.php';
require_once './site/audit.php';

$report = new Report();
$report->getConnect(); // loads .env
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');

// Range from the query string (both dates inclusive); this month by default.
$parseDate = function ($value, string $fallback): string {
    $date = DateTime::createFromFormat('!Y-m-d', (string)$value);
    return $date && $date->format('Y-m-d') === $value ? $value : $fallback;
};
$from  = $parseDate($_GET['from'] ?? '', date('Y-m-01'));
$to    = $parseDate($_GET['to'] ?? '', date('Y-m-d'));
if ($from > $to) {
    [$from, $to] = [$to, $from];
}
$group = array_key_exists($_GET['group'] ?? '', Report::GROUPS) ? $_GET['group'] : 'day';
$toExclusive = date('Y-m-d', strtotime($to . ' +1 day'));

$summary = $report->summary($from, $toExclusive);
$rows    = $report->grouped($group, $from, $toExclusive);

// CSV download (opens in Excel). Text cells that start like a formula are
// prefixed with ' so a product name can't run as a spreadsheet formula.
if (($_GET['export'] ?? '') === 'csv') {
    $cell = fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    $filename = 'aar9speed-sales-by-' . $group . '-' . $from . '-to-' . $to . '.csv';
    log_action('report_export', $group . ' ' . $from . ' to ' . $to);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 marker so Excel shows names correctly
    $put = fn(array $fields) => fputcsv($out, $fields, ',', '"', '');
    $put([Report::GROUPS[$group], $group === 'day' ? '' : ($group === 'product' ? 'Code' : 'Name'), 'Invoices', 'Units', 'Revenue', 'Cost', 'Profit', 'Margin %']);
    foreach ($rows as $r) {
        $put([$cell((string)$r['label']), $cell((string)$r['detail']), $r['invoices'], $r['units'],
            number_format($r['revenue'], 2, '.', ''), number_format($r['cost'], 2, '.', ''),
            $r['profit'] === null ? '' : number_format($r['profit'], 2, '.', ''), $r['margin'] ?? '']);
    }
    $put(['Total', '', $summary['invoices'], $summary['units'], number_format($summary['revenue'], 2, '.', ''),
        number_format($summary['cost'], 2, '.', ''), $summary['profit'] === null ? '' : number_format($summary['profit'], 2, '.', ''), $summary['margin'] ?? '']);
    $put(['Refunds', '', $summary['refund_count'], $summary['refund_units'], number_format(-(float)$summary['refund_value'], 2, '.', '')]);
    fclose($out);
    exit;
}

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);
$url = fn(array $change) => 'Reports.php?' . http_build_query(array_merge(['from' => $from, 'to' => $to, 'group' => $group], $change));

$today = date('Y-m-d');
$presets = [
    'Today'        => [$today, $today],
    'Last 7 days'  => [date('Y-m-d', strtotime('-6 days')), $today],
    'This month'   => [date('Y-m-01'), $today],
    'Last month'   => [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
    'This year'    => [date('Y-01-01'), $today],
];
$rangeText = $from === $to ? date('j M Y', strtotime($from)) : date('j M Y', strtotime($from)) . ' – ' . date('j M Y', strtotime($to));
$netSales = $summary['revenue'] - (float)$summary['refund_value'];
// Share of revenue that has a cost, e.g. "42%" or "under 1%".
$shareText = $summary['costed_share'] > 0 && $summary['costed_share'] < 1 ? 'under 1%' : (int)round($summary['costed_share']) . '%';
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
                            <div class="page-pretitle">Sales · Admin only</div>
                            <h1 class="page-title">Sales reports</h1>
                        </div>
                        <div class="col-auto ms-auto d-flex gap-2">
                            <a href="<?php echo $e($url(['export' => 'csv'])) ?>" class="btn btn-primary"><i class="ti ti-file-spreadsheet me-2"></i>Download CSV</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="card mb-3">
                        <div class="card-body">
                            <form class="row g-2 align-items-end" method="get" action="Reports.php">
                                <input type="hidden" name="group" value="<?php echo $e($group) ?>">
                                <div class="col-6 col-md-auto">
                                    <label class="form-label" for="report-from">From</label>
                                    <input type="date" class="form-control" id="report-from" name="from" value="<?php echo $e($from) ?>" max="<?php echo $e($today) ?>" required>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <label class="form-label" for="report-to">To</label>
                                    <input type="date" class="form-control" id="report-to" name="to" value="<?php echo $e($to) ?>" max="<?php echo $e($today) ?>" required>
                                </div>
                                <div class="col-12 col-md-auto">
                                    <button type="submit" class="btn w-100">Show</button>
                                </div>
                                <div class="col-12 col-md d-flex flex-wrap gap-1 justify-content-md-end">
                                    <?php foreach ($presets as $label => [$pFrom, $pTo]) : $active = $pFrom === $from && $pTo === $to; ?>
                                        <a href="<?php echo $e($url(['from' => $pFrom, 'to' => $pTo])) ?>" class="btn btn-pill btn-sm<?php echo $active ? ' btn-primary' : '' ?>"<?php echo $active ? ' aria-current="true"' : '' ?>><?php echo $e($label) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row row-cards mb-3">
                        <div class="col-sm-6 col-xl-3">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Net sales</div>
                                <div class="h2 mb-0 num"><?php echo $e($peso($netSales)) ?></div>
                                <div class="small text-secondary"><?php echo $e($rangeText) ?></div>
                            </div></div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Gross sales</div>
                                <div class="h2 mb-0 num"><?php echo $e($peso($summary['revenue'])) ?></div>
                                <div class="small text-secondary"><?php echo number_format($summary['invoices']) ?> invoices · <?php echo number_format($summary['units']) ?> items</div>
                            </div></div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Profit</div>
                                <?php if ($summary['profit'] !== null) : ?>
                                    <div class="h2 mb-0 num<?php echo $summary['profit'] < 0 ? ' text-danger' : '' ?>"><?php echo $e($peso($summary['profit'])) ?></div>
                                    <div class="small text-secondary"><?php echo $e($summary['margin']) ?>% margin<?php echo $summary['costed_share'] < 99.5 ? ' · on ' . $shareText . ' of sales' : '' ?></div>
                                <?php else : ?>
                                    <div class="h2 mb-0 text-secondary">—</div>
                                    <div class="small text-secondary">No sales with a cost yet</div>
                                <?php endif; ?>
                            </div></div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Refunds</div>
                                <div class="h2 mb-0 num"><?php echo $e($peso($summary['refund_value'])) ?></div>
                                <div class="small text-secondary"><?php echo (int)$summary['refund_count'] ?> refunds · <?php echo (int)$summary['refund_units'] ?> items</div>
                            </div></div>
                        </div>
                    </div>

                    <?php if ($summary['revenue'] > 0 && $summary['costed_share'] < 99.5) : ?>
                        <div class="alert alert-info" role="status">
                            Profit covers <?php echo $shareText ?> of these sales. The rest were sold before their product had a cost. Set costs on the Products page, or record them on purchase orders, and new sales will include profit.
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" aria-label="Group by">
                                <?php foreach (Report::GROUPS as $key => $label) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link<?php echo $key === $group ? ' active' : '' ?>" href="<?php echo $e($url(['group' => $key])) ?>"<?php echo $key === $group ? ' aria-current="page"' : '' ?>>By <?php echo strtolower($label) ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php if ($rows) : ?>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th><?php echo $e(Report::GROUPS[$group]) ?></th>
                                            <th class="text-end">Invoices</th>
                                            <th class="text-end">Units</th>
                                            <th class="text-end">Revenue</th>
                                            <th class="text-end">Cost</th>
                                            <th class="text-end">Profit</th>
                                            <th class="text-end">Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $r) : ?>
                                            <tr>
                                                <td>
                                                    <?php if ($group === 'day') : ?>
                                                        <span class="num"><?php echo $e(date('D, j M Y', strtotime($r['label']))) ?></span>
                                                    <?php else : ?>
                                                        <div class="fw-semibold"><?php echo $e($r['label']) ?></div>
                                                        <?php if ($r['detail'] !== '') : ?><div class="small text-secondary<?php echo $group === 'product' ? ' code' : '' ?>"><?php echo $group === 'product' ? '#' : '' ?><?php echo $e($r['detail']) ?></div><?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end num"><?php echo number_format($r['invoices']) ?></td>
                                                <td class="text-end num"><?php echo number_format($r['units']) ?></td>
                                                <td class="text-end num fw-semibold"><?php echo $e($peso($r['revenue'])) ?></td>
                                                <td class="text-end num text-secondary"><?php echo $r['cost'] > 0 ? $e($peso($r['cost'])) : '—' ?></td>
                                                <td class="text-end num<?php echo $r['profit'] !== null && $r['profit'] < 0 ? ' text-danger' : '' ?>"><?php echo $r['profit'] === null ? '<span class="text-secondary">—</span>' : $e($peso($r['profit'])) ?></td>
                                                <td class="text-end num text-secondary"><?php echo $r['margin'] === null ? '—' : $e($r['margin']) . '%' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td>Total</td>
                                            <td class="text-end num"><?php echo number_format($summary['invoices']) ?></td>
                                            <td class="text-end num"><?php echo number_format($summary['units']) ?></td>
                                            <td class="text-end num"><?php echo $e($peso($summary['revenue'])) ?></td>
                                            <td class="text-end num"><?php echo $summary['cost'] > 0 ? $e($peso($summary['cost'])) : '—' ?></td>
                                            <td class="text-end num"><?php echo $summary['profit'] === null ? '—' : $e($peso($summary['profit'])) ?></td>
                                            <td class="text-end num"><?php echo $summary['margin'] === null ? '—' : $e($summary['margin']) . '%' ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php if ($group === 'product') : ?>
                                <div class="card-footer text-secondary small">An invoice with several products counts once for each of them, so the invoice column adds up to more than the total.</div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="card-body text-center text-secondary py-5">No sales for <?php echo $e($rangeText) ?>.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'site/layout/scripts.php' ?>
</body>

</html>
