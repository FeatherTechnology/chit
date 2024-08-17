<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];
include 'collectionStatus.php';

$collectionSts = new CollectionStsClass($pdo);

$group_id = $_POST['group_id'];
$cus_id = $_POST['cus_id'];
$auction_id = $_POST['auction_id'];
$auction_month = $_POST['auction_month'];
$chit_value = $_POST['chit_value'];
$chit_amount = $_POST['chit_amount'];
$pending = $_POST['pending_amt'];
$payable = $_POST['payable_amnt'];
$collection_amount = $_POST['collection_amount'];
$collection_date = $_POST['collection_date']; // Ensure this is being sent correctly


// Update collection status using the method
$status = $collectionSts->updateCollectionStatus($auction_id, $group_id, $cus_id, $auction_month, $chit_amount);

// Build the SQL query
$qry = "INSERT INTO collection (
    auction_id, group_id, cus_id, auction_month, chit_value, chit_amount, pending, payable,
    coll_status, collection_date, collection_amount, insert_login_id, created_on
) VALUES (
    '$auction_id', '$group_id', '$cus_id', '$auction_month', '$chit_value', '$chit_amount', '$pending', '$payable',
    '$status', '$collection_date', '$collection_amount', '$user_id', NOW()
)";

// Execute the SQL statement
if ($pdo->query($qry)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
