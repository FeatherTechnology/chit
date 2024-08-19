<?php
require '../../ajaxconfig.php';

$group_id = $_POST['group_id'];
$cus_id = $_POST['cus_id'];

// Fetch the current auction details
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
LEFT JOIN collection cl ON ad.group_id = cl.group_id 
WHERE
    ad.group_id = '$group_id'
GROUP BY ad.group_id, ad.auction_month";

// Fetch the next auction details to calculate pending amount
$next_auction_query = "SELECT
    ad.auction_month,
    COALESCE(SUM(cl.collection_amount), 0) AS collection_amount
FROM
    auction_details ad
LEFT JOIN collection cl ON ad.group_id = cl.group_id 
WHERE
    ad.group_id = '$group_id'
    AND ad.auction_month > (
        SELECT MAX(auction_month)
        FROM auction_details
        WHERE group_id = '$group_id'
    )
GROUP BY ad.auction_month";

$current_statement = $pdo->query($current_auction_query);
$next_statement = $pdo->query($next_auction_query);

$response = [];
if ($current_statement->rowCount() > 0) {
    $current_row = $current_statement->fetch(PDO::FETCH_ASSOC);

    // Fetch the next auction data
    $next_row = $next_statement->fetch(PDO::FETCH_ASSOC);
    $next_collection_amount = $next_row ? $next_row['collection_amount'] : 0;

    $pending_amt = $current_row['chit_amount'] - $current_row['collection_amount']; // Calculate pending amount
    $payable_amnt = $current_row['chit_amount']; // Default to chit amount for the current auction

    // If there is a next auction, calculate payable amount including pending
    if ($next_row) {
        $payable_amnt += $pending_amt; // Update payable amount for the next auction
    }

    $response = [
        'success' => true,
        'group_name' => $current_row['grp_name'],
        'auction_month' => $current_row['auction_month'],
        'date' => date('d-m-Y', strtotime($current_row['date'])),
        'chit_value' => ($current_row['chit_value']),
        'chit_amount' => ($current_row['chit_amount']),
        'pending_amt' => ($next_collection_amount), // Show pending amount for the next auction
        'pend_amt' => $next_collection_amount, // Hidden field value
        'payable_amnt' => ($payable_amnt),
        'payableAmount' => $payable_amnt // Hidden field value
    ];
} else {
    $response = ['success' => false];
}

echo json_encode($response);

function moneyFormatIndia($num) {
    // Convert the number to float and format it
    $num = floatval($num);
    $num = number_format($num, 2, '.', ',');
    
    // Remove trailing zeros and the decimal point if there are no decimal places
    $parts = explode('.', $num);
    if (count($parts) > 1 && $parts[1] === '00') {
        return $parts[0];
    }
    
    return $num;
}
?>
