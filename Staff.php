<?php
$page = "staff";
$pageTitle = "Staff";
include 'site/protected.php';
$adminGatePage = 'SecurityCheck.php';
include 'site/require_admin.php';
require_once "./Model/Staff.php";

$staff = new Staff();
$members = $staff->listWithStats();
$roles = $staff->role();
$me = (int)($_SESSION['getId'] ?? 0);

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
                            <div class="page-pretitle">People · Admin only</div>
                            <h1 class="page-title">Staff</h1>
                        </div>
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-staff-modal">
                                <i class="ti ti-user-plus me-2"></i>Add staff account
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
                                <label for="staff-search" class="visually-hidden">Search staff</label>
                                <input type="search" id="staff-search" class="form-control" placeholder="Search name or username" autocomplete="off" data-table-search="#staff-rows" data-empty="#staff-empty">
                            </div>
                            <div class="card-actions text-secondary small"><?php echo count($members) === 1 ? '1 account' : count($members) . ' accounts' ?></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th class="text-end">Sales recorded</th>
                                        <th><span class="visually-hidden">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody id="staff-rows">
                                    <?php foreach ($members as $m) :
                                        $name = trim($clean($m['STAFF_FNAME']) . ' ' . $clean($m['STAFF_LNAME']));
                                        $label = $name !== '' ? $name : $m['STAFF_USER'];
                                        $isMe = (int)$m['STAFF_ID'] === $me;
                                        $isAdmin = (int)$m['role_id'] === 1;
                                        $mark = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $label), 0, 2)) ?: '?';
                                    ?>
                                        <tr data-id="<?php echo (int)$m['STAFF_ID'] ?>"
                                            data-name="<?php echo $e($label) ?>"
                                            data-search="<?php echo $e(strtolower($name . ' ' . $m['STAFF_USER'])) ?>"
                                            data-fname="<?php echo $e($m['STAFF_FNAME']) ?>" data-lname="<?php echo $e($m['STAFF_LNAME']) ?>"
                                            data-email="<?php echo $e($clean($m['STAFF_EMAIL'])) ?>" data-phone="<?php echo $e($clean($m['STAFF_PHONENUM'])) ?>"
                                            data-address="<?php echo $e($clean($m['STAFF_ADDRESS'])) ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="avatar bg-primary-lt text-primary fw-bold" aria-hidden="true"><?php echo $e($mark) ?></span>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo $e($label) ?><?php echo $isMe ? ' <span class="badge bg-primary-lt ms-1">You</span>' : '' ?></div>
                                                        <div class="code text-secondary">@<?php echo $e($m['STAFF_USER']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge <?php echo $isAdmin ? 'bg-primary-lt' : 'bg-secondary-lt' ?>"><?php echo $isAdmin ? 'Admin' : 'Staff' ?></span></td>
                                            <td class="text-secondary"><?php echo $e($clean($m['STAFF_EMAIL']) ?: '—') ?></td>
                                            <td class="num"><?php echo $e($clean($m['STAFF_PHONENUM']) ?: '—') ?></td>
                                            <td class="text-end num"><?php echo (int)$m['sales'] ?></td>
                                            <td class="text-end text-nowrap">
                                                <div class="row-actions">
                                                    <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#edit-staff-modal" aria-label="Edit <?php echo $e($label) ?>" title="Edit"><i class="ti ti-pencil"></i></button>
                                                    <?php if (!$isMe) : ?>
                                                        <button type="button" class="btn btn-icon btn-outline-danger" data-row-ask aria-label="Delete <?php echo $e($label) ?>" title="Delete"><i class="ti ti-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!$isMe) : ?>
                                                    <div class="row-confirm d-none align-items-center justify-content-end gap-2">
                                                        <span class="small text-secondary">Delete this account?</span>
                                                        <button type="button" class="btn btn-sm" data-row-cancel>Keep</button>
                                                        <button type="button" class="btn btn-sm btn-danger" data-row-delete data-action="deleteStaff" data-key="Cus_id">Delete</button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body text-center text-secondary" id="staff-empty" hidden>No staff match your search.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-staff-modal" tabindex="-1" aria-labelledby="add-staff-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" action="ajax/operation.php" data-ajax-form data-success="Created the account @{user}." novalidate autocomplete="off">
                <input type="hidden" name="action" value="insertStaff">
                <div class="modal-header">
                    <h2 class="modal-title" id="add-staff-title">Add staff account</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger form-error" role="alert" hidden></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required" for="new-user">Username</label>
                            <input type="text" class="form-control" id="new-user" name="user" maxlength="20" pattern="\S+" autocomplete="off" required>
                            <div class="invalid-feedback">Enter a username without spaces.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="new-pass">Password</label>
                            <input type="password" class="form-control" id="new-pass" name="pass" minlength="8" autocomplete="new-password" required>
                            <div class="invalid-feedback">Use at least 8 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="new-fname">First name</label>
                            <input type="text" class="form-control" id="new-fname" name="name" maxlength="50" required>
                            <div class="invalid-feedback">Enter a first name.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="new-lname">Last name</label>
                            <input type="text" class="form-control" id="new-lname" name="lastname" maxlength="50" required>
                            <div class="invalid-feedback">Enter a last name.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="new-email">Email</label>
                            <input type="email" class="form-control" id="new-email" name="email" maxlength="100">
                            <div class="invalid-feedback">Enter a valid email, or leave it empty.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="new-phone">Phone</label>
                            <input type="tel" class="form-control" id="new-phone" name="phone" maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="new-address">Address</label>
                            <input type="text" class="form-control" id="new-address" name="address" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="new-role">Role</label>
                            <select class="form-select" id="new-role" name="role" required>
                                <?php foreach ($roles as $r) : ?>
                                    <option value="<?php echo (int)$r['role_id'] ?>"<?php echo (int)$r['role_id'] === 2 ? ' selected' : '' ?>><?php echo (int)$r['role_id'] === 1 ? 'Admin' : 'Staff' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-hint mt-3">Admins can open Staff and Purchase orders. Staff can use everything else.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create account</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="edit-staff-modal" tabindex="-1" aria-labelledby="edit-staff-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="ajax/operation.php" data-ajax-form data-success="Saved {name} {lastname}." novalidate>
                <input type="hidden" name="action" value="updatingStaff">
                <input type="hidden" name="id" id="edit-staff-id">
                <div class="modal-header">
                    <h2 class="modal-title" id="edit-staff-title">Edit staff details</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger form-error" role="alert" hidden></div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label required" for="edit-fname">First name</label>
                            <input type="text" class="form-control" id="edit-fname" name="name" maxlength="50" required>
                            <div class="invalid-feedback">Enter a first name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required" for="edit-lname">Last name</label>
                            <input type="text" class="form-control" id="edit-lname" name="lastname" maxlength="50" required>
                            <div class="invalid-feedback">Enter a last name.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="edit-email">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" maxlength="100">
                            <div class="invalid-feedback">Enter a valid email, or leave it empty.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="edit-phone">Phone</label>
                            <input type="tel" class="form-control" id="edit-phone" name="phone" maxlength="20">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="edit-address">Address</label>
                            <input type="text" class="form-control" id="edit-address" name="address" maxlength="100">
                        </div>
                    </div>
                    <div class="form-hint mt-3">Each person changes their own username and password from <b>Update my account</b> in the top-right menu.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        $('#add-staff-modal').on('show.bs.modal', function () {
            this.querySelector('form').reset();
        }).on('shown.bs.modal', function () { $('#new-user').trigger('focus'); });

        $('#edit-staff-modal').on('show.bs.modal', function (event) {
            var $row = $(event.relatedTarget).closest('tr');
            $('#edit-staff-id').val($row.data('id'));
            $('#edit-fname').val($row.attr('data-fname'));
            $('#edit-lname').val($row.attr('data-lname'));
            $('#edit-email').val($row.attr('data-email'));
            $('#edit-phone').val($row.attr('data-phone'));
            $('#edit-address').val($row.attr('data-address'));
        }).on('shown.bs.modal', function () { $('#edit-fname').trigger('focus'); });
    </script>
</body>

</html>
