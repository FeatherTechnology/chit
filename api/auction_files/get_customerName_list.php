<?php
require '../../ajaxconfig.php';
@session_start();

$group_id = $_POST['group_id'];
$auction_month = $_POST['auction_month'];

if (isset($group_id) && !empty($group_id) && isset($auction_month) && !empty($auction_month)) {
    $customer_list_arr = array();

    // Get the list of customer IDs who have taken the auction in any previous month for the same group
    $taken_auction_qry = "
        SELECT 
            cus_name 
        FROM 
            auction_details 
        WHERE 
            group_id = '$group_id' 
            AND auction_month < '$auction_month'
    ";
    $taken_customers = $pdo->query($taken_auction_qry)->fetchAll(PDO::FETCH_COLUMN);

    // Get eligible customers for the current auction month
 $qry = "
        SELECT 
            cc.first_name, 
            cc.last_name,
            cc.id, 
            CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name,
            gcm.joining_month,
            (SELECT COUNT(*) FROM group_cus_mapping WHERE cus_id = gcm.cus_id AND grp_creation_id = '$group_id') AS chit_count
        FROM 
            group_cus_mapping gcm
        JOIN 
            customer_creation cc ON gcm.cus_id = cc.id
        WHERE 
            gcm.grp_creation_id = '$group_id'
            AND gcm.joining_month <= '$auction_month'
        GROUP BY 
            cc.id
    ";

    $customers = $pdo->query($qry)->fetchAll(PDO::FETCH_ASSOC);

    // Filter customers based on their chit count and auction participation
    foreach ($customers as $customer) {
        $customer_id = $customer['id'];
        $chit_count = $customer['chit_count'];

        // Count how many times this customer has taken part in auctions
        $auction_taken_count = count(array_filter($taken_customers, fn($id) => $id == $customer_id)); // Use '==' for comparison
        
        // // Debugging output
        // echo "Customer ID: $customer_id, Taken Count: $auction_taken_count, Chit Count: $chit_count\n";

        // Check eligibility: customer can participate if they have chits left to use
        if ($auction_taken_count <$chit_count) {
            $customer_list_arr[] = $customer;
        }
    }

    $pdo = null; // Close Connection.
    echo json_encode($customer_list_arr);
} else {
    echo json_encode([]);
}
