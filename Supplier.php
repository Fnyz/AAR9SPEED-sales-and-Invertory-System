<?php
$page = "supplier";
include 'site/protected.php';
require_once "./Model/Supplier.php";

$supp =  new Supplier();
$profile = $supp->showCustomer();

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

        <section id="con">
            <h2>SUPPLIER</h2>
            <div>

                <table id="example" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>

                            <th>Company</th>
                            <th>Address</th>
                            <th>PhoneNumber</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($profile as $value) : extract($value) ?>
                            <tr>

                                <td><?php echo $SUPPLIER_COMPANY ?></td>
                                <td><?php echo $SUPPLIER_ADDRESS ?></td>
                                <td><?php echo $SUPPLIER_PHONENUM ?></td>

                                <td class="butt">
                                    <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="getId(<?php echo $SUPPLIER_ID ?>)"><i class="fa-regular fa-pen-to-square"></i></a></i>

                                    <a href="" data-bs-toggle="modal" data-bs-target="#View">
                                        <i class="fa-sharp fa-solid fa-eye-slash" onclick="viewSingle(<?php echo $SUPPLIER_ID ?>)"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="sideBtn">

                    <!-- Button trigger modal -->

                    <div id="addBtn">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            <i class="fa-solid fa-plus"></i>ADD SUPPLIER
                        </button>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">ADD SUPPLIER</h1>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">First name:</label>
                                        <input type="text" class="form-control" placeholder="Name" id="Fname" name="name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Last name:</label>
                                        <input type="text" class="form-control" placeholder="Lastname" id="Lname" name="lastname">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone number:</label>
                                        <input type="number" class="form-control" placeholder="Phone number" id="Pnum" name="phone">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Company name:</label>
                                        <input type="text" class="form-control" placeholder="COMPANY NAME" id="comp">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Location:</label>
                                        <input type="text" class="form-control" placeholder="Address" id="address" name="address">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary save" onclick="saveBtn()">Save</button>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
        </section>

    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="ViewUpdate">

            </div>
        </div>
    </div>

    <!-- View -->
    <div class="modal fade" id="View" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">INFORMATION</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="expand">
                        <div class="card-body" id="ViewPage">

                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
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

            var $name = $('#Fname').val();
            var $last = $('#Lname').val();
            var $phone = $('#Pnum').val();
            var $company = $('#comp').val();
            var $add = $('#address').val();

            if ($name == "" || $last == "" || $phone == "" || $company == "" || $add == "") {
                swal({
                    title: "Warning!",
                    text: "Please input all fields...",
                    icon: "error",
                    button: "OK",
                });

            } else {
                $.post('ajax/operation.php', {
                    name: $name,
                    lastname: $last,
                    address: $add,
                    comp: $company,
                    phone: $phone,
                    action: 'insertSupp'
                }, function(data, status) {

                    location.reload();
                    $('#Fname').val("");
                    $('#Lname').val("");
                    $('#Pnum').val("");
                    $('#email').val("");
                    $('#address').val("");

                    swal({
                        title: "Good job!",
                        text: "Supplier is added successfully!",
                        icon: "success",
                        button: "Done",
                    });

                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                })
            }

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

        function deleteOne(id) {

            $.post('ajax/operation.php', {

                Cus_id: id,
                action: 'deleteSupp'
            }, function(data, status) {

                location.reload();
                console.log(status);
            })
        }

        function getId(id) {

            var thing = $('#ViewUpdate');

            $.get('ajax/operation.php', {

                Cus_id: id,
                action: 'getSingleSupp'
            }, function(data, status) {

                thing.html(data);
            })
        }

        function viewSingle(id) {

            var thing = $('#ViewPage');

            $.get('ajax/operation.php', {

                id: id,
                action: 'viewSingleSupp'
            }, function(data, status) {

                thing.html(data);
            })
        }

        function updateME(id) {

            var $name = $('#fname').val();
            var $last = $('#lname').val();
            var $add = $('#add').val();
            var company = $('#compa').val();
            var $phone = $('#nums').val();

            $.post('ajax/operation.php', {
                id: id,
                name: $name,
                lastname: $last,
                address: $add,
                company: company,
                phone: $phone,
                action: 'updatingSupp'
            }, function(data, status) {

                swal({
                    title: "Good job!",
                    text: "Updated successfully!",
                    icon: "success",
                    button: "Done",
                });

                setTimeout(function() {
                    location.reload();
                }, 1000);

            })

        }
    </script>

</body>

</html>