<?php
$page = "invoice";
$pageTitle = "Invoices";
include 'site/protected.php';
require_once './Model/Invoice.php';

$invoiceModel = new Invoice();
$invoices = $invoiceModel->listInvoices();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);
$today = date('Y-m-d');
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
                            <div class="page-pretitle">Sales</div>
                            <h1 class="page-title">Invoices</h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <a href="Refund.php" class="btn"><i class="ti ti-receipt-refund me-2"></i>Refunds</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-cards mb-3">
                        <div class="col-sm-6">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader" id="sum-label">All invoices</div>
                                <div class="h2 mb-0 num" id="sum-count"><?php echo count($invoices) ?></div>
                            </div></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Sales total</div>
                                <div class="h2 mb-0 num" id="sum-total"><?php echo $e($peso(array_sum(array_column($invoices, 'total')))) ?></div>
                            </div></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex-wrap gap-2">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist" aria-label="Date range">
                                <?php foreach (['1' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days', 'all' => 'All'] as $days => $label) : ?>
                                    <li class="nav-item" role="presentation">
                                        <button type="button" class="nav-link<?php echo $days === 'all' ? ' active' : '' ?>" role="tab" aria-selected="<?php echo $days === 'all' ? 'true' : 'false' ?>" data-days="<?php echo $days ?>"><?php echo $label ?></button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-body border-bottom py-3">
                            <div class="input-icon" style="max-width: 24rem">
                                <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                                <label for="invoice-search" class="visually-hidden">Search invoices</label>
                                <input type="search" id="invoice-search" class="form-control" placeholder="Search invoice code or customer" autocomplete="off">
                            </div>
                        </div>
                        <?php if ($invoices) : ?>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Sold by</th>
                                            <th class="text-end">Items</th>
                                            <th class="text-end">Total</th>
                                            <th><span class="visually-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoice-rows">
                                        <?php foreach ($invoices as $inv) :
                                            $customer = in_array(strtoupper(trim($inv['customer'])), ['', 'WALK IN'], true) ? 'Walk-in' : $inv['customer'];
                                            $staff = trim($inv['staff'], " -") ?: ($inv['staff_user'] ?? '—');
                                            $date = substr((string)$inv['created_at'], 0, 10);
                                            $refunded = (int)$inv['refunded'];
                                        ?>
                                            <tr data-date="<?php echo $e($date) ?>" data-total="<?php echo (float)$inv['total'] ?>"
                                                data-search="<?php echo $e(strtolower($inv['trans_code'] . ' ' . $customer)) ?>">
                                                <td class="code"><?php echo $e($inv['trans_code']) ?></td>
                                                <td class="text-secondary num text-nowrap"><?php echo $e(date('j M Y, g:i A', strtotime($inv['created_at']))) ?></td>
                                                <td><?php echo $e($customer) ?></td>
                                                <td class="text-secondary"><?php echo $e($staff) ?></td>
                                                <td class="text-end num">
                                                    <?php echo (int)$inv['items'] ?>
                                                    <?php if ($refunded > 0) : ?>
                                                        <span class="badge bg-yellow-lt ms-1" title="<?php echo $refunded ?> refunded"><?php echo $refunded ?> refunded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end num fw-semibold"><?php echo $e($peso($inv['total'])) ?></td>
                                                <td class="text-end text-nowrap">
                                                    <a href="transaction.php?code=<?php echo urlencode($inv['trans_code']) ?>" class="btn btn-outline-secondary"><i class="ti ti-receipt me-1"></i>Receipt</a>
                                                    <a href="Refund.php?code=<?php echo urlencode($inv['trans_code']) ?>" class="btn btn-ghost-secondary ms-1">Refund</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-body text-center text-secondary" id="invoice-empty" hidden>No invoices match. Try another date range or search.</div>
                        <?php else : ?>
                            <div class="card-body text-secondary">No sales recorded yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        var TODAY = <?php echo json_encode($today) ?>;
        var rangeDays = 'all';
        var rangeLabels = { '1': 'Invoices today', '7': 'Invoices, last 7 days', '30': 'Invoices, last 30 days', 'all': 'All invoices' };

        function daysAgo(n) {
            var d = new Date(TODAY + 'T00:00:00');
            d.setDate(d.getDate() - (n - 1));
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function applyFilters() {
            var query = $.trim($('#invoice-search').val()).toLowerCase();
            var from = rangeDays === 'all' ? '' : daysAgo(Number(rangeDays));
            var count = 0, total = 0;
            $('#invoice-rows tr').each(function () {
                var $row = $(this);
                var match = (!from || $row.attr('data-date') >= from)
                    && (query === '' || $row.attr('data-search').indexOf(query) !== -1);
                $row.prop('hidden', !match);
                if (match) { count++; total += Number($row.attr('data-total')); }
            });
            $('#invoice-empty').prop('hidden', count > 0);
            $('#sum-label').text(rangeLabels[rangeDays]);
            $('#sum-count').text(count);
            $('#sum-total').text('₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        $('[data-days]').on('click', function () {
            rangeDays = String($(this).data('days'));
            $('[data-days]').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            applyFilters();
        });
        $('#invoice-search').on('input', applyFilters);
    </script>
</body>

</html>
