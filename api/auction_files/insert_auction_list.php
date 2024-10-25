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

    foreach ($tableData as $entry) {
        // Format date
        $formattedDate = date('Y-m-d', strtotime($entry['date']));

        // Use cus_id as cus_name
        $cusName = $entry['cus_id']; // Store cus_id in cus_name

        // Insert auction details into auction_modal
        $insertQuery = "INSERT INTO auction_modal (auction_id, group_id, date, cus_name, value, inserted_login_id, created_on) 
                        VALUES ('" . strip_tags($entry['id']) . "', '" . strip_tags($entry['group_id']) . "', '$formattedDate', 
                        '" . strip_tags($cusName) . "', '" . strip_tags($entry['value']) . "', '$user_id', NOW())";

        if (!$pdo->query($insertQuery)) {
            echo json_encode(['success' => false, 'message' => 'Failed to insert data into auction_modal.']);
            exit;
        }
    }

    // Find the highest value, with priority for cus_name = -1 if it exists
    $group_id = $tableData[0]['group_id']; // Assuming all entries have the same group_id
    $date = $formattedDate; // Assuming all entries have the same date

    // Query to find the row with cus_name = -1 or the maximum value otherwise
    $maxQuery = "
        SELECT cus_name, value 
        FROM auction_modal 
        WHERE group_id = '$group_id' AND date = '$date' 
        ORDER BY (cus_name = -1) DESC, value DESC 
        LIMIT 1";

    $maxResult = $pdo->query($maxQuery)->fetch(PDO::FETCH_ASSOC);

    if ($maxResult) {
        $max_value = $maxResult['value'];
        $cus_name = $maxResult['cus_name'];

        // If cus_name is -1, update status to 3 in auction_details table
        $status = ($cus_name == -1) ? 3 : 2;

        // Update the auction_details table
        $updateDetailsQuery = "UPDATE auction_details SET auction_value = '$max_value', cus_name = '$cus_name', 
                               status = '$status', update_login_id = '$user_id', updated_on = NOW() 
                               WHERE group_id = '$group_id' AND date = '$date'";
        if (!$pdo->query($updateDetailsQuery)) {
            echo json_encode(['success' => false, 'message' => 'Failed to update auction_details table.']);
            exit;
        }

        // Update status to 3 in group_creation table if status is 3 
        if ($status == 3) {
            $updateGroupQuery = "UPDATE group_creation SET status = 3, update_login_id = '$user_id', updated_on = NOW() 
                                 WHERE grp_id = '$group_id'";
            if (!$pdo->query($updateGroupQuery)) {
                echo json_encode(['success' => false, 'message' => 'Failed to update group_creation table.']);
                exit;
            }
        }

        // Fetch date and end_month from group_creation table
        $groupCreationQuery = "SELECT date, end_month FROM group_creation WHERE grp_id = '$group_id'";
        $groupCreationResult = $pdo->query($groupCreationQuery)->fetch(PDO::FETCH_ASSOC);

        if ($groupCreationResult) {

            $endMonth = $groupCreationResult['end_month']; // Format: 'yyyy-mm'
            $currentMonth = date('m'); // e.g., '10' for October
            $currentYear = date('Y');
            $currentMonthYear = date('Y-m');
            // Current date
            $currentDate = date('Y-m-d');

            // If the constructed date matches the current date, update status to 4
            if ($endMonth == $currentMonthYear) {
                $updateStatusQuery = "UPDATE group_creation SET status = 4, update_login_id = '$user_id', updated_on = NOW() 
                                      WHERE grp_id = '$group_id'";
                if (!$pdo->query($updateStatusQuery)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to update group_creation status to 4.']);
                    exit;
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No group creation details found.']);
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
