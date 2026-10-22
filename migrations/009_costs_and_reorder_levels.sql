-- Cost prices (for profit) and per-product reorder levels.
--
-- item.ITEM_COST              what one unit costs the shop (latest purchase cost)
-- item.ITEM_REORDER_LEVEL     alert when stock is at or below this; NULL = the
--                             shop-wide LOW_STOCK_THRESHOLD in .env (default 5)
-- transactions.TRANS_UNIT_COST  the item's cost at the moment of the sale, so
--                             profit stays correct after costs change
-- purchase_order.PORD_UNIT_COST what was paid per unit on that order
--
-- All new columns allow NULL: existing products simply have no cost yet.

ALTER TABLE item
    ADD COLUMN ITEM_COST          DECIMAL(10,2) NULL AFTER ITEM_PRICE,
    ADD COLUMN ITEM_REORDER_LEVEL INT           NULL AFTER ITEM_QUANTITY;

ALTER TABLE transactions
    ADD COLUMN TRANS_UNIT_COST DECIMAL(10,2) NULL AFTER TRANS_PRICE;

ALTER TABLE purchase_order
    ADD COLUMN PORD_UNIT_COST DECIMAL(10,2) NULL AFTER PORD_QUANTITY;
