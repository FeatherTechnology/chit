<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$group_id = $_POST['group_id'];
$cus_id = $_POST['cus_id'];
$auction_id = $_POST['auction_id'];
$cus_mapping_id = $_POST['cus_mapping_id'];
$auction_month = $_POST['auction_month'];
$chit_value = $_POST['chit_value'];
$chit_amount = $_POST['chit_amount'];
$pending = $_POST['pending_amt'];
$payable = $_POST['payable_amnt'];
$collection_amount = $_POST['collection_amount'];
$collection_date = $_POST['collection_date'];
$coll_mode=$_POST['coll_mode'];
$transaction_id=$_POST['transaction_id'];
// Convert the collection_date to 'yyyy-mm-dd' format
$date = DateTime::createFromFormat('d-m-Y', $collection_date); 
$collection_date_formatted = $date->format('Y-m-d');
// Determine the status based on collection amount and chit amount
if ($collection_amount >= $payable) {
    $status = 'Paid';
} else {
    $status = 'Payable';
}

$qry = $pdo->query("INSERT INTO collection (cus_mapping_id,auction_id,group_id,cus_id,auction_month,chit_value,chit_amount,pending,payable,coll_status,collection_date,coll_mode,transaction_id,collection_amount,insert_login_id,created_on
) VALUES ('$cus_mapping_id','$auction_id','$group_id','$cus_id','$auction_month','$chit_value','$chit_amount','$pending','$payable','$status','$collection_date_formatted','$coll_mode','$transaction_id','$collection_amount','$user_id', CURRENT_TIMESTAMP()
)");

if ($qry) {
    $result = 1;
} else {
    $result = 0;
}   
echo json_encode($result);
// Execute the SQL statement

?>
