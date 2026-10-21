<?php
session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../site/validate.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../Model/Sale.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    csrf_verify();

    $lines = json_decode((string)($_POST['lines'] ?? '[]'), true);
    if (!is_array($lines)) {
        json_validation_error('The sale could not be read. Reload the page and try again.');
    }
    $cash = (string)($_POST['cash'] ?? '0');
    if ($cash !== '' && !is_numeric($cash)) {
        json_validation_error('Enter the cash received as a number.');
    }

    $result = (new Sale())->checkout(
        $lines,
        (int)($_POST['customer_id'] ?? Sale::WALK_IN_CUSTOMER),
        (int)($_SESSION['getId'] ?? 0),
        (string)($_POST['method'] ?? 'cash'),
        (float)$cash,
        (string)($_POST['reference'] ?? '')
    );
    if (isset($result['error'])) {
        json_validation_error($result['error']);
    }
    log_action('sale', $result['code'] . ' ₱' . number_format($result['total'], 2));
    echo json_encode(['code' => $result['code']]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action.']);
