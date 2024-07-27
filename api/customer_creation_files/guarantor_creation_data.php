<?php
require '../../ajaxconfig.php';

$id = $_POST['id'];

$qry = $pdo->query("SELECT pi.id,fi.id, fi.fam_name , fi.fam_relationship as gua_relationship,pi.existing_cus,pi.details,pi.gu_pic
FROM guarantor_info pi 
JOIN family_info fi ON pi.gua_relationship = fi.id  WHERE pi.id='$id'");
if ($qry->rowCount() > 0) {
    $result = $qry->fetchAll(PDO::FETCH_ASSOC);
}
$pdo = null; //Close connection.

echo json_encode($result);