<?php
require '../../ajaxconfig.php';

$group_id = $_POST['group_id'];
$auction_month = $_POST['auction_month'];

// Fetch auction details from auction_details table
$auction_qry = "SELECT * FROM auction_details WHERE group_id = '$group_id' AND auction_month = '$auction_month'";
$auction_result = $pdo->query($auction_qry);
$auction_detail = $auction_result->fetch(PDO::FETCH_ASSOC); // Use fetch with PDO::FETCH_ASSOC to get associative array

// Get current date and time
$current_date = date('Y-m-d'); // Get current date in Y-m-d format
$current_hour = date('h'); // Get current hour in 12-hour format
$current_minute = date('i'); // Get current minute
$current_ampm = date('A'); // AM or PM

// Fetch group details from group_creation table to check time
$group_qry = "SELECT hours, minutes, ampm FROM group_creation WHERE grp_id = '$group_id'";
$group_result = $pdo->query($group_qry);
$group_time = $group_result->fetch(PDO::FETCH_ASSOC); // Use fetch with PDO::FETCH_ASSOC to get associative array

// Validate if auction date and time are valid
$is_valid = false;
if ($auction_detail && $group_time) {
    $auction_date = $auction_detail['date']; // Auction date from database
    $group_hour = $group_time['hours']; // Auction start hour
    $group_minute = $group_time['minutes']; // Auction start minute
    $group_ampm = $group_time['ampm']; // Auction start AM/PM

    // Check if the auction date is today or in the future
    if ($auction_date < $current_date) {
        $is_valid = true; // Future auction date, so it's valid
    } else if ($auction_date == $current_date) {
        // If the auction date is today, check the time
    
        // Ensure the current hour and minute are two digits
        $current_hour = str_pad($current_hour, 2, '0', STR_PAD_LEFT);
        $current_minute = str_pad($current_minute, 2, '0', STR_PAD_LEFT);
    
        // Ensure the group (auction) hour and minute are two digits
        $group_hour = str_pad($group_hour, 2, '0', STR_PAD_LEFT);
        $group_minute = str_pad($group_minute, 2, '0', STR_PAD_LEFT);
    
        // Convert times to comparable formats (12-hour format with AM/PM)
        $current_time = strtotime("$current_hour:$current_minute $current_ampm");
        // echo 'Current Time: ', $current_time, '<br>';
    
        $auction_time = strtotime("$group_hour:$group_minute $group_ampm");
        // echo 'Auction Time: ', $auction_time, '<br>';
    
        // If the current time is equal to or after the auction time, it's valid
        if ($current_time >= $auction_time) {
            $is_valid = true;
        }
    }
    
    // If auction_date is in the past, the modal won't open
}

$response = [
    'is_valid' => $is_valid,
    'auction_detail' => $is_valid ? $auction_detail : null
];

echo json_encode($response);
