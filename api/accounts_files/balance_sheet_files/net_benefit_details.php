<?php
require "../../../ajaxconfig.php";

$type = $_POST['type'];

if ($_POST['user_id'] != '') {
    $userwhere = " AND insert_login_id = '" . $_POST['user_id'] . "' ";
    $siuserswhere = " AND c.insert_login_id = '" . $_POST['user_id'] . "' ";
} else {
    $userwhere = '';
    $siuserswhere = '';
}

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

// Query to get the chit value, commission, total members, and how many paid today
$qry = $pdo->query("
   SELECT 
    gc.grp_id, 
    gc.chit_value, 
    gc.commission, 
    COUNT(DISTINCT c.cus_mapping_id) AS total_paid_members, 
    gc.total_members
FROM 
    group_creation gc
LEFT JOIN 
    collection c ON c.group_id = gc.grp_id 
WHERE 
  $siwhere
    AND (
        -- Condition 1: Full payment without pending in auction month
        (c.auction_month = 1 AND c.collection_amount >= c.payable)
        
        -- Condition 2: Partial payment in the auction month with no pending
        OR (c.auction_month = 1 AND c.collection_amount < c.payable AND 
            NOT EXISTS (
                SELECT 1 
                FROM collection c2 
                WHERE c2.cus_mapping_id = c.cus_mapping_id 
                AND c2.auction_month = c.auction_month 
                AND c2.collection_amount < c.payable
            )
        )
        
        -- Condition 3: Payments in the following auction months with no pending
        OR (c.auction_month > 1 AND 
            (SELECT SUM(collection_amount) 
             FROM collection c2 
             WHERE c2.cus_mapping_id = c.cus_mapping_id 
             AND c2.auction_month < c.auction_month) + c.collection_amount >= c.payable
        )
    )
GROUP BY 
    gc.grp_id
 
HAVING 
    COUNT(DISTINCT c.cus_mapping_id) > 0;

"); 

$benefit = 0;
if ($qry->rowCount() > 0) {
    while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
        // Calculate benefit
        $commission_value = ($row['chit_value'] * $row['commission']) / 100;
        $benefit_per_group = ($commission_value / $row['total_members']) * $row['total_paid_members'];
        $benefit += $benefit_per_group; 
    }
}

// Other Income
$qry3 = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS oi_dr FROM `other_transaction` WHERE trans_cat = '8' AND type = '1' AND $where "); 
$oicr = $qry3->fetchColumn() ?: 0;

// Expenses
$qry4 = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS exp_dr FROM `expenses` WHERE $where "); 
$expdr = $qry4->fetchColumn() ?: 0;

// Prepare result
$result[0]['benefit'] = $benefit;
$result[0]['oicr'] = $oicr;
$result[0]['expdr'] = $expdr;

// Output result as JSON
echo json_encode($result);
