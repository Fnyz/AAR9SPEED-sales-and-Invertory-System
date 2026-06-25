<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once "../Model/Order.php";
$order = new Order();

if ($_GET['action'] === "insert") {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

        if (isset($_POST['save'])) {

            $staffId = $_POST['staff'];
            $order->Item_id = $_POST['Code'];
            $order->Staff_id = $staffId;
            $order->quan = $_POST['Quantity'];

            if ($order->inserted()) {
                header("Location: ../Order.php");
            };
        }
    }
}
