<div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
    <div class="offcanvas-header sides">
        <img src="img/logo.png" class="offcanvas-title" id="offcanvasScrollingLabel" width="300px"></img>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body sides">
        <div class="card sides" style="width: 18rem;">
            <div class="rnd">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="card-body ">
                <h5 class="card-title">
                    <?php echo strtoupper(htmlspecialchars($_SESSION['userId'], ENT_QUOTES, 'UTF-8')) ?>

                </h5>
                <div class="btns">
                    <a href="" data-bs-toggle="modal" data-bs-target="#UpdateAdmin" class="btn btn-success">UPDATE</a>
                    <a href="Model/logout.php" class="btn btn-danger">LOG-OUT</a>
                </div>
            </div>
        </div>

        <div class="list-group ">
            <a href="Dashboard.php" class="list-group-item list-group-item-action <?php if ($page == 'dashboard') echo 'active';
                                                                                    else echo 'sides' ?>" aria-current="true">
                <i class="fa-sharp fa-solid fa-house-user"></i>HOME</a></li>
            </a>
            <a href="Customer.php" class="list-group-item list-group-item-action <?php if ($page == 'customer') echo 'active';
                                                                                    else echo 'sides' ?>"><i class="fa-sharp fa-solid fa-users"></i>CUSTOMER</a></li></a>
            <a href="SecurityCheck.php" class="list-group-item list-group-item-action <?php if ($page == 'staff') echo 'active';
                                                                                        else echo 'sides' ?>"><i class="fa-solid fa-user-plus"></i>STAFF</a></li></a>
            <a href="Category.php" class="list-group-item list-group-item-action <?php if ($page == 'category') echo 'active';
                                                                                    else echo 'sides' ?>"><i class="fa-solid fa-box-open"></i>CATEGORY</a></li></a>
            <a href="Product.php" class="list-group-item list-group-item-action <?php if ($page == 'product') echo 'active';
                                                                                else echo 'sides' ?>"><i class="fa-sharp fa-solid fa-cart-shopping"></i>PRODUCT</a>
            <a href="invoice.php" class="list-group-item list-group-item-action <?php if ($page == 'invoice') echo 'active';
                                                                                else echo 'sides' ?>"><i class="fa-solid fa-receipt"></i> INVOICE</a>
            <a href="Supplier.php" class="list-group-item list-group-item-action <?php if ($page == 'supplier') echo 'active';
                                                                                    else echo 'sides' ?>"><i class="fa-sharp fa-solid fa-user"></i>SUPPLIER</a>
            <a href="Securitylogin.php" class="list-group-item list-group-item-action <?php if ($page == 'Order') echo 'active';
                                                                                        else echo 'sides' ?>"><i class="fa-solid fa-bag-shopping"></i>PURCHASE ORDER</a>
            <a href="Refund.php" class="list-group-item list-group-item-action <?php if ($page == 'refunds') echo 'active';
                                                                                    else echo 'sides' ?>"><i class="fa-solid fa-rotate-left"></i>REFUNDS</a>

        </div>

    </div>
</div>

