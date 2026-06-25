
<?php

session_start();
require_once "../site/csrf.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once "../site/audit.php";
require_once "../site/mailer.php";
require_once "../Model/Product.php";
require_once "../Model/Order.php";

$prod = new Product();
$order = new Order();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($_POST['action'] === 'insertOrder') {

        $order->ids = $_POST['code'];
        $order->spp_id = $_POST['supp'];
        $order->newQuan = $_POST['quan'];
        $order->UpdateMe();

        $order->Item_id = $_POST['code'];
        $order->Staff_id = $_POST['staff'];
        $order->quan = $_POST['quan'];
        $order->inserted();
        log_action('order_insert', (string)($_POST['code'] ?? ''));
        $db = new Database(); $conn = $db->getConnect();
        $s = $conn->prepare("SELECT ITEM_NAME, ITEM_QUANTITY FROM items WHERE ITEM_ID = :id");
        $s->execute([':id' => $_POST['code']]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['ITEM_QUANTITY'] <= (int)($_ENV['LOW_STOCK_THRESHOLD'] ?? 5)) {
            send_low_stock_alert($row['ITEM_NAME'], (int)$row['ITEM_QUANTITY']);
        }
    }

    if ($_POST['action'] === 'insertOrder2') {

        $order->ids2 = $_POST['code'];
        $order->spp_id2 = $_POST['supp'];
        $order->UpdateMe2();

        $order->Item_id = $_POST['code'];
        $order->Staff_id = $_POST['staff'];
        $order->quan = $_POST['quan'];
        $order->inserted();
        log_action('order_insert2', (string)($_POST['code'] ?? ''));
    }

    if ($_POST['action'] === 'insertInactive') {

        $order->ids = $_POST['code'];
        $order->spp_id = $_POST['supp'];
        $order->quan = $_POST['quan'];
        $order->UpdateMe3();

        $order->Item_id = $_POST['code'];
        $order->Staff_id = $_POST['staff'];
        $order->quan = $_POST['quan'];
        $order->inserted2();
        log_action('order_inactive_insert', (string)($_POST['code'] ?? ''));
    }

    if ($_POST['action'] === 'getMetheProduct') {

        $order->supp_id = $_POST['catIds'];
        $r = $order->getPendingOnly();

        echo '<option selected disabled="">Choose...</option>';
        foreach ($r as $val) {
            extract($val);
            echo '<option value="' . $ITEM_ID . '">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }

    if ($_POST['action'] === 'getInactive') {

        $order->supp_id = $_POST['catIds'];
        $r = $order->getInactiveOnly();

        echo '<option selected disabled="">Choose...</option>';
        foreach ($r as $val) {
            extract($val);
            echo '<option value="' . $ITEM_ID . '">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }

    if ($_POST['action'] === 'getProductss') {

        $prod->supp_id = $_POST['catId'];
        $r = $prod->getITEM();

        echo '<option selected disabled="">Choose...</option>';
        foreach ($r as $val) {
            extract($val);
            echo '<option value="' . $ITEM_ID . '">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }

    if ($_POST['action'] === 'placeProd') {

        $prod->place_id = $_POST['catId'];
        $r = $prod->placeInput();
        echo json_encode($r);
    }

    if ($_POST['action'] === 'placeProd2') {

        $prod->place_id = $_POST['catId'];
        $r = $prod->placeInput();
        echo json_encode($r);
    }

    if ($_POST['action'] === 'filter') {

        $prod->id = $_POST['catId'] ?? 'No record! found!';
        $res = $prod->filterItem();

        foreach ($res as $value) {
            extract($value);

            echo '<tr>';
            echo '<td>' . htmlspecialchars($ITEM_CODE, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($ITEM_PRICE, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($ITEM_DESC, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td class="butt">
                                <form action="ProductView.php?action=view" method="post">
                                <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="getId(&quot;' . htmlspecialchars($ITEM_CODE, ENT_QUOTES, 'UTF-8') . '&quot;)"><i class="fa-regular fa-pen-to-square"></i></a>
                                    <input type="hidden" value="' . htmlspecialchars($CATEGORY_ID, ENT_QUOTES, 'UTF-8') . '" name="id">
                                    <button type="submit" class="btn btn-success">DETAILS
                                    </button>
                                </form>
                   </td>';
            echo '</tr>';
        }
    }

    if ($_POST['action'] === 'delete') {

        $prod->id = $_POST['prodId'];
        $prod->deleteData();
    }

    if ($_POST['action'] === 'pass') {

        $order->id = $_POST['catId'];
        $pr =  $order->getProds();

        echo '  <option selected disabled="">Choose...</option>';

        foreach ($pr as $val) {
            extract($val);
            echo ' <option value="' . htmlspecialchars($ITEM_CODE, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }

    if ($_POST['action'] === 'updating') {

        $prod->id = $_POST['id'];
        $prod->prod = $_POST['Pname'];
        $prod->price = $_POST['price'];
        $prod->desc = $_POST['desc'];
        $prod->quantity = $_POST['quantity'];
        $prod->UpdateMe();
    }

    if ($_POST['action'] === 'getSingleProd') {

        $prod->id = $_POST['ProdId'];
        $val = $prod->getSingle();

        extract($val);

        $div = '

        <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">UPDATE CUSTOMER</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                         PRODUCT NAME:
                        <input type="text" class="form-control" placeholder="Name" value="' . htmlspecialchars($ITEM_CODE, ENT_QUOTES, 'UTF-8') . '" disabled>
                    </div>
                    <div class="mb-3">
                         PRODUCT NAME:
                        <input type="text" class="form-control" placeholder="Name" value="' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '" id="prodName">
                    </div>
                    <div class="mb-3">
                        PRODUCT_PRICE
                        <input type="number" class="form-control" placeholder="Lastname" value="' . htmlspecialchars($ITEM_PRICE, ENT_QUOTES, 'UTF-8') . '" id="prodPrice">
                    </div>
                    <div class="mb-3">
                        PRODUCT_DESC
                        <input type="text" class="form-control" placeholder="Phone number" value="' . htmlspecialchars($ITEM_DESC, ENT_QUOTES, 'UTF-8') . '" id="prodDesc">
                    </div>
                    <div class="mb-3">
                        PRODUCT_QUANTITY
                        <input type="email" class="form-control" placeholder="Email" value="' . htmlspecialchars($TOTAL_COUNT, ENT_QUOTES, 'UTF-8') . '" id="prodQuan">
                    </div>
                    <div class="mb-3">
                        PRODUCT_QUANTITY
                        <input type="email" class="form-control" placeholder="Email" value="' . htmlspecialchars($ITEM_CREATED_AT, ENT_QUOTES, 'UTF-8') . '" disabled>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateME(' . (int)$ITEM_ID . ')">Save changes</button>
                </div>

        ';

        echo $div;
    }

    if ($_POST['action'] === 'placeProdinactive') {

        $prod->place_id = $_POST['catIds'];
        $r = $prod->placeInput2();

        foreach ($r as $val) {

            extract($val);

            $div = '

             <div class="mb-3">
                                         <label for="">PRODUCT ID:</label>
                                            <input type="number" class="form-control" value="' . (int)$ITEM_ID . '"  id="prod_id3" disabled>
                                        </div>

        <div class="mb-3">

                                            <label for="">PRODUCT CODE:</label>
                                            <input type="text" class="form-control" value="' . htmlspecialchars($ITEM_CODE, ENT_QUOTES, 'UTF-8') . '" placeholder="Product Code" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="">PRODUCT NAME:</label>
                                            <input type="text" class="form-control"  value="' . htmlspecialchars($ITEM_NAME, ENT_QUOTES, 'UTF-8') . '" placeholder="Product Name" disabled>

                                           <div class="mb-3">
                                            <label for="">QUANTITY ONHAND:</label>
                                            <input type="number" class="form-control " placeholder="On Hand" id="onhand"   value="' . (int)$ITEM_QUANTITY . '" disabled>
                                        </div>

                                        <div class="mb-3">
                                          <label for="">INPUT QUANTITY:</label>
                                            <input type="number" class="form-control " placeholder="INPUT QUANTITY" onkeyup="mykey4(this.value)" name="ProdQuan" id="quan3" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="">TOTAL QUANTITY:</label>
                                            <input type="number" class="form-control " placeholder="Quantity" id="total_quan" name="ProdQuan" value= "0" disabled>
                                        </div>

</div>
                                           <div class="mb-3">
                                            <label for="">PRODUCT PRICE:</label>
                                            <input type="number" class="form-control " placeholder="On Hand" id="price"   value="' . htmlspecialchars($ITEM_PRICE, ENT_QUOTES, 'UTF-8') . '" disabled>
                                        </div>

                                         </div>

        ';

            echo $div;

            break;
        }
    }
}
?>
