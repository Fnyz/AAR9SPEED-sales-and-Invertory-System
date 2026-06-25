

<?php
session_start();
require_once "../site/csrf.php";

require_once "../site/audit.php";
include_once "../Model/Dashboard.php";

$dash =  new Dashboard();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $dash->id = $_POST['id'];
    $dash->user = $_POST['user'];
    $dash->name = $_POST['fname'];
    $dash->pass2 = !empty($_POST['pass']) ? password_hash($_POST['pass'], PASSWORD_DEFAULT) : null;
    $dash->lastName = $_POST['lname'];
    $dash->add = $_POST['add'];
    $dash->email = $_POST['email'];
    $dash->phone = $_POST['phone'];
    if ($dash->UpdateMeStaff()) {
        log_action('profile_update', (string)($_POST['id'] ?? ''));
        $_SESSION['success'] = '';
        header("location: ../Dashboard.php");
    }
}

?>
