<?php
require '../../ajaxconfig.php';

$id = $_POST['id'];
$result = 0;
$cnt = '0';

$qry = $pdo->query("SELECT * FROM `noc` WHERE doc_id='$id'");
if ($qry->rowCount() > 0) {
    $cnt = '1';
}
if ($cnt == '1') {
    $result = '2'; // Used in User Creation.

} else {
    $qry = $pdo->query("SELECT upload FROM `document_info` WHERE id='$id'");
    if ($qry->rowCount() > 0) {
        $row = $qry->fetch();
        unlink("../../uploads/doc_info/" . $row['upload']);

        $qry = $pdo->query("DELETE FROM `document_info` WHERE id='$id'");
        if ($qry) {
            $result = 1;
        } else {
            $result = 3;
        }
    }
}

$pdo = null; //Close connection.

echo json_encode($result);
