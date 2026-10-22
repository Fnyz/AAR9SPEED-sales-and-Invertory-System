<?php
$page = "category";
$pageTitle = "Categories";
include 'site/protected.php';
require_once "./Model/Category.php";

$cat = new Category();
$categories = $cat->showCategoryWithCounts();

$totalProducts = array_sum(array_column($categories, 'product_count'));
$maxCount = max(1, (int)max(array_column($categories, 'product_count') ?: [0]));
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
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
                    <div class="page-pretitle">Inventory</div>
                    <h1 class="page-title">Categories</h1>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-cards align-items-start">

                        <div class="col-lg-4">
                            <form class="card" id="add-form" novalidate>
                                <div class="card-header">
                                    <h2 class="card-title">Add category</h2>
                                </div>
                                <div class="card-body">
                                    <label for="cname" class="form-label">Category name</label>
                                    <input type="text" class="form-control" id="cname" name="catn" maxlength="100" autocomplete="off" placeholder="e.g. Brakes &amp; suspension" required>
                                    <div class="invalid-feedback" id="cname-error"></div>
                                    <div class="form-hint">Shown in product forms and filters.</div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary w-100" id="add-btn">Add category</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title">All categories</h2>
                                    <div class="card-actions text-secondary small">
                                        <?php echo count($categories) ?> categories · <?php echo (int)$totalProducts ?> products
                                    </div>
                                </div>
                                <?php if ($categories) : ?>
                                    <div class="table-responsive">
                                        <table class="table card-table table-vcenter">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th class="w-50">Products</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categories as $c) :
                                                    $count = (int)$c['product_count'];
                                                    $countText = $count === 1 ? '1 product' : $count . ' products';
                                                ?>
                                                    <tr data-id="<?php echo (int)$c['CATEGORY_ID'] ?>" data-name="<?php echo $e($c['CATEGORY_NAME']) ?>" data-count="<?php echo $count ?>">
                                                        <td class="fw-semibold"><?php echo $e($c['CATEGORY_NAME']) ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="progress progress-sm flex-fill">
                                                                    <div class="progress-bar bg-primary" style="width: <?php echo round($count / $maxCount * 100) ?>%" role="progressbar" aria-label="<?php echo $e($countText) ?>"></div>
                                                                </div>
                                                                <span class="text-secondary num text-nowrap" style="min-width: 5.5rem"><?php echo $e($countText) ?></span>
                                                            </div>
                                                        </td>
                                                        <td class="text-end text-nowrap">
                                                            <div class="row-actions">
                                                                <button type="button" class="btn btn-icon btn-outline-secondary js-rename" data-bs-toggle="modal" data-bs-target="#rename-modal" aria-label="Rename <?php echo $e($c['CATEGORY_NAME']) ?>" title="Rename">
                                                                    <i class="ti ti-pencil"></i>
                                                                </button>
                                                                <?php if (is_admin()) : ?>
                                                                <button type="button" class="btn btn-icon btn-outline-danger js-ask-delete" aria-label="Delete <?php echo $e($c['CATEGORY_NAME']) ?>" title="Delete">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="row-confirm d-none align-items-center justify-content-end gap-2">
                                                                <?php if ($count > 0) : ?>
                                                                    <span class="small text-secondary text-wrap text-start">Move its <?php echo $e($countText) ?> to another category first.</span>
                                                                    <button type="button" class="btn btn-sm js-cancel">OK</button>
                                                                <?php else : ?>
                                                                    <span class="small text-secondary">Delete this category?</span>
                                                                    <button type="button" class="btn btn-sm js-cancel">Keep</button>
                                                                    <button type="button" class="btn btn-sm btn-danger js-delete">Delete</button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else : ?>
                                    <div class="card-body text-secondary">No categories yet. Add your first one with the form.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rename-modal" tabindex="-1" aria-labelledby="rename-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="rename-form" novalidate>
                <div class="modal-header">
                    <h2 class="modal-title" id="rename-title">Rename category</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rename-id">
                    <label for="rename-name" class="form-label">Category name</label>
                    <input type="text" class="form-control" id="rename-name" maxlength="100" autocomplete="off" required>
                    <div class="invalid-feedback" id="rename-error"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save name</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        // Field-level error from a 422 response, without the global toast.
        function fieldError($input, $error, message) {
            $input.addClass('is-invalid');
            $error.text(message);
            $input.trigger('focus');
        }

        $('#add-form').on('submit', function (event) {
            event.preventDefault();
            var $input = $('#cname'), name = $.trim($input.val());
            $input.removeClass('is-invalid');
            if (name === '') {
                fieldError($input, $('#cname-error'), 'Enter a category name.');
                return;
            }
            $('#add-btn').prop('disabled', true);
            $.ajax({
                url: 'ajax/operation.php', method: 'POST', global: false,
                data: { action: 'insertCategory', catn: name }
            }).done(function () {
                reloadWith('Added "' + name + '".');
            }).fail(function (xhr) {
                fieldError($input, $('#cname-error'), errorText(xhr));
                $('#add-btn').prop('disabled', false);
            });
        });
        $('#cname, #rename-name').on('input', function () { $(this).removeClass('is-invalid'); });

        // The button opens the modal via data-bs-toggle; this fills in the row's values.
        $(document).on('click', '.js-rename', function () {
            var $row = $(this).closest('tr');
            $('#rename-id').val($row.data('id'));
            $('#rename-name').val($row.attr('data-name')).removeClass('is-invalid');
        });
        $('#rename-modal').on('shown.bs.modal', function () { $('#rename-name').trigger('select'); });

        $('#rename-form').on('submit', function (event) {
            event.preventDefault();
            var $input = $('#rename-name'), name = $.trim($input.val());
            if (name === '') {
                fieldError($input, $('#rename-error'), 'Enter a category name.');
                return;
            }
            $.ajax({
                url: 'ajax/operation.php', method: 'POST', global: false,
                data: { action: 'updatingCat', id: $('#rename-id').val(), name: name }
            }).done(function () {
                reloadWith('Renamed to "' + name + '".');
            }).fail(function (xhr) {
                fieldError($input, $('#rename-error'), errorText(xhr));
            });
        });

        $(document).on('click', '.js-ask-delete', function () {
            var $cell = $(this).closest('td');
            $cell.find('.row-actions').addClass('d-none');
            $cell.find('.row-confirm').removeClass('d-none').addClass('d-flex');
        });
        $(document).on('click', '.js-cancel', function () {
            var $cell = $(this).closest('td');
            $cell.find('.row-confirm').addClass('d-none').removeClass('d-flex');
            $cell.find('.row-actions').removeClass('d-none');
        });
        $(document).on('click', '.js-delete', function () {
            var $row = $(this).closest('tr'), name = $row.attr('data-name');
            $(this).prop('disabled', true);
            $.post('ajax/operation.php', { action: 'deleteCat', id: $row.data('id') }, function () {
                reloadWith('Deleted "' + name + '".');
            });
        });
    </script>
</body>

</html>
