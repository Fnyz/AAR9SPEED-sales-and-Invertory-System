
<?php

session_start();
require_once "../site/csrf.php";
require_once "../site/audit.php";
require_once "../site/validate.php";
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once "../Model/Customer.php";
require_once "../Model/Supplier.php";
require_once "../Model/Staff.php";
require_once "../Model/Category.php";

$cust  = new Customer();
$supp  = new Supplier();
$staff = new Staff();
$cat   = new Category();

/* CUSTOMER OPERATION */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($_POST['action'] === 'insert') {
        validate([
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'email'    => ['email' => true, 'max' => 100],
            'phone'    => ['max' => 20],
            'address'  => ['max' => 100],
        ], $_POST);
        $cust->name     = $_POST['name'];
        $cust->lastName = $_POST['lastname'];
        $cust->add      = $_POST['address'];
        $cust->email    = $_POST['email'];
        $cust->phone    = $_POST['phone'];
        $cust->inserted();
        log_action('customer_insert', $_POST['name'] . ' ' . $_POST['lastname']);
    }

    if ($_POST['action'] === 'delete') {
        $cust->id = $_POST['Cus_id'];
        $cust->deleteData();
        log_action('customer_delete', (string)($_POST['Cus_id'] ?? ''));
    }

    if ($_POST['action'] === 'updating') {
        validate([
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'email'    => ['email' => true, 'max' => 100],
            'phone'    => ['max' => 20],
            'address'  => ['max' => 100],
        ], $_POST);
        $cust->id       = $_POST['id'];
        $cust->name     = $_POST['name'];
        $cust->lastName = $_POST['lastname'];
        $cust->add      = $_POST['address'];
        $cust->email    = $_POST['email'];
        $cust->phone    = $_POST['phone'];
        $cust->UpdateMe();
        log_action('customer_update', (string)($_POST['id'] ?? ''));
    }

    /* Supplier function */

    if ($_POST['action'] === 'insertSupp') {
        validate([
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'address'  => ['max' => 100],
            'comp'     => ['required' => true, 'max' => 100],
            'phone'    => ['max' => 20],
        ], $_POST);
        $supp->name     = $_POST['name'];
        $supp->lastName = $_POST['lastname'];
        $supp->add      = $_POST['address'];
        $supp->company  = $_POST['comp'];
        $supp->phone    = $_POST['phone'];
        $supp->inserted();
        log_action('supplier_insert', $_POST['comp']);
    }

    if ($_POST['action'] === 'deleteSupp') {
        $supp->id = $_POST['Cus_id'];
        $supp->deleteData();
        log_action('supplier_delete', (string)($_POST['Cus_id'] ?? ''));
    }

    if ($_POST['action'] === 'updatingSupp') {
        validate([
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'address'  => ['max' => 100],
            'company'  => ['required' => true, 'max' => 100],
            'phone'    => ['max' => 20],
        ], $_POST);
        $supp->id       = $_POST['id'];
        $supp->name     = $_POST['name'];
        $supp->lastName = $_POST['lastname'];
        $supp->add      = $_POST['address'];
        $supp->company  = $_POST['company'];
        $supp->phone    = $_POST['phone'];
        $supp->UpdateMe();
        log_action('supplier_update', (string)($_POST['id'] ?? ''));
    }

    /* Staff function */

    if ($_POST['action'] === 'insertStaff') {
        validate([
            'user'     => ['required' => true, 'max' => 50],
            'pass'     => ['required' => true],
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'email'    => ['email' => true, 'max' => 100],
            'phone'    => ['max' => 20],
            'address'  => ['max' => 100],
        ], $_POST);
        $staff->Username = $_POST['user'];
        $staff->password = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $staff->name     = $_POST['name'];
        $staff->lastName = $_POST['lastname'];
        $staff->add      = $_POST['address'];
        $staff->email    = $_POST['email'];
        $staff->phone    = $_POST['phone'];
        $staff->role     = $_POST['role'];
        $staff->inserted();
        log_action('staff_insert', $_POST['user']);
    }

    if ($_POST['action'] === 'deleteStaff') {
        $staff->id = $_POST['Cus_id'];
        $staff->deleteData();
        log_action('staff_delete', (string)($_POST['Cus_id'] ?? ''));
    }

    if ($_POST['action'] === 'updatingStaff') {
        validate([
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'email'    => ['email' => true, 'max' => 100],
            'phone'    => ['max' => 20],
            'address'  => ['max' => 100],
        ], $_POST);
        $staff->id       = $_POST['id'];
        $staff->name     = $_POST['name'];
        $staff->lastName = $_POST['lastname'];
        $staff->add      = $_POST['address'];
        $staff->email    = $_POST['email'];
        $staff->phone    = $_POST['phone'];
        $staff->UpdateMe();
        log_action('staff_update', (string)($_POST['id'] ?? ''));
    }

    /*Category function */

    if ($_POST['action'] === 'insertCategory') {
        validate([
            'catn' => ['required' => true, 'max' => 100],
        ], $_POST);
        $cat->cname = $_POST['catn'];
        $cat->insertCat();
        log_action('category_insert', $_POST['catn']);
    }

    if ($_POST['action'] === 'deleteCat') {
        $cat->id = $_POST['id'];
        $cat->deleteData();
        log_action('category_delete', (string)($_POST['id'] ?? ''));
    }

    if ($_POST['action'] === 'updatingCat') {
        validate([
            'name' => ['required' => true, 'max' => 100],
        ], $_POST);
        $cat->id    = $_POST['id'];
        $cat->cname = $_POST['name'];
        $cat->UpdateMe();
        log_action('category_update', (string)($_POST['id'] ?? ''));
    }
}
/* ----------------------------GET METHOD-------------------------------- */

