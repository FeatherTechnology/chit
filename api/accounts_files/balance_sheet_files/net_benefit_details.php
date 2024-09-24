<?php
require "../../../ajaxconfig.php";

$type = $_POST['type'];
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : ''; // Check if user_id is set
$userwhere = $user_id != '' ? " AND insert_login_id = '" . $user_id . "' " : ''; // User-based filtering
$siuserswhere = $user_id != '' ? " AND c.insert_login_id = '" . $user_id . "' " : ''; // Settlement info user-based filtering    

// Date conditions based on type (today, day range, or month)
if ($type == 'today') {
    $where = " DATE(created_on) = CURRENT_DATE $userwhere";
    $siwhere = " DATE(c.collection_date) = CURRENT_DATE $siuserswhere";
} else if ($type == 'day') {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $where = " (DATE(created_on) >= '$from_date' AND DATE(created_on) <= '$to_date') $userwhere ";
    $siwhere = " (DATE(c.collection_date) >= '$from_date' AND DATE(c.collection_date) <= '$to_date') $siuserswhere ";
} else if ($type == 'month') {
    $month = date('m', strtotime($_POST['month']));
    $year = date('Y', strtotime($_POST['month']));
    $where = " (MONTH(created_on) = '$month' AND YEAR(created_on) = $year) $userwhere";
    $siwhere = " (MONTH(c.collection_date) = '$month' AND YEAR(c.collection_date) = $year) $siuserswhere";
}

$result = array();
$excluded_customers = array(); // Array to store customers who don't meet the conditions

