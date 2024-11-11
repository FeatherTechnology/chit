<?php
require '../../ajaxconfig.php';
@session_start();

class GraceperiodClass
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateGraceStatus($cus_mapping_id, $group_id)
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $current_date = date('Y-m-d');

        // Query to calculate unpaid amount
        $qry1 = "SELECT 
                    COALESCE(SUM(ad.chit_amount), 0) - COALESCE(SUM(c.collection_amount), 0) AS unpaid_amount
                 FROM 
                    group_creation gc
                 JOIN 
                    auction_details ad ON gc.grp_id = ad.group_id
                 LEFT JOIN 
                    collection c ON ad.group_id = c.group_id
                 WHERE 
                    c.cus_mapping_id = '$cus_mapping_id' 
                    AND c.group_id = '$group_id' 
                    AND ad.status IN (2, 3)
                    AND YEAR(ad.date) < '$currentYear'
                    AND MONTH(ad.date) < '$currentMonth'
                    AND (c.collection_date <= CURRENT_DATE OR c.collection_date IS NULL)";

        $result = $this->pdo->query($qry1)->fetch(PDO::FETCH_ASSOC);
        $unpaid_amount = $result['unpaid_amount'] ?? 0;

        // If unpaid amount is greater than 0, return 'red'
        if ($unpaid_amount > 0) {
            return 'red';
        }

        // Fetch the grace period for the group
        $qryCount = "SELECT gc.grace_period, ad.date
                     FROM auction_details ad
                     LEFT JOIN group_creation gc ON ad.group_id = gc.grp_id
                     WHERE ad.group_id = '$group_id'
                       AND YEAR(ad.date) = '$currentYear'
                       AND MONTH(ad.date) = '$currentMonth'";

        $group_data = $this->pdo->query($qryCount)->fetch(PDO::FETCH_ASSOC);

        if ($group_data) {
            $auction_date = $group_data['date'] ?? '';
            $grace_period = $group_data['grace_period'] ?? 0;

            // Calculate the grace end date
            $grace_end_date = date('Y-m-d', strtotime($auction_date . ' + ' . $grace_period . ' days'));

            // If within the grace period, return 'orange', else return 'red'
            if ($grace_end_date >= $current_date) {
                return 'orange'; // Within grace period
            } else {
                return 'red'; // Past grace period
            }
        }

        return 'orange'; // Default return 'orange' if no other condition is met
    }
}
