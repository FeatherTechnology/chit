<?php
require '../../ajaxconfig.php';

$id = $_POST['id'];

$qry = $pdo->query("SELECT 
            gi.id,
            gi.guarantor_name,
            CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name
        FROM 
            auction_details ad
        JOIN 
            customer_creation cc ON ad.cus_name = cc.id 
        LEFT JOIN 
            guarantor_info gi ON cc.cus_id = gi.cus_id
        WHERE 
            ad.id = '$id'");

$result = [];

if ($qry->rowCount() > 0) {
    $guarantorResults = $qry->fetchAll(PDO::FETCH_ASSOC);

    foreach ($guarantorResults as $row) {
        // Push the guarantor name (if available)
        if (!empty($row['guarantor_name'])) {
            $result[] = [
                'id' => $row['id'],
                'name' => $row['guarantor_name'],
               
            ];
        }
        // Push the customer name
        $result[] = [
            'id' => null, // Assuming customer doesn't have an id in this context
            'name' => $row['cus_name'],
           
        ];
    }
}

$pdo = null; // Close connection
echo json_encode($result);
?>
