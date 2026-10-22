<?php
require_once __DIR__ . '/../config/Database/Database.php';

/** Reads the audit log written by site/audit.php (log_action). */
class Log extends Database
{
    // Filter groups => SQL LIKE patterns on logs.action.
    public const TYPES = [
        'signin'   => ['Sign-ins', ['login%', 'logout%', 'admin_unlock%']],
        'sales'    => ['Sales and refunds', ['sale', 'refund%', 'report%']],
        'products' => ['Products and stock', ['product%', 'price_change', 'cost_change', 'order%']],
        'people'   => ['Customers, suppliers and staff', ['customer%', 'supplier%', 'staff%', 'profile%', 'category%']],
    ];

    /**
     * One page of entries, newest first. $filters: user, type, from, to (Y-m-d,
     * inclusive), q (text in the details). Returns [rows, total matching].
     */
    public function search(array $filters, int $page, int $perPage): array
    {
        $where = [];
        $params = [];
        if (($filters['user'] ?? '') !== '') {
            $where[] = 'staff_user = :user';
            $params[':user'] = $filters['user'];
        }
        if (isset(self::TYPES[$filters['type'] ?? ''])) {
            $likes = [];
            foreach (self::TYPES[$filters['type']][1] as $n => $pattern) {
                $likes[] = "action LIKE :type$n";
                $params[":type$n"] = $pattern;
            }
            $where[] = '(' . implode(' OR ', $likes) . ')';
        }
        if (($filters['from'] ?? '') !== '') {
            $where[] = 'created_at >= :from';
            $params[':from'] = $filters['from'];
        }
        if (($filters['to'] ?? '') !== '') {
            $where[] = 'created_at < :to + INTERVAL 1 DAY';
            $params[':to'] = $filters['to'];
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = '(target LIKE :q OR action LIKE :q2)';
            $params[':q'] = '%' . $filters['q'] . '%';
            $params[':q2'] = '%' . str_replace(' ', '_', $filters['q']) . '%';
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $conn = $this->getConnect();

        $count = $conn->prepare("SELECT COUNT(*) FROM logs $whereSql");
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $stmt = $conn->prepare(
            "SELECT log_id, staff_user, action, target, ip_address, created_at FROM logs $whereSql
             ORDER BY created_at DESC, log_id DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)(max(0, $page - 1) * $perPage)
        );
        $stmt->execute($params);
        return [$stmt->fetchAll(PDO::FETCH_ASSOC), $total];
    }

    /** Usernames that appear in the log, for the filter. */
    public function users(): array
    {
        return $this->getConnect()->query("SELECT DISTINCT staff_user FROM logs ORDER BY staff_user")->fetchAll(PDO::FETCH_COLUMN);
    }
}
