<?php
require '../../ajaxconfig.php'; // Make sure this file sets up PDO connection
@session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $date = $_POST['date'];
    $auction_data = $_POST['auction_data']; // Assuming this is provided in POST data
    $max_value = $_POST['max_value'];
    $max_customer = $_POST['max_customer'];

    try {
        // Insert each auction data row into auction_list table
        foreach ($auction_data as $data) {
            $sql = "INSERT INTO auction_list (group_id, date, cus_name, value, inserted_login_id, created_on) 
                    VALUES ('$group_id', '$date', '{$data['cus_name']}', '{$data['value']}', 1, NOW())";
            $pdo->exec($sql);
        }

        // Update the auction_details table with max value and customer name
        $sql = "UPDATE auction_details 
                SET cus_name = '$max_customer', auction_value = '$max_value', status = 2 
                WHERE group_id = '$group_id' AND date = '$date' AND auction_value = (
                    SELECT MAX(auction_value) 
                    FROM auction_details 
                    WHERE group_id = '$group_id' AND date = '$date'
                )";
                
        $pdo->exec($sql);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
