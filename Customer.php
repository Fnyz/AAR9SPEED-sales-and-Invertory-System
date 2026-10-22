<?php
$page = "customer";
$pageTitle = "Customers";
include 'site/protected.php';
require_once "./Model/Customer.php";

$cust = new Customer();
$customers = $cust->listWithStats();

const WALK_IN_ID = 1;
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);
$initials = function (string $first, string $last): string {
    $text = strtoupper(substr(trim($first), 0, 1) . substr(trim($last), 0, 1));
    return $text !== '' ? $text : '?';
};
?>
<!doctype html>
<html lang="en">

<head>
    <?php include 'site/layout/head.php' ?>
</head>

<body>
    <div class="page">
        <?php include 'site/layout/navbar.php' ?>

        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="page-pretitle">People</div>
                            <h1 class="page-title">Customers</h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customer-modal" data-mode="add">
                                <i class="ti ti-plus me-2"></i>Add customer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="card">
                        <div class="card-header">
                            <div class="input-icon w-100" style="max-width: 24rem">
                                <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                                <label for="customer-search" class="visually-hidden">Search customers</label>
                                <input type="search" id="customer-search" class="form-control" placeholder="Search name, email or phone" autocomplete="off" data-table-search="#customer-rows" data-empty="#customer-empty">
                            </div>
                            <div class="card-actions text-secondary small"><?php echo count($customers) === 1 ? '1 customer' : count($customers) . ' customers' ?></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th class="text-end">Purchases</th>
                                        <th class="text-end">Total spent</th>
                                        <th>Last purchase</th>
                                        <th><span class="visually-hidden">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody id="customer-rows">
                                    <?php foreach ($customers as $c) :
                                        $isWalkIn = (int)$c['CUS_ID'] === WALK_IN_ID;
                                        $fullName = trim($c['CUS_FNAME'] . ' ' . $c['CUS_LNAME']);
                                        $displayName = $isWalkIn ? 'Walk-in customer' : $fullName;
                                    ?>
                                        <tr data-id="<?php echo (int)$c['CUS_ID'] ?>"
                                            data-name="<?php echo $e($displayName) ?>"
                                            data-search="<?php echo $e(strtolower($fullName . ' ' . $c['CUS_EMAIL'] . ' ' . $c['CUS_PHONENUM'])) ?>"
                                            data-fname="<?php echo $e($c['CUS_FNAME']) ?>" data-lname="<?php echo $e($c['CUS_LNAME']) ?>"
                                            data-email="<?php echo $e($c['CUS_EMAIL']) ?>" data-phone="<?php echo $e($c['CUS_PHONENUM']) ?>"
                                            data-address="<?php echo $e($c['CUS_ADDRESS']) ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="avatar bg-primary-lt text-primary fw-bold" aria-hidden="true"><?php echo $isWalkIn ? '<i class="ti ti-walk"></i>' : $e($initials($c['CUS_FNAME'] ?? '', $c['CUS_LNAME'] ?? '')) ?></span>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo $e($displayName) ?></div>
                                                        <div class="small text-secondary"><?php echo $isWalkIn ? 'Used for sales without a named customer' : $e($c['CUS_EMAIL'] ?: 'No email') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="num"><?php echo $e($c['CUS_PHONENUM'] ?: '—') ?></td>
                                            <td class="text-secondary"><?php echo $e($c['CUS_ADDRESS'] ?: '—') ?></td>
                                            <td class="text-end num"><?php echo (int)$c['purchases'] ?></td>
                                            <td class="text-end num fw-semibold"><?php echo $e($peso($c['spent'])) ?></td>
                                            <td class="text-secondary num"><?php echo $c['last_purchase'] ? $e(date('j M Y', strtotime($c['last_purchase']))) : 'Never' ?></td>
                                            <td class="text-end text-nowrap">
                                                <?php if (!$isWalkIn) : ?>
                                                    <div class="row-actions">
                                                        <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#customer-modal" data-mode="edit" aria-label="Edit <?php echo $e($displayName) ?>" title="Edit"><i class="ti ti-pencil"></i></button>
                                                        <?php if (is_admin()) : ?>
                                                            <button type="button" class="btn btn-icon btn-outline-danger" data-row-ask aria-label="Delete <?php echo $e($displayName) ?>" title="Delete"><i class="ti ti-trash"></i></button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="row-confirm d-none align-items-center justify-content-end gap-2">
                                                        <span class="small text-secondary">Delete this customer?</span>
                                                        <button type="button" class="btn btn-sm" data-row-cancel>Keep</button>
                                                        <button type="button" class="btn btn-sm btn-danger" data-row-delete data-action="delete" data-key="Cus_id">Delete</button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body text-center text-secondary" id="customer-empty" hidden>No customers match your search.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customer-modal" tabindex="-1" aria-labelledby="customer-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="ajax/operation.php" data-ajax-form data-success="Saved {name} {lastname}." novalidate>
                <input type="hidden" name="action" id="customer-action" value="insert">
                <input type="hidden" name="id" id="customer-id">
                <div class="modal-header">
                    <h2 class="modal-title" id="customer-title">Add customer</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger form-error" role="alert" hidden></div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label required" for="customer-fname">First name</label>
                            <input type="text" class="form-control" id="customer-fname" name="name" maxlength="50" required>
                            <div class="invalid-feedback">Enter a first name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required" for="customer-lname">Last name</label>
                            <input type="text" class="form-control" id="customer-lname" name="lastname" maxlength="50" required>
                            <div class="invalid-feedback">Enter a last name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="customer-phone">Phone</label>
                            <input type="tel" class="form-control" id="customer-phone" name="phone" maxlength="20" placeholder="09XX XXX XXXX">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="customer-email">Email</label>
                            <input type="email" class="form-control" id="customer-email" name="email" maxlength="100">
                            <div class="invalid-feedback">Enter a valid email, or leave it empty.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="customer-address">Address</label>
                            <input type="text" class="form-control" id="customer-address" name="address" maxlength="100" placeholder="Street, city">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="customer-submit">Add customer</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        $('#customer-modal').on('show.bs.modal', function (event) {
            var $trigger = $(event.relatedTarget), editing = $trigger.data('mode') === 'edit';
            var $row = editing ? $trigger.closest('tr') : $();
            $('#customer-action').val(editing ? 'updating' : 'insert');
            $('#customer-id').val(editing ? $row.data('id') : '');
            $('#customer-title').text(editing ? 'Edit customer' : 'Add customer');
            $('#customer-submit').text(editing ? 'Save changes' : 'Add customer');
            $('#customer-fname').val(editing ? $row.attr('data-fname') : '');
            $('#customer-lname').val(editing ? $row.attr('data-lname') : '');
            $('#customer-phone').val(editing ? $row.attr('data-phone') : '');
            $('#customer-email').val(editing ? $row.attr('data-email') : '');
            $('#customer-address').val(editing ? $row.attr('data-address') : '');
        }).on('shown.bs.modal', function () { $('#customer-fname').trigger('focus'); });
    </script>
</body>

</html>
