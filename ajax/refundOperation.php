<?php
session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../site/validate.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../Model/Refund.php";

$refund = new Refund();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($_POST['action'] === 'processRefund') {
        validate([
            'trans_code' => ['required' => true, 'max' => 100],
            'item_id'    => ['required' => true, 'integer' => true, 'positive' => true],
            'quantity'   => ['required' => true, 'integer' => true, 'positive' => true],
        ], $_POST);

        $refund->trans_code  = $_POST['trans_code'];
        $refund->item_id     = (int)$_POST['item_id'];
        $refund->quantity    = (int)$_POST['quantity'];
        $refund->reason      = $_POST['reason'] ?? '';
        $refund->refunded_by = $_SESSION['userId'];

        if ($refund->processRefund()) {
            log_action('refund_process', $_POST['trans_code'] . ' item:' . $_POST['item_id']);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Refund could not be processed.']);
        }
    }
}
