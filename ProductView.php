<?php
$page = "product";
include 'site/protected.php';
require_once "./Model/Product.php";

// Accept ?code= links as well as the older POST "id" form (the product code).
$code = trim((string)($_GET['code'] ?? $_POST['id'] ?? ''));
$prod = new Product();
$item = $code !== '' ? $prod->detailsByCode($code) : null;
$pageTitle = $item ? $item['ITEM_NAME'] : 'Product not found';

if ($item) {
    $itemId     = (int)$item['ITEM_ID'];
    $stats      = $prod->salesStats($itemId);
    $sales      = $prod->recentSales($itemId);
    $deliveries = $prod->purchaseHistory($itemId);
}
require_once './site/stock.php';
$isAdmin = is_admin();
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
            <?php if (!$item) : ?>
                <div class="page-body">
                    <div class="container-xl">
                        <div class="empty">
                            <div class="empty-icon"><i class="ti ti-package-off fs-1"></i></div>
                            <p class="empty-title">No product found<?php echo $code !== '' ? ' with code ' . $e($code) : '' ?></p>
                            <p class="empty-subtitle text-secondary">It may have been removed. Find it on the Products page.</p>
                            <div class="empty-action"><a href="Product.php" class="btn btn-primary">Go to products</a></div>
                        </div>
                    </div>
                </div>
            <?php else :
                $qty = (int)$item['ITEM_QUANTITY'];
                $status = strtolower((string)$item['ITEM_STATUS']);
                if ($qty >= 999999) {
                    $chip = ['bg-secondary-lt', 'Service'];
                } elseif ($qty <= 1) {
                    $chip = ['bg-red-lt', $qty <= 0 ? 'Out of stock' : '1 left'];
                } elseif ($qty <= reorder_level($item)) {
                    $chip = ['bg-yellow-lt', $qty . ' left'];
                } else {
                    $chip = ['bg-green-lt', number_format($qty) . ' in stock'];
                }
                $statusBadge = ['active' => ['bg-green-lt', 'Active'], 'pending' => ['bg-yellow-lt', 'Pending'], 'inactive' => ['bg-red-lt', 'Out of stock']][$status] ?? ['bg-secondary-lt', ucfirst($status)];
                $deliveryMode = ['pending' => 'activate', 'inactive' => 'reactivate'][$status] ?? null;
            ?>
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <div class="page-pretitle"><a href="Product.php" class="text-reset">Products</a> · <span class="code">#<?php echo $e($item['ITEM_CODE']) ?></span></div>
                                <h1 class="page-title d-flex align-items-center gap-2">
                                    <?php echo $e($item['ITEM_NAME']) ?>
                                    <span class="badge <?php echo $statusBadge[0] ?>"><?php echo $e($statusBadge[1]) ?></span>
                                </h1>
                            </div>
                            <div class="col-auto ms-auto d-flex gap-2">
                                <a href="Product.php" class="btn btn-ghost-secondary"><i class="ti ti-arrow-left me-1"></i>All products</a>
                                <?php if ($deliveryMode) : ?>
                                    <a href="Order.php?item=<?php echo $itemId ?>&amp;mode=<?php echo $deliveryMode ?>" class="btn btn-primary"><i class="ti ti-truck-delivery me-1"></i>Record delivery</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-body">
                    <div class="container-xl">
                        <div class="row row-cards">
                            <div class="col-lg-4">
                                <div class="card">
                                    <?php if ($item['ITEM_IMAGE']) : ?>
                                        <div class="img-responsive img-responsive-4x3 card-img-top" style="background-image: url('<?php echo $e($item['ITEM_IMAGE']) ?>'); background-size: contain; background-repeat: no-repeat; background-color: #ffffff" role="img" aria-label="<?php echo $e($item['ITEM_NAME']) ?>"></div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                                            <span class="h2 mb-0 num"><?php echo $e($peso($item['ITEM_PRICE'])) ?></span>
                                            <span class="badge <?php echo $chip[0] ?> stock-chip"><?php echo $e($chip[1]) ?></span>
                                        </div>
                                        <?php if ($item['ITEM_DESC']) : ?>
                                            <p class="text-secondary"><?php echo $e($item['ITEM_DESC']) ?></p>
                                        <?php endif; ?>
                                        <dl class="row mb-0 small">
                                            <dt class="col-5 fw-normal text-secondary">Category</dt><dd class="col-7"><?php echo $e($item['CATEGORY_NAME'] ?? '—') ?></dd>
                                            <dt class="col-5 fw-normal text-secondary">Supplier</dt><dd class="col-7"><?php echo $e($item['SUPPLIER_COMPANY'] ?? '—') ?><?php echo !empty($item['SUPPLIER_PHONENUM']) ? '<br><span class="text-secondary num">' . $e($item['SUPPLIER_PHONENUM']) . '</span>' : '' ?></dd>
                                            <?php if ($isAdmin) : ?>
                                                <dt class="col-5 fw-normal text-secondary">Cost</dt>
                                                <dd class="col-7 num"><?php
                                                    if ($item['ITEM_COST'] === null) {
                                                        echo 'Not set';
                                                    } else {
                                                        echo $e($peso($item['ITEM_COST']));
                                                        if ((float)$item['ITEM_PRICE'] > 0) {
                                                            echo ' <span class="text-secondary">· ' . (int)round(((float)$item['ITEM_PRICE'] - (float)$item['ITEM_COST']) / (float)$item['ITEM_PRICE'] * 100) . '% margin</span>';
                                                        }
                                                    }
                                                ?></dd>
                                            <?php endif; ?>
                                            <?php if ($qty < 999999) : ?>
                                                <dt class="col-5 fw-normal text-secondary">Reorder at</dt>
                                                <dd class="col-7 num"><?php echo reorder_level($item) ?><?php echo $item['ITEM_REORDER_LEVEL'] === null ? ' <span class="text-secondary">(default)</span>' : '' ?></dd>
                                            <?php endif; ?>
                                            <dt class="col-5 fw-normal text-secondary">Added</dt><dd class="col-7 num"><?php echo $e(date('j M Y', strtotime($item['ITEM_CREATED_AT']))) ?></dd>
                                            <dt class="col-5 fw-normal text-secondary">Last updated</dt><dd class="col-7 num mb-0"><?php echo $e(date('j M Y', strtotime($item['ITEM_UPDATED_AT']))) ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="row row-cards">
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="card card-sm"><div class="card-body">
                                            <div class="subheader">Sold, 30 days</div>
                                            <div class="h2 mb-0 num"><?php echo number_format((int)$stats['units_30']) ?></div>
                                        </div></div>
                                    </div>
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="card card-sm"><div class="card-body">
                                            <div class="subheader">Revenue, 30 days</div>
                                            <div class="h2 mb-0 num"><?php echo $e($peso($stats['revenue_30'])) ?></div>
                                        </div></div>
                                    </div>
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="card card-sm"><div class="card-body">
                                            <div class="subheader">Sold, all time</div>
                                            <div class="h2 mb-0 num"><?php echo number_format((int)$stats['units']) ?></div>
                                        </div></div>
                                    </div>
                                    <div class="col-sm-6 col-xl-3">
                                        <div class="card card-sm"><div class="card-body">
                                            <div class="subheader">Last sold</div>
                                            <div class="h2 mb-0 num"><?php echo $stats['last_sold'] ? $e(date('j M Y', strtotime($stats['last_sold']))) : 'Never' ?></div>
                                        </div></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header"><h2 class="card-title">Recent sales</h2></div>
                                            <?php if ($sales) : ?>
                                                <div class="table-responsive">
                                                    <table class="table card-table table-vcenter">
                                                        <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th class="text-end">Units</th><th class="text-end">Amount</th></tr></thead>
                                                        <tbody>
                                                            <?php foreach ($sales as $s) :
                                                                $customer = in_array(strtoupper(trim($s['customer'])), ['', 'WALK IN'], true) ? 'Walk-in' : $s['customer'];
                                                            ?>
                                                                <tr>
                                                                    <td><a href="transaction.php?code=<?php echo urlencode($s['trans_code']) ?>" class="code"><?php echo $e($s['trans_code']) ?></a></td>
                                                                    <td class="text-secondary num"><?php echo $e(date('j M Y', strtotime($s['TRANS_CREATED_AT']))) ?></td>
                                                                    <td><?php echo $e($customer) ?></td>
                                                                    <td class="text-end num"><?php echo (int)$s['TRANS_QUANTITY'] ?></td>
                                                                    <td class="text-end num fw-semibold"><?php echo $e($peso($s['TRANS_PRICE'])) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else : ?>
                                                <div class="card-body text-secondary">No sales yet.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header"><h2 class="card-title">Deliveries</h2></div>
                                            <?php if ($deliveries) : ?>
                                                <div class="table-responsive">
                                                    <table class="table card-table table-vcenter">
                                                        <thead><tr><th>Order</th><th>Date</th><th class="text-end">Units</th><?php if ($isAdmin) : ?><th class="text-end">Unit cost</th><?php endif; ?><th>Recorded by</th></tr></thead>
                                                        <tbody>
                                                            <?php foreach ($deliveries as $d) : $number = 'PO-' . str_pad((string)$d['PORD_ID'], 5, '0', STR_PAD_LEFT); ?>
                                                                <tr>
                                                                    <td><a href="print_order.php?id=<?php echo (int)$d['PORD_ID'] ?>" class="code"><?php echo $e($number) ?></a></td>
                                                                    <td class="text-secondary num"><?php echo $d['PORD_DATE'] ? $e(date('j M Y', strtotime($d['PORD_DATE']))) : '—' ?></td>
                                                                    <td class="text-end num"><?php echo number_format((int)$d['PORD_QUANTITY']) ?></td>
                                                                    <?php if ($isAdmin) : ?><td class="text-end num"><?php echo $d['PORD_UNIT_COST'] === null ? '—' : $e($peso($d['PORD_UNIT_COST'])) ?></td><?php endif; ?>
                                                                    <td><?php echo $e($d['STAFF_USER'] ?? '—') ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else : ?>
                                                <div class="card-body text-secondary">No deliveries recorded yet.</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'site/layout/scripts.php' ?>
</body>

</html>
