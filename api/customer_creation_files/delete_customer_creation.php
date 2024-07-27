<?php
require "../../ajaxconfig.php";

$id = $_POST['id'];

$qry = $pdo->query("DELETE FROM `customer_creation` WHERE id = '$id' ");
if ($qry) {
    $result = '0';
} else {
    $result = '1';
}

echo json_encode($result);