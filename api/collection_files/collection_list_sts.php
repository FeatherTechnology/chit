

<?php
require '../../ajaxconfig.php';
@session_start();

class CollectStsClass
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateCollectStatus($auction_id, $group_id, $cus_id, $auction_month, $chit_amount)
    {
        $coll_status = 'Payable';

        $query = "SELECT collection_amount, collection_date FROM collection 
                  WHERE auction_id = :auction_id AND group_id = :group_id 
                  AND cus_id = :cus_id AND auction_month = :auction_month";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':auction_id' => $auction_id,
            ':group_id' => $group_id,
            ':cus_id' => $cus_id,
            ':auction_month' => $auction_month
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $collection_amount = $row['collection_amount'];
            $collection_date = $row['collection_date'];

            if ($collection_amount >= $chit_amount) {
                $coll_status = 'Paid';
            } else {
                $due_date = date('Y-m-t', strtotime($auction_month . '-01'));
                $current_date = date('Y-m-d');
                if ($current_date > $due_date) {
                    $coll_status = 'Payable';
                } else {
                    $coll_status = 'Payable';
                }
            }
        } else {
            $due_date = date('Y-m-t', strtotime($auction_month . '-01'));
            $current_date = date('Y-m-d');

            if ($current_date > $due_date) {
                $coll_status = 'Payable';
            } else {
                $coll_status = 'Payable';
            }
        }

        $update_query = "UPDATE collection 
                         SET coll_status = :coll_status WHERE
                         auction_id = :auction_id AND group_id = :group_id 
                         AND cus_id = :cus_id AND auction_month = :auction_month";
        $update_stmt = $this->pdo->prepare($update_query);
        $update_stmt->execute([
            ':coll_status' => $coll_status,
            ':auction_id' => $auction_id,
            ':group_id' => $group_id,
            ':cus_id' => $cus_id,
            ':auction_month' => $auction_month
        ]);

        return $coll_status;
    }
}

?>
