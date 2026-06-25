<?php

require_once "./Model/Product.php";

$prod = new Product();
$supp = $prod->getSupplier();
$cat = $prod->getCategory();
$viewProd = $prod->showProduct();

$prod->id = $_POST['id'];
$show = $prod->showDetails();

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
    <?php include_once "./site/dataUser.php" ?>

    <div id="ham"> <button class="btn " type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling"><i class="fa-solid fa-bars text-black"></i></button></div>
    <div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
        <div class="offcanvas-header sides">
            <img src="img/logo.png" class="offcanvas-title" id="offcanvasScrollingLabel" width="300px"></img>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body sides">
            <div class="card sides" style="width: 18rem;">
                <div class="rnd">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="card-body ">
                    <h5 class="card-title">
                        <i class="fa-solid fa-pen"></i>
                        Dadi BUSICS
                    </h5>
                    <div class="btns">
                        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#UpdateAdmin">UPDATE</a>
                        <a href="login.php" class="btn btn-danger">LOG-OUT</a>
                    </div>
                </div>
            </div>

            <div class="list-group ">
                <a href="Dashboard.php" class="list-group-item list-group-item-action sides " aria-current="true">
                    <i class="fa-sharp fa-solid fa-house-user"></i>HOME</a></li>
                </a>
                <a href="Customer.php" class="list-group-item list-group-item-action sides"><i class="fa-sharp fa-solid fa-users"></i>CUSTOMER</a></li></a>
                <a href="Staff.php" class="list-group-item list-group-item-action sides"><i class="fa-solid fa-user-plus"></i>STAFF</a></li></a>
                <a href="Category.php" class="list-group-item list-group-item-action sides"> <i class="fa-solid fa-box-open"></i> CATEGORY</a></li></a>
                <a href="Product.php" class="list-group-item list-group-item-action active"><i class="fa-sharp fa-solid fa-cart-shopping"></i>PRODUCT</a>
                <a href="Invoice.php" class="list-group-item list-group-item-action sides"><i class="fa-solid fa-receipt"></i> INVOICE</a>
                <a href="Supplier.php" class="list-group-item list-group-item-action sides"><i class="fa-sharp fa-solid fa-user"></i>SUPPLIER</a>
                <a href="Order.php" class="list-group-item list-group-item-action sides"><i class="fa-solid fa-bag-shopping"></i>PURCHASE ORDER</a>

            </div>

        </div>
    </div>
    <div class="red">

        <section class="con">
            <?php foreach ($show as $value) : extract($value) ?>
                <label for="" style="color:#0652DD;">CATEGORY: <h2 class="mb-4" style="color:#EA2027;"><?php echo $CATEGORY_NAME ?></h2></label>
            <?php break;
            endforeach; ?>

            <table id="example" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Supplier</th>
                        <th>DATE CREATED</th>
                        <th>DATE UPDATED</th>

                    </tr>
                </thead>
                <tbody id="fill" class="sh">
                    <?php foreach ($show as $value) : extract($value) ?>
                        <tr>
                            <td><?php echo $ITEM_CODE ?></td>
                            <td><?php echo $ITEM_NAME ?></td>
                            <?php if ($ITEM_QUANTITY > 5) : ?>
                                <td><?php echo $ITEM_QUANTITY  ?></td>
                            <?php elseif ($ITEM_QUANTITY <= 5) :

                                $mg = "is almost depleted!";
                                $prod->subject = $ITEM_NAME;
                                $prod->msg = $mg;
                                $prod->notify(); ?>
                                <td><?php echo $ITEM_QUANTITY  ?> <i class="fa-solid fa-triangle-exclamation text text-danger"></i></td>
                            <?php endif; ?>
                            <td style="color:red; font-weight:bold;"><?php echo $SUPPLIER_COMPANY ?></td>
                            <td><?php echo $ITEM_CREATED_AT ?></td>
                            <td><?php echo $ITEM_UPDATED_AT ?></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href="Product.php" class="btn btn-success mt-2">BACK</i></a>

        </section>
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

            getCount();

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

        function deleteOne(id) {

            $.post('ajax/ProdOperation.php', {

                prodId: id,
                action: 'delete'
            }, function(data, status) {

                location.reload();
                console.log(status);
            })
        }

        function getCount() {

            $.post('ajax/ProdOperation.php', {
                action: 'getCounting'
            }).done(function(data) {

                datas = JSON.parse(data);
                datas.map(function(data) {
                    if (data.total_pending != 0) {

                        $('#pendings').text(data.total_pending);
                    } else {

                        $('#pendings').hide;

                    }
                })

            })

        }

        function getId(id) {

            var thing = $('#ViewUpdate');

            $.get('ajax/ProdOperation.php', {

                ProdId: id,
                action: 'getSingleProd'
            }, function(data, status) {

                thing.html(data);
            })
        }

        function updateME(id) {

            var $Pname = $('#prodName').val();
            var $Pprice = $('#prodPrice').val();
            var $Pdesc = $('#prodDesc').val();
            var $Pquan = $('#prodQuan').val();

            $.post('ajax/ProdOperation.php', {
                id: id,
                Pname: $Pname,
                price: $Pprice,
                desc: $Pdesc,
                quantity: $Pquan,
                action: 'updating'
            }, function(data, status) {

                location.reload();

            })

        }
    </script>

</body>

</html>