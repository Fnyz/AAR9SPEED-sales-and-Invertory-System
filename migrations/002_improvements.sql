-- Widen staff_pass to accommodate bcrypt hashes (60 chars minimum; 255 for headroom)
ALTER TABLE staff MODIFY staff_pass VARCHAR(255);

-- Audit log
CREATE TABLE IF NOT EXISTS logs (
    log_id      INT          NOT NULL AUTO_INCREMENT,
    staff_user  VARCHAR(100) NOT NULL DEFAULT 'unknown',
    action      VARCHAR(100) NOT NULL,
    target      VARCHAR(255) NOT NULL DEFAULT '',
    ip_address  VARCHAR(45)  NOT NULL DEFAULT '',
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Refunds / returns
CREATE TABLE IF NOT EXISTS refunds (
    REFUND_ID       INT          NOT NULL AUTO_INCREMENT,
    TRANS_CODE      VARCHAR(100) NOT NULL,
    ITEM_ID         INT          NOT NULL,
    REFUND_QUANTITY INT          NOT NULL,
    REFUND_REASON   TEXT,
    REFUNDED_BY     VARCHAR(100) NOT NULL,
    REFUND_DATE     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (REFUND_ID),
    KEY idx_trans (TRANS_CODE),
    KEY idx_item  (ITEM_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
