<?php
session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../site/validate.php";
require_once "../site/auth.php";
if (session_expire_if_idle()) {
    http_response_code(401);
    exit('Signed out');
}
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../Model/Refund.php";

$refund = new Refund();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($_POST['action'] === 'processRefund') {
        require_admin_json('process refunds');
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

        header('Content-Type: application/json');
        if ($refund->processRefund()) {
            log_action('refund_process', $_POST['trans_code'] . ' item:' . $_POST['item_id']);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(422);
            echo json_encode(['error' => $refund->error ?? 'Refund could not be processed.']);
        }
    }

    // Items on an invoice, for the refund form.
    if ($_POST['action'] === 'invoiceLines') {
        validate(['trans_code' => ['required' => true, 'max' => 100]], $_POST);
        $lines = $refund->invoiceLines(trim($_POST['trans_code']));
        header('Content-Type: application/json');
        if (!$lines) {
            http_response_code(422);
            echo json_encode(['error' => 'No invoice found with the code "' . trim($_POST['trans_code']) . '".']);
            exit;
        }
        echo json_encode(['lines' => array_map(fn($l) => [
            'item_id'    => (int)$l['ITEM_ID'],
            'name'       => $l['ITEM_NAME'],
            'code'       => $l['ITEM_CODE'],
            'sold'       => (int)$l['sold'],
            'refunded'   => (int)$l['refunded'],
            'refundable' => (int)$l['refundable'],
            'unit_price' => round((float)$l['unit_price'], 2),
        ], $lines)]);
    }
}
