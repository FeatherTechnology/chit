<?php
require "../../ajaxconfig.php";

$endorsement_info_arr = array();
$cus_id = $_POST['cus_id'];
$grp_id = $_POST['grp_id'];
$qry = $pdo->query("SELECT di.`id`,di.`doc_name`,di.`doc_type`,ad.auction_month,gi.`guarantor_name`,di.`upload`,di.`date_of_noc`, di.`noc_member`, di.`noc_relationship`, di.`noc_status` FROM `document_info` di LEFT JOIN guarantor_info gi ON di.holder_name = gi.id  JOIN  auction_details ad ON di.auction_id = ad.id WHERE di.`cus_id` = '$cus_id'AND 
ad.group_id = '$grp_id'");
if ($qry->rowCount() > 0) {
    while ($result = $qry->fetch()) {
        $result['doc_type'] = ($result['doc_type'] == '1') ? 'Original' : 'Xerox';
        $result['d_noc'] = '';
        $result['h_person'] = '';
        $result['relation'] = '';
        $result['upload'] = "<a href='uploads/doc_info/" . $result['upload'] . "' target='_blank'>" . $result['upload'] . "</a>";
        $result['action'] = "<input type='checkbox' class='noc_doc_info_chkbx' name='noc_doc_info_chkbx' value='" . $result['id'] . "' data-id='".$result['noc_status']."'>";
        $endorsement_info_arr[] = $result;
    }
}

echo json_encode($endorsement_info_arr);