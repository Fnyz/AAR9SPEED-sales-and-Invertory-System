<?php
$page = "refunds";
include 'site/protected.php';
require_once './Model/Refund.php';

$refund = new Refund();
$refunds = $refund->getAllRefunds();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once "./site/title.php" ?>
    <link rel="stylesheet" href="styles/Cust.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.5.0/css/select.bootstrap5.min.css">
    <link rel="stylesheet" href="styles/des.css">
    <?php require_once "styles/stylish.php"; ?>
</head>

<body>
    <?php include 'site/hamburger.php' ?>
    <?php include 'site/sidebar.php' ?>
    <?php include_once "./site/dataUser.php" ?>

    <div class="red">
        <section class="con mt-5">

            <!-- Process Refund Card -->
            <div class="card mb-4">
                <div class="card-header"><b>PROCESS REFUND / RETURN</b></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" id="r_trans_code" class="form-control" placeholder="Transaction Code">
                        </div>
                        <div class="col-md-2">
                            <input type="number" id="r_item_id" class="form-control" placeholder="Item ID" min="1">
                        </div>
                        <div class="col-md-2">
                            <input type="number" id="r_quantity" class="form-control" placeholder="Quantity" min="1">
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="r_reason" class="form-control" placeholder="Reason (optional)">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-warning w-100" onclick="submitRefund()">SUBMIT REFUND</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Refund History Table -->
            <div class="card">
                <div class="card-header"><b>REFUND HISTORY</b></div>
                <div class="card-body">
                    <table id="refundTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Transaction</th>
                                <th>Product</th>
                                <th>Qty Returned</th>
                                <th>Reason</th>
                                <th>Refunded By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($refunds as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['REFUND_ID'] ?></td>
                                <td><?php echo htmlspecialchars($r['TRANS_CODE'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?php echo htmlspecialchars($r['ITEM_NAME'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?php echo (int)$r['REFUND_QUANTITY'] ?></td>
                                <td><?php echo htmlspecialchars($r['REFUND_REASON'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?php echo htmlspecialchars($r['REFUNDED_BY'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?php echo htmlspecialchars($r['REFUND_DATE'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="./scripts/sweetalert.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#refundTable').DataTable();

            notify();

            function load() {
                document.querySelector('.load').classList.add('loader--hidder');
            }
            var c = setInterval(load, 2000);
            document.querySelector('.load').addEventListener("transitionend", function() {
                document.body.removeChild(document.querySelector('.load'));
                clearInterval(c);
            });
        });

        $('#notch').on('click', function() {
            $('#pendings').hide();
        });

        function submitRefund() {
            var transCode = $('#r_trans_code').val().trim();
            var itemId    = $('#r_item_id').val().trim();
            var quantity  = $('#r_quantity').val().trim();
            var reason    = $('#r_reason').val().trim();

            if (!transCode || !itemId || !quantity) {
                swal({ title: 'Missing Fields', text: 'Transaction code, item ID, and quantity are required.', icon: 'warning', button: 'OK' });
                return;
            }

            $.post('ajax/refundOperation.php', {
                action:     'processRefund',
                trans_code: transCode,
                item_id:    itemId,
                quantity:   quantity,
                reason:     reason
            }, function(data) {
                if (data.success) {
                    swal({ title: 'Refund Processed', text: 'The refund has been recorded and stock restored.', icon: 'success', button: 'OK' })
                        .then(function() { location.reload(); });
                }
            }, 'json').fail(function(jqxhr) {
                if (jqxhr.status !== 422) {
                    swal({ title: 'Error', text: 'Refund could not be processed.', icon: 'error', button: 'OK' });
                }
            });
        }

        function notify(view) {
            view = view || "";
            $.ajax({
                url: 'ajax/notification.php',
                method: 'post',
                data: { view: view },
                dataType: "json",
                success: function(data) {
                    $('.dropdown-menu').html(data.depleted + data.pending + data.inactive);
                    if (data.unseen != 0) {
                        $('#pendings').text(data.unseen);
                    } else {
                        $('#pendings').hide();
                    }
                }
            });
        }
    </script>

</body>
</html>
