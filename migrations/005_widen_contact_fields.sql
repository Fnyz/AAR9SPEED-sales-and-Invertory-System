-- Widen name, email and address columns to match the limits the forms and
-- ajax/operation.php validate (names 50, addresses and emails 100). The original
-- sizes (e.g. STAFF_EMAIL VARCHAR(20)) made valid input fail under strict mode.

ALTER TABLE customer
    MODIFY CUS_FNAME   VARCHAR(50)  DEFAULT NULL,
    MODIFY CUS_LNAME   VARCHAR(50)  DEFAULT NULL,
    MODIFY CUS_ADDRESS VARCHAR(100) DEFAULT NULL,
    MODIFY CUS_EMAIL   VARCHAR(100) DEFAULT NULL;

ALTER TABLE supplier
    MODIFY SUPPLIER_FNAME   VARCHAR(50)  DEFAULT NULL,
    MODIFY SUPPLIER_LNAME   VARCHAR(50)  DEFAULT NULL,
    MODIFY SUPPLIER_ADDRESS VARCHAR(100) DEFAULT NULL;

ALTER TABLE staff
    MODIFY STAFF_FNAME   VARCHAR(50)  DEFAULT NULL,
    MODIFY STAFF_LNAME   VARCHAR(50)  DEFAULT NULL,
    MODIFY STAFF_ADDRESS VARCHAR(100) DEFAULT NULL,
    MODIFY STAFF_EMAIL   VARCHAR(100) DEFAULT NULL;

-- Match the collation of the original tables (utf8mb4_general_ci). On MySQL 8,
-- tables created by migrations 002/003 without an explicit collation got
-- utf8mb4_0900_ai_ci, and joins such as refunds.TRANS_CODE = transactions.trans_code
-- then fail with "Illegal mix of collations".
ALTER TABLE logs            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE refunds         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE automation_runs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE stock_alerts    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
