<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once "site/title.php" ?>
    <link rel="stylesheet" href="styles/Cust.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles/des.css">
    <?php require_once "styles/stylish.php"; ?>
</head>

<body>

    <?php include 'site/hamburger.php' ?>
    <?php include 'site/sidebar.php' ?>
    <?php include_once "site/dataUser.php" ?>

    <div id="particles-js">
        <div class="Box">
            <form action="" method="post">
                <div class="Box">
                    <img src="Img/Logo.png" srcset="" width="400px">
                    INPUT PASSWORD TO OPEN
                    <?php echo csrf_field(); ?>
                    <input type="password" placeholder="PASSWORD" name="password" autocomplete="current-password">
                    <button type="submit">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript" src="particles.js"></script>
    <script type="text/javascript" src="app.js"></script>
    <?php require_once "scripts/jquaryScript.php" ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="./scripts/sweetalert.min.js"></script>

    <?php if ($gate_message): ?>
    <script>
        swal({
            title: "Invalid Credentials!",
            text: "Only admin can access this page.",
            icon: "error",
            button: "OK",
        });
    </script>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
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
