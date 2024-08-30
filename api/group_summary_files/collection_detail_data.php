<?php
require '../../ajaxconfig.php';

$response = array();

if (isset($_POST['group_id'])) { 
    $group_id = $_POST['group_id'];
    
    try {
        $qry = "SELECT
    ad.id,
    cc.id,
    ad.id AS auction_id,
    ad.group_id,
    cc.cus_id,
    CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name,
    cc.mobile1,
    pl.place,
    (
        SELECT
            GROUP_CONCAT(sc.occupation SEPARATOR ', ')
        FROM 
            SOURCE sc
        WHERE
            sc.cus_id = cc.cus_id
    ) AS occupations,
    gcm.id AS cus_mapping_id,
    gc.chit_value,
    gc.grace_period,
    ad.date,
    ad.chit_amount,
    ad.auction_month
FROM
    auction_details ad
LEFT JOIN group_cus_mapping gcm ON
    ad.group_id = gcm.grp_creation_id
LEFT JOIN customer_creation cc ON
    gcm.cus_id = cc.id
LEFT JOIN place pl ON
    cc.place = pl.id
LEFT JOIN group_creation gc ON
    ad.group_id = gc.grp_id
JOIN users us ON
    FIND_IN_SET(gc.branch, us.branch)  GROUP BY
    cc.cus_id
ORDER BY 
   cc.cus_id";
               

        // Execute the query
        $stmt = $pdo->query($qry);

        // Fetch the result
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } else {
            echo json_encode([]);
        }

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    // Close the PDO connection
    $pdo = null;
}
?>
