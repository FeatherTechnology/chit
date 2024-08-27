<?php
require '../../ajaxconfig.php';

$group_id = $_POST['group_id'];
$cusMappingID = $_POST['cus_mapping_id'];

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
        ad.group_id = '$group_id'
    GROUP BY ad.group_id, ad.auction_month";

// Fetch next auction details to calculate pending amount
$next_auction_query = "SELECT
        ad.auction_month,
        ad.chit_amount,
        COALESCE(SUM(cl.collection_amount), 0) AS collection_amount
    FROM
        auction_details ad
    LEFT JOIN collection cl ON ad.group_id = cl.group_id AND ad.auction_month = cl.auction_month
    WHERE
        ad.group_id = '$group_id'
        AND ad.auction_month > (
            SELECT MAX(auction_month)
            FROM auction_details
            WHERE group_id = '$group_id'
        )
    GROUP BY ad.auction_month
    ORDER BY ad.auction_month ASC
    LIMIT 1"; // Fetch only the next auction

$current_statement = $pdo->query($current_auction_query);
$next_statement = $pdo->query($next_auction_query);

$response = [];
if ($current_statement->rowCount() > 0) {
    $current_row = $current_statement->fetch(PDO::FETCH_ASSOC);

    // Fetch the next auction data
    $next_row = $next_statement->fetch(PDO::FETCH_ASSOC);
    $next_collection_amount = $next_row ? $next_row['collection_amount'] : 0;
    $next_chit_amount = $next_row ? $next_row['chit_amount'] : 0;

    // Calculate the total amount collected in the current auction month
    $total_collected = $current_row['collection_amount'];
    $remaining_balance = 0;
    // Initial payable amount for the current month is chit_amount + pending amount (if any)
    $initial_payable_amnt = $current_row['chit_amount'] + ($remaining_balance > 0 ? $remaining_balance : 0);

    // Calculate the remaining balance for the current auction month
    $remaining_balance = $initial_payable_amnt - $total_collected;

    // Set the payable amount for the current month
    $payable_amnt = ($remaining_balance > 0) ? $remaining_balance : 0;

    // Calculate pending amount for the next auction month if applicable
    $pending_amt = ($remaining_balance > 0) ? $remaining_balance : 0;

    $response = [
        'success' => true,
        'group_name' => $current_row['grp_name'],
        'auction_month' => $current_row['auction_month'],
        'date' => date('d-m-Y', strtotime($current_row['date'])),
        'chit_value' => $current_row['chit_value'],
        'chit_amount' => $current_row['chit_amount'],
        'pending_amt' => ($next_row ? $pending_amt : 0), // Show pending amount only for next month
        'pend_amt' => $next_collection_amount, // Hidden field value for next auction pending amount
        'payable_amnt' => $payable_amnt,
        'payableAmount' => $initial_payable_amnt // Hidden field value for next month's payable amount
    ];
} else {
    $response = ['success' => false];
}

echo json_encode($response);
?>
