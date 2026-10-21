<?php

// Over HTTPS, the session cookie is only ever sent over HTTPS.
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $isHttps]);
session_start();
require_once "./site/csrf.php";
csrf_init();

if (isset($_SESSION['userId'])) {
    header('location:Dashboard.php');
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

        $value = $stmt->rowCount() == 1 ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        // Plain-text passwords are no longer accepted; run
        // `php migrations/004_hash_passwords.php` once to convert old accounts.
        if ($value && password_verify($password, $value['staff_pass'])) {
            session_regenerate_id(true);
            $_SESSION['userId'] = $username;
            $_SESSION['getId'] = $value['STAFF_ID'];
            header('location:Dashboard.php');
            exit;
        } else {
            $errors[] = "Incorrect username or password. Please try again.";
        }
    }

}

$pageTitle = 'Sign in';
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">

<head>
	<?php include 'site/layout/head.php' ?>
</head>

<body class="d-flex flex-column">
	<div class="position-absolute top-0 end-0 p-3">
		<button type="button" class="btn btn-icon hide-theme-dark" data-theme-set="dark" aria-label="Switch to dark mode" title="Switch to dark mode"><i class="ti ti-moon"></i></button>
		<button type="button" class="btn btn-icon hide-theme-light" data-theme-set="light" aria-label="Switch to light mode" title="Switch to light mode"><i class="ti ti-sun"></i></button>
	</div>

	<div class="page page-center">
		<div class="container container-tight py-4">
			<div class="text-center mb-4">
				<span class="d-inline-flex align-items-center gap-2 fs-1">
					<span class="brand-mark" aria-hidden="true">A9</span>
					<span class="brand-name">AAR9<span>SPEED</span></span>
				</span>
			</div>

			<div class="card card-md">
				<div class="card-body">
					<h1 class="h2 text-center mb-1">Sign in to your account</h1>
					<p class="text-center text-secondary mb-4">Sales &amp; Inventory System</p>

					<?php foreach ($errors as $message) : ?>
						<div class="alert alert-danger" role="alert"><?php echo $e($message) ?></div>
					<?php endforeach; ?>

					<form action="login.php" method="post">
						<input type="hidden" name="csrf_token" value="<?php echo $e($_SESSION['csrf_token']) ?>">
						<div class="mb-3">
							<label class="form-label" for="username">Username</label>
							<input type="text" class="form-control" id="username" name="username" autocomplete="username" value="<?php echo $e($_POST['username'] ?? '') ?>" required autofocus>
						</div>
						<div class="mb-3">
							<label class="form-label d-flex justify-content-between" for="password">
								Password
								<span class="form-label-description fw-normal">Forgot it? Ask an admin to reset it.</span>
							</label>
							<div class="input-group input-group-flat">
								<input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
								<span class="input-group-text">
									<button type="button" class="btn btn-link link-secondary p-0" id="toggle-password" aria-label="Show password" title="Show password"><i class="ti ti-eye"></i></button>
								</span>
							</div>
						</div>
						<div class="form-footer">
							<button type="submit" class="btn btn-primary w-100">Sign in</button>
						</div>
					</form>
				</div>
			</div>
			<p class="text-center text-secondary small mt-3">AAR9SPEED · Cebu City</p>
		</div>
	</div>

	<script>
		document.getElementById('toggle-password').addEventListener('click', function () {
			var input = document.getElementById('password');
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
			this.setAttribute('title', show ? 'Hide password' : 'Show password');
			this.querySelector('i').className = show ? 'ti ti-eye-off' : 'ti ti-eye';
		});
		document.querySelectorAll('[data-theme-set]').forEach(function (button) {
			button.addEventListener('click', function () {
				var theme = button.getAttribute('data-theme-set');
				document.documentElement.setAttribute('data-bs-theme', theme);
				try { localStorage.setItem('aar9-theme', theme); } catch (e) {}
			});
		});
	</script>
</body>

</html>