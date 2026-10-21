-- Automations: scheduler bookkeeping and low-stock alert de-duplication

-- Last time each scheduled task ran ('scheduler', 'daily_report')
CREATE TABLE IF NOT EXISTS automation_runs (
    task        VARCHAR(50) NOT NULL,
    last_run_at DATETIME    NOT NULL,
    PRIMARY KEY (task)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Items already reported as low on stock; cleared when the item is restocked
CREATE TABLE IF NOT EXISTS stock_alerts (
    item_id     INT      NOT NULL,
    alerted_qty INT      NOT NULL,
    alerted_at  DATETIME NOT NULL,
    PRIMARY KEY (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
