<?php
$page = "Order";
$pageTitle = "Purchase orders";
include 'site/protected.php';
$adminGatePage = 'Securitylogin.php';
include 'site/require_admin.php';
require_once "./Model/Order.php";

$order = new Order();
$orders = $order->listOrders();
$items = $order->orderableItems();
$suppliers = $order->getSupplier();

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
$monthStart = date('Y-m-01');
$thisMonth = array_filter($orders, fn($o) => substr((string)$o['PORD_DATE'], 0, 10) >= $monthStart);
$modeCounts = ['restock' => 0, 'activate' => 0, 'reactivate' => 0];
$modeForStatus = ['active' => 'restock', 'pending' => 'activate', 'inactive' => 'reactivate'];
foreach ($items as $it) {
    if (isset($modeForStatus[$it['status']])) {
        $modeCounts[$modeForStatus[$it['status']]]++;
    }
}
$itemsJson = json_encode(array_map(fn($it) => [
    'id' => (int)$it['ITEM_ID'], 'name' => $it['ITEM_NAME'], 'code' => $it['ITEM_CODE'], 'qty' => (int)$it['ITEM_QUANTITY'],
    'price' => (float)$it['ITEM_PRICE'], 'supplier' => (int)$it['SUPPLIER_ID'], 'status' => $it['status'],
], $items), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
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
                            <div class="page-pretitle">Inventory · Admin only</div>
                            <h1 class="page-title">Purchase orders</h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#order-modal">
                                <i class="ti ti-truck-delivery me-2"></i>New purchase order
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-cards mb-3">
                        <div class="col-sm-4">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Orders this month</div>
                                <div class="h2 mb-0 num"><?php echo count($thisMonth) ?></div>
                            </div></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Units received this month</div>
                                <div class="h2 mb-0 num"><?php echo number_format(array_sum(array_column($thisMonth, 'PORD_QUANTITY'))) ?></div>
                            </div></div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card card-sm"><div class="card-body">
                                <div class="subheader">Waiting for an order</div>
                                <div class="h2 mb-0 num"><?php echo $modeCounts['activate'] + $modeCounts['reactivate'] ?></div>
                                <div class="small text-secondary"><?php echo $modeCounts['activate'] ?> pending · <?php echo $modeCounts['reactivate'] ?> out of stock</div>
                            </div></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="input-icon w-100" style="max-width: 24rem">
                                <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                                <label for="order-search" class="visually-hidden">Search purchase orders</label>
                                <input type="search" id="order-search" class="form-control" placeholder="Search product, supplier or order number" autocomplete="off" data-table-search="#order-rows" data-empty="#order-empty">
                            </div>
                            <div class="card-actions text-secondary small"><?php echo count($orders) === 1 ? '1 order' : count($orders) . ' orders' ?></div>
                        </div>
                        <?php if ($orders) : ?>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Date</th>
                                            <th>Product</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Units</th>
                                            <th class="text-end">Value</th>
                                            <th>Ordered by</th>
                                            <th><span class="visually-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody id="order-rows">
                                        <?php foreach ($orders as $o) :
                                            $number = 'PO-' . str_pad((string)$o['PORD_ID'], 5, '0', STR_PAD_LEFT);
                                            $by = $o['purchaser'] !== '' && $o['purchaser'] !== '-' && $o['purchaser'] !== '- -' ? $o['purchaser'] : ($o['STAFF_USER'] ?? '—');
                                        ?>
                                            <tr data-id="<?php echo (int)$o['PORD_ID'] ?>" data-name="<?php echo $e($number) ?>"
                                                data-search="<?php echo $e(strtolower($number . ' ' . $o['ITEM_NAME'] . ' ' . $o['ITEM_CODE'] . ' ' . $o['SUPPLIER_COMPANY'])) ?>">
                                                <td class="code"><?php echo $e($number) ?></td>
                                                <td class="text-secondary num text-nowrap"><?php echo $o['PORD_DATE'] ? $e(date('j M Y', strtotime($o['PORD_DATE']))) : '—' ?></td>
                                                <td>
                                                    <div class="fw-semibold"><?php echo $e($o['ITEM_NAME']) ?></div>
                                                    <div class="code text-secondary">#<?php echo $e($o['ITEM_CODE']) ?></div>
                                                </td>
                                                <td><?php echo $e($o['SUPPLIER_COMPANY'] ?? '—') ?></td>
                                                <td class="text-end num"><?php echo number_format((int)$o['PORD_QUANTITY']) ?></td>
                                                <td class="text-end num fw-semibold"><?php echo $e($peso($o['TOTAL_PRICE'])) ?></td>
                                                <td><?php echo $e($by) ?></td>
                                                <td class="text-end text-nowrap">
                                                    <div class="row-actions">
                                                        <a href="print_order.php?id=<?php echo (int)$o['PORD_ID'] ?>" class="btn btn-icon btn-outline-secondary" aria-label="Print <?php echo $e($number) ?>" title="Print"><i class="ti ti-printer"></i></a>
                                                        <button type="button" class="btn btn-icon btn-outline-danger" data-row-ask aria-label="Remove <?php echo $e($number) ?>" title="Remove record"><i class="ti ti-trash"></i></button>
                                                    </div>
                                                    <form action="ajax/deleteOrder.php" method="post" class="row-confirm d-none align-items-center justify-content-end gap-2 m-0">
                                                        <?php echo csrf_field() ?>
                                                        <input type="hidden" name="id" value="<?php echo (int)$o['PORD_ID'] ?>">
                                                        <span class="small text-secondary text-wrap text-start">Remove this record? Stock is not changed.</span>
                                                        <button type="button" class="btn btn-sm" data-row-cancel>Keep</button>
                                                        <button type="submit" class="btn btn-sm btn-danger" name="save" value="1">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-body text-center text-secondary" id="order-empty" hidden>No purchase orders match your search.</div>
                        <?php else : ?>
                            <div class="card-body text-secondary">No purchase orders yet. Use <b>New purchase order</b> when stock arrives from a supplier.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="order-modal" tabindex="-1" aria-labelledby="order-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="order-form" novalidate>
                <div class="modal-header">
                    <h2 class="modal-title" id="order-title">New purchase order</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger form-error" role="alert" hidden></div>
                    <div class="mb-3">
                        <div class="form-label">What arrived?</div>
                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="mode" value="restock" class="form-selectgroup-input" checked>
                                <span class="form-selectgroup-label d-flex align-items-center gap-3 p-3 text-start">
                                    <span class="form-selectgroup-check"></span>
                                    <span><b>More stock</b> of an active product <span class="badge bg-secondary-lt ms-1"><?php echo $modeCounts['restock'] ?></span></span>
                                </span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="mode" value="activate" class="form-selectgroup-input">
                                <span class="form-selectgroup-label d-flex align-items-center gap-3 p-3 text-start">
                                    <span class="form-selectgroup-check"></span>
                                    <span><b>First delivery</b> of a pending product <span class="badge bg-secondary-lt ms-1"><?php echo $modeCounts['activate'] ?></span></span>
                                </span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="mode" value="reactivate" class="form-selectgroup-input">
                                <span class="form-selectgroup-label d-flex align-items-center gap-3 p-3 text-start">
                                    <span class="form-selectgroup-check"></span>
                                    <span><b>Restock</b> an out-of-stock product <span class="badge bg-secondary-lt ms-1"><?php echo $modeCounts['reactivate'] ?></span></span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label" for="order-supplier">Supplier</label>
                            <select class="form-select" id="order-supplier">
                                <option value="">All suppliers</option>
                                <?php foreach ($suppliers as $s) : ?>
                                    <option value="<?php echo (int)$s['SUPPLIER_ID'] ?>"><?php echo $e($s['SUPPLIER_COMPANY']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label required" for="order-item">Product</label>
                            <select class="form-select" id="order-item" required></select>
                        </div>
                        <div class="col-12" id="order-qty-group">
                            <label class="form-label required" for="order-qty" id="order-qty-label">Units received</label>
                            <input type="number" class="form-control" id="order-qty" min="1" step="1" inputmode="numeric">
                        </div>
                    </div>
                    <div class="mt-3 p-3 rounded bg-secondary-lt small" id="order-summary">Choose a product.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="order-save">Record order</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        var ITEMS = <?php echo $itemsJson ?>;
        var STATUS_FOR = { restock: 'active', activate: 'pending', reactivate: 'inactive' };
        var peso = function (n) { return '₱' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

        function currentMode() { return $('input[name="mode"]:checked').val(); }
        function selectedItem() {
            var id = Number($('#order-item').val());
            return ITEMS.filter(function (it) { return it.id === id; })[0] || null;
        }

        function fillProducts() {
            var mode = currentMode(), supplier = Number($('#order-supplier').val()) || null;
            var list = ITEMS.filter(function (it) {
                return it.status === STATUS_FOR[mode] && (!supplier || it.supplier === supplier);
            });
            var $select = $('#order-item').empty();
            if (!list.length) {
                $select.append($('<option value="">').text('No products to order here'));
            } else {
                $select.append($('<option value="">').text('Choose a product'));
                list.forEach(function (it) {
                    $select.append($('<option>').val(it.id).text(it.name + ' · #' + it.code + ' · ' + it.qty + ' on hand'));
                });
            }
            $('#order-qty-group').prop('hidden', mode === 'activate');
            $('#order-qty-label').text(mode === 'reactivate' ? 'New stock level' : 'Units received');
            updateSummary();
        }

        function updateSummary() {
            var mode = currentMode(), it = selectedItem(), qty = parseInt($('#order-qty').val(), 10);
            var text;
            if (!it) {
                text = 'Choose a product.';
            } else if (mode === 'activate') {
                text = it.name + ' becomes active with its current ' + it.qty + ' units, and appears in Products.';
            } else if (!(qty > 0)) {
                text = it.name + ' has ' + it.qty + ' on hand. Enter how many units arrived.';
            } else {
                var after = mode === 'restock' ? it.qty + qty : qty;
                text = 'Stock goes from ' + it.qty + ' to ' + after + '. Order value ' + peso(qty * it.price) + '.';
            }
            $('#order-summary').text(text);
        }

        $('input[name="mode"], #order-supplier').on('change', fillProducts);
        $('#order-item, #order-qty').on('change input', function () {
            $('#order-form .form-error').prop('hidden', true);
            updateSummary();
        });
        // Opened from Pending / Out of stock / product details: Order.php?item=ID&mode=activate|reactivate
        var PRESELECT = <?php echo json_encode(['item' => (int)($_GET['item'] ?? 0), 'mode' => in_array($_GET['mode'] ?? '', ['restock', 'activate', 'reactivate'], true) ? $_GET['mode'] : '']) ?>;

        $('#order-modal').on('show.bs.modal', function () {
            $('#order-form .form-error').prop('hidden', true);
            $('#order-qty').val('');
            if (PRESELECT && PRESELECT.item && PRESELECT.mode) {
                $('input[name="mode"][value="' + PRESELECT.mode + '"]').prop('checked', true);
                $('#order-supplier').val('');
                fillProducts();
                $('#order-item').val(String(PRESELECT.item));
                updateSummary();
                PRESELECT = null;
            } else {
                fillProducts();
            }
        }).on('shown.bs.modal', function () {
            $($('#order-item').val() ? '#order-qty' : '#order-item').filter(':visible').trigger('focus');
        });
        if (PRESELECT && PRESELECT.item && PRESELECT.mode) {
            $(function () { $('[data-bs-target="#order-modal"]').first().trigger('click'); });
        }

        $('#order-form').on('submit', function (event) {
            event.preventDefault();
            var mode = currentMode(), it = selectedItem(), qty = parseInt($('#order-qty').val(), 10);
            var $error = $('#order-form .form-error');
            if (!it) {
                $error.text('Choose a product.').prop('hidden', false);
                return;
            }
            if (mode !== 'activate' && !(qty > 0)) {
                $error.text('Enter a quantity of at least 1.').prop('hidden', false);
                $('#order-qty').trigger('focus');
                return;
            }
            $('#order-save').prop('disabled', true);
            $.ajax({
                url: 'ajax/orderOperation.php', method: 'POST', global: false,
                data: { action: 'createOrder', mode: mode, item_id: it.id, quantity: mode === 'activate' ? '' : qty }
            }).done(function () {
                reloadWith('Recorded the order for ' + it.name + '.');
            }).fail(function (xhr) {
                $error.text(errorText(xhr)).prop('hidden', false);
                $('#order-save').prop('disabled', false);
            });
        });
    </script>
</body>

</html>
