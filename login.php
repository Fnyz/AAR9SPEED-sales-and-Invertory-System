<?php

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
require_once "./site/csrf.php";
csrf_init();

if (isset($_SESSION['userId'])) {
    header('location:dashboard.php');
    exit;
}

require_once "./config/Database/Database.php";

$db = new Database();
$conn = $db->getConnect();

$errors = array();

if ($_POST) {

    csrf_verify();

    $username = str_replace(' ', '', $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "All fields are required, please try again!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM staff WHERE STAFF_USER = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $value = $stmt->fetch(PDO::FETCH_ASSOC);
            $stored = $value['staff_pass'];
            $authenticated = false;

            if (password_verify($password, $stored)) {
                $authenticated = true;
            } elseif ($password === $stored) {
                // Plaintext password — rehash transparently
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE staff SET staff_pass = :hash WHERE STAFF_USER = :username");
                $upd->bindParam(':hash', $newHash);
                $upd->bindParam(':username', $username);
                $upd->execute();
                $authenticated = true;
            }

            if ($authenticated) {
                $_SESSION['userId'] = $username;
                $_SESSION['getId'] = $value['STAFF_ID'];
                header('location:dashboard.php');
                exit;
            } else {
                $errors[] = "Incorrect username/password combination!, please try again!";
            }
        } else {
            $errors[] = "Username does not exist!";
        }
    }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles/des.css">

	<?php include_once "./site/title.php" ?>
	<script src="./scripts/sweetalert.min.js"></script>
	<title>Document</title>
</head>

<body>

	<div id="particles-js">

		<form action=" <?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<div class="Box">

				<img src="Img/Logo.png" srcset="" width="400px">

				<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
				<input type="text" placeholder="USERNAME" name="username" autocomplete="off">
				<input type="password" placeholder="PASSWORD" name="password" autocomplete="off">
				<div class="messages">
					<?php if ($errors) : ?>

						<?php foreach ($errors as $key => $value) : ?>

							<script>
								swal({

									title: "INVALID CREDINTIALS",
									text: <?php echo json_encode($value) ?>,
									button: "Okay",
								});
							</script>

						<?php endforeach; ?>

					<?php endif; ?>

				</div>
				<button type="submit">LOGIN</button>
			</div>
		</form>

	</div>

	<script type="text/javascript" src="particles.js"></script>
	<script type="text/javascript" src="app.js"></script>

</body>

</html>