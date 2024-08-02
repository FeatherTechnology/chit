<?php
require '../../ajaxconfig.php';

$doc_need_arr = array();
$cusProfileId = $_POST['cus_id'];
$qry = $pdo->query("SELECT id,occupation,occ_detail,source,income FROM source where cus_id = '$cusProfileId' ");
if ($qry->rowCount() > 0) {
    while ($DocNeed_info = $qry->fetch(PDO::FETCH_ASSOC)) {
        $DocNeed_info['action'] = "<span class='icon-trash-2 sourceDeleteBtn' value='" . $DocNeed_info['id'] . "'></span>";
        $doc_need_arr[] = $DocNeed_info;
    }
}
$pdo = null; //Connection Close.
echo json_encode($doc_need_arr);