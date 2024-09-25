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

    if ($chit_amount >= $collection_amount) {
        // Add current auction ID to the excluded customers list along with the group_id
        if (!isset($excluded_customers[$cus_mapping_id])) {
            $excluded_customers[$cus_mapping_id] = array(); // Initialize the array for this customer
        }

        $excluded_customers[$cus_mapping_id][] = array(
            'cus_mapping_id' => $cus_mapping_id,
            'auction_id' => $auction_id,
            'group_id' => $group_id
        );
    } else {
        // Conditions met, proceed with benefit calculation
        if ($commission_value >= $difference) {
            $final_val = 0; // No additional value if commission fully covers the difference
        } else {
            $final_val = $difference - $commission_value;
        }

        // Check if total members is greater than zero to avoid division by zero
        if ($row['total_members'] > 0) {
            // Calculate benefit per group
            $benefit_per_group = ($commission_value / $row['total_members']) * $row['total_paid_members'];
            $benefit += $benefit_per_group;
        }
    }
}
// Get previous auction IDs

$prevAuctionQry = $pdo->query("
SELECT 
  ad.id AS previous_auction_id,
  ad.chit_amount AS prev_chit_amount
FROM 
  auction_details ad
JOIN collection c3 ON ad.id=c3.auction_id
WHERE 
  ad.id < '$auction_id' AND ad.group_id = '$group_id' AND c3.cus_mapping_id='$cus_mapping_id' GROUP BY ad.id
ORDER BY 
  ad.id 
");
// Loop through previous auction results
if ($prevAuctionQry->rowCount() > 0) {
    $final_val = 0; // Initialize final value
    $benefit_from_next_auction = 0; // Initialize to store benefit value to pass to the previous auction
    $auction_ids_with_benefit = []; // Array to hold auction IDs with benefit

    while ($prevAuctionRow = $prevAuctionQry->fetch(PDO::FETCH_ASSOC)) {
        $previous_auction_id = $prevAuctionRow['previous_auction_id']; // Get the ID of the previous auction
        $prev_chit_amount = $prevAuctionRow['prev_chit_amount']; // Get previous chit amount

        // Fetch previous collection data for this auction
        $qry2 = $pdo->query("
            SELECT SUM(c1.collection_amount) AS prev_total_collect 
            FROM collection c1 
            WHERE c1.cus_mapping_id = '$cus_mapping_id' 
              AND c1.group_id = '$group_id' 
              AND c1.auction_id = '$previous_auction_id';
        ");

        $prev_collection_data = $qry2->fetch(PDO::FETCH_ASSOC);
        $prev_total_collect = $prev_collection_data['prev_total_collect'] ?: 0;

        // Add the benefit from the current auction to the previous auction's total
        $prev_total_collect += $benefit_from_next_auction;

        // Query to find commission value for this auction
        $qry3 = $pdo->query("
            SELECT 
                gc.chit_value, 
                gc.commission, 
                COUNT(DISTINCT c.cus_mapping_id) AS total_paid_members,
                gc.total_members
            FROM 
                collection c 
            JOIN 
                group_creation gc ON c.group_id = gc.grp_id 
            WHERE c.cus_mapping_id = '$cus_mapping_id' 
              AND c.group_id = '$group_id' 
              AND c.auction_id = '$previous_auction_id'
            GROUP BY 
                gc.grp_id, c.cus_mapping_id
        ");

        if ($qry3->rowCount() > 0) {
            $row = $qry3->fetch(PDO::FETCH_ASSOC);
            $commission_value = ($row['chit_value'] * $row['commission']) / 100; // Calculate commission value
            $total_chit = $prev_chit_amount - $commission_value; // Adjust total chit based on commission
            $difference = $prev_total_collect - $total_chit;

            // Check if total chit amount is greater or equal to collected amount
            if ($total_chit >= $prev_total_collect) {
                // Add current auction ID to the excluded customers list
                if (!isset($excluded_customers[$cus_mapping_id])) {
                    $excluded_customers[$cus_mapping_id] = []; // Initialize the array for this customer
                }

                $excluded_customers[$cus_mapping_id][] = [
                    'auction_id' => $previous_auction_id,
                    'group_id' => $group_id
                ];
            } else {
                // Conditions met, proceed with benefit calculation
                if ($commission_value >= $difference) {
                    $final_val = 0; // No additional value if commission fully covers the difference
                } else {
                    $final_val = $difference - $commission_value;
                    // Pass the benefit value to the next iteration for previous auction
                    $benefit_from_next_auction = $final_val; // Store this value to add to the previous auction's collection

                    // Check if the benefit is positive, then add auction ID to the array
                    if ($benefit_from_next_auction > 0) {
                        $auction_ids_with_benefit[] = $previous_auction_id; // Store auction ID with benefit
                    }
                }
            }
        }
    }

    // Now you can use $auction_ids_with_benefit for further processing
    if (!empty($auction_ids_with_benefit)) {
        foreach ($auction_ids_with_benefit as $auction_id) {
            // Perform further actions for each auction ID with benefit
            echo "Auction ID with benefit: " . $auction_id . "\n";

            // Execute your additional logic here based on auction_id
            $qry_last_auction = $pdo->query("
                SELECT id as previous_auction_id 
                FROM auction_details 
                WHERE id < '$auction_id' AND group_id ='$group_id' 
                ORDER BY id DESC 
                LIMIT 1; 
            ");
        
            $last_previous_auction = $qry_last_auction->fetchColumn();
        
            // Close the cursor to free resources before executing another query
            $qry_last_auction->closeCursor();
        
            // Fetch the previous auction's collection amount
         
            $qry2_last = $pdo->query("
                SELECT SUM(c1.collection_amount) AS prev_total_collect_last,
                c1.chit_amount
                FROM collection c1 
                WHERE c1.cus_mapping_id = '$cus_mapping_id' 
                  AND c1.group_id = '$group_id' 
                  AND c1.auction_id = '$last_previous_auction';
            ");
        
            $prev_collection_data_last = $qry2_last->fetch(PDO::FETCH_ASSOC);
            $prev_total_collect_last = $prev_collection_data_last['prev_total_collect_last'] ?: 0;
            $chit_amount =$prev_collection_data_last['chit_amount'] ?: 0;
            // Close the cursor again before the next query
            $qry2_last->closeCursor();
      
            // Add the benefit value to the last previous auction's collection
            $updated_total_collect_last = $prev_total_collect_last + $final_val;
        echo  $updated_total_collect_last;
            // Recheck the conditions with the updated collection amount for the last auction
            $qry3_last = $pdo->query("
                SELECT 
                    gc.chit_value, 
                    gc.commission
                FROM 
                    group_creation gc 
                WHERE gc.grp_id = '$group_id'
            ");
        
            $row_last = $qry3_last->fetch(PDO::FETCH_ASSOC);
            $commission_value_last = ($row_last['chit_value'] * $row_last['commission']) / 100; // Calculate commission value
         
            $total_chit_last = $chit_amount - $commission_value_last; // Adjust based on last auction
            $difference_last = $updated_total_collect_last - $total_chit_last;
        
            // Close the cursor again before the next query
            $qry3_last->closeCursor();
        echo  $total_chit_last;
        echo  $updated_total_collect_last;
            // Final condition check for the last auction
            if ($total_chit_last >= $updated_total_collect_last) {
                echo "The last previous auction now satisfies the condition with the added benefit.\n";
            } else {
                echo "The last previous auction still does not satisfy the condition even after adding the benefit.\n";
            }
        }
    }
}

// print_r($excluded_customers);
// Check conditions for excluded customers after the while loop
foreach ($excluded_customers as $cus_mapping_id => $auction_data) {
    // Now iterate through all auctions including previous ones
    if (isset($excluded_customers[$cus_mapping_id]) && is_array($excluded_customers[$cus_mapping_id])) {
        foreach ($excluded_customers[$cus_mapping_id] as $auction_info) {
            $excluded_auction_id = $auction_info['auction_id'];
            $group_id = $auction_info['group_id']; // Access group_id here

            // Fetch previous auction details, including chit amount

            $prevAuctionQry = $pdo->query("
                SELECT ad.chit_amount AS prev_chit_amount
                FROM auction_details ad
                WHERE ad.id = '$excluded_auction_id'
                LIMIT 1
            ");

            // Fetch the previous chit amount if it exists
            if ($prevAuctionQry->rowCount() > 0) {
                $prevAuctionRow = $prevAuctionQry->fetch(PDO::FETCH_ASSOC);
                $chit_amount = $prevAuctionRow['prev_chit_amount'];
            } else {
                $chit_amount = 0; // Default to 0 if no previous chit amount is found
            }

            // Fetch previous collection data
            $qry2 = $pdo->query("
                SELECT SUM(c1.collection_amount) AS prev_total_collect 
                FROM collection c1 
                WHERE c1.cus_mapping_id = '$cus_mapping_id' 
                  AND group_id = '$group_id' 
                  AND c1.auction_id = '$excluded_auction_id';
            ");

            $prev_collection_data = $qry2->fetch(PDO::FETCH_ASSOC);
            $prev_total_collect = $prev_collection_data['prev_total_collect'] ?: 0;

            // Calculate new total with previous collections

            $final_val += $prev_total_collect;
            $collection_amount = $final_val;

            // Query to find commission value
            $qry3 = $pdo->query("
                SELECT 
                    gc.chit_value, 
                    gc.commission, 
                    c.cus_mapping_id, 
                    COUNT(DISTINCT c.cus_mapping_id) AS total_paid_members,
                    gc.total_members
                FROM 
                    collection c 
                JOIN 
                    group_creation gc ON c.group_id = gc.grp_id 
                WHERE c.cus_mapping_id = '$cus_mapping_id' 
                  AND group_id = '$group_id' 
                  AND c.auction_id = '$excluded_auction_id'
                GROUP BY 
                    gc.grp_id, c.cus_mapping_id
            ");

            if ($qry3->rowCount() > 0) {
                $row = $qry3->fetch(PDO::FETCH_ASSOC);
                $commission_value = ($row['chit_value'] * $row['commission']) / 100; // Calculate commission value
                $total_chit = $chit_amount - $commission_value; // Adjust total chit based on commission
                $difference = $collection_amount - $total_chit;

                if ($total_chit >= $collection_amount) {
                    // Add current auction ID to the excluded customers list along with the group_id
                    if (!isset($excluded_customers[$cus_mapping_id])) {
                        $excluded_customers[$cus_mapping_id] = array(); // Initialize the array for this customer
                    }

                    $excluded_customers[$cus_mapping_id][] = array(
                        'auction_id' => $excluded_auction_id,
                        'group_id' => $group_id
                    );
                } else {
                    // Conditions met, proceed with benefit calculation
                    if ($commission_value >= $difference) {
                        $final_val = 0; // No additional value if commission fully covers the difference
                    } else {
                        $final_val = $difference - $commission_value;
                    }
                    // Check if total members is greater than zero to avoid division by zero
                    if ($row['total_paid_members'] > 0) {
                        // Calculate benefit per group
                        $benefit_per_group = ($commission_value / $row['total_members']) * $row['total_paid_members'];
                        $benefit += $benefit_per_group;
                    }

                    // Get previous auction ID
                    $prevAuctionQry = $pdo->query("
                        SELECT 
                            ad.id AS previous_auction_id,
                            ad.chit_amount AS prev_chit_amount
                        FROM 
                            auction_details ad
                        WHERE 
                            ad.id = (SELECT MAX(id) FROM auction_details WHERE id < '$excluded_auction_id' AND group_id = '$group_id')
                        LIMIT 1
                    ");

                    if ($prevAuctionQry->rowCount() > 0) {
                        $prevAuctionRow = $prevAuctionQry->fetch(PDO::FETCH_ASSOC);
                        $previous_auction_id = $prevAuctionRow['previous_auction_id'];
                        $prev_chit_amount = $prevAuctionRow['prev_chit_amount']; // Get previous chit amount

                        // Add the previous auction ID to the customer's list
                        $excluded_customers[$cus_mapping_id][] = array(
                            'auction_id' => $previous_auction_id,
                            'chit_amount' => $prev_chit_amount,
                            'group_id' => $group_id
                        );
                    }
                }
            }
        }
    }
}
// Query for Other Income
$qry3 = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS oi_dr FROM `other_transaction` WHERE trans_cat = '8' AND type = '1' AND $where");
$oicr = $qry3->fetchColumn() ?: 0; // Fetch total other income or default to 0

// Query for Expenses
$qry4 = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS exp_dr FROM `expenses` WHERE $where");
$expdr = $qry4->fetchColumn() ?: 0; // Fetch total expenses or default to 0

$result[0]['oicr'] = $oicr;
$result[0]['expdr'] = $expdr;
// Calculate and prepare the final result
$result[0]['benefit'] = $benefit; // Total benefit value calculated
$result['excluded_customers'] = $excluded_customers; // List of excluded customers

echo json_encode($result); // Return result as JSON
