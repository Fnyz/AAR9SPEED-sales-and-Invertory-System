<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Refund extends Database
{
    public ?string $trans_code  = null;
    public ?int    $item_id     = null;
    public ?int    $quantity    = null;
    public ?string $reason      = null;
    public ?string $refunded_by = null;

    public function processRefund(): bool
    {
        $db   = new Database();
        $conn = $db->getConnect();
        $conn->beginTransaction();
        try {
            $ins = $conn->prepare(
                "INSERT INTO refunds (TRANS_CODE, ITEM_ID, REFUND_QUANTITY, REFUND_REASON, REFUNDED_BY)
                 VALUES (:tc, :iid, :qty, :reason, :by)"
            );
            $ins->execute([
                ':tc'     => $this->trans_code,
                ':iid'    => $this->item_id,
                ':qty'    => $this->quantity,
                ':reason' => $this->reason,
                ':by'     => $this->refunded_by,
            ]);
            $upd = $conn->prepare(
                "UPDATE items SET ITEM_QUANTITY = ITEM_QUANTITY + :qty WHERE ITEM_ID = :id"
            );
            $upd->execute([':qty' => $this->quantity, ':id' => $this->item_id]);
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            return false;
        }
    }

    public function getAllRefunds(): array
    {
        $db   = new Database();
        $conn = $db->getConnect();
        $stmt = $conn->prepare(
            "SELECT r.*, i.ITEM_NAME
             FROM refunds r
             JOIN items i ON r.ITEM_ID = i.ITEM_ID
             ORDER BY r.REFUND_DATE DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRefundsByTrans(string $transCode): array
    {
        $db   = new Database();
        $conn = $db->getConnect();
        $stmt = $conn->prepare(
            "SELECT r.*, i.ITEM_NAME
             FROM refunds r
             JOIN items i ON r.ITEM_ID = i.ITEM_ID
             WHERE r.TRANS_CODE = :tc
             ORDER BY r.REFUND_DATE DESC"
        );
        $stmt->execute([':tc' => $transCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
