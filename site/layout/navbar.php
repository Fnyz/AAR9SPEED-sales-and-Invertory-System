<?php
// Shared Tabler header and menu. Set $page (e.g. "dashboard") before including;
// the page must already have included site/protected.php.
require_once __DIR__ . '/../../Model/Dashboard.php';

$dash = $dash ?? new Dashboard();
$dash->id = $_SESSION['getId'] ?? 0;
$currentStaff = $dash->getStafftoUpdate()[0] ?? [];

$displayName = trim(($currentStaff['STAFF_FNAME'] ?? '') . ' ' . ($currentStaff['STAFF_LNAME'] ?? ''), " -");
if ($displayName === '') {
    $displayName = $_SESSION['userId'] ?? 'Staff';
}
$navInitials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $displayName), 0, 2)) ?: 'ST';
$navRole = (int)($currentStaff['role_id'] ?? 0) === 1 ? 'Administrator' : 'Staff';

$navItems = [
    ['dashboard', 'Dashboard.php',       'ti-home',          'Dashboard'],
    ['sale',      'Sale.php',            'ti-cash-register', 'New sale'],
    ['product',   'Product.php',         'ti-package',       'Products'],
    ['category',  'Category.php',        'ti-tag',           'Categories'],
    ['Order',     'Securitylogin.php',   'ti-truck',         'Purchase orders'],
    // A list of children renders as a dropdown.
    ['people',    [
        ['customer', 'Customer.php',      'ti-users',          'Customers'],
        ['supplier', 'Supplier.php',      'ti-building-store', 'Suppliers'],
        ['staff',    'SecurityCheck.php', 'ti-shield-lock',    'Staff (admin)'],
    ],                                   'ti-users-group',   'People'],
    ['invoice',   'invoice.php',         'ti-file-invoice',  'Invoices'],
    ['refunds',   'Refund.php',          'ti-receipt-refund', 'Refunds'],
];
$navE = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<header class="navbar navbar-expand-md d-print-none brand-bar">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a href="Dashboard.php" class="navbar-brand d-flex align-items-center gap-2 me-md-3 text-decoration-none" aria-label="AAR9SPEED dashboard">
            <span class="brand-mark" aria-hidden="true">A9</span>
            <span class="brand-name d-none d-sm-inline">AAR9<span>SPEED</span></span>
        </a>
        <div class="navbar-nav flex-row order-md-last align-items-center gap-1">
            <button type="button" class="nav-link px-2 hide-theme-dark btn btn-ghost-secondary" data-theme-set="dark" title="Switch to dark mode" aria-label="Switch to dark mode">
                <i class="ti ti-moon fs-2"></i>
            </button>
            <button type="button" class="nav-link px-2 hide-theme-light btn btn-ghost-secondary" data-theme-set="light" title="Switch to light mode" aria-label="Switch to light mode">
                <i class="ti ti-sun fs-2"></i>
            </button>
            <div class="nav-item dropdown">
                <button type="button" class="nav-link px-2 position-relative btn btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Stock alerts" aria-expanded="false">
                    <i class="ti ti-bell fs-2"></i>
                    <span id="pendings" class="badge bg-red text-red-fg badge-notification badge-pill" hidden></span>
                </button>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                    <div class="card" style="min-width: 20rem">
                        <div class="card-header">
                            <h3 class="card-title">Stock alerts</h3>
                        </div>
                        <ul id="notification-list" class="list-unstyled m-0 py-1">
                            <li class="px-3 py-2 text-secondary">Loading…</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="nav-item dropdown ms-1">
                <button type="button" class="nav-link d-flex align-items-center gap-2 lh-1 p-1 btn btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Account menu" aria-expanded="false">
                    <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold"><?php echo $navE($navInitials) ?></span>
                    <span class="d-none d-xl-flex flex-column text-start">
                        <span class="fw-semibold"><?php echo $navE($displayName) ?></span>
                        <span class="small text-secondary mt-1"><?php echo $navE($navRole) ?></span>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#UpdateAdmin"><i class="ti ti-user-edit me-2"></i>Update my account</button>
                    <div class="dropdown-divider"></div>
                    <a href="Model/logout.php" class="dropdown-item"><i class="ti ti-logout me-2"></i>Log out</a>
                </div>
            </div>
        </div>
    </div>
</header>
<header class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <ul class="navbar-nav">
                    <?php foreach ($navItems as [$key, $href, $icon, $label]) : ?>
                        <?php if (is_array($href)) :
                            $childActive = in_array($page ?? '', array_column($href, 0), true);
                        ?>
                            <li class="nav-item dropdown<?php echo $childActive ? ' active' : '' ?>">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti <?php echo $navE($icon) ?> fs-2"></i></span>
                                    <span class="nav-link-title text-nowrap"><?php echo $navE($label) ?></span>
                                </a>
                                <div class="dropdown-menu">
                                    <?php foreach ($href as [$childKey, $childHref, $childIcon, $childLabel]) : ?>
                                        <a class="dropdown-item<?php echo ($page ?? '') === $childKey ? ' active' : '' ?>" href="<?php echo $navE($childHref) ?>"<?php echo ($page ?? '') === $childKey ? ' aria-current="page"' : '' ?>>
                                            <i class="ti <?php echo $navE($childIcon) ?> me-2"></i><?php echo $navE($childLabel) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php else : ?>
                            <li class="nav-item<?php echo ($page ?? '') === $key ? ' active' : '' ?>">
                                <a class="nav-link" href="<?php echo $navE($href) ?>"<?php echo ($page ?? '') === $key ? ' aria-current="page"' : '' ?>>
                                    <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti <?php echo $navE($icon) ?> fs-2"></i></span>
                                    <span class="nav-link-title text-nowrap"><?php echo $navE($label) ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</header>
<?php include __DIR__ . '/../dataUser.php'; ?>
