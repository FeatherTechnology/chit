<?php
require '../../ajaxconfig.php';
@session_start();

$response = array();
$user_id = $_SESSION['user_id'];

// Retrieve data from request
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is valid
if (isset($data['data']) && is_array($data['data'])) {
    $tableData = $data['data'];

    // Prepare SQL statement for inserting auction details into auction_modal
    $stmt = $pdo->prepare("INSERT INTO auction_modal (auction_id, group_id, date, cus_name, value, inserted_login_id, created_on) VALUES (:auction_id, :group_id, :date, :cus_name, :value, :user_id, NOW())");

    foreach ($tableData as $entry) {
        // Format date
        $formattedDate = date('Y-m-d', strtotime($entry['date']));
        
        // Use cus_id as cus_name
        $cusName = $entry['cus_id']; // Store cus_id in cus_name
        
        // Bind values and execute
        $stmt->bindValue(':auction_id', $entry['id']); // Ensure 'id' is your auction_id
        $stmt->bindValue(':group_id', $entry['group_id']);
        $stmt->bindValue(':date', $formattedDate);
        $stmt->bindValue(':cus_name', $cusName);
        $stmt->bindValue(':value', $entry['value']);
        $stmt->bindValue(':user_id', $user_id);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to insert data into auction_modal.']);
            exit;
        }
    }

    // Find the maximum value and its corresponding cus_name
    $group_id = $tableData[0]['group_id']; // Assuming all entries have the same group_id
    $date = $formattedDate; // Assuming all entries have the same date

    $maxQuery = "SELECT cus_name, value FROM auction_modal WHERE group_id = ? AND date = ? ORDER BY value DESC LIMIT 1";
    $stmt = $pdo->prepare($maxQuery);
    $stmt->execute([$group_id, $date]);
    $maxResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($maxResult) {
        $max_value = $maxResult['value'];
        $cus_name = $maxResult['cus_name'];

        // If cus_name is -1, update status to 3 in auction_details table
        $status = ($cus_name == -1) ? 3 : 2;

        // Update the auction_details table
        $updateDetailsQuery = "UPDATE auction_details SET auction_value = ?, cus_name = ?, status = ?, `update_login_id` = '$user_id', `updated_on` = NOW() WHERE group_id = ? AND date = ?";
        $stmt = $pdo->prepare($updateDetailsQuery);
        if (!$stmt->execute([$max_value, $cus_name, $status, $group_id, $date])) {
            echo json_encode(['success' => false, 'message' => 'Failed to update auction_details table.']);
            exit;
        }

        // Update status to 3 in group_creation table if status is 3 
            $updateGroupQuery = "UPDATE group_creation SET status = 3, `update_login_id` = '$user_id', `updated_on` = NOW() WHERE grp_id = ?";
            $stmt = $pdo->prepare($updateGroupQuery);
            if (!$stmt->execute([$group_id])) {
                echo json_encode(['success' => false, 'message' => 'Failed to update group_creation table.']);
                exit;
            }
     
    } else {
        echo json_encode(['success' => false, 'message' => 'No auction details found.']);
        exit;
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
}
?>
