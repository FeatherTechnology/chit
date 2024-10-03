<?php
require '../../ajaxconfig.php';

$response =array();
$cus_id = $_POST['cus_id'];

$qry = $pdo->query("SELECT DISTINCT
        ad.auction_month
    FROM 
        auction_details ad
    LEFT JOIN 
        group_creation gc ON ad.group_id = gc.grp_id
    LEFT JOIN 
        group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
    LEFT JOIN 
        customer_creation cc ON gcm.cus_id = cc.id
WHERE
    cc.cus_id = '$cus_id' AND ad.cus_name=cc.id Group BY ad.id");
if ($qry->rowCount() > 0) {
    $response = $qry->fetchAll(PDO::FETCH_ASSOC);
}
$pdo = null; //Close Connection

echo json_encode($response);