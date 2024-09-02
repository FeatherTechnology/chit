<?php
require '../../ajaxconfig.php';

$group_id = $_POST['group_id'];
$cusMappingID = $_POST['cus_mapping_id'];
$currentMonth = date('m'); // Get the current month
$currentYear = date('Y'); // Get the current year

// Fetch start month from the group_creation table
$start_month_query = "SELECT start_month FROM group_creation WHERE grp_id = '$group_id'";
$start_month_result = $pdo->query($start_month_query);
$start_month_row = $start_month_result->fetch(PDO::FETCH_ASSOC);
$start_month = $start_month_row['start_month'];

$auction_month_current = ($currentYear * 12 + $currentMonth) - (substr($start_month, 0, 4) * 12 + substr($start_month, 5, 2)) + 1;

// Fetch current auction details including collections
$current_auction_query = "SELECT
        gc.grp_name,
        ad.auction_month,
        ad.date,
        gc.chit_value,
        ad.chit_amount,
        COALESCE(SUM(cl.collection_amount), 0) AS collection_amount
    FROM
        auction_details ad
    LEFT JOIN group_creation gc ON ad.group_id = gc.grp_id
    LEFT JOIN collection cl ON ad.group_id = cl.group_id AND ad.auction_month = cl.auction_month AND cl.cus_mapping_id = '$cusMappingID'
    WHERE
        ad.group_id = '$group_id' AND ad.auction_month = $auction_month_current
    GROUP BY ad.group_id, ad.auction_month";

// Fetch previous auction details to calculate pending amount
$previous_auction_query = "SELECT
    ad.auction_month,
    ad.chit_amount,
    COALESCE(SUM(cl.collection_amount), 0) AS collection_amount
FROM
    auction_details ad
LEFT JOIN
    collection cl ON ad.group_id = cl.group_id
    AND ad.auction_month = cl.auction_month
    AND cl.cus_mapping_id = '$cusMappingID'
WHERE
    ad.group_id = '$group_id'
    AND ad.auction_month IN (
        SELECT auction_month
        FROM auction_details
        WHERE group_id = '$group_id'
        AND auction_month < $auction_month_current
        ORDER BY auction_month DESC
    )
GROUP BY
    ad.auction_month
ORDER BY
    ad.auction_month DESC";

// Execute the queries
$current_statement = $pdo->query($current_auction_query);
$previous_statement = $pdo->query($previous_auction_query);

$response = [];
if ($current_statement->rowCount() > 0) {
    $current_row = $current_statement->fetch(PDO::FETCH_ASSOC);

    // Initialize the pending amount to zero
    $pending_amount = 0;

    // Loop through the previous auction data to calculate pending amount
    $count = 0;
    while ($previous_row = $previous_statement->fetch(PDO::FETCH_ASSOC)) {
        $previous_collection_amount = (int)$previous_row['collection_amount'];
        $previous_chit_amount = (int)$previous_row['chit_amount'];
        $pending_amount += max(0, $previous_chit_amount - $previous_collection_amount);
        $count++;

        // Limit the calculation to the previous 2 months only
       
    }

    // Calculate the total amount collected in the current auction month
    $total_collected = (int)$current_row['collection_amount'];

    // Initial payable amount for the current month is chit_amount + pending amount (if any)
    $initial_payable_amnt = (int)$current_row['chit_amount'] + $pending_amount;

    // Calculate the remaining balance for the current auction month
    $remaining_balance = $initial_payable_amnt - $total_collected;

    // Set the payable amount for the current month
    $payable_amnt = max(0, $remaining_balance);

    $response = [
        'success' => true,
        'group_name' => $current_row['grp_name'],
        'auction_month' => $current_row['auction_month'],
        'date' => date('d-m-Y', strtotime($current_row['date'])),
        'chit_value' => $current_row['chit_value'],
        'chit_amount' => (int)$current_row['chit_amount'],
        'pending_amt' => $pending_amount,
        'payable_amnt' => $payable_amnt,
        'payableAmount' => $initial_payable_amnt 
    ];
} else {
    $response = ['success' => false];
}

echo json_encode($response);
?>
