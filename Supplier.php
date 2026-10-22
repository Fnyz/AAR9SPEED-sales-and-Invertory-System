<?php
$page = "supplier";
$pageTitle = "Suppliers";
include 'site/protected.php';
require_once "./Model/Supplier.php";

$supp = new Supplier();
$suppliers = $supp->listWithStats();

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$clean = fn($s) => trim((string)$s) === '-' ? '' : trim((string)$s);
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
                            <h1 class="page-title">Suppliers</h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#supplier-modal" data-mode="add">
                                <i class="ti ti-plus me-2"></i>Add supplier
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
                                <label for="supplier-search" class="visually-hidden">Search suppliers</label>
                                <input type="search" id="supplier-search" class="form-control" placeholder="Search company, contact or phone" autocomplete="off" data-table-search="#supplier-rows" data-empty="#supplier-empty">
                            </div>
                            <div class="card-actions text-secondary small"><?php echo count($suppliers) === 1 ? '1 supplier' : count($suppliers) . ' suppliers' ?></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Contact person</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th class="text-end">Products</th>
                                        <th><span class="visually-hidden">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody id="supplier-rows">
                                    <?php foreach ($suppliers as $s) :
                                        $contact = trim($clean($s['SUPPLIER_FNAME']) . ' ' . $clean($s['SUPPLIER_LNAME']));
                                        $count = (int)$s['products'];
                                        $words = preg_split('/\s+/', trim($s['SUPPLIER_COMPANY']));
                                        $mark = strtoupper(substr($words[0] ?? '?', 0, 1) . substr($words[1] ?? '', 0, 1));
                                    ?>
                                        <tr data-id="<?php echo (int)$s['SUPPLIER_ID'] ?>"
                                            data-name="<?php echo $e($s['SUPPLIER_COMPANY']) ?>"
                                            data-search="<?php echo $e(strtolower($s['SUPPLIER_COMPANY'] . ' ' . $contact . ' ' . $s['SUPPLIER_PHONENUM'])) ?>"
                                            data-company="<?php echo $e($s['SUPPLIER_COMPANY']) ?>"
                                            data-fname="<?php echo $e($s['SUPPLIER_FNAME']) ?>" data-lname="<?php echo $e($s['SUPPLIER_LNAME']) ?>"
                                            data-phone="<?php echo $e($s['SUPPLIER_PHONENUM']) ?>" data-address="<?php echo $e($s['SUPPLIER_ADDRESS']) ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="avatar bg-primary-lt text-primary fw-bold" aria-hidden="true"><?php echo $e($mark) ?></span>
                                                    <span class="fw-semibold"><?php echo $e($s['SUPPLIER_COMPANY']) ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo $e($contact ?: '—') ?></td>
                                            <td class="num"><?php echo $e($s['SUPPLIER_PHONENUM'] ?: '—') ?></td>
                                            <td class="text-secondary"><?php echo $e($s['SUPPLIER_ADDRESS'] ?: '—') ?></td>
                                            <td class="text-end">
                                                <a href="Product.php" class="badge bg-secondary-lt text-decoration-none num"><?php echo $count === 1 ? '1 product' : $count . ' products' ?></a>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <div class="row-actions">
                                                    <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#supplier-modal" data-mode="edit" aria-label="Edit <?php echo $e($s['SUPPLIER_COMPANY']) ?>" title="Edit"><i class="ti ti-pencil"></i></button>
                                                    <?php if (is_admin()) : ?>
                                                        <button type="button" class="btn btn-icon btn-outline-danger" data-row-ask aria-label="Delete <?php echo $e($s['SUPPLIER_COMPANY']) ?>" title="Delete"><i class="ti ti-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="row-confirm d-none align-items-center justify-content-end gap-2">
                                                    <?php if ($count > 0) : ?>
                                                        <span class="small text-secondary text-wrap text-start">Move or delete its <?php echo $count === 1 ? 'product' : $count . ' products' ?> first.</span>
                                                        <button type="button" class="btn btn-sm" data-row-cancel>OK</button>
                                                    <?php else : ?>
                                                        <span class="small text-secondary">Delete this supplier?</span>
                                                        <button type="button" class="btn btn-sm" data-row-cancel>Keep</button>
                                                        <button type="button" class="btn btn-sm btn-danger" data-row-delete data-action="deleteSupp" data-key="Cus_id">Delete</button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body text-center text-secondary" id="supplier-empty" hidden>No suppliers match your search.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="supplier-modal" tabindex="-1" aria-labelledby="supplier-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="ajax/operation.php" data-ajax-form data-success="Saved supplier." novalidate>
                <input type="hidden" name="action" id="supplier-action" value="insertSupp">
                <input type="hidden" name="id" id="supplier-id">
                <div class="modal-header">
                    <h2 class="modal-title" id="supplier-title">Add supplier</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger form-error" role="alert" hidden></div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required" for="supplier-company">Company</label>
                            <input type="text" class="form-control" id="supplier-company" name="comp" maxlength="100" required>
                            <div class="invalid-feedback">Enter the company name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required" for="supplier-fname">Contact first name</label>
                            <input type="text" class="form-control" id="supplier-fname" name="name" maxlength="50" required>
                            <div class="invalid-feedback">Enter a first name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required" for="supplier-lname">Contact last name</label>
                            <input type="text" class="form-control" id="supplier-lname" name="lastname" maxlength="50" required>
                            <div class="invalid-feedback">Enter a last name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="supplier-phone">Phone</label>
                            <input type="tel" class="form-control" id="supplier-phone" name="phone" maxlength="20">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="supplier-address">Address</label>
                            <input type="text" class="form-control" id="supplier-address" name="address" maxlength="100" placeholder="City, province">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="supplier-submit">Add supplier</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        $('#supplier-modal').on('show.bs.modal', function (event) {
            var $trigger = $(event.relatedTarget), editing = $trigger.data('mode') === 'edit';
            var $row = editing ? $trigger.closest('tr') : $();
            // The existing handler names the company field "comp" when adding and "company" when editing.
            $('#supplier-action').val(editing ? 'updatingSupp' : 'insertSupp');
            $('#supplier-company').attr('name', editing ? 'company' : 'comp').val(editing ? $row.attr('data-company') : '');
            $(this).find('form').data('success', editing ? 'Saved {company}.' : 'Added {comp}.');
            $('#supplier-id').val(editing ? $row.data('id') : '');
            $('#supplier-title').text(editing ? 'Edit supplier' : 'Add supplier');
            $('#supplier-submit').text(editing ? 'Save changes' : 'Add supplier');
            $('#supplier-fname').val(editing ? $row.attr('data-fname') : '');
            $('#supplier-lname').val(editing ? $row.attr('data-lname') : '');
            $('#supplier-phone').val(editing ? $row.attr('data-phone') : '');
            $('#supplier-address').val(editing ? $row.attr('data-address') : '');
        }).on('shown.bs.modal', function () { $('#supplier-company').trigger('focus'); });
    </script>
</body>

</html>
