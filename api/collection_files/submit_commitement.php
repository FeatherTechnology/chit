<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$group_id = $_POST['group_id'];
$cus_mapping_id=$_POST['cus_mapping_id'];
$label = $_POST['label'];
$remark = $_POST['remark'];

// Build the SQL query
$qry = "INSERT INTO commitment_info (cus_mapping_id,
     group_id, label, remark,insert_login_id, created_on
) VALUES ('$cus_mapping_id',
     '$group_id', '$label', '$remark' ,'$user_id', NOW()
)";

if ($pdo->query($qry)) {
    $result = 1;
} else {
    $result = 0;
}   
echo json_encode($result);

?>
