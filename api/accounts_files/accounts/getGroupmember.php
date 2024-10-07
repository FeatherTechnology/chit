<?php
require "../../../ajaxconfig.php";
@session_start();

$group_id = $_POST['group_id'];

if (isset($group_id) && !empty($group_id)) {
    $customer_list_arr = array();
    $taken_auction_qry = "
        SELECT
    ad.cus_name,
    ad.date,
    ad.auction_month
FROM
    auction_details ad
WHERE
    group_id = '$group_id' AND (
        MONTH(ad.date) < MONTH(CURDATE()) AND YEAR(ad.date) = YEAR(CURDATE())); 
    ";
    $taken_customers = $pdo->query($taken_auction_qry)->fetchAll(PDO::FETCH_COLUMN);
   
    $transaction_qry = "
    SELECT group_mem 
    FROM other_transaction 
    WHERE group_id = '$group_id' AND type IN (1, 2)
HAVING COUNT(DISTINCT type) = 2;
";
$transaction_customers = $pdo->query($transaction_qry)->fetchAll(PDO::FETCH_COLUMN);
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

    // Filter customers based on their chit count, auction participation, and transactions in the other_transaction table
    foreach ($customers as $customer) {
        $customer_id = $customer['id'];
        $chit_count = $customer['chit_count'];

        // Count how many times this customer has taken part in auctions
        $auction_taken_count = count(array_filter($taken_customers, fn($id) => $id == $customer_id)); // Use '==' for comparison
        // Check if customer exists in other_transaction

        $in_other_transaction = in_array($customer_id, $transaction_customers);

        // Check eligibility: customer can participate if they have chits left to use and they are not in other_transaction
        if ($auction_taken_count < $chit_count  && !$in_other_transaction) {
            $customer_list_arr[] = $customer;
        }
    }

    $pdo = null; // Close Connection.
    echo json_encode($customer_list_arr);
} else {
    echo json_encode([]);
}