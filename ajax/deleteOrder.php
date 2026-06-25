

<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once "../Model/Order.php";
$ord = new Order();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['save'])) {

        $ord->id = $_POST['id'];

        if ($ord->deleteData()) {
            header("Location: ../Order.php");
        };
    }
}

?>
