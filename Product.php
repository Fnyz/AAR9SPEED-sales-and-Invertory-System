<?php
$page = "product";
$pageTitle = "Products";
include 'site/protected.php';
require_once "./Model/Product.php";

$prod = new Product();

// Mark sold-out items inactive (the item trigger only covers later updates).
$prod->getConnect()->exec("UPDATE item SET ITEM_STATUS = 'INACTIVE' WHERE ITEM_QUANTITY = 0 AND ITEM_STATUS <> 'INACTIVE'");

$threshold    = (int)($_ENV['LOW_STOCK_THRESHOLD'] ?? 5);
$products     = $prod->showActiveWithNames();
$categories   = $prod->getCategory();
$suppliers    = $prod->getSupplier();
$pendingCount = (int)($prod->getPending()[0]['total_pending'] ?? 0);
$outCount     = (int)($prod->getInactive()[0]['total_pending'] ?? 0);

// Services (oil change, repairs) are stored with a huge quantity instead of real stock.
const SERVICE_QTY = 999999;

$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);

$rows = [];
$counts = ['all' => 0, 'low' => 0, 'ok' => 0, 'service' => 0];
foreach ($products as $p) {
    $qty = (int)$p['ITEM_QUANTITY'];
    if ($qty >= SERVICE_QTY) {
        $group = 'service'; $chip = ['bg-secondary-lt', 'Service'];
    } elseif ($qty <= $threshold) {
        $group = 'low'; $chip = [$qty <= 1 ? 'bg-red-lt' : 'bg-yellow-lt', $qty . ' left'];
    } else {
        $group = 'ok'; $chip = ['bg-green-lt', number_format($qty) . ' in stock'];
    }
    $counts['all']++;
    $counts[$group]++;
    $words = preg_split('/\s+/', trim($p['ITEM_NAME']));
    $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? substr($words[0] ?? '', 1), 0, 1));
    $rows[] = $p + ['group' => $group, 'chip' => $chip, 'qty' => $qty, 'initials' => $initials];
}
$restockable = array_filter($rows, fn($r) => $r['group'] !== 'service');
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
                            <div class="page-pretitle">Inventory</div>
                            <h1 class="page-title">Products</h1>
                        </div>
                        <div class="col-auto ms-auto d-flex flex-wrap gap-2">
                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#restock-modal" data-item-id="">
                                <i class="ti ti-truck-delivery me-2"></i>Restock
                            </button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-modal">
                                <i class="ti ti-plus me-2"></i>Add product
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-body">
                <div class="container-xl">
                    <div class="card">
                        <div class="card-header flex-wrap gap-2">
                            <ul class="nav nav-tabs card-header-tabs flex-wrap" role="tablist" aria-label="Filter products">
                                <?php foreach (['all' => 'All', 'low' => 'Low stock', 'ok' => 'In stock', 'service' => 'Services'] as $key => $label) : ?>
                                    <li class="nav-item" role="presentation">
                                        <button type="button" class="nav-link<?php echo $key === 'all' ? ' active' : '' ?>" role="tab" aria-selected="<?php echo $key === 'all' ? 'true' : 'false' ?>" data-group="<?php echo $key ?>">
                                            <?php echo $label ?>
                                            <span class="badge <?php echo $key === 'low' && $counts['low'] > 0 ? 'bg-yellow-lt' : 'bg-secondary-lt' ?> ms-2"><?php echo $counts[$key] ?></span>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="pendingProduct.php">Pending <span class="badge bg-secondary-lt ms-2"><?php echo $pendingCount ?></span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="InactiveProduct.php">Out of stock <span class="badge <?php echo $outCount > 0 ? 'bg-red-lt' : 'bg-secondary-lt' ?> ms-2"><?php echo $outCount ?></span></a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body border-bottom py-3">
                            <div class="d-flex flex-wrap gap-2">
                                <div class="input-icon flex-fill" style="min-width: 14rem; max-width: 24rem">
                                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                                    <label for="product-search" class="visually-hidden">Search products</label>
                                    <input type="search" id="product-search" class="form-control" placeholder="Search name or code" autocomplete="off">
                                </div>
                                <label for="category-filter" class="visually-hidden">Category</label>
                                <select id="category-filter" class="form-select w-auto">
                                    <option value="">All categories</option>
                                    <?php foreach ($categories as $c) : ?>
                                        <option value="<?php echo (int)$c['CATEGORY_ID'] ?>"><?php echo $e($c['CATEGORY_NAME']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Code</th>
                                        <th>Category</th>
                                        <th>Supplier</th>
                                        <th class="text-end">Price</th>
                                        <th>Stock</th>
                                        <th><span class="visually-hidden">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody id="product-rows">
                                    <?php foreach ($rows as $r) : ?>
                                        <tr data-group="<?php echo $r['group'] ?>"
                                            data-category="<?php echo (int)$r['CATEGORY_ID'] ?>"
                                            data-search="<?php echo $e(strtolower($r['ITEM_NAME'] . ' ' . $r['ITEM_CODE'])) ?>"
                                            data-item-id="<?php echo (int)$r['ITEM_ID'] ?>"
                                            data-code="<?php echo $e($r['ITEM_CODE']) ?>"
                                            data-name="<?php echo $e($r['ITEM_NAME']) ?>"
                                            data-price="<?php echo $e($r['ITEM_PRICE']) ?>"
                                            data-desc="<?php echo $e($r['ITEM_DESC']) ?>"
                                            data-image="<?php echo $e($r['ITEM_IMAGE']) ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php if ($r['ITEM_IMAGE']) : ?>
                                                        <span class="avatar avatar-md rounded" style="background-image: url('<?php echo $e($r['ITEM_IMAGE']) ?>')" role="img" aria-label="<?php echo $e($r['ITEM_NAME']) ?>"></span>
                                                    <?php else : ?>
                                                        <span class="avatar avatar-md rounded bg-primary-lt text-primary fw-bold" aria-hidden="true"><?php echo $e($r['initials']) ?></span>
                                                    <?php endif; ?>
                                                    <div class="text-truncate" style="max-width: 18rem">
                                                        <div class="fw-semibold"><?php echo $e($r['ITEM_NAME']) ?></div>
                                                        <div class="small text-secondary text-truncate"><?php echo $e($r['ITEM_DESC']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="code text-secondary"><?php echo $e($r['ITEM_CODE']) ?></td>
                                            <td><?php echo $e($r['CATEGORY_NAME'] ?? '—') ?></td>
                                            <td><?php echo $e($r['SUPPLIER_COMPANY'] ?? '—') ?></td>
                                            <td class="text-end num fw-semibold"><?php echo $e($peso($r['ITEM_PRICE'])) ?></td>
                                            <td><span class="badge <?php echo $r['chip'][0] ?> stock-chip"><?php echo $e($r['chip'][1]) ?></span></td>
                                            <td class="text-end text-nowrap">
                                                <button type="button" class="btn btn-icon btn-outline-secondary js-edit" data-bs-toggle="modal" data-bs-target="#edit-modal" aria-label="Edit <?php echo $e($r['ITEM_NAME']) ?>" title="Edit"><i class="ti ti-pencil"></i></button>
                                                <?php if ($r['group'] !== 'service') : ?>
                                                    <button type="button" class="btn btn-outline-secondary ms-1" data-bs-toggle="modal" data-bs-target="#restock-modal" data-item-id="<?php echo (int)$r['ITEM_ID'] ?>">Restock</button>
                                                <?php else : ?>
                                                    <span class="btn btn-outline-secondary ms-1 invisible" aria-hidden="true">Restock</span>
                                                <?php endif; ?>
                                                <a href="ProductView.php?code=<?php echo urlencode($r['ITEM_CODE']) ?>" class="btn btn-ghost-primary ms-1">Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body text-center text-secondary" id="no-results" hidden>No products match your search.</div>
                        <div class="card-footer text-secondary small" id="showing"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add product -->
    <div class="modal fade" id="add-modal" tabindex="-1" aria-labelledby="add-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" action="ajax/itemInsert.php?action=insert" method="post" enctype="multipart/form-data">
                <?php echo csrf_field() ?>
                <div class="modal-header">
                    <h2 class="modal-title" id="add-title">Add product</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required" for="add-name">Product name</label>
                            <input type="text" class="form-control" id="add-name" name="Product" maxlength="20" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required" for="add-code">Product code</label>
                            <input type="text" class="form-control code" id="add-code" name="Code" inputmode="numeric" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="add-category">Category</label>
                            <select class="form-select" id="add-category" name="category" required>
                                <option value="" selected disabled>Choose a category</option>
                                <?php foreach ($categories as $c) : ?>
                                    <option value="<?php echo (int)$c['CATEGORY_ID'] ?>"><?php echo $e($c['CATEGORY_NAME']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="add-supplier">Supplier</label>
                            <select class="form-select" id="add-supplier" name="supplier" required>
                                <option value="" selected disabled>Choose a supplier</option>
                                <?php foreach ($suppliers as $s) : ?>
                                    <option value="<?php echo (int)$s['SUPPLIER_ID'] ?>"><?php echo $e($s['SUPPLIER_COMPANY']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="add-price">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="add-price" name="Price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required" for="add-qty">Starting quantity</label>
                            <input type="number" class="form-control" id="add-qty" name="Quantity" min="1" step="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="add-desc">Description</label>
                            <textarea class="form-control" id="add-desc" name="Descrip" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="add-image">Photo</label>
                            <input type="file" class="form-control" id="add-image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                            <div class="form-hint">JPG, PNG, WebP or GIF, up to 5 MB.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-secondary small me-auto">New products are listed under Pending until you activate them.</span>
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="save" value="1">Add product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit product -->
    <div class="modal fade" id="edit-modal" tabindex="-1" aria-labelledby="edit-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="ajax/updateProduct.php?action=update" method="post" enctype="multipart/form-data">
                <?php echo csrf_field() ?>
                <input type="hidden" name="ids" id="edit-item-id">
                <input type="hidden" name="id" id="edit-code-hidden">
                <div class="modal-header">
                    <h2 class="modal-title" id="edit-title">Edit product</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar avatar-xl rounded" id="edit-photo" aria-hidden="true"></span>
                        <div>
                            <div class="text-secondary small">Product code</div>
                            <div class="code fw-semibold" id="edit-code"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required" for="edit-name">Product name</label>
                        <input type="text" class="form-control" id="edit-name" name="prod" maxlength="20" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required" for="edit-price">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="edit-price" name="price" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="edit-desc">Description</label>
                        <textarea class="form-control" id="edit-desc" name="desc" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="form-label" for="edit-image">Replace photo</label>
                        <input type="file" class="form-control" id="edit-image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        <div class="form-hint">Leave empty to keep the current photo. Stock is changed with Restock.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="save" value="1">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Restock -->
    <div class="modal fade" id="restock-modal" tabindex="-1" aria-labelledby="restock-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="restock-form" novalidate>
                <div class="modal-header">
                    <h2 class="modal-title" id="restock-title">Restock</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="restock-item">Product</label>
                        <select class="form-select" id="restock-item" required>
                            <option value="" selected disabled>Choose a product</option>
                            <?php foreach ($restockable as $r) : ?>
                                <option value="<?php echo (int)$r['ITEM_ID'] ?>" data-qty="<?php echo $r['qty'] ?>"><?php echo $e($r['ITEM_NAME'] . ' · #' . $r['ITEM_CODE'] . ' · ' . ($r['SUPPLIER_COMPANY'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="restock-qty">Units received</label>
                        <input type="number" class="form-control" id="restock-qty" min="1" step="1" inputmode="numeric">
                        <div class="invalid-feedback" id="restock-error"></div>
                    </div>
                    <div class="d-flex justify-content-between text-secondary">
                        <span>On hand <span class="num fw-semibold text-body" id="restock-onhand">—</span></span>
                        <span>After restock <span class="num fw-semibold text-body" id="restock-total">—</span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="restock-save">Add stock</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'site/layout/scripts.php' ?>
    <script>
        // Filtering: tab group + category + search, all client-side.
        var activeGroup = 'all';
        function applyFilters() {
            var query = $.trim($('#product-search').val()).toLowerCase();
            var category = $('#category-filter').val();
            var shown = 0, total = 0;
            $('#product-rows tr').each(function () {
                var $row = $(this);
                total++;
                var match = (activeGroup === 'all' || $row.data('group') === activeGroup)
                    && (!category || String($row.data('category')) === category)
                    && (query === '' || $row.attr('data-search').indexOf(query) !== -1);
                $row.prop('hidden', !match);
                if (match) shown++;
            });
            $('#no-results').prop('hidden', shown > 0);
            $('#showing').text(shown === total ? 'Showing all ' + total + ' active products' : 'Showing ' + shown + ' of ' + total + ' active products');
        }
        $('[data-group]').filter('button').on('click', function () {
            activeGroup = $(this).data('group');
            $('[data-group]').filter('button').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            applyFilters();
        });
        $('#product-search').on('input', applyFilters);
        $('#category-filter').on('change', applyFilters);
        $(applyFilters);

        // Edit: fill the modal from the row.
        $(document).on('click', '.js-edit', function () {
            var $row = $(this).closest('tr');
            $('#edit-item-id').val($row.data('item-id'));
            $('#edit-code-hidden').val($row.attr('data-code'));
            $('#edit-code').text('#' + $row.attr('data-code'));
            $('#edit-name').val($row.attr('data-name'));
            $('#edit-price').val($row.attr('data-price'));
            $('#edit-desc').val($row.attr('data-desc'));
            $('#edit-image').val('');
            var image = $row.attr('data-image');
            $('#edit-photo').css('background-image', image ? 'url("' + image.replace(/"/g, '%22') + '")' : '')
                .toggleClass('bg-primary-lt', !image);
        });

        // Restock
        function updateRestockPreview() {
            var $opt = $('#restock-item option:selected');
            var onHand = $opt.val() ? Number($opt.data('qty')) : null;
            var add = parseInt($('#restock-qty').val(), 10);
            $('#restock-onhand').text(onHand === null ? '—' : onHand.toLocaleString());
            $('#restock-total').text(onHand === null || !(add > 0) ? '—' : (onHand + add).toLocaleString());
        }
        $('#restock-modal').on('show.bs.modal', function (event) {
            var itemId = $(event.relatedTarget).attr('data-item-id');
            $('#restock-item').val(itemId || '');
            $('#restock-qty').val('').removeClass('is-invalid');
            updateRestockPreview();
        }).on('shown.bs.modal', function () {
            $($('#restock-item').val() ? '#restock-qty' : '#restock-item').trigger('focus');
        });
        $('#restock-item, #restock-qty').on('input change', function () {
            $('#restock-qty').removeClass('is-invalid');
            updateRestockPreview();
        });
        $('#restock-form').on('submit', function (event) {
            event.preventDefault();
            var itemId = $('#restock-item').val(), qty = parseInt($('#restock-qty').val(), 10);
            var name = $('#restock-item option:selected').text().split(' · ')[0];
            if (!itemId) {
                $('#restock-item').trigger('focus');
                return;
            }
            if (!(qty > 0)) {
                $('#restock-qty').addClass('is-invalid');
                $('#restock-error').text('Enter how many units arrived (1 or more).');
                $('#restock-qty').trigger('focus');
                return;
            }
            $('#restock-save').prop('disabled', true);
            $.post('ajax/ProdOperation.php', { action: 'addStock', item_id: itemId, quantity: qty }, function (data) {
                reloadWith('Added ' + qty + ' to ' + name + '. Now ' + Number(data.quantity).toLocaleString() + ' in stock.');
            }, 'json').always(function () {
                $('#restock-save').prop('disabled', false);
            });
        });
    </script>
</body>

</html>
