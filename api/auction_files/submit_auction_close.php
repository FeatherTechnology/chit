<?php
require '../../ajaxconfig.php';
$user_id = $_SESSION['user_id'];
$group_id = $_POST['group_id'];
$date = $_POST['date'];
$values = $_POST['values'] ?? null; // Use null coalescing operator to set default value
$date = date('Y-m-d', strtotime($date));

if (!$values || !is_array($values)) {
    echo json_encode(['status' => 'error', 'message' => 'No values provided.']);
    exit;
}

try {
    // Update the value column in auction_modal table
    $updateQuery = "UPDATE auction_modal SET value = ?, update_login_id=$user_id,updated_on=now() WHERE id = ?";
    $stmt = $pdo->prepare($updateQuery);
    
    // Iterate over each value and update the auction_modal table
    foreach ($values as $valueData) {
        $id = $valueData['id'];
        $value = $valueData['value'];

        if ($id) {
            $stmt->execute([$value, $id]);
        }
    }

    // Find the maximum value and corresponding cus_name
    $maxQuery = "SELECT cus_name, MAX(value) as max_value FROM auction_modal WHERE group_id = ? AND date = ? ";
    $stmt = $pdo->prepare($maxQuery);
    $stmt->execute([$group_id, $date]);
    $maxResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($maxResult) {
        $max_value = $maxResult['max_value'];
        $cus_name = $maxResult['cus_name'];

        // Update the auction_details table
        $updateDetailsQuery = "UPDATE auction_details SET auction_value = ?, cus_name = ?, status = 2 WHERE group_id = ? AND date = ?";
        $stmt = $pdo->prepare($updateDetailsQuery);
        $stmt->execute([$max_value, $cus_name, $group_id, $date]);

        // Update status to 3 in group_creation table
        $updateGroupQuery = "UPDATE group_creation SET status = 3 WHERE grp_id = ?";
        $stmt = $pdo->prepare($updateGroupQuery);
        $stmt->execute([$group_id]);

        echo json_encode(['status' => 'success', 'message' => 'Auction closed successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No data found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$pdo = null; // Close Connection
?>
