<?php
require '../../ajaxconfig.php';

$property_list_arr = array();
$cusMappingID = $_POST['cus_mapping_id'];
$groupId =$_POST['group_id'];
$i = 0;
$qry = $pdo->query("SELECT id, label, remark
FROM commitment_info
 WHERE cus_mapping_id = '$cusMappingID' AND group_id='$groupId'");

if ($qry->rowCount() > 0) {
    while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {

        $row['action'] = "<span class='icon-delete commitDeleteBtn' value='" . $row['id'] . "'></span>";

        $property_list_arr[$i] = $row; // Append to the array
        $i++;
    }
}

echo json_encode($property_list_arr);
$pdo = null; // Close Connection