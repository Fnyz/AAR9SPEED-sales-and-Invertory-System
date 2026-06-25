<?php
$page = "product";
include 'site/protected.php';
require_once "./Model/Product.php";
require_once "./Model/Order.php";

$ord = new Order();
$dd = $ord->getMePAPA();

$conn = mysqli_connect("localhost", "root", "202415", "aar9speed");

$query = "SELECT ITEM_ID FROM ITEM WHERE ITEM_QUANTITY = 0";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row["ITEM_ID"];
        $update_query = "UPDATE ITEM SET ITEM_STATUS = 'INACTIVE' WHERE ITEM_ID = '$product_id'";
        mysqli_query($conn, $update_query);
    }
}

$prod = new Product();
$supp = $prod->getSupplier();
$cat = $prod->getCategory();
$viewProd = $prod->showProductActive();

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_once "./site/title.php" ?>
    <link rel="stylesheet" href="./styles/Cust.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.5.0/css/select.bootstrap5.min.css">
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

            <h2>PRODUCT</h2>

            <div id="boxss">
                <select class="form-control mb-3" name="category" style="width: 170px;" id="category">
                    <option selected="" disabled="">-- Select Category --</option>
                    <?php foreach ($cat as $val) : extract($val) ?>
                        <option value="<?php echo $CATEGORY_ID ?>"><?php echo $CATEGORY_NAME ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-danger py-2" onclick="returns()"><i class="fa-solid fa-rotate-left"></i></button>
            </div>

            <table id="example" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Product Price</th>
                        <th>Product Quan</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody id="fill" class="sh">
                    <?php foreach ($viewProd as $value) : extract($value) ?>
                        <tr>
                            <td><?php echo $ITEM_CODE ?></td>
                            <td>
                                <img src="<?php echo $ITEM_IMAGE ?>" alt="" srcset="" style="width:50px; height:50px; border-radius:10%;">
                            </td>
                            <td><?php echo $ITEM_NAME ?></td>
                            <td><?php echo $ITEM_PRICE ?></td>
                            <td class="quanssss"><?php echo $ITEM_QUANTITY  ?></td>
                            <td><?php echo $ITEM_DESC ?></td>
                            <td class="text text-center fw-bold text-primary"><?php echo $ITEM_STATUS ?>

                            </td>
                            <td class="butt">

                                <form action="ProductView.php" method="post" class="d-flex align-items-center justity-content-center">
                                    <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="getId(<?php echo $ITEM_CODE ?>)"><i class="fa-regular fa-pen-to-square me-2"></i></a></i>
                                    <input type="hidden" value="<?php echo $ITEM_CODE ?>" name="id">
                                    <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-circle-info" style="margin-right:10px ; width:10px;"></i>DETAILS
                                    </button>
                                </form>

                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="sideBtn">

                <div class="btn-group mt-4" role="group" aria-label="Button group with nested dropdown">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            SELECT OPTION <span id="cnt" class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle">
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="fa-solid fa-plus"></i>ADD PRODUCT</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editQuantity"> <i class="fa-solid fa-pen"></i>EDIT QUANTITY</a></li>
                            <li><a class="dropdown-item" href="pendingProduct.php"><i class="fa-solid fa-clock-rotate-left"></i></i>VIEW PENDING <span id="count" class="position-absolute buttom-50 start-100 translate-middle badge rounded-pill bg-danger">
                                        99+</a></li>
                            <li><a class="dropdown-item" href="InactiveProduct.php"><i class="fa-solid fa-circle-xmark me-2"></i></i></i>VIEW INACTIVE PRODUCT <span id="count2" class="position-absolute buttom-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        99+</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ADD PRODUCT</h1>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <form action="ajax/itemInsert.php?action=insert" method="POST" rule="form" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <input type="file" class="form-control" name="image" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" placeholder="Product Code" name="Code" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" placeholder="Product Name" name="Product" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="number" class="form-control" placeholder="Price" name="Price" required>
                                    </div>
                                    <div class="mb-3">
                                        <input type="number" class="form-control" placeholder="Quantity" name="Quantity" required>
                                    </div>
                                    <div class="mb-3">
                                        <div class="input-group">
                                            <textarea class="form-control" aria-label="With textarea" placeholder="Description" name="Descrip"></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" name="status" value="pending">
                                    </div>
                                    <div class="mb-3">

                                        <div class="input-group mb-3">
                                            <label class="input-group-text" for="inputGroupSelect01">Category</label>
                                            <select class="form-select" id="inputGroupSelect01" name="category">
                                                <option selected disabled="">Choose...</option>
                                                <?php foreach ($cat as $val) : extract($val) ?>
                                                    <option value="<?php echo $CATEGORY_ID ?>"><?php echo $CATEGORY_NAME ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="mb-3">

                                        <div class="input-group mb-3">
                                            <label class="input-group-text" for="inputGroupSelect01">Supplier</label>
                                            <select class="form-select" id="inputGroupSelect01" name="supplier">
                                                <option selected disabled="">Choose...</option>
                                                <?php foreach ($supp as $val) : extract($val) ?>
                                                    <option value="<?php echo $SUPPLIER_ID ?>"><?php echo $SUPPLIER_COMPANY ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary" name="save">Save</button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

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
                                        <select class="form-select" id="supplr" name="supplier" required>
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
                                        <select class="form-select" id="placehere" required disabled>

                                        </select>
                                    </div>

                                </div>

                                <div id="placeme">

                                </div>

                            </div>
                            <div class="modal-footer">
                                <button id="btnss" type="submit" class="btn btn-primary" onclick="updateHere()">Update</button>
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

    </div>

    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <?php require_once "./styles/updateAccount.php" ?>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.5.0/js/dataTables.select.min.js"></script>
    <script src="./scripts/sweetalert.min.js"></script>

    <?php

    if (isset($_SESSION['PRODNAME'])) {
    ?>
        <script>
            swal({
                title: "Warning!",
                text: "<?php echo $_SESSION['PRODNAME'];  ?>",
                icon: "error",
                button: "OK",
            });
        </script>

    <?php
        unset($_SESSION['PRODNAME']);
    }

    if (isset($_SESSION['PRODNAME2'])) {
    ?>
        <script>
            swal({
                title: "Warning!",
                text: "<?php echo $_SESSION['PRODNAME2'];  ?>",
                icon: "error",
                button: "OK",
            });
        </script>

    <?php
        unset($_SESSION['PRODNAME2']);
    }

    ?>

    <?php

    if (isset($_SESSION['Saves'])) {
    ?>
        <script>
            swal({
                title: "GOOD JOB!",
                text: "<?php echo $_SESSION['Saves'];  ?>",
                icon: "success",
                button: "Done",
            });
        </script>

    <?php
        unset($_SESSION['Saves']);
    }

    ?>

    <?php

    if (isset($_SESSION['updated'])) {
    ?>
        <script>
            swal({
                title: "GOOD JOB!",
                text: "<?php echo $_SESSION['updated'];  ?>",
                icon: "success",
                button: "Done",
            });
        </script>

    <?php
        unset($_SESSION['updated']);
    }

    ?>

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                select: true
            });

            function load() {
                document.querySelector('.load').classList.add('loader--hidder');
            }
            var c = setInterval(load, 2000);

            document.querySelector('.load').addEventListener("transitionend", () => {
                document.body.removeChild(document.querySelector('.load'));

                clearInterval(c);
            })

            notify();
            getCount();

            $('#btnss').hide();

            getCountsss();

        });

        $('#notch').on('click', function() {
            $('#pendings').hide();
        })

        function notify(view = "") {

            $.ajax({
                url: 'ajax/notification.php',
                method: 'post',
                data: {
                    view: view
                },
                dataType: "json",
                success: function(data, status) {

                    $('#menu').html(data.depleted + data.pending + data.inactive);
                    if (data.unseen != 0) {
                        $('#pendings').text(data.unseen);

                    } else {
                        $('#pendings').hide();
                    }

                }
            })
        }

        function getCount() {

            $.post('ajax/ProdOperation.php', {
                action: 'getCounting'
            }).done(function(data) {

                datas = JSON.parse(data);
                datas.map(function(data) {
                    if (data.total_pending != 0) {
                        $('#count').text(data.total_pending);
                        $('#cnt').show();
                    } else {
                        $('#count').hide();
                        $('#cnt').hide();

                    }
                })

            })

        }

        function getCountsss() {

            $.post('ajax/ProdOperation.php', {
                action: 'getCounting2'
            }).done(function(data) {

                datas = JSON.parse(data);
                datas.map(function(data) {
                    if (data.total_pending != 0) {
                        $('#count2').text(data.total_pending);

                    } else {
                        $('#count2').hide();

                    }
                })

            })

        }

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
                $('#btnss').show();

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
            var prod = 0;

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
            var $fill = $('#placeme').val();
            var $fill2 = $('#placehere').val();

            if ($quan === "") {

                swal({
                    title: "Warning!",
                    text: "Please input fields...",
                    icon: "error",
                    button: "OK",
                });

            } else {

                $.post('ajax/ProdOperation.php', {
                    supp_id: $supp,
                    prod_id: $prod_id,
                    quantity: $quan,
                    action: 'updatingQuan'
                }, function(data, status) {

                    swal({
                        title: "Good job!",
                        text: "Updated successfully!",
                        icon: "success",
                        button: "Done",
                    });

                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                })

            }

        }
    </script>

</body>

</html>