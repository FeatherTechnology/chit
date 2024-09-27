<?php
require '../../../ajaxconfig.php';

$id = $_POST['id'];
$group_id =$_POST['group_id'];
$group_mem =$_POST['group_mem'];
$auction_month =$_POST['auction_month'];
$qry = $pdo->query("DELETE FROM `other_transaction` WHERE id='$id'");
$qry1 =$pdo->query("DELETE FROM `settlement_info` WHERE group_id='$group_id' AND group_mem ='$group_mem' AND auction_month");
if ($qry) {
    $result = 1;
} else {
    $result = 2;
}

$pdo = null; //Close connection.

echo json_encode($result);