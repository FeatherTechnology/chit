<?php
require '../../ajaxconfig.php';

$response = array();
$group_id = $_POST['group_id'];
$auction_month = $_POST['auction_month'];

// Initialize the SQL for total paid
$month_paid = "
SELECT COALESCE(SUM(c.collection_amount), 0) AS month_paid 
FROM collection c  
WHERE c.group_id='$group_id' AND c.auction_month ='$auction_month'
";
 $month_unpaid = "
SELECT 
    (ad.chit_amount * gc.total_members) AS total_chit_amount,
    COALESCE(SUM(c.collection_amount), 0) AS total_paid_amount,
    ((ad.chit_amount * gc.total_members) - COALESCE(SUM(c.collection_amount), 0)) AS month_unpaid
FROM 
    group_creation gc
JOIN 
    auction_details ad 
ON 
    ad.group_id = '$group_id' 
    AND ad.auction_month = '$auction_month'
    AND ad.status IN(2, 3)
LEFT JOIN 
    collection c 
ON 
    c.group_id = '$group_id' 
    AND c.auction_month = '$auction_month'
WHERE 
    gc.grp_id = '$group_id'  
GROUP BY 
    gc.grp_id;
";
$qry = $pdo->query($month_paid);
$response['month_paid'] = $qry->fetch()['month_paid'];
$qry = $pdo->query($month_unpaid);
$response['month_unpaid'] = $qry->fetch()['month_unpaid'];
echo json_encode($response);