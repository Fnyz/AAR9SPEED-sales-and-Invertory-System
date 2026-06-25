<?php

include_once './Model/Dashboard.php';
$dash = new Dashboard();
$quan = $dash->getLowQuan();
$pend = $dash->pending();

?>

<div id="ham">
    <div style="display: flex; justify-content:space-between; margin-right:50px;">

        <button class="btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling"><i class="fa-solid fa-bars text-white"></i></button>

        <a href="" id="notch" type="button" class="btn btn-primary position-relative" style="background: transparent; border-top:none; border-bottom:none; border-right:none; border-left:none;" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-sharp fa-solid fa-bell" style="font-size: 20px;"></i>
            <span id="pendings" class="mt-3 position-absolute top-5 left:3 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
            </span>
        </a>

        <ul id="menu" class="dropdown-menu">

        </ul>

    </div>

</div>