<?php
require_once __DIR__ . '/../config/Database/Database.php';

/**
 * Sales reports for a date range [$from, $to) (dates as 'Y-m-d', $to exclusive).
 *
 * Profit only counts sale lines that have a cost (transactions.TRANS_UNIT_COST,
 * saved at the time of sale). Sales made before a product had a cost are
 * reported as revenue with no profit figure, and the "costed" share says how
 * much of the revenue the profit covers.
 */
class Report extends Database
{
    public const GROUPS = ['day' => 'Day', 'product' => 'Product', 'staff' => 'Staff'];

    // Shared sums for every grouping.
    private const SUMS = "COUNT(DISTINCT t.trans_code) AS invoices,
                    COALESCE(SUM(t.TRANS_QUANTITY), 0) AS units,
                    COALESCE(SUM(t.TRANS_PRICE), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN t.TRANS_UNIT_COST IS NOT NULL THEN t.TRANS_PRICE END), 0) AS costed_revenue,
                    COALESCE(SUM(t.TRANS_UNIT_COST * t.TRANS_QUANTITY), 0) AS cost";

    /** Totals for the whole range, plus refunds. */
    public function summary(string $from, string $to): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT " . self::SUMS . " FROM transactions t
             WHERE t.TRANS_CREATED_AT >= :from AND t.TRANS_CREATED_AT < :to"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        $row = self::withProfit($stmt->fetch(PDO::FETCH_ASSOC));
        return $row + $this->refunds($from, $to);
    }

    /** Rows for one grouping (see GROUPS), biggest first except days (in date order). */
    public function grouped(string $group, string $from, string $to): array
    {
        switch ($group) {
            case 'product':
                $sql = "SELECT i.ITEM_NAME AS label, i.ITEM_CODE AS detail, " . self::SUMS . "
                        FROM transactions t JOIN item i ON i.ITEM_ID = t.ITEM_ID
                        WHERE t.TRANS_CREATED_AT >= :from AND t.TRANS_CREATED_AT < :to
                        GROUP BY t.ITEM_ID ORDER BY revenue DESC";
                break;
            case 'staff':
                $sql = "SELECT COALESCE(s.STAFF_USER, 'Removed account') AS label,
                               TRIM(CONCAT(COALESCE(s.STAFF_FNAME, ''), ' ', COALESCE(s.STAFF_LNAME, ''))) AS detail, " . self::SUMS . "
                        FROM transactions t LEFT JOIN staff s ON s.STAFF_ID = t.STAFF_ID
                        WHERE t.TRANS_CREATED_AT >= :from AND t.TRANS_CREATED_AT < :to
                        GROUP BY t.STAFF_ID ORDER BY revenue DESC";
                break;
            default:
                $sql = "SELECT DATE(t.TRANS_CREATED_AT) AS label, '' AS detail, " . self::SUMS . "
                        FROM transactions t
                        WHERE t.TRANS_CREATED_AT >= :from AND t.TRANS_CREATED_AT < :to
                        GROUP BY DATE(t.TRANS_CREATED_AT) ORDER BY label";
        }
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute([':from' => $from, ':to' => $to]);
        return array_map([self::class, 'withProfit'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /** Refunds recorded in the range, valued at the price on the original invoice. */
    private function refunds(string $from, string $to): array
    {
        try {
            $stmt = $this->getConnect()->prepare(
                "SELECT COUNT(*) AS refund_count, COALESCE(SUM(r.REFUND_QUANTITY), 0) AS refund_units,
                        COALESCE(SUM(r.REFUND_QUANTITY * line.unit_price), 0) AS refund_value
                 FROM refunds r
                 LEFT JOIN (SELECT trans_code, ITEM_ID, SUM(TRANS_PRICE) / NULLIF(SUM(TRANS_QUANTITY), 0) AS unit_price
                            FROM transactions GROUP BY trans_code, ITEM_ID) line
                        ON line.trans_code = r.TRANS_CODE AND line.ITEM_ID = r.ITEM_ID
                 WHERE r.REFUND_DATE >= :from AND r.REFUND_DATE < :to"
            );
            $stmt->execute([':from' => $from, ':to' => $to]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // refunds table only exists after migrations/002_improvements.sql
            return ['refund_count' => 0, 'refund_units' => 0, 'refund_value' => 0];
        }
    }

    private static function withProfit(array $row): array
    {
        foreach (['revenue', 'costed_revenue', 'cost'] as $key) {
            $row[$key] = round((float)$row[$key], 2);
        }
        $row['invoices'] = (int)$row['invoices'];
        $row['units'] = (int)$row['units'];
        $row['profit'] = $row['costed_revenue'] > 0 ? round($row['costed_revenue'] - $row['cost'], 2) : null;
        $row['margin'] = $row['costed_revenue'] > 0 ? round($row['profit'] / $row['costed_revenue'] * 100, 1) : null;
        $row['costed_share'] = $row['revenue'] > 0 ? $row['costed_revenue'] / $row['revenue'] * 100 : 0; // unrounded
        return $row;
    }
}
