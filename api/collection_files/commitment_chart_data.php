<?php
require '../../ajaxconfig.php';

$property_list_arr = array();
$cusMappingID = $_POST['cus_mapping_id'];
$groupId =$_POST['group_id'];
$i = 0;
$qry = $pdo->query("SELECT id, created_on, label, remark FROM commitment_info WHERE cus_mapping_id = '$cusMappingID' AND group_id='$groupId'");

if ($qry->rowCount() > 0) {
    while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
        
        // Format the created_on date to dd-mm-yyyy
        if (!empty($row['created_on'])) {
            $date = new DateTime($row['created_on']);
            $row['created_on'] = $date->format('d-m-Y'); // Format to dd-mm-yyyy
        }

        $property_list_arr[$i] = $row; // Append to the array
        $i++;
    }
}

echo json_encode($property_list_arr);
$pdo = null; // Close Connection