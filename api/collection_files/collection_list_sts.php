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

    public function updateCollectStatus($cus_id, $auction_month)
    {
        // Fetch all groups for the customer
        $qry1 = "SELECT DISTINCT ad.group_id
                  FROM auction_details ad
                  LEFT JOIN group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
                  LEFT JOIN customer_creation cc ON gcm.cus_id = cc.id
                  WHERE cc.cus_id = :cus_id
                    AND ad.status IN (2, 3)";

        $statement = $this->pdo->prepare($qry1);
        $statement->execute([':cus_id' => $cus_id]);
        $groups = $statement->fetchAll(PDO::FETCH_ASSOC);

        $overallStatus = 'Paid'; // Default status is "Paid"

        foreach ($groups as $group) {
            $group_id = $group['group_id'];

            // Query to fetch the most recent collection record for the same group
            $qry2 = "SELECT c.coll_status
                     FROM collection c
                     WHERE c.cus_id = :cus_id
                       AND c.group_id = :group_id
                       AND c.auction_month = :auction_month
                     ORDER BY c.created_on DESC
                     LIMIT 1";

            $stmt2 = $this->pdo->prepare($qry2);
            $stmt2->execute([
                ':cus_id' => $cus_id,
                ':group_id' => $group_id,
                ':auction_month' => $auction_month
            ]);

            $result = $stmt2->fetch(PDO::FETCH_ASSOC);
            $coll_status = $result['coll_status'] ?? 'Payable'; // Default to 'Payable' if no status is found

            // If any group has a 'Payable' status, set overall status to 'Payable'
            if ($coll_status === 'Payable') {
                $overallStatus = 'Payable';
                break; // Exit loop early as we found a 'Payable' status
            }
        }

        return $overallStatus;
    }
}
?>