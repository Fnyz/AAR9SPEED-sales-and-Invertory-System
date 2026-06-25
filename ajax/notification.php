<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}

include_once "../Model/Notification.php";

$notify = new Notification();

if (isset($_POST['view'])) {
    csrf_verify();

    $depleted = '';
    if (count($notify->showdepleted()) > 0) {
        $r = $notify->showdepleted();

        foreach ($r as $val) {
            extract($val);

            $depleted .= '
            <li>
            <a class="dropdown-item" href="../Product.php">
<strong class="text text-primary">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</strong> <em> is <small class="text text-success">depleted </small> product.</a>
            </li></em>
            ';
        }
    }

    $pending = '';
    if (count($notify->showPending()) > 0) {
        $s = $notify->showPending();

        foreach ($s as $val) {
            extract($val);

            $pending .= '
            <li>
            <a class="dropdown-item" href="../pendingProduct.php"><strong class="text text-success">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</strong><em> is a <small class="text text-danger"> pending </small> product. </a>
            </li></em>
            ';
        }
    }

    $inactive = '';
    if (count($notify->showInactive()) > 0) {
        $s = $notify->showInactive();

        foreach ($s as $val) {
            extract($val);

            $inactive .= '
            <li>
            <a class="dropdown-item" href="../InactiveProduct.php"><strong class="text text-pink">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</strong><em> is an <small class="text text-danger"> inactive </small> product. </a>
            </li></em>
            ';
        }
    }

    $count = $notify->count();

    $data = array(
        'depleted' => $depleted,
        'pending' => $pending,
        'inactive' => $inactive,
        'unseen' => $count
    );
    echo json_encode($data);
}

?>
