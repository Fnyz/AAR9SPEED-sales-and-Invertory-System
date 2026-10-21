
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
        $cust->add      = $_POST['address'] ?? '';
        $cust->email    = $_POST['email'] ?? '';
        $cust->phone    = $_POST['phone'] ?? '';
        $cust->inserted();
        log_action('customer_insert', $_POST['name'] . ' ' . $_POST['lastname']);
    }

    if ($_POST['action'] === 'delete') {
        $cust->id = $_POST['Cus_id'];
        if ($cust->usageCount() > 0) {
            json_validation_error('This customer has sales on record, so they can\'t be deleted. Their invoices still refer to them.');
        }
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
        $cust->add      = $_POST['address'] ?? '';
        $cust->email    = $_POST['email'] ?? '';
        $cust->phone    = $_POST['phone'] ?? '';
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
        $supp->add      = $_POST['address'] ?? '';
        $supp->company  = $_POST['comp'];
        $supp->phone    = $_POST['phone'] ?? '';
        $supp->inserted();
        log_action('supplier_insert', $_POST['comp']);
    }

    if ($_POST['action'] === 'deleteSupp') {
        $supp->id = $_POST['Cus_id'];
        $inUse = $supp->usageCount();
        if ($inUse > 0) {
            json_validation_error('This supplier still has ' . $inUse . ($inUse === 1 ? ' product' : ' products') . '. Move or delete them first.');
        }
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
        $supp->add      = $_POST['address'] ?? '';
        $supp->company  = $_POST['company'];
        $supp->phone    = $_POST['phone'] ?? '';
        $supp->UpdateMe();
        log_action('supplier_update', (string)($_POST['id'] ?? ''));
    }

    /* Staff function */

    // Staff accounts can only be changed after passing the admin password page
    // (site/admin_gate.php), whatever the browser sends.
    if (in_array($_POST['action'], ['insertStaff', 'deleteStaff', 'updatingStaff'], true)
        && ($_SESSION['adminId'] ?? 0) !== 1) {
        http_response_code(403);
        header('Content-Type: application/json');
        exit(json_encode(['error' => 'Only an admin can change staff accounts. Open Staff and enter the admin password first.']));
    }

    if ($_POST['action'] === 'insertStaff') {
        validate([
            'user'     => ['required' => true, 'max' => 20],
            'pass'     => ['required' => true],
            'name'     => ['required' => true, 'max' => 50],
            'lastname' => ['required' => true, 'max' => 50],
            'email'    => ['email' => true, 'max' => 100],
            'phone'    => ['max' => 20],
            'address'  => ['max' => 100],
        ], $_POST);
        $username = str_replace(' ', '', $_POST['user']);
        if (strlen($_POST['pass']) < 8) {
            json_validation_error('The password must be at least 8 characters.');
        }
        if (!in_array((string)($_POST['role'] ?? ''), ['1', '2'], true)) {
            json_validation_error('Choose a role: Admin or Staff.');
        }
        if ($staff->usernameExists($username)) {
            json_validation_error('The username "' . $username . '" is already taken.');
        }
        $staff->Username = $username;
        $staff->password = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $staff->name     = $_POST['name'];
        $staff->lastName = $_POST['lastname'];
        $staff->add      = $_POST['address'] ?? '';
        $staff->email    = $_POST['email'] ?? '';
        $staff->phone    = $_POST['phone'] ?? '';
        $staff->role     = $_POST['role'];
        $staff->inserted();
        log_action('staff_insert', $_POST['user']);
    }

    if ($_POST['action'] === 'deleteStaff') {
        $staff->id = $_POST['Cus_id'];
        $target = $staff->getSingle();
        if (!$target) {
            json_validation_error('That staff member no longer exists.');
        }
        if ((int)$target['STAFF_ID'] === (int)($_SESSION['getId'] ?? 0)) {
            json_validation_error('You can\'t delete your own account.');
        }
        if ((int)$target['role_id'] === 1 && $staff->adminCount() <= 1) {
            json_validation_error('This is the only admin account, so it can\'t be deleted.');
        }
        if ($staff->usageCount() > 0) {
            json_validation_error('This staff member has sales or purchase orders on record, so the account can\'t be deleted.');
        }
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
        $staff->add      = $_POST['address'] ?? '';
        $staff->email    = $_POST['email'] ?? '';
        $staff->phone    = $_POST['phone'] ?? '';
        $staff->UpdateMe();
        log_action('staff_update', (string)($_POST['id'] ?? ''));
    }

    /*Category function */

    if ($_POST['action'] === 'insertCategory') {
        validate([
            'catn' => ['required' => true, 'max' => 100],
        ], $_POST);
        if ($cat->nameExists($_POST['catn'])) {
            json_validation_error('A category named "' . trim($_POST['catn']) . '" already exists.');
        }
        $cat->cname = trim($_POST['catn']);
        $cat->insertCat();
        log_action('category_insert', $_POST['catn']);
    }

    if ($_POST['action'] === 'deleteCat') {
        $cat->id = $_POST['id'];
        $inUse = $cat->productCount();
        if ($inUse > 0) {
            json_validation_error('This category still has ' . $inUse . ($inUse === 1 ? ' product' : ' products') . '. Move them to another category first.');
        }
        $cat->deleteData();
        log_action('category_delete', (string)($_POST['id'] ?? ''));
    }

    if ($_POST['action'] === 'updatingCat') {
        validate([
            'name' => ['required' => true, 'max' => 100],
        ], $_POST);
        if ($cat->nameExists($_POST['name'], $_POST['id'])) {
            json_validation_error('A category named "' . trim($_POST['name']) . '" already exists.');
        }
        $cat->id    = $_POST['id'];
        $cat->cname = trim($_POST['name']);
        $cat->UpdateMe();
        log_action('category_update', (string)($_POST['id'] ?? ''));
    }
}
