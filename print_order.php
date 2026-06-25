<?php

require_once "./Model/Order.php";
$order = new Order();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $order->pord_id = $id;
    $val = $order->showReceipt();
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_once "./site/title.php" ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.5.0/css/select.bootstrap5.min.css">
    <link rel="stylesheet" href="./styles/Cust.css">
    <link rel="stylesheet" href="./styles/loader.css">

    <?php
    require_once "styles/stylish.php";
    ?>
</head>

<body>

    <?php include 'site/loads.php' ?>

    <div class="red">
        <section class="con mt-5">

            <div class="container-fluid mb-2" id="print-sales">
                <style>
                    table {
                        border-collapse: collapse;
                    }

                    .wborder {
                        border: 1px solid gray;
                    }

                    .bbottom {
                        border-bottom: 1px solid black
                    }

                    td p,
                    th p {
                        margin: unset
                    }

                    .text-center {
                        text-align: center
                    }

                    .text-right {
                        text-align: right
                    }

                    .clear {
                        padding: 10px
                    }

                    #uni_modal .modal-footer {
                        display: none;
                    }
                </style>
                <table width="100%">

                    <?php foreach ($val as $r) : extract($r) ?>

                        <tr>
                            <th class="text-center">
                                <p>

                                    <b class="text text-uppercase">- Receipt -</b>
                                </p>
                                <img src="./img/logo.png" alt="" srcset="" style="width: 300px;">
                            </th>
                        </tr>
                        <tr>
                            <td class="clear">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <table width="100%">
                                    <tr>
                                        <td width="20%" class="text-right">Buyer :</td>
                                        <td width="40%" class="bbottom px-2"> <?php echo $STAFF_FNAME ?></td>
                                        <td width="20%" class="text-right">Date :</td>
                                        <td width="20%" class="bbottom px-2"> <?php echo $PORD_CREATED_AT ?></td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-right"> Supplier Company:</td>
                                        <td width="80%" class="bbottom px-2" colspan="3"> <?php echo $SUPPLIER_COMPANY ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="clear">&nbsp;</td>
                        </tr>
                        <tr>
                            <table width="100%">
                                <tr>
                                    <th width="20%" class="wborder">Qty</th>
                                    <th width="30%" class="wborder">Product</th>
                                    <th width="25%" class="wborder">Unit Price</th>
                                    <th width="25%" class="wborder">Amount</th>
                                </tr>

                                <tr>
                                    <td class="wborder text-center">
                                        <?php echo $PORD_QUANTITY ?>
                                    </td>
                                    <td class="wborder text-center">
                                        <p class="pname">Name: <b> <?php echo $ITEM_NAME ?></b></p>
                                        <p class="pdesc"><small><i>Description: <b><?php echo $ITEM_DESC ?></b></i></small></p>
                                    </td>
                                    <td class="wborder text-right"> ₱ <?php echo $ITEM_PRICE ?></td>
                                    <td class="wborder text-right"> ₱ <?php echo $TOTAL_PRICE ?></td>

                                </tr>

                                <tr>
                                    <th class="text-right wborder" colspan="3">Total:</th>
                                    <th class="text-right wborder text-danger">₱ <?php echo $TOTAL_PRICE ?></th>
                                </tr>
                            </table>
                        </tr>
                        <tr>
                            <td class="clear">&nbsp;</td>
                        </tr>
                        <tr>
                            <th>
                                <p class="text-center"><i>This is not an official receipt.</i></p>
                            </th>
                        </tr>

                    <?php break;
                    endforeach;  ?>
                </table>
                <hr>
                <div class="text-right">
                    <div class="col-md-12">
                        <div class="row">
                            <button type="button" class="btn btn-sm btn-primary mb-2" id="print"><i class="fa fa-print"></i> Print</button>
                            <a href="Order.php" class="btn btn-sm btn-secondary">RETURN</a>

                        </div>
                    </div>
                </div>

            </div>

        </section>

        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <?php require_once "scripts/jquaryScript.php" ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/select/1.5.0/js/dataTables.select.min.js"></script>

    </div>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.5.0/js/dataTables.select.min.js"></script>

    <script>
        $('#print').click(function() {
            var _html = $('#print-sales').clone();
            var newWindow = window.open("", "_blank", "menubar=no,scrollbars=yes,resizable=yes,width=700,height=600");
            newWindow.document.write(_html.html())
            newWindow.document.close()
            newWindow.focus()
            newWindow.print()
            setTimeout(function() {
                ;
                newWindow.close();
            }, 1500);
        })

        function load() {
            document.querySelector('.load').classList.add('loader--hidder');
        }
        var c = setInterval(load, 2000);

        document.querySelector('.load').addEventListener("transitionend", () => {
            document.body.removeChild(document.querySelector('.load'));

            clearInterval(c);
        })
    </script>

</body>

</html>