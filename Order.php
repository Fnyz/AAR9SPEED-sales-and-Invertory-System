<?php
$page = "order";
include 'site/protected.php';

require_once "./Model/Order.php";

$staff = new Order();
$st = $staff->getStaff();
$supp = $staff->getSupplier();
$records = $staff->showProduct();

if (isset($_SESSION['adminId']) && isset($_SESSION['userId'])) {

    $staff->role = intval($_SESSION['adminId']);
    $staff->admin = strtoupper($_SESSION['userId']);
    $st = $staff->getStaff();
}

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
            <a href="Securitylogin.php" class="btn btn-danger">LOG-OUT</a>
            <h2>PURCHASE ORDER</h2>

            <table id="example" class="order-column" style="width:100%">
                <thead>
                    <tr>
                        <th>PORD_ID</th>
                        <th>Product Name</th>
                        <th>Total Quantity</th>
                        <th>Product Price</th>
                        <th>Total Price</th>
                        <th>SUPPLIER</th>
                        <th>PURCHASER</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="fill" class="sh">
                    <?php foreach ($records as $value) : extract($value) ?>
                        <tr>
                            <td><?php echo $PORD_ID ?></td>
                            <td><?php echo $ITEM_NAME ?></td>
                            <td><?php echo $PORD_QUANTITY ?></td>
                            <td><?php echo $ITEM_PRICE ?></td>
                            <td><?php echo $TOTAL_PRICE ?></td>
                            <td><?php echo $SUPPLIER_COMPANY ?></td>
                            <td><?php echo $STAFF_FNAME ?></td>
                            <td class="text text-warning fw-bold"><?php echo $PORD_STATUS ?></td>
                            <td class="text text-warning fw-bold">
                                <div class="d-flex justify-content-center" style="gap:5px;">

                                    <form action="print_order.php" method="post" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?php echo $PORD_ID ?>">
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-receipt"></i></button>
                                    </form>

                                    <form action="ajax/deleteOrder.php" method="post">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?php echo $PORD_ID ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" name="save"><i class="fa-solid fa-x"></i></button>
                                    </form>

                                </div>

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
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#staticBackdrop2"><i class="fa-solid fa-thumbs-up"></i>ACTIVE PURCHASE ORDER</a>
                                <span id="count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    99+</a>
                            </li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#staticBackdrop3"><i class="fa-solid fa-circle-xmark"></i></i>ACTIVE INACTIVE PRODUCT</a>
                                <span id="count2" class="position-absolute top-50 start-100 translate-middle badge rounded-pill bg-danger">
                                    99+</a>
                            </li>
                            </li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#staticBackdrop"> <i class="fa-solid fa-cart-plus"></i>ADD PURCHASE ORDER</a>
                            </li>

                    </div>
                </div>

                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ADD PURCHASE ORDER</h1>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">

                                    <?php foreach ($st as $val) : extract($val) ?>
                                        <input type="hidden" value="<?php echo $STAFF_ID ?>" class="form-control" id="staff">
                                        <label for="">PURCHASER:</label>
                                        <input type="text" value="<?php echo $STAFF_FNAME ?>" class="form-control" disabled>
                                    <?php break;
                                    endforeach; ?>

                                </div>

                                <div class="mb-3">

                                    <div class="input-group mb-3">
                                        <label class="input-group-text" for="inputGroupSelect01">SUPPLIER</label>
                                        <select class="form-select" id="supplr">
                                            <option selected disabled="">Choose...</option>
                                            <?php foreach ($supp as $val) : extract($val) ?>
                                                <option value="<?php echo $SUPPLIER_ID ?>"><?php echo $SUPPLIER_COMPANY ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                </div>

                                <div class="mb-3">

                                    <div class="input-group mb-3">
                                        <label class="input-group-text" for="inputGroupSelect01">Product Purchase</label>
                                        <select class="form-select" id="placehere" disabled="disabled">

                                        </select>
                                    </div>
                                </div>

                                <div id="placeme">

                                    <div class="mb-3">
                                        <label for="">PRODUCT ID:</label>
                                        <input type="number" class="form-control" value="" id="prod_id" disabled>
                                    </div>

                                    <div class="mb-3">

                                        <label for="">PRODUCT CODE:</label>
                                        <input type="text" class="form-control" value="" id="prodCode" placeholder="Product Code" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="">PRODUCT NAME:</label>
                                        <input type="text" class="form-control" value="" placeholder="Product Name" id="prod_Name" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="">QUANTITY ONHAND:</label>
                                        <input type="number" class="form-control " placeholder="On Hand" id="onhand" value="" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="">INPUT QUANTITY:</label>
                                        <input type="number" class="form-control " placeholder="INPUT QUANTITY" onkeyup="mykey(this.value)" name="ProdQuan" id="quan" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="">TOTAL QUANTITY:</label>
                                        <input type="number" class="form-control " placeholder="Quantity" id="total_quan" name="ProdQuan" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="">PRODUCT PRICE:</label>
                                        <input type="number" class="form-control " placeholder="On Hand" id="price" value="' . $ITEM_PRICE . '" disabled>
                                    </div>

                                    <div class="mb-3">
                                        <label for="">TOTAL AMOUNT:</label>
                                        <input type="number" class="form-control" placeholder="" id="total_quan2" disabled>

                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" id="btn2222" class="btn btn-primary" name="save" onclick="saveBtn()">Save</button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="staticBackdrop2" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ACTIVE ORDER</h1>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">

                                    <?php foreach ($st as $val) : extract($val) ?>
                                        <input type="hidden" value="<?php echo $STAFF_ID ?>" class="form-control" id="staff2">
                                        <label for="">PURCHASER:</label>
                                        <input type="text" value="<?php echo $STAFF_FNAME ?>" class="form-control" disabled>
                                    <?php break;
                                    endforeach; ?>

                                </div>

                                <div class="mb-3">

                                    <div class="input-group mb-3">
                                        <label class="input-group-text" for="inputGroupSelect01">SUPPLIER</label>
                                        <select class="form-select" id="supplr2">
                                            <option selected disabled="">Choose...</option>
                                            <?php foreach ($supp as $val) : extract($val) ?>
                                                <option value="<?php echo $SUPPLIER_ID ?>"><?php echo $SUPPLIER_COMPANY ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                </div>

                                <div class="mb-3">

                                    <div class="input-group mb-3">
                                        <label class="input-group-text" for="inputGroupSelect01">Product Purchase</label>
                                        <select class="form-select" id="placehere2" disabled="disabled">

                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="">PRODUCT ID:</label>
                                    <input type="number" class="form-control" value="" id="prod_id2" disabled>
                                </div>

                                <div class="mb-3">

                                    <label for="">PRODUCT CODE:</label>
                                    <input type="text" class="form-control" value="" placeholder="Product Code" id="prodCode2" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="">PRODUCT NAME:</label>
                                    <input type="text" class="form-control" value="" id="prodName2" placeholder="Product Name" disabled>
                                </div>

                                <div class="mb-3">
                                    <label for="">QUANTITY ONHAND:</label>
                                    <input type="number" class="form-control " placeholder="On Hand" value="" id="quan2" disabled>
                                </div>

                                <div class="mb-3">
                                    <label for="">PRODUCT PRICE:</label>
                                    <input type="number" class="form-control " placeholder="On Hand" value="" id="price2" disabled>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" id="btnsss" class="btn btn-primary" name="save" onclick="saveBtn2()">Save</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>

                        </div>
                    </div>

                </div>

        </section>

        <div class="modal fade" id="staticBackdrop3" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">ACTIVE INACTIVE PRODUCT</h1>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">

                            <?php foreach ($st as $val) : extract($val) ?>
                                <input type="hidden" value="<?php echo $STAFF_ID ?>" class="form-control" id="staff3">
                                <label for="">PURCHASER:</label>
                                <input type="text" value="<?php echo $STAFF_FNAME ?>" class="form-control" disabled>
                            <?php break;
                            endforeach; ?>

                        </div>

                        <div class="mb-3">

                            <div class="input-group mb-3">
                                <label class="input-group-text" for="inputGroupSelect01">SUPPLIER</label>
                                <select class="form-select" id="supplr3">
                                    <option selected disabled="">Choose...</option>
                                    <?php foreach ($supp as $val) : extract($val) ?>
                                        <option value="<?php echo $SUPPLIER_ID ?>"><?php echo $SUPPLIER_COMPANY ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>

                        <div class="mb-3">

                            <div class="input-group mb-3">
                                <label class="input-group-text" for="inputGroupSelect01">Product Purchase</label>
                                <select class="form-select" id="placehere3" disabled="disabled">

                                </select>
                            </div>
                        </div>

                        <div id="placeme3"></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" id="btn444" class="btn btn-primary" name="save" onclick="saveBtn4()">Save</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

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

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                select: true
            });

            notify();
            getCount();

            function load() {
                document.querySelector('.load').classList.add('loader--hidder');
            }
            var c = setInterval(load, 2000);

            document.querySelector('.load').addEventListener("transitionend", () => {
                document.body.removeChild(document.querySelector('.load'));

                clearInterval(c);
            })
            getCountsss();
            $('#btnsss').hide();
            $('#btn2222').hide();
            $('#btn444').hide();
            $(document).ready(function() {
                $('#activeMePur').modal();
            });

        });

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

                swal({
                    title: "Good job!",
                    text: "Purchase Product Successfully!",
                    icon: "success",
                    button: "Done",
                });

                setTimeout(function() {
                    location.reload();
                }, 2000);

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
                datas = JSON.parse(data)
                datas.map(function(d) {
                    $('#prod_id').val(d.ITEM_ID);
                    $('#prodCode').val(d.ITEM_CODE);
                    $('#prod_Name').val(d.ITEM_NAME);
                    $('#onhand').val(d.ITEM_QUANTITY);
                    $('#price').val(d.ITEM_PRICE);

                })

                $('#btn2222').show();

            })

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

        function saveBtn2() {

            var $staff = $('#staff2').val();
            var $code = $('#prod_id2').val();
            var $quan = $('#quan2').val();
            var $supp = $('#supplr2').val();
            var $fill = $('#placehere2').val();

            $.post('ajax/orderOperation.php', {
                staff: $staff,
                code: $code,
                quan: $quan,
                supp: $supp,
                action: 'insertOrder2'
            }, function(data, status) {

                swal({
                    title: "Good job!",
                    text: "Product Active Successfully!",
                    icon: "success",
                    button: "Done",
                });

                setTimeout(function() {
                    location.reload();
                }, 2000);

            })

        }

        function saveBtn4() {

            var $staff = $('#staff3').val();
            var $supp = $('#supplr3').val();
            var $code = $('#prod_id3').val();
            var $quan = $('#quan3').val();

            $.post('ajax/orderOperation.php', {
                staff: $staff,
                code: $code,
                quan: $quan,
                supp: $supp,
                action: 'insertInactive'
            }, function(data, status) {

                swal({
                    title: "Good job!",
                    text: "Purchase Product Successfully!",
                    icon: "success",
                    button: "Done",
                });

                setTimeout(function() {
                    location.reload();
                }, 2000);

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

                datas = JSON.parse(data)
                datas.map(function(d) {

                    $('#prod_id2').val(d.ITEM_ID);
                    $('#prodCode2').val(d.ITEM_CODE);
                    $('#prodName2').val(d.ITEM_NAME);
                    $('#quan2').val(d.ITEM_QUANTITY);
                    $('#price2').val(d.ITEM_PRICE);
                })

                $('#btnsss').show();

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

        function mykey4(value) {

            var $onhand = $('#onhand').val();
            var sum;

            if (value != 0) {
                sum = parseInt($onhand) + parseInt(value);
            }
            $('#total_quan').val(sum);

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

        ///////////////////////////////////////////////////////////////

        $('#supplr3').change(function() {
            var $op = $('#supplr3 option:selected').val();
            var $fill = $('#placehere3');

            $.post('ajax/orderOperation.php', {
                catIds: $op,
                action: 'getInactive'
            }, function(data, status) {
                $fill.html(data);
                document.getElementById('placehere3').removeAttribute('disabled');
            })

        })

        $('#placehere3').change(function() {
            var $op = $('#placehere3 option:selected').val();
            var $fill = $('#placeme3');

            $.post('ajax/orderOperation.php', {
                catIds: $op,
                action: 'placeProdinactive'
            }, function(data, status) {
                $fill.html(data);
                $('#btn444').show();

            })

        })
    </script>

</body>

</html>