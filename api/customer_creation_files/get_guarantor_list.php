<?php
require '../../ajaxconfig.php';

$property_list_arr = array();
$cus_id = $_POST['cus_id'];
$i = 0;

$qry = $pdo->query("SELECT pi.id, fi.fam_name , fi.fam_relationship as gua_relationship 
FROM guarantor_info pi 
JOIN family_info fi ON pi.gua_relationship = fi.id WHERE pi.cus_id = '$cus_id'");

if ($qry->rowCount() > 0) {
    while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
        $row['action'] = "<span class='icon-border_color guaranatorActionBtn' value='" . $row['id'] . "'></span>&nbsp;&nbsp;&nbsp;<span class='icon-delete guarantorDeleteBtn' value='" . $row['id'] . "'></span>";
        $property_list_arr[$i] = $row; // Append to the array
        $i++;
    }
}

echo json_encode($property_list_arr);
$pdo = null; // Close Connection
?>

