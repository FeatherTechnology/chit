<?php
require '../../ajaxconfig.php';

$group_id = $_POST['group_id'];
$date = $_POST['date'];
$values = $_POST['values'] ?? null; // Use null coalescing operator to set default value
$date = date('Y-m-d', strtotime($date));
if (!$values || !is_array($values)) {
    echo json_encode(['status' => 'error', 'message' => 'No values provided.']);
    exit;
}

// Update the value column in auction_modal table
$updateQuery = "UPDATE auction_modal SET value = ? WHERE group_id = ? AND date = ? AND id = ?";
$stmt = $pdo->prepare($updateQuery);
foreach ($values as $id => $value) {
    $stmt->execute([$value, $group_id, $date, $id]);
}

// Find the maximum value and corresponding cus_name
$maxQuery = "SELECT cus_name, MAX(value) as max_value FROM auction_modal WHERE group_id = ? AND date = ? GROUP BY cus_name";
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
    $updateGroupQuery = "UPDATE group_creation SET status = 3 WHERE group_id = ?";
    $stmt = $pdo->prepare($updateGroupQuery);
    $stmt->execute([$group_id]);

    echo json_encode(['status' => 'success', 'message' => 'Auction closed successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data found.']);
}
?>
