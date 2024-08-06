<?php
require '../../ajaxconfig.php';
@session_start();

$cus_name = $_POST['cus_name'];
$group_id = $_POST['group_id'];
$date = $_POST['date'];
$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => ''];

try {
    // Prepare the SQL statement
    $sql = "INSERT INTO auction_list (group_id, date, cus_name, inserted_login_id) VALUES (:group_id, :date, :cus_name, :insert_login_id)";
    $stmt = $pdo->prepare($sql);
    
    // Execute the SQL statement
    $stmt->execute([
        ':group_id' => $group_id,
        ':date' => $date,
        ':cus_name' => $cus_name,
        ':insert_login_id' => $user_id // Adjusted parameter name
    ]);

    // Check if the insertion was successful
    if ($stmt->rowCount() > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Customer mapping inserted successfully.';
    } else {
        $response['message'] = 'Failed to insert customer mapping.';
    }
} catch (PDOException $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$pdo = null; // Close Connection
?>
