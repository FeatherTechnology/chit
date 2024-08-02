<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];
$total_members = intval($_POST['total_members']);
$total_months = intval($_POST['total_months']);
$group_id = $pdo->quote($_POST['group_id']); // Properly quote to prevent SQL injection
$cus_name = $pdo->quote($_POST['cus_name']); // Properly quote to prevent SQL injection

// Check the current count of customer mappings for the group
$stmt = $pdo->query("SELECT COUNT(*) FROM group_cus_mapping WHERE grp_creation_id = $group_id");
$current_count = $stmt->fetchColumn();

if ($current_count < $total_members) {
    $qry = $pdo->query("INSERT INTO group_cus_mapping (grp_creation_id, cus_id, insert_login_id, created_on) VALUES ($group_id, $cus_name, '$user_id', NOW())");
    if ($qry) {
        // After insertion, check if the count of mappings equals total_members
        $stmt = $pdo->query("SELECT COUNT(*) FROM group_cus_mapping WHERE grp_creation_id = $group_id");
        $new_count = $stmt->fetchColumn();

        if ($new_count == $total_members) {
            // Check the total_months condition
            $stmt = $pdo->query("SELECT COUNT(*) FROM auction_details WHERE group_id = $group_id");
            $auction_count = $stmt->fetchColumn();

            if ($total_months == $auction_count) {
                // Update the status in the group_creation table
                $stmt = $pdo->prepare("UPDATE group_creation SET status = 'Proceed' WHERE id = ?");
                if ($stmt->execute([$group_id])) {
                    $response = ['result' => 1]; // Success
                } else {
                    $response = ['result' => 2]; // Failure
                }
            } else {
                $response = ['result' => 1, 'message' => 'Status not updated']; // Success, but status not updated
            }
        } else {
            $response = ['result' => 1, 'message' => 'Status not updated']; // Success, but status not updated
        }
    } else {
        $response = ['result' => 2]; // Failure
    }
} else {
    // Customer mapping limit exceeded
    $response = ['result' => 3, 'message' => 'Customer Mapping Limit is Exceeded'];
}

echo json_encode($response);
?>
