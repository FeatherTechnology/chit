<?php
require "../../ajaxconfig.php";

$endorsement_info_arr = array();
$cus_id = $_POST['cus_id'];
$grp_id = $_POST['grp_id'];

// Modify query to use IFNULL or COALESCE to select guarantor_name, and if NULL, fetch concatenated customer name from customer_creation
$qry = $pdo->query("
    SELECT 
        di.`id`, 
        di.`doc_name`, 
        di.`doc_type`, 
        ad.auction_month, 
        IFNULL(gi.`guarantor_name`, CONCAT(cc.first_name, ' ', cc.last_name)) AS guarantor_name,  -- Fetch customer name if guarantor_name is NULL
        di.`upload`, 
        di.`date_of_noc`, 
        IFNULL(gu.`guarantor_name`, CONCAT(cc.first_name, ' ', cc.last_name)) AS noc_member_name, -- Fetch customer name if noc_member's guarantor_name is NULL
        di.`noc_relationship`, 
        di.`noc_status`
    FROM 
        `document_info` di 
    LEFT JOIN 
        guarantor_info gi ON di.holder_name = gi.id 
    LEFT JOIN 
        guarantor_info gu ON di.noc_member = gu.id 
    JOIN 
        auction_details ad ON di.auction_id = ad.id 
    LEFT JOIN 
        customer_creation cc ON di.cus_id = cc.cus_id  -- Join with customer_creation to fetch customer name if guarantor_name is NULL
    WHERE 
        di.`cus_id` = '$cus_id' 
    AND 
        ad.group_id = '$grp_id'
");

if ($qry->rowCount() > 0) {
    while ($result = $qry->fetch()) {
        // Set doc_type as 'Original' or 'Xerox'
        $result['doc_type'] = ($result['doc_type'] == '1') ? 'Original' : 'Xerox';

        // Initialize other required fields
        $result['d_noc'] = '';
        $result['h_person'] = '';
        $result['relation'] = '';

        // Create upload link
        $result['upload'] = "<a href='uploads/doc_info/" . $result['upload'] . "' target='_blank'>" . $result['upload'] . "</a>";

        // Action checkbox with noc_status data-id
        $result['action'] = "<input type='checkbox' class='noc_doc_info_chkbx' name='noc_doc_info_chkbx' value='" . $result['id'] . "' data-id='".$result['noc_status']."'>";

        // Append to endorsement info array
        $endorsement_info_arr[] = $result;
    }
}

// Return the result as JSON
echo json_encode($endorsement_info_arr);