if (isset($_GET['action']) && $_GET['action'] === 'getSingle') {

    $cust->id = $_GET['Cus_id'];
    $prof = $cust->getSingle();

    extract($prof);

    $div = '

        <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">UPDATE CUSTOMER</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        FIRSTNAME:
                        <input type="text" class="form-control" placeholder="Name" value="' . htmlspecialchars($CUS_FNAME, ENT_QUOTES, 'UTF-8') . '" id="fname">
                    </div>
                    <div class="mb-3">
                         LASTNAME:
                        <input type="text" class="form-control" placeholder="Lastname" value="' . htmlspecialchars($CUS_LNAME, ENT_QUOTES, 'UTF-8') . '" id="lname">
                    </div>
                    <div class="mb-3">
                         LOCATION:
                        <input type="text" class="form-control" placeholder="Phone number" value="' . htmlspecialchars($CUS_ADDRESS, ENT_QUOTES, 'UTF-8') . '" id="add">
                    </div>
                    <div class="mb-3">
                         EMAIL:
                        <input type="email" class="form-control" placeholder="Email" value="' . htmlspecialchars($CUS_EMAIL, ENT_QUOTES, 'UTF-8') . '" id="emls">
                    </div>
                    <div class="mb-3">
                         PHONE-NUM:
                        <input type="text" class="form-control" placeholder="Address" value="' . htmlspecialchars($CUS_PHONENUM, ENT_QUOTES, 'UTF-8') . '" id="nums">
                    </div>
                     <div class="mb-3">
                         ADDED DATE:
                        <input type="text" class="form-control" placeholder="Address" value="' . htmlspecialchars($CUS_CREATED_AT, ENT_QUOTES, 'UTF-8') . '" id="nums" disabled>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateME(' . (int)$CUS_ID . ')">SAVE CHANGES</button>
                </div>

        ';

    echo $div;
}

