<?php
require '../../ajaxconfig.php';

$cus_id = $_POST['cus_id'];

$qry = $pdo->query("SELECT 
            gi.id,
            gi.guarantor_name
        FROM 
            customer_creation cc 
        LEFT JOIN 
            guarantor_info gi ON cc.cus_id = gi.cus_id
        WHERE 
         cc.cus_id ='$cus_id'
        GROUP BY 
            cc.id, gi.id"); 

$result = [];

if ($qry->rowCount() > 0) {
    $guarantorResults = $qry->fetchAll(PDO::FETCH_ASSOC); // Fetch the results
}

$pdo = null; // Close connection
echo json_encode($guarantorResults); // Corrected: echo the results, not $result
?>
