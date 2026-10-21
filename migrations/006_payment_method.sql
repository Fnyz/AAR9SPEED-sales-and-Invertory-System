-- Record how each sale was paid (cash or GCash) and the GCash reference number.
-- Existing sales were all cash. Each sales line of an invoice repeats the same
-- values, like TRANS_PAYMENT already does.

ALTER TABLE transactions
    ADD COLUMN TRANS_PAYMENT_METHOD VARCHAR(20) NOT NULL DEFAULT 'CASH' AFTER TRANS_PAYMENT,
    ADD COLUMN TRANS_PAYMENT_REF    VARCHAR(50) NULL AFTER TRANS_PAYMENT_METHOD,
    ADD INDEX idx_trans_code (trans_code);
