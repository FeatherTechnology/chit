<?php
require "../../../ajaxconfig.php";

$qry = $pdo->query("SELECT id, grp_id FROM group_creation where status BETWEEN 2 AND 3 ");
if ($qry->rowCount() > 0) {
    $response = $qry->fetchAll(PDO::FETCH_ASSOC);
}
$pdo = null; //Close Connection

echo json_encode($response);