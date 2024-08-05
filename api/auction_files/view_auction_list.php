<?php
require '../../ajaxconfig.php';
@session_start();
$user_id = $_SESSION['user_id'];

$group_id = $_POST['group_id'];
$group_list_arr = array();

$qry = $pdo->query("SELECT 
    ad.group_id, 
    ad.auction_month, 
    ad.date,
    ad.low_value,
    ad.high_value,
    ad.status,
    ad.cus_id,
    ad.auction_value
FROM auction_details ad
WHERE ad.group_id = '$group_id' 
ORDER BY ad.auction_month DESC");

if ($qry->rowCount() > 0) {
    while ($auctionInfo = $qry->fetch(PDO::FETCH_ASSOC)) {
        $auctionInfo['action'] = "<button class='btn btn-primary auctionBtn' value='". $auctionInfo['group_id'] ."'>&nbsp;Auction</button>
                                  <button class='btn btn-primary postponeBtn' value='". $auctionInfo['group_id'] ."'>&nbsp;Postpone</button>";

        if ($auctionInfo['status'] == '2') {
            $auctionInfo['action'] .= "<button class='btn btn-primary viewBtn' value='". $auctionInfo['group_id'] ."'>&nbsp;View</button>
                                       <button class='btn btn-primary calculateBtn' value='". $auctionInfo['group_id'] ."'>&nbsp;Calculation</button>";
        }
        
        $group_list_arr[] = $auctionInfo;
    }
}

$pdo = null; // Close Connection.
echo json_encode($group_list_arr);
?>
