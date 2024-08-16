<?php
require '../../ajaxconfig.php';

$id = $_POST['id'];

$qry = $pdo->query("SELECT 
            gi.id,
            gi.guarantor_name
        FROM 
            auction_details ad
        JOIN 
            customer_creation cc ON ad.cus_name = cc.id 
        JOIN 
            guarantor_info gi ON cc.cus_id = gi.cus_id
        WHERE 
            ad.id = '$id'");

if ($qry->rowCount() > 0) {
    $result = $qry->fetchAll(PDO::FETCH_ASSOC);
} else {
    $result = []; // No records found
}

$pdo = null; // Close connection
echo json_encode($result);
?>
