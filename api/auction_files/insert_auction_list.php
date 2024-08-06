<?php
require '../../ajaxconfig.php';
@session_start();

$group_id = $_POST['group_id'];
$date = $_POST['date'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$value = $_POST['value'];
$user_id = $_SESSION['user_id'];

$response = array();

try {
    // Prepare the SQL statement
    $sql = "INSERT INTO auction_list (group_id, date, cus_name, value, inserted_login_id) VALUES (:group_id, :date, :cus_name, :value, :user_id)";
    $stmt = $pdo->prepare($sql);
    
    // Create customer name from first and last name
    $cus_name = $first_name . ' ' . $last_name;
    
    // Execute the SQL statement
    $stmt->execute([
        ':group_id' => $group_id,
        ':date' => $date,
        ':cus_name' => $cus_name,
        ':value' => $value,
        ':user_id' => $user_id
    ]);

    if ($stmt->rowCount() > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Row inserted successfully.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to insert row.';
    }
} catch (PDOException $e) {
    $response['status'] = 'error';
    $response['message'] = 'Database error: ' . $e->getMessage();
}

$pdo = null; // Close Connection
echo json_encode($response);
?>
