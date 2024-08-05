<?php

require '../../ajaxconfig.php';

$response =array();
//$cus_id = $_POST['cus_id'];
$qry = $pdo->query("SELECT id, first_name FROM customer_creation where reference =1 ");
if ($qry->rowCount() > 0) {
    $response = $qry->fetchAll(PDO::FETCH_ASSOC);
}
$pdo = null; //Close Connection

echo json_encode($response);