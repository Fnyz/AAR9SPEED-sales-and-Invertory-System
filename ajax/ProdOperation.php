<?php

session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../site/validate.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../Model/Product.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'addStock') {
    csrf_verify();

    // Restock from the Products page: adds to the current quantity on the server.
    validate([
        'item_id'  => ['required' => true, 'integer' => true, 'positive' => true],
        'quantity' => ['required' => true, 'integer' => true, 'positive' => true],
    ], $_POST);
    $newQty = (new Product())->addStock((int)$_POST['item_id'], (int)$_POST['quantity']);
    if ($newQty === null) {
        json_validation_error('That product no longer exists. Reload the page and try again.');
    }
    log_action('product_restock', $_POST['item_id'] . ' +' . (int)$_POST['quantity']);
    echo json_encode(['quantity' => $newQty]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action.']);
