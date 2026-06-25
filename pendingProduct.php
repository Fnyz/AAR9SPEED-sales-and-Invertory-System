<?php

require_once "./Model/Product.php";
session_start();

$message = "";

if (isset($_SESSION['PRODNAME'])) {
    $message = "Product is already exist!";
}

$prod = new Product();
$supp = $prod->getSupplier();
$cat = $prod->getCategory();
$viewProd = $prod->showProductPending();

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

        <section class="con">
            <h2><?php echo $message ?></h2>
            <h2>PENDING PRODUCTS</h2>
            <table id="example" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Product Price</th>
                        <th>Description</th>
                        <th>ITEM QUANTITY</th>
                        <th>Status</th>

                    </tr>
                </thead>
                <tbody id="fill" class="sh">
                    <?php foreach ($viewProd as $value) : extract($value) ?>
                        <tr>
                            <td><?php echo $ITEM_CODE ?></td>
                            <td>
                                <img src="<?php echo $ITEM_IMAGE ?>" alt="" srcset="" style="width:100px; border-radius:10%;">
                            </td>
                            <td><?php echo $ITEM_NAME ?></td>
                            <td><?php echo $ITEM_PRICE ?></td>
                            <td><?php echo $ITEM_DESC ?></td>
                            <td><?php echo $ITEM_QUANTITY ?></td>
                            <td class="text text-center fw-bold text-danger text-uppercase"><a href="Securitylogin.php" class="text-decoration-none" style="cursor: pointer;"><?php echo $ITEM_STATUS ?></a></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href="Product.php" class="btn btn-primary mt-3">RETURN</a>

            <div class="modal fade" id="editQuantity" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">EDIT QUANTITY</h1>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">

                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="inputGroupSelect01">Supplier</label>
                                    <select class="form-select" id="supplr" name="supplier">
                                        <option selected disabled="">Choose...</option>
                                        <?php foreach ($supp as $val) : extract($val) ?>
                                            <option value="<?php echo $SUPPLIER_ID ?>"><?php echo $SUPPLIER_COMPANY ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">

                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="inputGroupSelect01">Product Supplied</label>
                                    <select class="form-select" id="placehere" disabled>

                                    </select>
                                </div>

                            </div>

                            <div id="placeme">

                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" onclick="updateHere()">Update</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>

                    </div>
                </div>
            </div>

    </div>
    </section>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="ViewUpdate">

            </div>
        </div>
    </div>

    </div><?php unset($_SESSION['PRODNAME']) ?>

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

            function load() {
                document.querySelector('.load').classList.add('loader--hidder');
            }
            var c = setInterval(load, 1000);

            document.querySelector('.load').addEventListener("transitionend", () => {
                document.body.removeChild(document.querySelector('.load'));

                clearInterval(c);
            })
        });

        $('#category').change(function() {
            var $op = $('#category option:selected').val();
            var $fill = $('#fill');

            $.post('ajax/ProdOperation.php', {
                catId: $op,
                action: 'filter'
            }, function(data, status) {
                $fill.html(data);

            })

        })

        $('#supplr').change(function() {
            var $op = $('#supplr option:selected').val();
            var $fill = $('#placehere');

            $.post('ajax/ProdOperation.php', {
                catId: $op,
                action: 'getProdss'
            }, function(data, status) {
                $fill.html(data);
                document.getElementById('placehere').removeAttribute('disabled');
            })

        })

        $('#placehere').change(function() {
            var $op = $('#placehere option:selected').val();
            var $fill = $('#placeme');

            $.post('ajax/ProdOperation.php', {
                catId: $op,
                action: 'placeProd'
            }, function(data, status) {
                $fill.html(data);

            })

        })

        $('#category').change(function() {
            var $op = $('#category option:selected').val();
            var $fill = $('#fill');

            $.post('ajax/ProdOperation.php', {
                catId: $op,
                action: 'filter'
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

        var total = function() {

            var sum = 0;

            $('.total').each(function() {
                var res = $(this).val().replace(',', '');
                if (res != 0) {
                    sum += parseInt(res);

                }

            })

            $('#total_quan').val(sum)

        }

        function mykey() {

            total();

        }

        function updateHere() {
            var $supp = $('#supplr').val();
            var $prod_id = $('#prod_id').val();
            var $quan = $('#total_quan').val();

            $.post('ajax/ProdOperation.php', {
                supp_id: $supp,
                prod_id: $prod_id,
                quantity: $quan,
                action: 'updatingQuan'
            }, function(data, status) {
                location.reload();
            })

        }
    </script>

</body>

</html>