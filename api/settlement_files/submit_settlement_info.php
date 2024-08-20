<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$auction_id = $_POST['auction_id'];
$settle_date = $_POST['settle_date'];
$settle_amount = $_POST['settle_amount'];
$settle_balance = $_POST['settle_balance'];
$payment_type = $_POST['payment_type'];
$settle_type = $_POST['settle_type'];
$bank_name = $_POST['bank_name'];
$settle_cash = $_POST['settle_cash'];
$cheque_no = $_POST['cheque_no'];
$cheque_val = $_POST['cheque_val'];
$cheque_remark = $_POST['cheque_remark'];
$transaction_id = $_POST['transaction_id'];
$transaction_val = $_POST['transaction_val'];
$transaction_remark = $_POST['transaction_remark'];
$balance_amount = $_POST['balance_amount'];
$gua_name = $_POST['gua_name'];
$gua_relationship = $_POST['gua_relationship'];

$qry = $pdo->query("INSERT INTO settlement_info (auction_id, settle_date, settle_amount, settle_balance, payment_type, settle_type, bank_name, settle_cash, cheque_no, cheque_val, cheque_remark, transaction_id, transaction_val, transaction_remark, 
        balance_amount, guarantor_name, guarantor_relationship, insert_login_id, created_on) VALUES ('$auction_id', '$settle_date', '$settle_amount', '$settle_balance', '$payment_type', '$settle_type', '$bank_name', '$settle_cash', '$cheque_no', '$cheque_val', '$cheque_remark', '$transaction_id', 
        '$transaction_val', '$transaction_remark', '$balance_amount', '$gua_name', '$gua_relationship','$user_id', NOW())");
if ($payment_type == "1") {
    // Check if balance_amount is zero
    if ($balance_amount == 0) {
        // Update auction_details status
        $update_query = $pdo->query("UPDATE auction_details SET 
                    status = '3', update_login_id = '$user_id', updated_on = NOW() 
                    WHERE id = '$auction_id'");
        if ($qry && $update_query) {
            $result = 1;
        } else {
            $result = 0;
        }
    } else {
        $result = $qry ? 1 : 0;
    }
} else if ($payment_type == "2") {
    // Always update auction_details status for payment_type 2
    $update_query = $pdo->query("UPDATE auction_details SET 
                status = '3', update_login_id = '$user_id', updated_on = NOW() 
                WHERE id = '$auction_id'");
    $result = $qry && $update_query ? 1 : 0;
} else {
    $result = 0;
}

echo json_encode($result);

// Close the connection
$pdo = null;
?>
