<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$id = $_POST['groupid'];
$group_id = $_POST['group_id'];
$group_name = $_POST['group_name'];
$chit_value = $_POST['chit_value'];
$grp_date = $_POST['grp_date'];
$commission = $_POST['commission'];
$hours = $_POST['hours'];
$minutes = $_POST['minutes'];
$ampm = $_POST['ampm'];
$total_members = $_POST['total_members'];
$total_month = $_POST['total_month'];
$start_month = $_POST['start_month'];
$end_month = $_POST['end_month'];
$branch = $_POST['branch'];
$grace_period = $_POST['grace_period'];

if($id ==''){
    $qry = $pdo->query("INSERT INTO `group_creation`( `grp_id`, `grp_name`, `chit_value`, `date`, `commission`, `hours`, `minutes`, `ampm`, `total_members`, `total_months`, `start_month`, `end_month`, `branch`, `grace_period`, `insert_login_id`, `created_on`) VALUES ('$group_id','$group_name','$chit_value','$grp_date','$commission','$hours','$minutes','$ampm','$total_members','$total_month','$start_month','$end_month','$branch','$grace_period','$user_id',now() ) ");

}else{
    $qry = $pdo->query("UPDATE `group_creation` SET `grp_id`='$group_id',`grp_name`='$group_name',`chit_value`='$chit_value',`date`='$grp_date',`commission`='$commission',`hours`='$hours',`minutes`='$minutes',`ampm`='$ampm',`total_members`='$total_members',`total_months`='$total_month',`start_month`='$start_month',`end_month`='$end_month',`branch`='$branch',`grace_period`='$grace_period',`update_login_id`='$user_id',`updated_on`=now() WHERE `id`='$id' ");
}
if ($qry) {
    $result = 1;
    $last_id = $pdo->lastInsertId();
} else {
    $result = 0;
    $last_id = '';
}

echo json_encode(array('result'=>$result, 'last_id'=>$last_id));
