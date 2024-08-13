<?php
require '../../ajaxconfig.php';
@session_start();

$group_id = $_POST['group_id'];

if (isset($group_id) && !empty($group_id)) {
    $customer_list_arr = array();

    // Query to get the highest value for each cus_id within the auction_modal table for the given group_id
    $max_value_qry = $pdo->prepare("
        SELECT 
            cus_name, 
            MAX(value) AS max_value
        FROM 
            auction_modal
        WHERE 
            group_id = :group_id
        GROUP BY 
            cus_name
    ");
    $max_value_qry->bindParam(':group_id', $group_id, PDO::PARAM_STR);
    $max_value_qry->execute();

    $max_values = $max_value_qry->fetchAll(PDO::FETCH_ASSOC);

    // Prepare an array to store the highest value for each customer
    $highest_values = [];
    foreach ($max_values as $row) {
        $highest_values[$row['cus_name']] = $row['max_value'];
    }

    // Query to get the customers associated with the given group_id from group_cus_mapping
    $qry = $pdo->prepare("
        SELECT 
            cc.first_name, 
            cc.last_name,
            cc.id, 
            CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name
        FROM 
            group_cus_mapping gcm
        JOIN 
            customer_creation cc ON gcm.cus_id = cc.id
        WHERE 
            gcm.grp_creation_id = :group_id
        GROUP BY 
            cc.id
    ");
    $qry->bindParam(':group_id', $group_id, PDO::PARAM_STR);
    $qry->execute();

    $customers = $qry->fetchAll(PDO::FETCH_ASSOC);

    // Filter out customers who have the highest value and not eligible based on timing
    foreach ($customers as $customer) {
        $cus_name = $customer['cus_name'];
        if (!isset($highest_values[$cus_name]) || $highest_values[$cus_name] == 0) {
            $customer_list_arr[] = $customer;
        }
    }

    $pdo = null; // Close Connection.
    echo json_encode($customer_list_arr);
} else {
    echo json_encode([]);
}
?>
