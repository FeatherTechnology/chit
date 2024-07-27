<?php
require "../../ajaxconfig.php";
$id = $_POST['id'];

$qry = $pdo->query("DELETE FROM `group_cus_mapping` WHERE `id`='$id'");
if ($qry) {
    $result = 1;
} else {
    $result = 2;
}

echo json_encode($result);
