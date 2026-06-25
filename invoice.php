<?php

$page = "invoice";
include 'site/protected.php';
require_once './Model/Invoice.php';
$trans = new Invoice();
$history = $trans->invoiceHistory();

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_once "./site/title.php" ?>
    <link rel="stylesheet" href="styles/Cust.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.5.0/css/select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="./styles/loader.css">

    <?php
    require_once "styles/stylish.php";
    ?>
</head>

<body>

    <?php include 'site/hamburger.php' ?>
    <?php include 'site/sidebar.php' ?>
    <?php include 'site/loads.php' ?>
    <?php include_once "./site/dataUser.php" ?>

    <div class="red">

        <section class="con" style="margin-top: 50px;">
            <h2>INVOICE HISTORY</h2>

            <table id="example" class="order-column" style="width:100%">
                <thead>
                    <tr>
                        <th>Trans Code</th>
                        <th>Customer Name</th>
                        <th>Total Quantity</th>
                        <th>Total Price</th>
                        <th>Saler</th>
                        <th>Trans Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="fill" class="sh">
                    <?php foreach ($history as $value) : extract($value) ?>
                        <tr>
                            <td><?php echo $TRANS_CODE ?></td>
                            <td><?php echo $CUS_FNAME ?></td>
                            <td><?php echo $total_quan ?></td>
                            <td><?php echo $total_price ?></td>
                            <td><?php echo $STAFF_FNAME ?></td>
                            <td><?php echo $TRANS_CREATED_AT ?></td>
                            <td class="text text-warning fw-bold">
                                <form action="transaction.php" method="post">
                                    <input type="hidden" name="id" value="<?php echo $TRANS_CODE ?>">
                                    <button type="submit" class="btn btn-success">VIEW TRANSACTION</button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </section>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" id="ViewUpdate">

                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <?php require_once "./styles/updateAccount.php" ?>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.5.0/js/dataTables.select.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                select: true
            });
            notify();

            function load() {
                document.querySelector('.load').classList.add('loader--hidder');
            }
            var c = setInterval(load, 2000);

            document.querySelector('.load').addEventListener("transitionend", () => {
                document.body.removeChild(document.querySelector('.load'));

                clearInterval(c);
            })
        });

        $('#notch').on('click', function() {
            $('#pendings').hide();
        })

        function saveBtn() {

            var $staff = $('#staff').val();
            var $supp = $('#supplr').val();
            var $code = $('#prod_id').val();
            var $quan = $('#quan').val();

            $.post('ajax/orderOperation.php', {
                staff: $staff,
                code: $code,
                quan: $quan,
                supp: $supp,
                action: 'insertOrder'
            }, function(data, status) {

                location.reload();

            })

        }

        $('#supplr').change(function() {
            var $op = $('#supplr option:selected').val();
            var $fill = $('#placehere');

            $.post('ajax/orderOperation.php', {
                catId: $op,
                action: 'getProductss'
            }, function(data, status) {
                $fill.html(data);
                document.getElementById('placehere').removeAttribute('disabled');
            })

        })

        $('#placehere').change(function() {
            var $op = $('#placehere option:selected').val();
            var $fill = $('#placeme');

            $.post('ajax/orderOperation.php', {
                catId: $op,
                action: 'placeProd'
            }, function(data, status) {
                $fill.html(data);

            })

        })

        function saveBtn2() {

            var $staff = $('#staff2').val();
            var $code = $('#prod_id2').val();
            var $quan = $('#quan2').val();
            var $supp = $('#supplr2').val();

            console.log($supp, $code);

            $.post('ajax/orderOperation.php', {
                staff: $staff,
                code: $code,
                quan: $quan,
                supp: $supp,
                action: 'insertOrder2'
            }, function(data, status) {

                location.reload();

            })

        }

        $('#supplr2').change(function() {
            var $op = $('#supplr2 option:selected').val();
            var $fill = $('#placehere2');

            $.post('ajax/orderOperation.php', {
                catIds: $op,
                action: 'getMetheProduct'
            }, function(data, status) {
                console.log($fill.html(data));
                document.getElementById('placehere2').removeAttribute('disabled');
            })

        })

        $('#placehere2').change(function() {
            var $op = $('#placehere2 option:selected').val();
            var $fill = $('#placeme2');

            $.post('ajax/orderOperation.php', {
                catId: $op,
                action: 'placeProd2'
            }, function(data, status) {
                $fill.html(data);

            })

        })

        function mykey(value) {

            var $onhand = $('#onhand').val();
            var $price = $('#price').val();
            var sum, prod;

            if (value != 0) {
                sum = parseInt($onhand) + parseInt(value);
                prod = parseInt($price) * parseInt(sum);
            }

            $('#total_quan').val(sum);
            $('#total_quan2').val(prod);

        }

        function notify(view = "") {

            $.ajax({
                url: 'ajax/notification.php',
                method: 'post',
                data: {
                    view: view
                },
                dataType: "json",
                success: function(data, status) {

                    $('.dropdown-menu').html(data.depleted + data.pending + data.inactive);
                    if (data.unseen != 0) {
                        $('#pendings').text(data.unseen);

                    } else {
                        $('#pendings').hide();
                    }

                }
            })
        }

        $('#pashere').change(function() {
            var $op = $('#pashere option:selected').val();
            var $fill = $('#pashere2');
            $.post('ajax/orderOperation.php', {
                code: $op,
                action: 'fillOut'
            }, function(data, status) {
                $fill.html(data);

            })

        })

        function getId(id) {

            var thing = $('#ViewUpdate');

            $.post('ajax/ProdOperation.php', {

                ProdId: id,
                action: 'getSingleProd'
            }, function(data, status) {

                thing.html(data);
            })
        }

        function returns() {
            location.reload();
        }
    </script>

</body>

</html>