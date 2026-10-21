<?php

session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../site/validate.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../Model/Order.php";

header('Content-Type: application/json');

// Purchase orders from Order.php. The purchaser is the signed-in user and the
// supplier comes from the product record; neither is taken from the browser.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'createOrder') {
    csrf_verify();

    if (($_SESSION['adminId'] ?? 0) !== 1) {
        http_response_code(403);
        exit(json_encode(['error' => 'Only an admin can create purchase orders. Open Purchase orders and enter the admin password first.']));
    }
    validate(['item_id' => ['required' => true, 'integer' => true, 'positive' => true]], $_POST);
    $mode = (string)($_POST['mode'] ?? '');
    $qty  = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT);
    $error = (new Order())->createPurchase($mode, (int)$_POST['item_id'], $qty === false ? 0 : $qty, (int)($_SESSION['getId'] ?? 0));
    if ($error !== null) {
        json_validation_error($error);
    }
    log_action('order_' . $mode, (string)$_POST['item_id']);
    exit(json_encode(['success' => true]));
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action.']);
