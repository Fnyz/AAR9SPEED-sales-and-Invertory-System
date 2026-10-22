<?php
// Admin password page for admin-only actions and areas other than Staff and
// Purchase orders: AdminUnlock.php?next=Refund.php. Only pages listed in
// $gateLabels (site/admin_gate_page.php) can be the destination.
$next = basename((string)($_GET['next'] ?? ''));
$unlockable = ['Refund.php', 'Reports.php', 'Logs.php', 'Product.php', 'Customer.php', 'Supplier.php', 'Category.php'];
$destination = in_array($next, $unlockable, true) ? $next : 'Dashboard.php';
$page = ['Refund.php' => 'refunds', 'Reports.php' => 'reports', 'Logs.php' => 'logs', 'Product.php' => 'product',
         'Customer.php' => 'customer', 'Supplier.php' => 'supplier', 'Category.php' => 'category'][$destination] ?? 'dashboard';
include 'site/protected.php';
include 'site/admin_gate.php';
include 'site/admin_gate_page.php';
