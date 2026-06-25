<?php

include 'site/protected.php';
require_once './Model/Invoice.php';
$trans = new Invoice();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $trans->trans_code = $id;
    $val = $trans->transacs();
    $total = $trans->totalTrans();
    $change = $trans->totalchange();
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_once "./site/title.php" ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
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

                    @media print {
                        body * { visibility: hidden; }
                        #print-sales, #print-sales * { visibility: visible; }
                        #print-sales { position: absolute; top: 0; left: 0; width: 100%; }
                        .no-print { display: none !important; }
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
                                        <td width="20%" class="text-right">Transaction Code:</td>
                                        <td width="40%" class="bbottom"><?php echo $TRANS_CODE ?></td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="text-right">Customer:</td>
                                        <td width="80%" class="bbottom" colspan="3"><?php echo $CUS_LNAME ?>, <?php echo $CUS_FNAME ?></td>
                                        <td width="20%" class="text-right">Date :</td>
                                        <td width="20%" class="bbottom"><?php echo $TRANS_CREATED_AT  ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    <?php break;
                    endforeach;  ?>
                    <tr>
                        <td class="clear">&nbsp;</td>
                    </tr>
                    <tr>
                        <table width="100%">
                            <tr class="text text-center">
                                <th width="20%" class="wborder">Qty</th>
                                <th width="30%" class="wborder">Product</th>
                                <th width="25%" class="wborder">Unit Price</th>
                                <th width="25%" class="wborder">Amount</th>
                            </tr>

                            <tr>
                                <td class="wborder text-center">
                                    <?php foreach ($val as $r) : extract($r) ?>
                                        <?php echo $TRANS_QUANTITY  ?> <br>
                                    <?php
                                    endforeach;  ?>

                                </td>
                                <td class="wborder">
                                    <?php foreach ($val as $r) : extract($r) ?>
                                        <p class="pname">Name: <b><?php echo $ITEM_NAME  ?> </b></p>
                                    <?php
                                    endforeach;  ?>
                                </td>
                                <td class="wborder text-center">
                                    <?php foreach ($val as $r) : extract($r) ?>
                                        ₱<?php echo $Unit_price ?> <br>
                                    <?php
                                    endforeach;  ?>
                                </td>
                                <td class="wborder text-right">
                                    <?php foreach ($total as $r) : extract($r) ?>
                                        ₱ <?php echo $total_price ?> <br>
                                    <?php break;
                                    endforeach;  ?>
                                </td>

                            </tr>

                            <tr>
                                <th class="text-right wborder" colspan="3">Total</th>
                                <th class="text-right wborder text text-danger">
                                    <?php foreach ($total as $r) : extract($r) ?>
                                        ₱ <?php echo $total_price ?> <br>
                                    <?php break;
                                    endforeach;  ?>
                                </th>

                            </tr>
                            <tr>
                                <th class="text-right wborder" colspan="3">Payment: </th>
                                <th class="text-right wborder">
                                    <?php foreach ($val as $r) : extract($r) ?>
                                        ₱ <?php echo $TRANS_PAYMENT  ?>
                                    <?php break;
                                    endforeach;  ?>
                                </th>
                            </tr>
                            <tr>
                                <th class="text-right wborder" colspan="3">Change: </th>
                                <th class="text-right wborder">

                                    <?php foreach ($change as $r) : extract($r) ?>
                                        ₱ <?php echo $Total_CHANGE  ?>
                                    <?php break;
                                    endforeach;  ?>

                                </th>
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

                </table>
                <hr>
                <div class="text-right no-print">
                    <div class="col-md-12">
                        <div class="row">
                            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                            <a href="invoice.php" class="btn btn-sm btn-secondary">GO BACK</a>

                        </div>
                    </div>
                </div>

            </div>

        </section>

    </div>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.5.0/js/dataTables.select.min.js"></script>

    <script>
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