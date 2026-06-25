

<?php
session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../Model/Product.php";
$prod = new Product();

if ($_GET['action'] === "update") {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();

        $prod->id = $_POST['id'];
        $val = $prod->getSingle();

        if (isset($_POST['save'])) {

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
            $fullpath = $val['ITEM_IMAGE'];

            if ($image && $image['tmp_name']) {

                $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $allowed_ext  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $max_bytes    = 5 * 1024 * 1024;

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $image['tmp_name']);
                finfo_close($finfo);
                $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

                if (!in_array($mime, $allowed_mime) || !in_array($ext, $allowed_ext)) {
                    $_SESSION['updated'] = "Only image files (jpg, png, webp, gif) are allowed.";
                    header("Location: ../Product.php");
                    exit;
                }
                if ($image['size'] > $max_bytes) {
                    $_SESSION['updated'] = "Image must be under 5 MB.";
                    header("Location: ../Product.php");
                    exit;
                }

                if ($val['ITEM_IMAGE'] && file_exists($val['ITEM_IMAGE'])) {
                    unlink($val['ITEM_IMAGE']);
                }

                $path = "../images/";
                $folder = randoms(8) . "/";
                $ImName = $image['name'];

                $fullpath = "images/" . $folder . $ImName;
                mkdir(dirname($path . $folder . $ImName));

                move_uploaded_file($image['tmp_name'], $path . $folder . $ImName);
            }

            $prod->id = $_POST['ids'];
            $prod->prod = $_POST['prod'];
            $prod->price = $_POST['price'];
            $prod->desc = $_POST['desc'];
            $prod->img = $fullpath;

            if ($prod->UpdateMe()) {
                log_action('product_update', (string)($_POST['ids'] ?? ''));
                $_SESSION['updated'] = "PRODUCT IS UPDATED SUCCESSFULL!";
                header("Location: ../Product.php");
                exit;
            };
        }
    }
}

?>
