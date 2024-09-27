<?php
require "../../../ajaxconfig.php";
@session_start();

$user_id = $_SESSION['user_id'];
$coll_mode = $_POST['coll_mode'];
$bank_id = $_POST['bank_id'];
$trans_category = $_POST['trans_category'];
$group_id = $_POST['group_id'];
$other_trans_name = $_POST['other_trans_name'];
$group_mem = $_POST['group_mem'];
$cat_type = $_POST['cat_type'];
$other_ref_id = $_POST['other_ref_id'];
$other_trans_id = $_POST['other_trans_id'];
$other_amnt = $_POST['other_amnt'];
$auction_month = $_POST['auction_month'];
$other_remark = $_POST['other_remark'];
$settle_date_formatted = date('Y-m-d'); // Correct the date formatting

// Insert into `other_transaction`
$qry = $pdo->query("INSERT INTO `other_transaction`( `coll_mode`, `bank_id`, `trans_cat`, `group_id`, `name`, `group_mem`, `type`, `ref_id`, `trans_id`, `amount`, `auction_month`, `remark`, `insert_login_id`, `created_on`) 
VALUES ('$coll_mode', '$bank_id', '$trans_category', '$group_id', '$other_trans_name', '$group_mem', '$cat_type', '$other_ref_id', '$other_trans_id', '$other_amnt', '$auction_month', '$other_remark', '$user_id', NOW())");

// Fetch the auction ID
$auction_qry = $pdo->query("SELECT id FROM auction_details WHERE group_id = '$group_id' AND auction_month = '$auction_month'");
$auction_id = $auction_qry->fetchColumn(); // Fetch a single auction ID

if ($trans_category == '7') {
    if ($coll_mode == 1) {
        $qry1 = $pdo->query("INSERT INTO settlement_info (auction_id, settle_date, settle_amount, settle_balance, payment_type, settle_type, bank_id, settle_cash, cheque_no, cheque_val, cheque_remark, transaction_id, transaction_val, transaction_remark, balance_amount, guarantor_name, guarantor_relationship, insert_login_id, created_on) 
        VALUES ('$auction_id', '$settle_date_formatted', '$other_amnt', '$other_amnt', '1', '$coll_mode', '$bank_id', '$other_amnt', NULL, NULL, NULL, NULL, NULL, NULL, '$other_amnt', '0', 'Customer', '$user_id', NOW())");
    } else {
        $qry1 = $pdo->query("INSERT INTO settlement_info (auction_id, settle_date, settle_amount, settle_balance, payment_type, settle_type, bank_id, settle_cash, cheque_no, cheque_val, cheque_remark, transaction_id, transaction_val, transaction_remark, balance_amount, guarantor_name, guarantor_relationship, insert_login_id, created_on) 
        VALUES ('$auction_id', '$settle_date_formatted', '$other_amnt', '$other_amnt', '1', '$coll_mode', '$bank_id', NULL, NULL, NULL, NULL, '$other_trans_id', '$other_amnt', NULL, '$other_amnt', '0', 'Customer', '$user_id', NOW())");
    }
}
// Check if both queries were successful
if ($qry) {
    $result = 1;
} else {
    $result = 2;
}

// Return the result as JSON
echo json_encode($result);
