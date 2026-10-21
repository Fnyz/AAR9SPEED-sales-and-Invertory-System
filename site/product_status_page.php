<?php
// Shared list for pendingProduct.php and InactiveProduct.php. The caller sets:
// $statusKey ('pending' | 'inactive'), $pageTitle, $intro, $deliveryMode ('activate' | 'reactivate').
$page = "product";
include __DIR__ . '/protected.php';
require_once __DIR__ . '/../Model/Product.php';

$prod = new Product();
$rows = $prod->listByStatus($statusKey);
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
?>
<!doctype html>
<html lang="en">

<head>
    <?php include __DIR__ . '/layout/head.php' ?>
</head>

<body>
    <div class="page">
        <?php include __DIR__ . '/layout/navbar.php' ?>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="page-pretitle"><a href="Product.php" class="text-reset">Products</a></div>
                            <h1 class="page-title"><?php echo $e($pageTitle) ?></h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <a href="Product.php" class="btn btn-ghost-secondary"><i class="ti ti-arrow-left me-1"></i>All products</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-secondary mb-0"><?php echo $e($intro) ?></p>
                        </div>
                        <?php if ($rows) : ?>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Code</th>
                                            <th>Category</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">On hand</th>
                                            <th>Since</th>
                                            <th><span class="visually-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $r) :
                                            $words = preg_split('/\s+/', trim($r['ITEM_NAME']));
                                            $mark = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? substr($words[0] ?? '', 1), 0, 1));
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php if ($r['ITEM_IMAGE']) : ?>
                                                            <span class="avatar avatar-md rounded" style="background-image: url('<?php echo $e($r['ITEM_IMAGE']) ?>')" role="img" aria-label="<?php echo $e($r['ITEM_NAME']) ?>"></span>
                                                        <?php else : ?>
                                                            <span class="avatar avatar-md rounded bg-primary-lt text-primary fw-bold" aria-hidden="true"><?php echo $e($mark) ?></span>
                                                        <?php endif; ?>
                                                        <a href="ProductView.php?code=<?php echo urlencode($r['ITEM_CODE']) ?>" class="fw-semibold text-reset"><?php echo $e($r['ITEM_NAME']) ?></a>
                                                    </div>
                                                </td>
                                                <td class="code text-secondary"><?php echo $e($r['ITEM_CODE']) ?></td>
                                                <td><?php echo $e($r['CATEGORY_NAME'] ?? '—') ?></td>
                                                <td><?php echo $e($r['SUPPLIER_COMPANY'] ?? '—') ?></td>
                                                <td class="text-end num"><?php echo $e($peso($r['ITEM_PRICE'])) ?></td>
                                                <td class="text-end num"><?php echo number_format((int)$r['ITEM_QUANTITY']) ?></td>
                                                <td class="text-secondary num"><?php echo $e(date('j M Y', strtotime($r['ITEM_UPDATED_AT']))) ?></td>
                                                <td class="text-end text-nowrap">
                                                    <a href="Order.php?item=<?php echo (int)$r['ITEM_ID'] ?>&amp;mode=<?php echo $e($deliveryMode) ?>" class="btn btn-primary"><i class="ti ti-truck-delivery me-1"></i>Record delivery</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer small text-secondary">Recording a delivery needs the admin password.</div>
                        <?php else : ?>
                            <div class="empty">
                                <div class="empty-icon"><i class="ti ti-circle-check fs-1"></i></div>
                                <p class="empty-title">Nothing here</p>
                                <p class="empty-subtitle text-secondary"><?php echo $statusKey === 'pending' ? 'There are no pending products waiting for a first delivery.' : 'No products are out of stock.' ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/layout/scripts.php' ?>
</body>

</html>
