<?php
// Low-stock rules, shared by alerts, the bell, the dashboard and stock chips.
//
// Each product may have its own reorder level (item.ITEM_REORDER_LEVEL). When
// it's empty, the shop-wide LOW_STOCK_THRESHOLD from .env applies (default 5).

require_once __DIR__ . '/../config/Database/Database.php';

function low_stock_threshold(): int
{
    Database::loadEnv();
    return max(0, (int)($_ENV['LOW_STOCK_THRESHOLD'] ?? 5));
}

/** SQL for an item's reorder level. $alias is the item table alias, e.g. 'i.'. */
function reorder_level_sql(string $alias = ''): string
{
    return 'COALESCE(' . $alias . 'ITEM_REORDER_LEVEL, ' . low_stock_threshold() . ')';
}

/** Reorder level for an item row (needs ITEM_REORDER_LEVEL in the row). */
function reorder_level(array $item): int
{
    $own = $item['ITEM_REORDER_LEVEL'] ?? null;
    return $own === null || $own === '' ? low_stock_threshold() : (int)$own;
}
