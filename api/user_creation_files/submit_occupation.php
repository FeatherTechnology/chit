<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];
$occ_type = $_POST['occ_type'];
$id = $_POST['id'];

$qry = $pdo->query("SELECT * FROM `occupation` WHERE REPLACE(TRIM(occ_type), ' ', '') = REPLACE(TRIM('$occ_type'), ' ', '') ");
if ($qry->rowCount() > 0) {
    $result = 0; //already Exists.

} else {
    if ($id != '0' && $id != '') {
        $pdo->query("UPDATE `occupation` SET `occ_type`='$occ_type',`update_login_id`='$user_id',`updated_on`=now() WHERE `id`='$id' ");
        $result = 1; //update

    } else {
        $pdo->query("INSERT INTO `occupation`(`occ_type`, `insert_login_id`, `created_on`) VALUES ('$occ_type','$user_id', now())");
        $result = 2; //Insert
    }
}

echo json_encode($result);