// Query to get group information and total paid members
$qry = $pdo->query("
  SELECT 
    c.auction_id, 
    gc.grp_id, 
    gc.chit_value, 
    gc.commission, 
    c.cus_mapping_id, 
    COUNT(DISTINCT c.cus_mapping_id) AS total_paid_members, 
    gc.total_members 
FROM 
    collection c 
JOIN 
    group_creation gc ON c.group_id = gc.grp_id 
WHERE 
    $siwhere 
GROUP BY 
    gc.grp_id, c.cus_mapping_id;
");

$benefit = 0; // Initialize benefit variable

// Loop through each group to calculate benefits
while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
    $auction_id = $row['auction_id'];
    $group_id = $row['grp_id'];
    $cus_mapping_id = $row['cus_mapping_id'];
    $chit_amount = 0;
    $final_val = 0; // Initialize final_val for each iteration

    // Fetch chit amount and auction date for the specific auction ID
    $auctionQry = $pdo->query("
        SELECT 
            ad.date AS auction_date,
            ad.chit_amount
        FROM 
            auction_details ad
        WHERE 
            ad.id = '$auction_id'
        LIMIT 1
    ");

    if ($auctionQry->rowCount() > 0) {
        $auctionRow = $auctionQry->fetch(PDO::FETCH_ASSOC);
        $chit_amount = $auctionRow['chit_amount'];
        $start_date = $auctionRow['auction_date']; // Start date from auction ID
    } else {
        continue; // Skip this iteration if no auction data is found
    }

    // Get the next auction date
    $nextAuctionQry = $pdo->query("
        SELECT 
            ad.date AS auction_date
        FROM 
            auction_details ad
        WHERE 
            ad.id = (SELECT MIN(id) FROM auction_details WHERE id > '$auction_id' AND group_id = '$group_id')
        LIMIT 1
    ");

    $end_date = ($nextAuctionQry->rowCount() > 0) 
        ? $nextAuctionQry->fetch(PDO::FETCH_ASSOC)['auction_date'] 
        : null;

    // Get the collection amount for the specific group and date range
    $qry1 = $pdo->query("
        SELECT 
            SUM(c1.collection_amount) AS total_collection 
        FROM 
            collection c1 
        WHERE 
            c1.cus_mapping_id ='$cus_mapping_id' AND group_id = '$group_id'
            AND c1.collection_date BETWEEN '$start_date' AND IFNULL('$end_date', NOW())
    ");

    $collection_data = $qry1->fetch(PDO::FETCH_ASSOC);
    $collection_amount = $collection_data['total_collection'] ?: 0; // Get total collection or default to 0 

    // Calculate commission based on the chit value and commission percentage
    $commission_value = ($row['chit_value'] * $row['commission']) / 100;
    $total_chit = $chit_amount - $commission_value;
    $difference = $collection_amount - $total_chit;

    if ($total_chit >= $collection_amount) {
        // Add current auction ID to the excluded customers list
        if (!isset($excluded_customers[$cus_mapping_id])) {
            $excluded_customers[$cus_mapping_id] = array(); // Initialize the array for this customer
        }

        $excluded_customers[$cus_mapping_id][] = $auction_id; // Add the auction ID to the customer's list

    } else {
        // Conditions met, proceed with benefit calculation
        if ($commission_value >= $difference) {
            $final_val = 0; // No additional value if commission fully covers the difference
        } else {
            $final_val = $difference - $commission_value;
        }

        // Get the previous auction ID
        $prevAuctionQry = $pdo->query("
            SELECT 
                ad.id AS previous_auction_id
            FROM 
                auction_details ad
            WHERE 
                ad.id = (SELECT MAX(id) FROM auction_details WHERE id < '$auction_id' AND group_id = '$group_id')
            LIMIT 1
        ");

        if ($prevAuctionQry->rowCount() > 0) {
            $prevAuctionRow = $prevAuctionQry->fetch(PDO::FETCH_ASSOC);
            $previous_auction_id = $prevAuctionRow['previous_auction_id']; // Fetch previous auction ID

            // Add the previous auction ID to the customer's list
            $excluded_customers[$cus_mapping_id][] = $previous_auction_id;
        }

        // Loop through excluded auction IDs and fetch collection amounts
        if (isset($excluded_customers[$cus_mapping_id]) && is_array($excluded_customers[$cus_mapping_id])) {
            foreach ($excluded_customers[$cus_mapping_id] as $excluded_auction_id) {
                $qry2 = $pdo->query("
                    SELECT SUM(c1.collection_amount) AS prev_total_collect 
                    FROM collection c1 
                    WHERE c1.cus_mapping_id = '$cus_mapping_id' 
                      AND group_id = '$group_id' 
                      AND c1.auction_id = '$excluded_auction_id';
                ");
                $prev_collection_data = $qry2->fetch(PDO::FETCH_ASSOC);
                $prev_total_collect = $prev_collection_data['prev_total_collect'] ?: 0;

                // Calculate the new total with previous collections
                $final_val += $prev_total_collect;
            }
        }

        // Check if total members is greater than zero to avoid division by zero
        if ($row['total_members'] > 0) {
            // Calculate benefit per group
            $benefit_per_group = ($commission_value / $row['total_members']) * $row['total_paid_members'];
            $benefit += $benefit_per_group;
        }
    }
}

// Check conditions for excluded customers after the while loop
foreach ($excluded_customers as $cus_mapping_id => $auction_ids) {
    foreach ($auction_ids as $auction_id) {
        $prevAuctionQry = $pdo->query("
        SELECT 
            ad.id AS previous_auction_id
        FROM 
            auction_details ad
        WHERE 
            ad.id = (SELECT MAX(id) FROM auction_details WHERE id < '$auction_id' AND group_id = '$group_id')
        LIMIT 1
    ");

        if ($prevAuctionQry->rowCount() > 0) {
            $prevAuctionRow = $prevAuctionQry->fetch(PDO::FETCH_ASSOC);
            $previous_auction_id = $prevAuctionRow['previous_auction_id']; // Fetch previous auction ID

            // Add the previous auction ID to the customer's list
            $excluded_customers[$cus_mapping_id][] = $previous_auction_id;
        }
    }
    if (isset($excluded_customers[$cus_mapping_id]) && is_array($excluded_customers[$cus_mapping_id])) {
        foreach ($excluded_customers[$cus_mapping_id] as $excluded_auction_id) {
            $qry2 = $pdo->query("
                SELECT SUM(c1.collection_amount) AS prev_total_collect 
                FROM collection c1 
                WHERE c1.cus_mapping_id = '$cus_mapping_id' 
                  AND group_id = '$group_id' 
                  AND c1.auction_id = '$excluded_auction_id';
            ");
            $prev_collection_data = $qry2->fetch(PDO::FETCH_ASSOC);
            $prev_total_collect = $prev_collection_data['prev_total_collect'] ?: 0;

            // Calculate the new total with previous collections
            $final_val += $prev_total_collect;
        }
    }
}

// Calculate and prepare the final result
$result[0]['benefit'] = $benefit; // Total benefit value calculated
$result['excluded_customers'] = $excluded_customers; // List of excluded customers

echo json_encode($result); // Return result as JSON
?>
