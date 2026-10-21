<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../site/audit.php";
require_once "../site/upload.php";
require_once "../Model/Product.php";
$prod = new Product();

function back_with(string $type, string $message): void
{
    flash($type, $message);
    header("Location: ../Product.php");
    exit;
}

if (($_GET['action'] ?? '') === "insert" && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    csrf_verify();

    $name     = trim($_POST['Product'] ?? '');
    $code     = trim($_POST['Code'] ?? '');
    $price    = $_POST['Price'] ?? '';
    $quantity = $_POST['Quantity'] ?? '';
    $category = $_POST['category'] ?? '';
    $supplier = $_POST['supplier'] ?? '';

    if ($name === '' || $code === '' || $category === '' || $supplier === '') {
        back_with('danger', 'Fill in the product name, code, category and supplier.');
    }
    if (!is_numeric($price) || (float)$price < 0) {
        back_with('danger', 'Enter a price of ₱0 or more.');
    }
    if (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int)$quantity <= 0) {
        back_with('danger', 'Enter a starting quantity of at least 1.');
    }

    $prod->item_NAME = $name;
    $prod->supplier = $supplier;
    if ($prod->getSingleProd() > 0) {
        back_with('danger', '"' . $name . '" already exists for this supplier.');
    }

    [$imagePath, $imageError] = save_product_image($_FILES['image'] ?? null);
    if ($imageError) {
        back_with('danger', $imageError);
    }

    $prod->prod = $name;
    $prod->desc = trim($_POST['Descrip'] ?? '');
    $prod->price = $price;
    $prod->quantity = (int)$quantity;
    $prod->code = $code;
    $prod->img = $imagePath ?? '';
    $prod->category = $category;
    $prod->supplier = $supplier;
    $prod->status = 'pending';

    $prod->subject = $name;
    $prod->msg = "is successfully added recently!";
    $prod->notify();

    if ($prod->insertItem()) {
        log_action('product_insert', $name);
        back_with('success', 'Added "' . $name . '". It is listed under Pending until you activate it.');
    }
    back_with('danger', 'The product could not be saved. Please try again.');
}

header("Location: ../Product.php");
