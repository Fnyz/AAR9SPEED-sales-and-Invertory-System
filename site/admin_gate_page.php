<?php
// Admin password page shown before admin-only areas and actions. Set by the caller:
// $destination (page to open), $page (menu key), $gate_message (from site/admin_gate.php).
$gateLabels = ['Staff.php' => 'Staff', 'Order.php' => 'Purchase orders', 'Reports.php' => 'Sales reports',
               'Logs.php' => 'the activity log', 'Refund.php' => 'refunds', 'Product.php' => 'price and cost changes',
               'Customer.php' => 'customer deletes', 'Supplier.php' => 'supplier deletes', 'Category.php' => 'category deletes'];
$gateTarget = $gateLabels[$destination] ?? 'this page';
$pageTitle = 'Admin access';
?>
<!doctype html>
<html lang="en">

<head>
    <?php include __DIR__ . '/layout/head.php' ?>
</head>

<body>
    <div class="page">
        <?php include __DIR__ . '/layout/navbar.php' ?>

        <div class="page-wrapper">
            <div class="page-body">
                <div class="container container-tight py-4">
                    <div class="card card-md">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <span class="avatar avatar-lg bg-primary-lt text-primary mb-3"><i class="ti ti-lock fs-1"></i></span>
                                <h1 class="h2 mb-1">Admin access</h1>
                                <p class="text-secondary mb-0">Enter the admin password to open <?php echo htmlspecialchars($gateTarget, ENT_QUOTES, 'UTF-8') ?>.</p>
                            </div>
                            <?php if ($gate_message === 'wrong') : ?>
                                <div class="alert alert-danger" role="alert">That password is not correct. Only an admin can open <?php echo htmlspecialchars($gateTarget, ENT_QUOTES, 'UTF-8') ?>.</div>
                            <?php elseif ($gate_message) : ?>
                                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($gate_message, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                            <form method="post">
                                <?php echo csrf_field(); ?>
                                <label for="gate-password" class="form-label">Admin password</label>
                                <input type="password" id="gate-password" name="password" class="form-control" autocomplete="current-password" required autofocus>
                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary w-100">Continue</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <p class="text-center text-secondary small mt-3"><a href="Dashboard.php">Back to the dashboard</a></p>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/layout/scripts.php' ?>
</body>

</html>
