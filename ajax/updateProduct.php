<?php
session_start();
require_once "../site/csrf.php";
require_once "../site/auth.php";
if (session_expire_if_idle()) {
    http_response_code(401);
    exit('Signed out');
}
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../site/audit.php";
require_once "../site/validate.php";
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
    // Prices and costs are admin-only. Staff can still edit names, photos and reorder levels.
    $priceChanged = round((float)$price, 2) !== round((float)$current['ITEM_PRICE'], 2);
    if ($priceChanged && !is_admin()) {
        back_with('danger', 'Only an admin can change prices. The other changes were not saved either; try again without changing the price.');
    }
    $cost = $current['ITEM_COST'];
    if (is_admin()) {
        $cost = optional_money($_POST['cost'] ?? '');
        if ($cost === false) {
            back_with('danger', 'Enter a cost of ₱0 or more, or leave it empty.');
        }
    }
    $reorderLevel = optional_count($_POST['reorder_level'] ?? '');
    if ($reorderLevel === false) {
        back_with('danger', 'Enter a reorder level of 0 or more, or leave it empty to use the default.');
    }

    [$newImage, $imageError] = save_product_image($_FILES['image'] ?? null);
    if ($imageError) {
        back_with('danger', $imageError);
    }

    $prod->id = $_POST['ids'];
    $prod->prod = $name;
    $prod->price = $price;
    $prod->cost = $cost;
    $prod->reorderLevel = $reorderLevel;
    $prod->desc = trim($_POST['desc'] ?? '');
    $prod->img = $newImage ?? $current['ITEM_IMAGE'];

    if ($prod->UpdateMe()) {
        if ($newImage) {
            delete_product_image($current['ITEM_IMAGE']);
        }
        log_action('product_update', $name . ' #' . $current['ITEM_CODE']);
        if ($priceChanged) {
            log_action('price_change', $name . ' #' . $current['ITEM_CODE'] . ': ₱' . number_format((float)$current['ITEM_PRICE'], 2) . ' → ₱' . number_format((float)$price, 2));
        }
        $costChanged = ($cost === null) !== ($current['ITEM_COST'] === null)
            || ($cost !== null && round((float)$cost, 2) !== round((float)$current['ITEM_COST'], 2));
        if ($costChanged) {
            log_action('cost_change', $name . ' #' . $current['ITEM_CODE'] . ': ' . ($current['ITEM_COST'] === null ? 'none' : '₱' . number_format((float)$current['ITEM_COST'], 2)) . ' → ' . ($cost === null ? 'none' : '₱' . number_format((float)$cost, 2)));
        }
        back_with('success', 'Saved changes to "' . $name . '".');
    }
    back_with('danger', 'The changes could not be saved. Please try again.');
}

header("Location: ../Product.php");
