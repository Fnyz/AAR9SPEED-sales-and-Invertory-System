<?php
$page = "sale";
$pageTitle = "New sale";
include 'site/protected.php';
require_once './Model/Sale.php';

$sale = new Sale();
$items = $sale->sellableItems();
$customers = $sale->customers();
require_once './site/stock.php';

$categories = [];
foreach ($items as $it) {
    if ($it['CATEGORY_ID']) {
        $categories[(int)$it['CATEGORY_ID']] = $it['CATEGORY_NAME'];
    }
}
asort($categories);

$itemsJson = json_encode(array_map(fn($it) => [
    'id' => (int)$it['ITEM_ID'], 'code' => (string)$it['ITEM_CODE'], 'name' => $it['ITEM_NAME'],
    'price' => (float)$it['ITEM_PRICE'], 'stock' => (int)$it['ITEM_QUANTITY'],
    'service' => (int)$it['ITEM_QUANTITY'] >= Sale::SERVICE_QTY,
], $items), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'site/layout/head.php' ?>
    <style>
        .product-card { text-align: left; width: 100%; height: 100%; }
        .product-card[aria-pressed="true"] { border-color: var(--tblr-primary); box-shadow: 0 0 0 1px var(--tblr-primary); }
        .product-card:disabled { opacity: .5; cursor: not-allowed; }
        .cart-panel { position: sticky; top: 1rem; }
        @media (max-width: 991.98px) { .cart-panel { position: static; } }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'site/layout/navbar.php' ?>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="page-pretitle">Point of sale</div>
                    <h1 class="page-title">New sale</h1>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="input-icon mb-3">
                                <span class="input-icon-addon"><i class="ti ti-barcode"></i></span>
                                <label for="sale-search" class="visually-hidden">Scan a barcode or search products</label>
                                <input type="search" id="sale-search" class="form-control form-control-lg" placeholder="Scan barcode or type a product name or code" autocomplete="off" autofocus>
                                <span class="input-icon-addon"><kbd>F2</kbd></span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Filter by category">
                                <button type="button" class="btn btn-pill btn-sm btn-primary" data-category="" aria-pressed="true">All</button>
                                <?php foreach ($categories as $id => $name) : ?>
                                    <button type="button" class="btn btn-pill btn-sm" data-category="<?php echo (int)$id ?>" aria-pressed="false"><?php echo $e($name) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($items) : ?>
                                <div class="row row-cards g-2" id="product-grid">
                                    <?php foreach ($items as $it) :
                                        $qty = (int)$it['ITEM_QUANTITY'];
                                        if ($qty >= Sale::SERVICE_QTY) {
                                            $chip = ['bg-secondary-lt', 'Service'];
                                        } elseif ($qty <= 1) {
                                            $chip = ['bg-red-lt', '1 left'];
                                        } elseif ($qty <= reorder_level($it)) {
                                            $chip = ['bg-yellow-lt', $qty . ' left'];
                                        } else {
                                            $chip = ['bg-green-lt', number_format($qty) . ' in stock'];
                                        }
                                    ?>
                                        <div class="col-6 col-md-4" data-product
                                             data-category="<?php echo (int)$it['CATEGORY_ID'] ?>"
                                             data-search="<?php echo $e(strtolower($it['ITEM_NAME'] . ' ' . $it['ITEM_CODE'])) ?>">
                                            <button type="button" class="card card-sm card-link product-card" data-add="<?php echo (int)$it['ITEM_ID'] ?>" aria-pressed="false">
                                                <span class="card-body d-flex flex-column gap-1 w-100">
                                                    <span class="fw-semibold"><?php echo $e($it['ITEM_NAME']) ?></span>
                                                    <span class="code text-secondary">#<?php echo $e($it['ITEM_CODE']) ?> · <?php echo $e($it['CATEGORY_NAME'] ?? '—') ?></span>
                                                    <span class="d-flex flex-wrap justify-content-between align-items-center gap-1 mt-auto pt-2">
                                                        <span class="fw-bold num">₱<?php echo number_format((float)$it['ITEM_PRICE'], 2) ?></span>
                                                        <span class="badge <?php echo $chip[0] ?> stock-chip" data-stock-label><?php echo $e($chip[1]) ?></span>
                                                    </span>
                                                </span>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="empty" id="no-products" hidden>
                                    <p class="empty-title">No products match</p>
                                    <p class="empty-subtitle text-secondary">Check the code, or try another category.</p>
                                </div>
                            <?php else : ?>
                                <div class="empty">
                                    <p class="empty-title">No products available to sell</p>
                                    <p class="empty-subtitle text-secondary">Products appear here once they are active and in stock.</p>
                                    <div class="empty-action"><a href="Product.php" class="btn btn-primary">Go to products</a></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-lg-4">
                            <form class="card cart-panel" id="sale-form" novalidate>
                                <div class="card-header">
                                    <h2 class="card-title">Current sale</h2>
                                    <div class="card-actions"><button type="button" class="btn btn-ghost-danger btn-sm" id="clear-sale">Clear</button></div>
                                </div>
                                <div class="card-body border-bottom py-3">
                                    <label for="sale-customer" class="form-label">Customer</label>
                                    <select id="sale-customer" class="form-select">
                                        <?php foreach ($customers as $c) :
                                            $isWalkIn = (int)$c['CUS_ID'] === Sale::WALK_IN_CUSTOMER;
                                        ?>
                                            <option value="<?php echo (int)$c['CUS_ID'] ?>"><?php echo $isWalkIn ? 'Walk-in customer' : $e(trim($c['CUS_FNAME'] . ' ' . $c['CUS_LNAME'])) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="list-group list-group-flush" id="cart-lines"></div>
                                <div class="card-body text-center text-secondary" id="cart-empty">Scan or tap a product to add it.</div>
                                <div class="card-body border-top d-flex flex-column gap-3">
                                    <div>
                                        <div class="d-flex justify-content-between text-secondary small"><span>Items</span><span class="num" id="item-count">0</span></div>
                                        <div class="d-flex justify-content-between align-items-baseline">
                                            <span class="fw-semibold">Total</span>
                                            <span class="fs-1 fw-bold num" id="sale-total">₱0.00</span>
                                        </div>
                                    </div>
                                    <div class="btn-group w-100" role="group" aria-label="Payment method">
                                        <input type="radio" class="btn-check" name="method" id="pay-cash" value="cash" checked>
                                        <label class="btn" for="pay-cash"><i class="ti ti-cash me-1"></i>Cash</label>
                                        <input type="radio" class="btn-check" name="method" id="pay-gcash" value="gcash">
                                        <label class="btn" for="pay-gcash"><i class="ti ti-device-mobile me-1"></i>GCash</label>
                                    </div>
                                    <div id="cash-fields">
                                        <label for="cash-received" class="form-label">Cash received</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control form-control-lg num" id="cash-received" min="0" step="0.01" inputmode="decimal">
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mt-2" id="quick-cash"></div>
                                        <div class="d-flex justify-content-between align-items-baseline mt-2">
                                            <span class="text-secondary">Change</span>
                                            <span class="fs-2 fw-bold num" id="change-due">₱0.00</span>
                                        </div>
                                    </div>
                                    <div id="gcash-fields" hidden>
                                        <label for="gcash-ref" class="form-label">GCash reference number</label>
                                        <input type="text" class="form-control code" id="gcash-ref" maxlength="50" inputmode="numeric" placeholder="From the customer's GCash receipt">
                                    </div>
                                    <div class="alert alert-danger mb-0 form-error" role="alert" hidden></div>
                                    <button type="submit" class="btn btn-primary btn-lg w-100" id="complete-sale" disabled>Complete sale</button>
                                    <div class="small text-secondary text-center" id="sale-hint">Add at least one product.</div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        var ITEMS = {};
        (<?php echo $itemsJson ?>).forEach(function (it) { ITEMS[it.id] = it; });
        var cart = {};           // item id -> quantity
        var order = [];          // item ids in the order they were added
        var activeCategory = '';
        var peso = function (n) { return '₱' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

        function totals() {
            var total = 0, count = 0;
            order.forEach(function (id) { total += Math.round(ITEMS[id].price * cart[id] * 100) / 100; count += cart[id]; });
            return { total: Math.round(total * 100) / 100, count: count };
        }

        function setQty(id, qty) {
            var item = ITEMS[id];
            qty = Math.max(0, Math.min(qty, item.service ? 9999 : item.stock));
            if (qty === 0) {
                delete cart[id];
                order = order.filter(function (x) { return x !== id; });
            } else {
                if (!cart[id]) order.push(id);
                cart[id] = qty;
            }
            render();
        }

        function add(id) {
            var item = ITEMS[id];
            if (!item) return;
            if (!item.service && (cart[id] || 0) >= item.stock) {
                showToast('Only ' + item.stock + ' ' + item.name + ' in stock.', 'warning');
                return;
            }
            setQty(id, (cart[id] || 0) + 1);
        }

        function render() {
            var $lines = $('#cart-lines').empty();
            order.forEach(function (id) {
                var it = ITEMS[id], qty = cart[id];
                var $row = $('<div class="list-group-item">').attr('data-line', id);
                var $top = $('<div class="d-flex align-items-center gap-2">').appendTo($row);
                var $name = $('<div class="flex-fill text-truncate">').appendTo($top);
                $('<div class="fw-medium text-truncate">').text(it.name).appendTo($name);
                $('<div class="small text-secondary num">').text(peso(it.price) + ' each').appendTo($name);
                var $stepper = $('<div class="input-group input-group-sm flex-nowrap" style="width: 7rem">').appendTo($top);
                $('<button type="button" class="btn" data-step="-1">').attr('aria-label', 'Remove one ' + it.name).text('−').appendTo($stepper);
                $('<input type="number" class="form-control text-center num px-1" min="0" data-qty>').attr('aria-label', it.name + ' quantity').val(qty).appendTo($stepper);
                $('<button type="button" class="btn" data-step="1">').attr('aria-label', 'Add one ' + it.name).text('+').appendTo($stepper);
                $('<div class="fw-semibold num text-end" style="min-width: 5.5rem">').text(peso(it.price * qty)).appendTo($top);
                $lines.append($row);
            });
            $('#cart-empty').prop('hidden', order.length > 0);

            $('[data-add]').each(function () {
                var id = Number($(this).data('add')), it = ITEMS[id], inCart = cart[id] || 0;
                $(this).attr('aria-pressed', inCart > 0 ? 'true' : 'false')
                    .prop('disabled', !it.service && inCart >= it.stock);
            });

            var t = totals();
            $('#item-count').text(t.count);
            $('#sale-total').text(peso(t.total));
            renderPayment(t.total);
        }

        function method() { return $('input[name="method"]:checked').val(); }

        function renderPayment(total) {
            var isCash = method() === 'cash';
            $('#cash-fields').prop('hidden', !isCash);
            $('#gcash-fields').prop('hidden', isCash);

            var $quick = $('#quick-cash').empty();
            if (isCash && total > 0) {
                var options = [total, Math.ceil(total / 100) * 100, Math.ceil(total / 500) * 500, Math.ceil(total / 1000) * 1000];
                options.filter(function (v, i, all) { return all.indexOf(v) === i; }).forEach(function (v, i) {
                    $('<button type="button" class="btn btn-sm num">').attr('data-cash', v).text(i === 0 ? 'Exact' : peso(v).replace('.00', '')).appendTo($quick);
                });
            }

            var cash = parseFloat($('#cash-received').val());
            var change = (isNaN(cash) ? 0 : cash) - total;
            var ready = order.length > 0 && (!isCash || (!isNaN(cash) && change >= -0.001));
            $('#change-due').text(change >= 0 || isNaN(cash) ? peso(Math.max(0, change)) : 'Short ' + peso(-change))
                .toggleClass('text-red', !isNaN(cash) && change < -0.001);
            $('#complete-sale').prop('disabled', !ready);
            $('#sale-hint').text(order.length === 0 ? 'Add at least one product.'
                : isCash && isNaN(cash) ? 'Enter the cash received.'
                : isCash && change < -0.001 ? 'Cash received is less than the total.'
                : 'Ready. Press Enter or click Complete sale.');
        }

        function applyFilters() {
            var query = $.trim($('#sale-search').val()).toLowerCase(), shown = 0;
            $('[data-product]').each(function () {
                var match = (!activeCategory || String($(this).data('category')) === activeCategory)
                    && (query === '' || $(this).attr('data-search').indexOf(query) !== -1);
                $(this).prop('hidden', !match);
                if (match) shown++;
            });
            $('#no-products').prop('hidden', shown > 0);
        }

        // Product grid and search (a barcode scanner types the code and presses Enter).
        $(document).on('click', '[data-add]', function () { add(Number($(this).data('add'))); });
        $('#sale-search').on('input', applyFilters).on('keydown', function (event) {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            var query = $.trim($(this).val()).toLowerCase();
            if (query === '') return;
            var exact = Object.keys(ITEMS).map(Number).filter(function (id) { return ITEMS[id].code.toLowerCase() === query; });
            var visible = $('[data-product]:not([hidden]) [data-add]');
            var id = exact.length ? exact[0] : (visible.length === 1 ? Number(visible.data('add')) : null);
            if (id) {
                add(id);
                $(this).val('');
                applyFilters();
            } else {
                showToast(visible.length ? 'Several products match. Tap the one you want.' : 'No product with that code.', 'warning');
            }
        });
        $('[data-category]').filter('button').on('click', function () {
            activeCategory = String($(this).data('category'));
            $('[data-category]').filter('button').removeClass('btn-primary').attr('aria-pressed', 'false');
            $(this).addClass('btn-primary').attr('aria-pressed', 'true');
            applyFilters();
        });
        $(document).on('keydown', function (event) {
            if (event.key === 'F2') { event.preventDefault(); $('#sale-search').trigger('focus').trigger('select'); }
        });

        // Cart controls
        $(document).on('click', '[data-step]', function () {
            var id = Number($(this).closest('[data-line]').data('line'));
            setQty(id, (cart[id] || 0) + Number($(this).data('step')));
        });
        // Enter in a quantity box confirms the number; it must not complete the sale.
        $(document).on('keydown', '[data-qty]', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); $(this).trigger('change'); }
        });
        $(document).on('change', '[data-qty]', function () {
            var id = Number($(this).closest('[data-line]').data('line'));
            setQty(id, parseInt($(this).val(), 10) || 0);
        });
        $('#clear-sale').on('click', function () {
            cart = {}; order = [];
            $('#cash-received, #gcash-ref').val('');
            render();
        });

        // Payment
        $('input[name="method"]').on('change', function () { renderPayment(totals().total); });
        $('#cash-received').on('input', function () { renderPayment(totals().total); });
        $(document).on('click', '[data-cash]', function () {
            $('#cash-received').val(Number($(this).data('cash')).toFixed(2));
            renderPayment(totals().total);
        });

        $('#sale-form').on('submit', function (event) {
            event.preventDefault();
            var $error = $('#sale-form .form-error').prop('hidden', true);
            if ($('#complete-sale').prop('disabled')) return;
            $('#complete-sale').prop('disabled', true).text('Saving…');
            $.ajax({
                url: 'ajax/saleOperation.php', method: 'POST', global: false, dataType: 'json',
                data: {
                    action: 'checkout',
                    lines: JSON.stringify(order.map(function (id) { return [id, cart[id]]; })),
                    customer_id: $('#sale-customer').val(),
                    method: method(),
                    cash: $('#cash-received').val(),
                    reference: $.trim($('#gcash-ref').val())
                }
            }).done(function (data) {
                window.location.href = 'transaction.php?code=' + encodeURIComponent(data.code) + '&new=1';
            }).fail(function (xhr) {
                $error.text(errorText(xhr)).prop('hidden', false);
                $('#complete-sale').prop('disabled', false).text('Complete sale');
            });
        });

        render();
    </script>
</body>

</html>
