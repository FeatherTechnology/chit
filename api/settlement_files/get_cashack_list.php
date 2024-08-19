<?php
require "../../ajaxconfig.php";

// Get the auction_id from POST data
$auction_id = isset($_POST['auction_id']) ? $_POST['auction_id'] : null;

// Query to join the tables
$qry = $pdo->query("
    SELECT si.id, si.settle_date, si.settle_cash, si.cheque_val, si.transaction_val,gi.guarantor_name, si.guarantor_relationship 
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
      // Check if guarantor_name is -1
      $row['total_amount'] = (
        ($row['settle_cash'] ? (float)$row['settle_cash'] : 0) +
        ($row['cheque_val'] ? (float)$row['cheque_val'] : 0) +
        ($row['transaction_val'] ? (float)$row['transaction_val'] : 0)
       
    );
    $row['balance_amount'] = $total_amount;
      if ($row['guarantor_name'] === null || $row['guarantor_name'] == -1) {
        $row['guarantor_name'] = 'Customer';
    }
        $result[] = $row;
    }
    // Output the result as JSON
    echo json_encode($result);
} else {
    echo json_encode([]);
}

$pdo = null; // Close the connection
?>
