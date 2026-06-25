<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
require_once "../site/audit.php";
require_once "../Model/Product.php";
$prod = new Product();

if ($_GET['action'] === "insert") {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();

        if (isset($_POST['save'])) {

            $prod->item_NAME = $_POST['Product'];
            $prod->supplier = $_POST['supplier'];
            $name = $prod->getSingleProd();
            $quan = $_POST['Quantity'];

            $prod->item_NAME = $_POST['Product'];
            $exist = $prod->getSingleProd();
            if ($quan <= 0) {
                $_SESSION['PRODNAME2'] = "QUANTITY IS TO SHORT!";
                header("Location: ../Product.php");
                exit;
            } else if ($exist > 0) {
                $_SESSION['PRODNAME'] = "PRODUCT IS ALREADY EXIST!";
                header("Location: ../Product.php");
                exit;
            } else {

                if (!is_dir("../images")) {
                    mkdir("../images");
                }

                function randoms($n)
                {
                    $charac = "123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    $str = "";
                    for ($i = 0; $i < $n; $i++) {
                        $index = rand(0, strlen($charac) - 1);
                        $str .= $charac[$index];
                    }
                    return $str;
                }

                $image = $_FILES['image'] ?? null;

                $fullpath = "";

                if ($image && $image['tmp_name']) {

                    $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $allowed_ext  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    $max_bytes    = 5 * 1024 * 1024;

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $image['tmp_name']);
                    finfo_close($finfo);
                    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

                    if (!in_array($mime, $allowed_mime) || !in_array($ext, $allowed_ext)) {
                        $_SESSION['PRODNAME'] = "Only image files (jpg, png, webp, gif) are allowed.";
                        header("Location: ../Product.php");
                        exit;
                    }
                    if ($image['size'] > $max_bytes) {
                        $_SESSION['PRODNAME'] = "Image must be under 5 MB.";
                        header("Location: ../Product.php");
                        exit;
                    }

                    $path = "../images/";
                    $folder = randoms(8) . "/";
                    $ImName = $image['name'];

                    $fullpath = "images/" . $folder . $ImName;
                    mkdir(dirname($path . $folder . $ImName));

                    move_uploaded_file($image['tmp_name'], $path . $folder . $ImName);
                }

                $catId = $_POST['category'];
                $suppId = $_POST['supplier'];
                $prod->prod = $_POST['Product'];
                $prod->desc = $_POST['Descrip'];
                $prod->price = $_POST['Price'];
                $prod->quantity = $_POST['Quantity'];
                $prod->code = $_POST['Code'];
                $prod->img = $fullpath;
                $prod->category = $catId;
                $prod->supplier = $suppId;
                $prod->status = $_POST['status'];

                $mg = "is successfully added recently!";
                $prod->subject = $_POST['Product'];
                $prod->msg = $mg;
                $prod->notify();

                if ($prod->insertItem()) {
                    log_action('product_insert', $_POST['Product'] ?? '');
                    $_SESSION['Saves'] = "PRODUCT IS SUCCESSFULLY ADDED!";
                    header("Location: ../Product.php");
                    exit;
                };
            }
        }
    }
}
