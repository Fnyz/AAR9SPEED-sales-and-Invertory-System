<?php
$page = "Order";
include 'site/protected.php';
$adminGatePage = 'Securitylogin.php';
include 'site/require_admin.php';
require_once "./Model/Order.php";

// Accept ?id= links as well as the older POST "id" form.
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$po = $id > 0 ? (new Order())->purchaseDocument($id) : null;
$number = 'PO-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
$pageTitle = $po ? 'Purchase order ' . $number : 'Purchase order not found';
$hasCost = $po && $po['PORD_UNIT_COST'] !== null;

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
                    <?php if (!$po) : ?>
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-file-off fs-1"></i></div>
                            <p class="empty-title">Purchase order not found</p>
                            <p class="empty-subtitle text-secondary">It may have been removed. Open Purchase orders to see the current list.</p>
                            <div class="empty-action"><a href="Order.php" class="btn btn-primary">Go to purchase orders</a></div>
                        </div>
                    <?php else :
                        $by = trim($po['purchaser'], " -") ?: $po['STAFF_USER'];
                    ?>
                        <div class="print-doc">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3 d-print-none">
                                <a href="Order.php" class="btn btn-ghost-secondary"><i class="ti ti-arrow-left me-1"></i>Purchase orders</a>
                                <button type="button" class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer me-1"></i>Print</button>
                            </div>

                            <div class="card">
                                <div class="card-body p-4 p-md-5">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 fs-2">
                                                <span class="brand-mark" aria-hidden="true">A9</span>
                                                <span class="brand-name">AAR9<span>SPEED</span></span>
                                            </div>
                                            <div class="text-secondary small mt-1">Motorcycle parts &amp; service · Cebu City</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="h2 mb-0">Purchase order</div>
                                            <div class="code fs-3"><?php echo $e($number) ?></div>
                                            <span class="badge bg-green-lt mt-1">Received</span>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-4">
                                        <div class="col-sm-6">
                                            <div class="doc-label mb-1">Supplier</div>
                                            <div class="fw-semibold"><?php echo $e($po['SUPPLIER_COMPANY'] ?? '—') ?></div>
                                            <?php if (!empty($po['SUPPLIER_ADDRESS'])) : ?><div class="text-secondary small"><?php echo $e($po['SUPPLIER_ADDRESS']) ?></div><?php endif; ?>
                                            <?php if (!empty($po['SUPPLIER_PHONENUM'])) : ?><div class="text-secondary small num"><?php echo $e($po['SUPPLIER_PHONENUM']) ?></div><?php endif; ?>
                                        </div>
                                        <div class="col-sm-6 text-sm-end">
                                            <div class="doc-label mb-1">Date</div>
                                            <div class="num"><?php echo $po['PORD_DATE'] ? $e(date('j F Y', strtotime($po['PORD_DATE']))) : '—' ?></div>
                                            <div class="doc-label mt-2 mb-1">Ordered by</div>
                                            <div><?php echo $e($by) ?></div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-vcenter mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-end">Units</th>
                                                    <th class="text-end">Unit cost</th>
                                                    <th class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo $e($po['ITEM_NAME']) ?></div>
                                                        <div class="small text-secondary"><span class="code">#<?php echo $e($po['ITEM_CODE']) ?></span><?php echo $po['ITEM_DESC'] ? ' · ' . $e($po['ITEM_DESC']) : '' ?></div>
                                                    </td>
                                                    <td class="text-end num"><?php echo number_format((int)$po['PORD_QUANTITY']) ?></td>
                                                    <td class="text-end num"><?php echo $hasCost ? $e($peso($po['PORD_UNIT_COST'])) : '—' ?></td>
                                                    <td class="text-end num fw-semibold"><?php echo $hasCost ? $e($peso($po['TOTAL_COST'])) : '—' ?></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-end">Total</th>
                                                    <th class="text-end num fs-3"><?php echo $hasCost ? $e($peso($po['TOTAL_COST'])) : '—' ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <p class="small text-secondary mt-2 mb-5"><?php echo $hasCost ? 'Amounts are what was paid to the supplier.' : 'No cost was recorded for this order.' ?></p>

                                    <div class="row g-5">
                                        <div class="col-6"><div class="signature-line">Received by</div></div>
                                        <div class="col-6"><div class="signature-line">Delivered by (supplier)</div></div>
                                    </div>
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
