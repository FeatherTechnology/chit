<?php
require '../../ajaxconfig.php';
@session_start();

$group_id = $_POST['group_id'];

if (isset($group_id) && !empty($group_id)) {
    $customer_list_arr = array();
    $qry = $pdo->query("SELECT 
        cc.first_name, 
        cc.last_name,
        cc.id, 
        CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name
    FROM 
        group_cus_mapping gcm
    JOIN 
        customer_creation cc ON gcm.cus_id = cc.id
    WHERE 
        gcm.grp_creation_id = '$group_id'");

    if ($qry->rowCount() > 0) {
        while ($customerInfo = $qry->fetch(PDO::FETCH_ASSOC)) {
            $customer_list_arr[] = $customerInfo;
        }
    }

    $pdo = null; // Close Connection.
    echo json_encode($customer_list_arr);
} else {
    echo json_encode([]);
}
?>

