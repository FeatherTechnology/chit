<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$groupid = $_POST['groupid'];
$cus_name = $_POST['cus_name'];

$qry = $pdo->query("INSERT INTO `group_cus_mapping`(`grp_creation_id`, `cus_id`, `insert_login_id`, `created_on`) VALUES ('$groupid','$cus_name','$user_id',now())");
if ($qry) {
    $result = 1;
} else {
    $result = 2;
}

echo json_encode($result);
