-- Messages waiting to be posted to Discord by a signed-in staff browser.
-- Used when the server can't make outgoing connections (NOTIFY_VIA_BROWSER=1),
-- e.g. on InfinityFree's free plan. See site/notify.php.

CREATE TABLE IF NOT EXISTS notify_outbox (
    id          INT          NOT NULL AUTO_INCREMENT,
    content     TEXT         NOT NULL,
    created_at  DATETIME     NOT NULL,
    claim_token VARCHAR(32)  NULL,
    claimed_at  DATETIME     NULL,
    sent_at     DATETIME     NULL,
    PRIMARY KEY (id),
    KEY idx_pending (sent_at, claimed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
