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

    public function updateCollectStatus($cus_id, $auction_month, $id)
{
    // Fetch the current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Fetch all groups for the customer
    $qry1 = "SELECT DISTINCT ad.group_id
             FROM auction_details ad
             LEFT JOIN group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
             LEFT JOIN customer_creation cc ON gcm.cus_id = cc.id
             WHERE cc.cus_id = :cus_id
               AND ad.status IN (2, 3)
               AND YEAR(ad.date) = :currentYear
               AND MONTH(ad.date) = :currentMonth";

    $statement = $this->pdo->prepare($qry1);
    $statement->execute([
        ':cus_id' => $cus_id,
        ':currentYear' => $currentYear,
        ':currentMonth' => $currentMonth,
    ]);
    $groups = $statement->fetchAll(PDO::FETCH_ASSOC);

    $overallStatus = 'Paid'; 

    foreach ($groups as $group) {
        $group_id = $group['group_id'];

        // Fetch count of customer mappings for this group
        $qryCount = "SELECT id as cc_id
                     FROM group_cus_mapping
                     WHERE grp_creation_id = :group_id
                       AND cus_id = :id";

        $stmtCount = $this->pdo->prepare($qryCount);
        $stmtCount->execute([
            ':group_id' => $group_id,
            ':id' => $id,
        ]);
        $mappings = $stmtCount->fetchAll(PDO::FETCH_ASSOC);

        // Check payment status for each mapping
        foreach ($mappings as $mapping) {
            $cus_mapping_id = $mapping['cc_id'];

            $qry2 = "SELECT c.coll_status
                     FROM collection c
                     LEFT JOIN auction_details ad ON c.auction_id = ad.id
                     WHERE c.cus_mapping_id = :cus_mapping_id
                       AND c.group_id = :group_id
                       AND c.auction_month = :auction_month
                       AND YEAR(ad.date) = :currentYear
                       AND MONTH(ad.date) = :currentMonth
                     ORDER BY c.created_on DESC
                     LIMIT 1";

            $stmt2 = $this->pdo->prepare($qry2);
            $stmt2->execute([
                ':cus_mapping_id' => $cus_mapping_id,
                ':group_id' => $group_id,
                ':auction_month' => $auction_month,
                ':currentYear' => $currentYear,
                ':currentMonth' => $currentMonth,
            ]);
            $result = $stmt2->fetch(PDO::FETCH_ASSOC);
            $coll_status = $result['coll_status'] ?? 'Payable'; // Default to 'Payable' if no status is found

            // If any mapping is 'Payable', set overall status to 'Payable'
            if ($coll_status === 'Payable') {
                $overallStatus = 'Payable';
                break 2; // Exit both loops early as we found a 'Payable' status
            }
        }
    }

    return $overallStatus;
}

}
