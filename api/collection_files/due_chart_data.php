<?php
require '../../ajaxconfig.php';

$due_list_arr = array();
$cusMappingID = $_POST['cus_mapping_id'];
$groupId = $_POST['group_id'];

// Fetch auction details for the given group and customer mapping ID
$qry1 = $pdo->query("SELECT 
    ad.auction_month, 
    ad.date AS auction_date
FROM 
    auction_details ad
JOIN 
    group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
WHERE 
    ad.group_id = '$groupId'
    AND gcm.id = '$cusMappingID'");

$auctionData = $qry1->fetchAll(PDO::FETCH_ASSOC);

// Determine the last auction month and date
$lastAuctionMonth = null;
$lastAuctionDate = null;
foreach ($auctionData as $auction) {
    $auction_date = $auction['auction_date'];
    if ($auction_date) {
        $auction_date = new DateTime($auction_date);
        if ($lastAuctionMonth === null || 
            $auction['auction_month'] > $lastAuctionMonth || 
            ($auction['auction_month'] == $lastAuctionMonth && $auction_date > $lastAuctionDate)) {
            $lastAuctionMonth = $auction['auction_month'];
            $lastAuctionDate = $auction_date;
        }
    }
}

// Helper array to keep track of auction months that have been used
$auctionMonthUsed = array();

$i = 0;

foreach ($auctionData as $auctionDetails) {
    $auction_month = $auctionDetails['auction_month'];
    $auction_date = $auctionDetails['auction_date'];

    // Format the auction_date to dd-mm-yyyy
    if (!empty($auction_date)) {
        $date = new DateTime($auction_date);
        $auction_date = $date->format('d-m-Y');
    }

    // Fetch collection details for the auction month
    $qry2 = $pdo->query("SELECT 
        ad.chit_amount, 
        c.chit_value, 
        c.payable, 
        c.collection_date, 
        c.collection_amount, 
        c.id as coll_id
    FROM 
        auction_details ad
    LEFT JOIN 
        collection c ON ad.group_id = c.group_id 
                     AND c.cus_mapping_id = '$cusMappingID'
                     AND ad.auction_month = c.auction_month
    WHERE 
        c.group_id = '$groupId' AND
        c.cus_mapping_id = '$cusMappingID'
        AND c.auction_month = '$auction_month'");

    if ($qry2->rowCount() > 0) {
        while ($row = $qry2->fetch(PDO::FETCH_ASSOC)) {
            // Calculate pending amount
            $payable = $row['payable'];
            $collection_amount = $row['collection_amount'];
            $pending = $payable - $collection_amount;

            // Ensure pending is not negative
            $pending = $pending > 0 ? $pending : 0;

            // Add auction_month and auction_date to the row
            $row['auction_month'] = !in_array($auction_month, $auctionMonthUsed) ? $auction_month : '';
            $row['auction_date'] = $auction_date;
            $row['pending'] = $pending;
            $row['action'] = "<a class='print_due_coll' id='" . $row['coll_id'] . "'> <i class='fa fa-print' aria-hidden='true'></i> </a>";

            $due_list_arr[$i] = $row;
            $i++;

            // Mark this auction month as used
            $auctionMonthUsed[] = $auction_month;
        }
    } else {
        // No data found, create a default entry 
        $due_list_arr[$i] = array(
            'auction_month' => !in_array($auction_month, $auctionMonthUsed) ? $auction_month : '',
            'auction_date' => $auction_date,
            'chit_amount' => '',
            'chit_value' => '',
            'payable' => '',
            'collection_date' => '',
            'collection_amount' => '',
            'pending' => '', // Ensure pending is 0
            'id' => '',
            'action' => ''
        );
        $i++;
    }
}

// Handle future collection dates by adding default entries with last known auction month and date
if ($lastAuctionDate) {
    $currentDate = new DateTime();
    $interval = new DateInterval('P1M'); // Monthly interval

    // Generate future auction months and dates
    while ($lastAuctionDate <= $currentDate) {
        $lastAuctionDate->add($interval);
        $futureMonth = $lastAuctionDate->format('m');
        $futureYear = $lastAuctionDate->format('Y');
        $futureMonthFormatted = "22-" . $lastAuctionDate->format('m-Y');

        $due_list_arr[] = array(
            'auction_month' => $futureMonth,
            'auction_date' => $futureMonthFormatted,
            'chit_amount' => '',
            'chit_value' => '',
            'payable' => '',
            'collection_date' => '',
            'collection_amount' => '',
            'pending' => '', // Ensure pending is 0
            'id' => '',
            'action' => ''
        );
    }
}

echo json_encode($due_list_arr);
$pdo = null; // Close Connection
?>
