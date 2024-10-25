<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$group_id = $_POST['group_id'];
$cus_id = $_POST['cus_id'];
$auction_id = $_POST['auction_id'];
$cus_mapping_id = $_POST['cus_mapping_id'];
$auction_month = $_POST['auction_month'];  // Assuming this is provided as a number (e.g., 1 for Aug 2024, 6 for Jan 2025)
$chit_value = $_POST['chit_value'];
$chit_amount = $_POST['chit_amount'];
$pending = $_POST['pending_amt'];
$payable = $_POST['payable_amnt'];
$collection_amount = $_POST['collection_amount'];
$collection_date = date('Y-m-d', strtotime($_POST['collection_date']));
$coll_mode = $_POST['coll_mode'];
$transaction_id = $_POST['transaction_id'];
$bank_name = $_POST['bank_name'];
if ($collection_amount >= $payable) {
    $status = 'Paid';
} else {
    $status = 'Payable';
}

// Insert the collection record
$qry = $pdo->query("INSERT INTO collection (cus_mapping_id, auction_id, group_id, cus_id, auction_month, chit_value, chit_amount, pending, payable, coll_status, collection_date, coll_mode, transaction_id, bank_id, collection_amount, insert_login_id, created_on) 
VALUES ('$cus_mapping_id', '$auction_id', '$group_id', '$cus_id', '$auction_month', '$chit_value', '$chit_amount', '$pending', '$payable', '$status', '$collection_date " . date(' H:i:s') . "', '$coll_mode', '$transaction_id', '$bank_name', '$collection_amount', '$user_id', CURRENT_TIMESTAMP())");

if ($qry) {
    // Fetch the group information
    $groupInfo = $pdo->query("SELECT start_month, end_month FROM group_creation WHERE grp_id = '$group_id' AND end_month <= DATE_FORMAT(CURDATE(), '%Y-%m')")->fetch(PDO::FETCH_ASSOC);

    if ($groupInfo) {
        $start_month = $groupInfo['start_month']; // Start month in format YYYY-MM (e.g., 2024-08)
        $end_month = $groupInfo['end_month'];     // End month in format YYYY-MM (e.g., 2025-01)

        // Calculate the month difference
        $startDate = new DateTime($start_month . '-01');
        $endDate = new DateTime($end_month . '-01');
        $interval = $startDate->diff($endDate);
        $totalMonths = $interval->y * 12 + $interval->m + 1; // Total months in the group period

        // Count paid customers for all months in the group
        $paidCustomersCount = $pdo->query("SELECT COUNT(DISTINCT cus_id) as paid_count 
                                           FROM collection 
                                           WHERE group_id = '$group_id' 
                                           AND coll_status = 'Paid' 
                                           AND auction_month BETWEEN 1 AND $totalMonths ORDER BY c.created_on DESC
                     LIMIT 1")->fetch(PDO::FETCH_ASSOC)['paid_count'];

        // Get the total number of customers in the group
        $totalCustomersCount = $pdo->query("SELECT COUNT(DISTINCT cus_id) as total_count 
                                            FROM group_cus_mapping 
                                            WHERE grp_creation_id = '$group_id'")->fetch(PDO::FETCH_ASSOC)['total_count'];

        if ($paidCustomersCount == $totalCustomersCount * $totalMonths) {
            // Update group status to 4
            $updateQry = $pdo->query("UPDATE group_creation SET status = 5 WHERE grp_id = '$group_id'");
            $result = $updateQry ? 1 : 0;
        } else {
            $result = 1; // Collection inserted but group status not updated
        }
    } else {
        $result = 1; // Collection inserted but group info not matching
    }
} else {
    $result = 0; // Collection insertion failed
}

echo json_encode($result);
?>
