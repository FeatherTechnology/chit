<?php
require "../../../ajaxconfig.php";

$coll_mode = isset($_POST['coll_mode']) ? $_POST['coll_mode'] : '';
$other_trans_name = isset($_POST['other_trans_name']) ? $_POST['other_trans_name'] : '';

$result = [];
$total_type_1_amount = 0; // Credit amount (type=1)
$total_type_2_amount = 0; // Debit amount (type=2)

$qry = $pdo->query("SELECT coll_mode, type, amount
                    FROM other_transaction
                    WHERE coll_mode = '$coll_mode' 
                    AND name = '$other_trans_name'");

if ($qry->rowCount() > 0) {
    $transactions = $qry->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($transactions as $transaction) {
        // Check if type is 1 (credit) and sum the amount
        if ($transaction['type'] == '1') {
            $total_type_1_amount += $transaction['amount'];
        }

        // Check if type is 2 (debit) and sum the amount
        if ($transaction['type'] == '2' ) {
            $total_type_2_amount += $transaction['amount'];
        }
    }

    // Add the credit and debit totals to the result
    $result['transactions'] = $transactions;
    $result['total_type_1_amount'] = $total_type_1_amount;
    $result['total_type_2_amount'] = $total_type_2_amount;
} else {
    $result['error'] = "No transactions found.";
}

$pdo = null; // Close connection.

echo json_encode($result);
