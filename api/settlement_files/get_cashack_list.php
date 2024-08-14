<?php
require "../../ajaxconfig.php";

// Get the auction_id from POST data
$auction_id = isset($_POST['auction_id']) ? $_POST['auction_id'] : null;

// Query to join the tables
$qry = $pdo->query("
    SELECT si.id, si.settle_date, si.settle_amount, gi.guarantor_name, si.guarantor_relationship 
    FROM settlement_info si
    LEFT JOIN guarantor_info gi ON si.guarantor_name = gi.id  
    WHERE si.auction_id = '$auction_id'
");

// Check if any rows are returned
if ($qry->rowCount() > 0) {
    $result = [];
    while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
        // Convert settle_date to dd-mm-yyyy format
        if (!empty($row['settle_date'])) {
            $date = new DateTime($row['settle_date']);
            $row['settle_date'] = $date->format('d-m-Y');
        }
    //    $row['settle_amount']?moneyFormatIndia($row['settle_amount']): '';
        $result[] = $row;
    }
    // Output the result as JSON
    echo json_encode($result);
} else {
    echo json_encode([]);
}

$pdo = null; // Close the connection
?>
