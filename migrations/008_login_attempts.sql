-- Failed password attempts, used to slow down password guessing on the sign-in
-- page and the admin password page. See site/login_limit.php.

CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT         NOT NULL AUTO_INCREMENT,
    scope        VARCHAR(20) NOT NULL,             -- 'login' or 'admin'
    username     VARCHAR(50) NOT NULL DEFAULT '',
    ip           VARCHAR(45) NOT NULL DEFAULT '',
    attempted_at DATETIME    NOT NULL,
    PRIMARY KEY (id),
    KEY idx_user (scope, username, attempted_at),
    KEY idx_ip (scope, ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
