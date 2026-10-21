<?php
$page = "invoice";
include 'site/protected.php';
require_once './Model/Invoice.php';

// Accept ?code= links as well as the older POST "id" form.
$code = trim((string)($_GET['code'] ?? $_POST['id'] ?? ''));
$receipt = $code !== '' ? (new Invoice())->receipt($code) : null;
$pageTitle = $receipt ? 'Receipt ' . $code : 'Receipt not found';

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);
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
            <div class="page-body">
                <div class="container-xl">
                    <?php if (!$receipt) : ?>
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-receipt-off fs-1"></i></div>
                            <p class="empty-title">No receipt found<?php echo $code !== '' ? ' for ' . $e($code) : '' ?></p>
                            <p class="empty-subtitle text-secondary">Check the invoice code, or find the sale on the Invoices page.</p>
                            <div class="empty-action"><a href="invoice.php" class="btn btn-primary">Go to invoices</a></div>
                        </div>
                    <?php else :
                        $h = $receipt['header'];
                        $customer = in_array(strtoupper(trim($h['customer'])), ['', 'WALK IN'], true) ? 'Walk-in' : $h['customer'];
                        $cashier = trim($h['staff'], " -") ?: $h['staff_user'];
                        $change = (float)$h['payment'] - (float)$h['total'];
                    ?>
                        <div class="receipt">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3 d-print-none">
                                <?php if (isset($_GET['new'])) : ?>
                                    <a href="Sale.php" class="btn btn-ghost-primary"><i class="ti ti-plus me-1"></i>Next sale</a>
                                <?php else : ?>
                                    <a href="invoice.php" class="btn btn-ghost-secondary"><i class="ti ti-arrow-left me-1"></i>Invoices</a>
                                <?php endif; ?>
                                <div class="d-flex gap-2">
                                    <?php if (!isset($_GET['new'])) : ?>
                                        <a href="Refund.php?code=<?php echo urlencode($h['trans_code']) ?>" class="btn">Refund</a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer me-1"></i>Print</button>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="d-inline-flex align-items-center gap-2 fs-2">
                                            <span class="brand-mark" aria-hidden="true">A9</span>
                                            <span class="brand-name">AAR9<span>SPEED</span></span>
                                        </div>
                                        <div class="text-secondary small mt-1">Motorcycle parts &amp; service · Cebu City</div>
                                        <div class="fw-semibold mt-2">Sales receipt</div>
                                    </div>

                                    <dl class="row small mb-2 g-1">
                                        <dt class="col-5 fw-normal text-secondary">Invoice</dt><dd class="col-7 text-end code mb-0"><?php echo $e($h['trans_code']) ?></dd>
                                        <dt class="col-5 fw-normal text-secondary">Date</dt><dd class="col-7 text-end num mb-0"><?php echo $e(date('j M Y, g:i A', strtotime($h['created_at']))) ?></dd>
                                        <dt class="col-5 fw-normal text-secondary">Customer</dt><dd class="col-7 text-end mb-0"><?php echo $e($customer) ?></dd>
                                        <dt class="col-5 fw-normal text-secondary">Cashier</dt><dd class="col-7 text-end mb-0"><?php echo $e($cashier) ?></dd>
                                    </dl>

                                    <div class="receipt-rule my-2"></div>
                                    <?php foreach ($receipt['lines'] as $line) : ?>
                                        <div class="py-1">
                                            <div class="d-flex justify-content-between gap-2">
                                                <span class="fw-medium"><?php echo $e($line['ITEM_NAME']) ?></span>
                                                <span class="num fw-semibold"><?php echo $e($peso($line['line_total'])) ?></span>
                                            </div>
                                            <div class="small text-secondary num"><?php echo (int)$line['qty'] ?> × <?php echo $e($peso($line['unit_price'])) ?> · #<?php echo $e($line['ITEM_CODE']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="receipt-rule my-2"></div>

                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <span class="fw-semibold">Total</span>
                                        <span class="fs-2 fw-bold num"><?php echo $e($peso($h['total'])) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-secondary"><span>Items</span><span class="num"><?php echo (int)$h['items'] ?></span></div>
                                    <?php if (strtoupper((string)$h['method']) === 'GCASH') : ?>
                                        <div class="d-flex justify-content-between small text-secondary"><span>Paid by GCash</span><span class="num"><?php echo $e($peso($h['payment'])) ?></span></div>
                                        <?php if (!empty($h['reference'])) : ?>
                                            <div class="d-flex justify-content-between small text-secondary"><span>Reference</span><span class="code"><?php echo $e($h['reference']) ?></span></div>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <div class="d-flex justify-content-between small text-secondary"><span>Cash</span><span class="num"><?php echo $e($peso($h['payment'])) ?></span></div>
                                        <div class="d-flex justify-content-between small"><span>Change</span><span class="num fw-semibold"><?php echo $e($peso(max(0, $change))) ?></span></div>
                                    <?php endif; ?>

                                    <?php if ($receipt['refunds']) : ?>
                                        <div class="receipt-rule my-2"></div>
                                        <div class="small fw-semibold mb-1">Refunds</div>
                                        <?php foreach ($receipt['refunds'] as $r) : ?>
                                            <div class="d-flex justify-content-between small text-secondary gap-2">
                                                <span><?php echo (int)$r['REFUND_QUANTITY'] ?> × <?php echo $e($r['ITEM_NAME']) ?><?php echo $r['REFUND_REASON'] ? ' · ' . $e($r['REFUND_REASON']) : '' ?></span>
                                                <span class="num text-nowrap"><?php echo $e(date('j M Y', strtotime($r['REFUND_DATE']))) ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <div class="receipt-rule my-3"></div>
                                    <div class="text-center small text-secondary">Thank you! Keep this receipt for returns and warranty.</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'site/layout/scripts.php' ?>
</body>

</html>
