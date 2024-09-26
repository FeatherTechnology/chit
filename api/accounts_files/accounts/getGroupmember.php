<?php
require "../../../ajaxconfig.php";
@session_start();

$group_id = $_POST['group_id'];

if (isset($group_id) && !empty($group_id)) {
    $customer_list_arr = array();
    $taken_auction_qry = "
        SELECT
    ad.cus_name,
    ad.date
FROM
    auction_details ad
WHERE
    group_id = '$group_id' AND (
        MONTH(ad.date) = MONTH(CURDATE()) AND YEAR(ad.date) = YEAR(CURDATE())); 
    ";
    $taken_customers = $pdo->query($taken_auction_qry)->fetchAll(PDO::FETCH_COLUMN);
 $qry = "
        SELECT
        cc.first_name,
        cc.last_name,
        cc.id,
        CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name,
        gcm.joining_month,
        (
        SELECT
            COUNT(*)
        FROM
            group_cus_mapping
        WHERE
            cus_id = gcm.cus_id AND grp_creation_id = '$group_id'
    ) AS chit_count
FROM
    group_cus_mapping gcm
JOIN customer_creation cc ON
    gcm.cus_id = cc.id
JOIN auction_details ad ON gcm.grp_creation_id=ad.group_id
WHERE
    gcm.grp_creation_id = '$group_id'
        AND gcm.joining_month <= ad.auction_month
        AND (MONTH(ad.date) = MONTH(CURDATE()) AND YEAR(ad.date) = YEAR(CURDATE()))
      GROUP BY 
            cc.id
    ";
    $customers = $pdo->query($qry)->fetchAll(PDO::FETCH_ASSOC);  
    foreach ($customers as $customer) {
        $customer_id = $customer['id'];
        $chit_count = $customer['chit_count'];

        $auction_taken_count = count(array_filter($taken_customers, fn($id) => $id == $customer_id)); // Use '==' for comparison
        if ($auction_taken_count <$chit_count) {
            $customer_list_arr[] = $customer;
        }
    }

    $pdo = null; // Close Connection.
    echo json_encode($customer_list_arr);
} else {
    echo json_encode([]);
}
