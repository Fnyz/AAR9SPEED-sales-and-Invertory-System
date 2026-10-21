<?php
$page = "refunds";
$pageTitle = "Refunds";
include 'site/protected.php';
require_once './Model/Refund.php';
require_once './Model/Invoice.php';

$refund = new Refund();
$refunds = $refund->getAllRefunds();
$recentInvoices = array_slice((new Invoice())->listInvoices(), 0, 30);
$prefill = trim((string)($_GET['code'] ?? ''));

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$unitsRefunded = array_sum(array_column($refunds, 'REFUND_QUANTITY'));
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
                    <div class="page-pretitle">Sales</div>
                    <h1 class="page-title">Refunds</h1>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-cards align-items-start">

                        <div class="col-lg-5">
                            <form class="card" id="refund-form" novalidate>
                                <div class="card-header">
                                    <h2 class="card-title">New refund</h2>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-danger form-error" role="alert" hidden></div>
                                    <label for="refund-code" class="form-label">Invoice code</label>
                                    <div class="input-group mb-1">
                                        <input type="text" class="form-control code" id="refund-code" list="recent-invoices" value="<?php echo $e($prefill) ?>" placeholder="e.g. 30118222945" autocomplete="off">
                                        <button type="button" class="btn" id="find-invoice">Find</button>
                                    </div>
                                    <datalist id="recent-invoices">
                                        <?php foreach ($recentInvoices as $inv) : ?>
                                            <option value="<?php echo $e($inv['trans_code']) ?>"><?php echo $e(date('j M Y', strtotime($inv['created_at'])) . ' · ₱' . number_format((float)$inv['total'], 2)) ?></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <div class="form-hint mb-3">Find it on the customer's receipt, or on the <a href="invoice.php">Invoices</a> page.</div>

                                    <div id="refund-lines" hidden>
                                        <div class="form-label">Product returned</div>
                                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column mb-3" id="line-options"></div>

                                        <div class="row g-3">
                                            <div class="col-sm-5">
                                                <label for="refund-qty" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="refund-qty" min="1" step="1" inputmode="numeric">
                                            </div>
                                            <div class="col-sm-7">
                                                <label for="refund-reason" class="form-label">Reason</label>
                                                <input type="text" class="form-control" id="refund-reason" maxlength="200" placeholder="e.g. Wrong size">
                                            </div>
                                        </div>
                                        <div class="mt-3 p-3 rounded bg-secondary-lt small" id="refund-summary">Choose the product being returned.</div>
                                    </div>
                                </div>
                                <div class="card-footer" id="refund-footer" hidden>
                                    <button type="submit" class="btn btn-primary w-100" id="refund-save">Record refund</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">Refund history</h2>
                                    <div class="card-actions text-secondary small"><?php echo count($refunds) === 1 ? '1 refund' : count($refunds) . ' refunds' ?> · <?php echo (int)$unitsRefunded === 1 ? '1 unit' : (int)$unitsRefunded . ' units' ?> returned to stock</div>
                                </div>
                                <?php if ($refunds) : ?>
                                    <div class="table-responsive">
                                        <table class="table card-table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Invoice</th>
                                                    <th>Product</th>
                                                    <th class="text-end">Qty</th>
                                                    <th>Reason</th>
                                                    <th>By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($refunds as $r) : ?>
                                                    <tr>
                                                        <td class="text-secondary num text-nowrap"><?php echo $e(date('j M Y', strtotime($r['REFUND_DATE']))) ?></td>
                                                        <td class="code"><?php echo $e($r['TRANS_CODE']) ?></td>
                                                        <td><?php echo $e($r['ITEM_NAME']) ?></td>
                                                        <td class="text-end num"><?php echo (int)$r['REFUND_QUANTITY'] ?></td>
                                                        <td class="text-secondary"><?php echo $e($r['REFUND_REASON'] ?: '—') ?></td>
                                                        <td><?php echo $e($r['REFUNDED_BY']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else : ?>
                                    <div class="card-body text-secondary">No refunds yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        var lines = [], loadedCode = '';
        var peso = function (n) { return '₱' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

        function showError(message) { $('#refund-form .form-error').text(message).prop('hidden', !message); }
        function selectedLine() {
            var id = Number($('input[name="refund-line"]:checked').val());
            return lines.filter(function (l) { return l.item_id === id; })[0] || null;
        }

        function findInvoice() {
            var code = $.trim($('#refund-code').val());
            showError('');
            if (code === '') {
                showError('Enter the invoice code.');
                $('#refund-code').trigger('focus');
                return;
            }
            $('#find-invoice').prop('disabled', true);
            $.ajax({ url: 'ajax/refundOperation.php', method: 'POST', global: false, dataType: 'json',
                     data: { action: 'invoiceLines', trans_code: code } })
                .done(function (data) {
                    lines = data.lines; loadedCode = code;
                    var $options = $('#line-options').empty();
                    lines.forEach(function (l) {
                        var none = l.refundable === 0;
                        var $label = $('<label class="form-selectgroup-item">');
                        $('<input type="radio" name="refund-line" class="form-selectgroup-input">').val(l.item_id).prop('disabled', none).appendTo($label);
                        var $body = $('<span class="form-selectgroup-label d-flex align-items-center gap-3 p-3 text-start">').appendTo($label);
                        $body.append('<span class="form-selectgroup-check"></span>');
                        var $text = $('<span class="flex-fill">').appendTo($body);
                        $('<span class="fw-semibold d-block">').text(l.name).appendTo($text);
                        $('<span class="small text-secondary">').text(l.sold + ' sold · ' + l.refunded + ' already refunded · ' + peso(l.unit_price) + ' each').appendTo($text);
                        $('<span class="badge">').addClass(none ? 'bg-secondary-lt' : 'bg-green-lt').text(none ? 'Fully refunded' : l.refundable + ' refundable').appendTo($body);
                        $options.append($label);
                    });
                    $('#refund-lines, #refund-footer').prop('hidden', false);
                    $('#refund-qty').val('');
                    updateSummary();
                })
                .fail(function (xhr) {
                    lines = []; loadedCode = '';
                    $('#refund-lines, #refund-footer').prop('hidden', true);
                    showError(errorText(xhr));
                })
                .always(function () { $('#find-invoice').prop('disabled', false); });
        }

        function updateSummary() {
            var line = selectedLine(), qty = parseInt($('#refund-qty').val(), 10);
            if (line) $('#refund-qty').attr('max', line.refundable);
            var text = !line ? 'Choose the product being returned.'
                : !(qty > 0) ? 'Up to ' + line.refundable + ' ' + line.name + ' can be refunded.'
                : qty > line.refundable ? 'Only ' + line.refundable + ' can be refunded on this invoice.'
                : 'Refund ' + peso(qty * line.unit_price) + ' and put ' + qty + ' back into stock.';
            $('#refund-summary').text(text);
        }

        $('#find-invoice').on('click', findInvoice);
        $('#refund-code').on('keydown', function (event) {
            if (event.key === 'Enter') { event.preventDefault(); findInvoice(); }
        }).on('input', function () {
            if ($.trim($(this).val()) !== loadedCode) $('#refund-lines, #refund-footer').prop('hidden', true);
        });
        $(document).on('change', 'input[name="refund-line"]', updateSummary);
        $('#refund-qty').on('input', updateSummary);

        $('#refund-form').on('submit', function (event) {
            event.preventDefault();
            var line = selectedLine(), qty = parseInt($('#refund-qty').val(), 10);
            showError('');
            if (!line) { showError('Choose the product being returned.'); return; }
            if (!(qty > 0) || qty > line.refundable) {
                showError('Enter a quantity from 1 to ' + line.refundable + '.');
                $('#refund-qty').trigger('focus');
                return;
            }
            $('#refund-save').prop('disabled', true);
            $.ajax({ url: 'ajax/refundOperation.php', method: 'POST', global: false,
                     data: { action: 'processRefund', trans_code: loadedCode, item_id: line.item_id, quantity: qty, reason: $.trim($('#refund-reason').val()) } })
                .done(function () { reloadWith('Refunded ' + qty + ' ' + line.name + '. Stock updated.'); })
                .fail(function (xhr) { showError(errorText(xhr)); $('#refund-save').prop('disabled', false); });
        });

        if ($.trim($('#refund-code').val()) !== '') $(findInvoice);
    </script>
</body>

</html>
