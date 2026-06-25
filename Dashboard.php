<?php
$page = "dashboard";
session_start();

if (!isset($_SESSION['userId'])) {
    header('location:login.php');
}

include_once './Model/Dashboard.php';

$dash = new Dashboard();
$noti = $dash->showDeplated();
$sales = $dash->sales();
$trans = $dash->transac();
$best = $dash->best_salers();

if (isset($_SESSION['getId'])) {
    $dash->id = $_SESSION['getId'];
    $res = $dash->getStafftoUpdate();
}

$salers = [];
$product = [];
foreach ($best as $val) {

    $product[] = $val['ITEM_NAME'];
    $salers[] = $val['best_salers'];
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_once "./site/title.php" ?>
    <link rel="stylesheet" href="./styles/styleDash.css">
    <?php
    require_once "styles/stylish.php";
    ?>
    <link rel="stylesheet" href="./styles/loader.css">
    <link href="https://fonts.googleapis.com/css2?family=Moon+Dance&family=Nunito:wght@800&display=swap" rel="stylesheet">
</head>

<body>

    <?php include 'site/hamburger.php' ?>
    <?php include 'site/sidebar.php' ?>
    <?php include 'site/loads.php' ?>

    <div class="content">
        <h1 class="title">DASHBOARD</h1>
        <section class="container center">
            <div>
                <label style=" color:#222f3e; display:flex;
                justify-content:center; align-items:center;  width:100%; height:100%; padding:3px; gap:5px;">

                    <span class="width:100%; height:100%; margin:105;">
                        <h3 style="font-weight:bold; font-size:3rem;">SALES TOTAL:</h3>
                        <?php foreach ($sales as $total) : extract($total) ?>
                            <p style="color:#c0392b; font-size:30px;">₱<?php echo number_format($tsales, 2, ".", ","); ?>
                                <i style="color:#222f3e;" class="fa-solid fa-cart-shopping"></i>
                            </p>

                        <?php break;
                        endforeach; ?>

                    </span>

                </label>
            </div>
            <div>

                <label style=" color:#222f3e; display:flex;
                justify-content:center; align-items:center;  width:100%; height:100%; padding:3px; gap:5px;">
                    <?php foreach ($trans as $total) : extract($total) ?>
                        <span class="width:100%; height:100%; margin:105;">
                            <h3 style="font-weight:bold; font-size:2rem;">TRANSACTION TOTAL: </h3>
                            <p style="color:#c0392b; font-size:30px;"><?php echo $tTrans; ?>
                                <i style="color:#222f3e;" class="fa-solid fa-receipt"></i>
                            </p>

                        </span>
                    <?php break;
                    endforeach; ?>
                </label>

            </div>
            <div>
                <canvas id="myChart"></canvas>
            </div>
            <div>
                <span class="notifying" style="color:#222f3e;">NOTIFICATION</span>
                <label for="" style="padding:10px;">
                    <p>
                        <?php foreach ($noti as $val) : extract($val) ?>
                            <b class=" text fw-bold text-uppercase text-danger fst-italic" style="margin-top: 10px;"><?php echo $subject ?> </b> <span style="margin-top: 10px;"><?php echo $message ?></span> <br>
                        <?php endforeach; ?>

                    </p>
                </label>

            </div>
        </section>

    </div>
    <?php include_once "./site/dataUser.php" ?>

    <script type="text/javascript" src="particles.js"></script>
    <script type="text/javascript" src="app.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="./scripts/sweetalert.min.js"></script>
    <?php

    if (isset($_SESSION['success'])) {
    ?>
        <script>
            swal({
                title: "WELL DONE!",
                text: "DATA UPDATED SUCCESSFULL.",
                icon: "success",
                button: "Done",
            });
        </script>

    <?php

        echo $_SESSION['success'];
        unset($_SESSION['success']);
    }

    ?>

    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="popover"]').popover();

            function load() {
                document.querySelector('.load').classList.add('loader--hidder');
            }
            var c = setInterval(load, 3000);

            document.querySelector('.load').addEventListener("transitionend", () => {
                document.body.removeChild(document.querySelector('.load'));

                clearInterval(c);
            })
            notify();

        });
        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($product) ?>,
                datasets: [{
                    label: 'BEST SELLERS PRODUCT',
                    data: <?php echo json_encode($salers) ?>,
                    borderWidth: 1,
                    backgroundColor: [
                        'rgba(225,99,132, 0.6)',
                        'rgba(54,162, 235, 0.6)',
                        'rgba(225,206,86, 0.6)',
                        'rgba(75,192,192, 0.6)',
                        'rgba(153,102,255, 0.6)'
                    ],

                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
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

                    $('.dropdown-menu').html(data.depleted + data.pending + data.inactive);
                    if (data.unseen != 0) {
                        $('#pendings').text(data.unseen);

                    } else {
                        $('#pendings').hide();
                    }

                }
            })
        }
    </script>
</body>

</html>