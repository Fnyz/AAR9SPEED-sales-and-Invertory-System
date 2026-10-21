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

if (($_GET['action'] ?? '') === "update" && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    csrf_verify();

    $prod->id = $_POST['id'] ?? '';
    $current = $prod->getSingle();
    if (!$current) {
        back_with('danger', 'That product no longer exists.');
    }

    $name  = trim($_POST['prod'] ?? '');
    $price = $_POST['price'] ?? '';
    if ($name === '') {
        back_with('danger', 'Enter a product name.');
    }
    if (!is_numeric($price) || (float)$price < 0) {
        back_with('danger', 'Enter a price of ₱0 or more.');
    }

    [$newImage, $imageError] = save_product_image($_FILES['image'] ?? null);
    if ($imageError) {
        back_with('danger', $imageError);
    }

    $prod->id = $_POST['ids'];
    $prod->prod = $name;
    $prod->price = $price;
    $prod->desc = trim($_POST['desc'] ?? '');
    $prod->img = $newImage ?? $current['ITEM_IMAGE'];

    if ($prod->UpdateMe()) {
        if ($newImage) {
            delete_product_image($current['ITEM_IMAGE']);
        }
        log_action('product_update', (string)($_POST['ids'] ?? ''));
        back_with('success', 'Saved changes to "' . $name . '".');
    }
    back_with('danger', 'The changes could not be saved. Please try again.');
}

header("Location: ../Product.php");
