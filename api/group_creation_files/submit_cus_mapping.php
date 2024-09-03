<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];
$total_members = intval($_POST['total_members']);
$group_id = $pdo->quote($_POST['group_id']); // Properly quote to prevent SQL injection
$cus_name = $pdo->quote($_POST['cus_name']); // Properly quote to prevent SQL injection

// Check the current count of customer mappings for the group
$stmt = $pdo->query("SELECT COUNT(*) FROM group_cus_mapping WHERE grp_creation_id = $group_id");
$current_count = $stmt->fetchColumn();

$response = ['result' => 2]; // Default to failure

if ($current_count < $total_members) {
    $qry = $pdo->query("INSERT INTO group_cus_mapping (grp_creation_id, cus_id, insert_login_id, created_on) VALUES ($group_id, $cus_name, '$user_id', NOW())");
    
    if ($qry) {
        // Check if count now equals total members
        $stmt = $pdo->query("SELECT COUNT(*) FROM group_cus_mapping WHERE grp_creation_id = $group_id");
        $current_count = $stmt->fetchColumn();

        if ($current_count == $total_members) {
            // Update the status in the group_creation table
            $update_stmt = $pdo->query("UPDATE group_creation SET status = '2' WHERE grp_id = $group_id");
            if ($update_stmt) {
                $response['result'] = 1; // Success
            } else {
                $response['result'] = 2; // Failure
            }
        } else {
            $response['result'] = 1; // Success, but not yet full
        }
    } else {
        // Failure during insertion
        $response['result'] = 2;
    }
} else {
    // Customer mapping limit exceeded
    $response = ['result' => 3, 'message' => 'Customer Mapping Limit is Exceeded'];
}

echo json_encode($response);
?>
