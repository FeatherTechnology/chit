<?php
require "../../../ajaxconfig.php";

$type = $_POST['type'];

if($_POST['user_id'] != ''){
    $userwhere = " AND insert_login_id = '" . $_POST['user_id'] . "' "; //for user based    
    $siuserswhere = " AND si.insert_login_id = '" . $_POST['user_id'] . "' "; //for user based    
}else{
    $userwhere = '';    
    $siuserswhere = '';    
} 

if ($type == 'today') {
    $where = " DATE(created_on) = CURRENT_DATE $userwhere";
    $siwhere = " DATE(si.settle_date) = CURRENT_DATE $siuserswhere";

} else if ($type == 'day') {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $where = " (DATE(created_on) >= '$from_date' && DATE(created_on) <= '$to_date' ) $userwhere ";
    $siwhere = " (DATE(si.settle_date) >= '$from_date' && DATE(si.settle_date) <= '$to_date' ) $siuserswhere ";

} else if ($type == 'month') {
    $month = date('m', strtotime($_POST['month']));
    $year = date('Y', strtotime($_POST['month']));
    $where = " (MONTH(created_on) = '$month' AND YEAR(created_on) = $year) $userwhere";
    $siwhere = " (MONTH(si.settle_date) = '$month' AND YEAR(si.settle_date) = $year) $siuserswhere";

}

$result = array();

$qry = $pdo->query("SELECT gc.chit_value, gc.commission FROM `settlement_info` si JOIN auction_details ad ON si.auction_id = ad.id JOIN group_creation gc ON ad.group_id = gc.grp_id WHERE $siwhere GROUP BY si.auction_id"); //Benefit Amount 
$benefit =0;
if ($qry->rowCount() > 0) {
    while($row = $qry->fetch(PDO::FETCH_ASSOC)){
        $benefit += $row['chit_value'] * $row['commission'] / 100;
    }
}

$qry3 = $pdo->query("SELECT COALESCE(SUM(amount),0) AS oi_dr FROM `other_transaction` WHERE trans_cat ='8' AND type = '1' AND $where "); //Other Income 
if ($qry3->rowCount() > 0) {
    $oicr = $qry3->fetch(PDO::FETCH_ASSOC)['oi_dr'];
}

$qry4 = $pdo->query("SELECT COALESCE(SUM(amount),0) AS exp_dr FROM `expenses` WHERE $where "); //Expenses 
if ($qry4->rowCount() > 0) {
    $expdr = $qry4->fetch(PDO::FETCH_ASSOC)['exp_dr'];
}

$result[0]['benefit'] = $benefit;
$result[0]['oicr'] = $oicr;

$result[0]['expdr']  = $expdr;

echo json_encode($result);