if (isset($_GET['action']) && $_GET['action'] === 'viewSingle') {

    $cust->id = $_GET['id'];
    $prof = $cust->getSingle();

    extract($prof);

    $div = '

          <p class="card-text" style="display: flex; justify-content: center; align-items: center; gap:10px; flex-direction: column;">
                                <label>NAME: ' . htmlspecialchars($CUS_FNAME, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>LASTNAME:' . htmlspecialchars($CUS_LNAME, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>ADDRESS: ' . htmlspecialchars($CUS_ADDRESS, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>EMAIL: ' . htmlspecialchars($CUS_EMAIL, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>PHONE-NUMBER: ' . htmlspecialchars($CUS_PHONENUM, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>DATE CREADTED: ' . htmlspecialchars($CUS_CREATED_AT, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>DATE UPDATED: ' . htmlspecialchars($CUS_UPDATED_AT, ENT_QUOTES, 'UTF-8') . '</label>

        </p>

        ';

    echo $div;
}

/* SUPPLIER OPERATION */

if (isset($_GET['action']) && $_GET['action'] === 'getSingleSupp') {

    $supp->id = $_GET['Cus_id'];
    $prof = $supp->getSingle();

    extract($prof);

    $div = '

        <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">UPDATE SUPPLIER</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        FIRSTNAME:
                        <input type="text" class="form-control" placeholder="Name" value="' . htmlspecialchars($SUPPLIER_FNAME, ENT_QUOTES, 'UTF-8') . '" id="fname">
                    </div>
                    <div class="mb-3">
                        LASTNAME:
                        <input type="text" class="form-control" placeholder="Lastname" value="' . htmlspecialchars($SUPPLIER_LNAME, ENT_QUOTES, 'UTF-8') . '" id="lname">
                    </div>
                    <div class="mb-3">
                        ADDRESS:
                        <input type="text" class="form-control" placeholder="Phone number" value="' . htmlspecialchars($SUPPLIER_ADDRESS, ENT_QUOTES, 'UTF-8') . '" id="add">
                    </div>
                    <div class="mb-3">
                        COMPANY NAME:
                        <input type="text" class="form-control" id="compa" value="' . htmlspecialchars($SUPPLIER_COMPANY, ENT_QUOTES, 'UTF-8') . '">
                    </div>
                    <div class="mb-3">
                        PHONE-NUM:
                        <input type="text" class="form-control" placeholder="Address" value="' . htmlspecialchars($SUPPLIER_PHONENUM, ENT_QUOTES, 'UTF-8') . '" id="nums">
                    </div>
                     <div class="mb-3">
                        CREATED DATE:
                        <input type="text" class="form-control" placeholder="Address" value="' . htmlspecialchars($SUPPLIER_CREATED_AT, ENT_QUOTES, 'UTF-8') . '" id="nums" disabled>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateME(' . (int)$SUPPLIER_ID . ')">SAVE CHANGES</button>
                </div>

        ';

    echo $div;
}

if (isset($_GET['action']) && $_GET['action'] === 'viewSingleSupp') {

    $supp->id = $_GET['id'];
    $prof = $supp->getSingle();

    extract($prof);

    $div = '

          <p class="card-text" style="display: flex; justify-content: center; align-items: center; gap:10px; flex-direction: column;">
                                <label>NAME: ' . htmlspecialchars($SUPPLIER_FNAME, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>LASTNAME:' . htmlspecialchars($SUPPLIER_LNAME, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>ADDRESS: ' . htmlspecialchars($SUPPLIER_ADDRESS, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>COMPANY NAME: ' . htmlspecialchars($SUPPLIER_COMPANY, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>PHONE-NUMBER: ' . htmlspecialchars($SUPPLIER_PHONENUM, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>DATE ADDED: ' . htmlspecialchars($SUPPLIER_CREATED_AT, ENT_QUOTES, 'UTF-8') . '</label>

        </p>

        ';

    echo $div;
}

/* STAFF OPERATION */

if (isset($_GET['action']) && $_GET['action'] === 'getSingleStaff') {

    $staff->id = $_GET['Cus_id'];
    $prof = $staff->getSingle();

    extract($prof);

    $div = '

        <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">UPDATE STAFF</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                         FIRSTNAME:
                        <input type="text" class="form-control" placeholder="Name" value="' . htmlspecialchars($STAFF_FNAME, ENT_QUOTES, 'UTF-8') . '" id="fname">
                    </div>
                    <div class="mb-3">
                         LASTNAME:
                        <input type="text" class="form-control" placeholder="Lastname" value="' . htmlspecialchars($STAFF_LNAME, ENT_QUOTES, 'UTF-8') . '" id="lname">
                    </div>
                    <div class="mb-3">
                         ADDRESS:
                        <input type="text" class="form-control" placeholder="Phone number" value="' . htmlspecialchars($STAFF_ADDRESS, ENT_QUOTES, 'UTF-8') . '" id="add">
                    </div>
                    <div class="mb-3">
                         EMAIL:
                        <input type="email" class="form-control" placeholder="Email" value="' . htmlspecialchars($STAFF_EMAIL, ENT_QUOTES, 'UTF-8') . '" id="emls">
                    </div>
                    <div class="mb-3">
                        PHONE-NUM:
                        <input type="text" class="form-control" placeholder="Address" value="' . htmlspecialchars($STAFF_PHONENUM, ENT_QUOTES, 'UTF-8') . '" id="nums">
                    </div>
                     <div class="mb-3">
                        ADDED DATE:
                        <input type="text" class="form-control" placeholder="Address" value="' . htmlspecialchars($STAFF_CREATED_AT, ENT_QUOTES, 'UTF-8') . '" disabled>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateME(' . (int)$STAFF_ID . ')">Save changes</button>
                </div>

        ';

    echo $div;
}

if (isset($_GET['action']) && $_GET['action'] === 'viewSingleStaff') {

    $staff->id = $_GET['id'];
    $prof = $staff->getSingle();

    extract($prof);

    $div = '

          <p class="card-text" style="display: flex; justify-content: center; align-items: center; gap:10px; flex-direction: column;">
                                <label>NAME: ' . htmlspecialchars($STAFF_FNAME, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>LASTNAME:' . htmlspecialchars($STAFF_LNAME, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>ADDRESS: ' . htmlspecialchars($STAFF_ADDRESS, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>EMAIL: ' . htmlspecialchars($STAFF_EMAIL, ENT_QUOTES, 'UTF-8') . '</label>
                                <label>PHONE-NUMBER: ' . htmlspecialchars($STAFF_PHONENUM, ENT_QUOTES, 'UTF-8') . '</label>

        </p>

        ';

    echo $div;
}

/* CATEGORY OPERATION */

if (isset($_GET['action']) && $_GET['action'] === 'getSingleCat') {

    $cat->id = $_GET['Cus_id'];
    $prof = $cat->getSingle();

    extract($prof);

    $div = '

        <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">UPDATE CATEGORY</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                         CATEGORY NAME:
                        <input type="text" class="form-control" placeholder="Name" value="' . htmlspecialchars($CATEGORY_NAME, ENT_QUOTES, 'UTF-8') . '" id="name">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="updateME(' . (int)$CATEGORY_ID . ')">Save changes</button>
                </div>

        ';

    echo $div;
}

if (isset($_GET['action']) && $_GET['action'] === 'viewSingleCat') {

    $cat->id = $_GET['id'];
    $prof = $cat->getSingle();

    extract($prof);

    $div = '

          <p class="card-text" style="display: flex; justify-content: center; align-items: center; gap:10px; flex-direction: column;">
                                <label>CATEGORY: ' . htmlspecialchars($CATEGORY_NAME, ENT_QUOTES, 'UTF-8') . '</label>

        </p>

        ';

    echo $div;
}

?>
