<?php
@session_start();
require '../../ajaxconfig.php';

$user_id = $_SESSION['user_id'];
include './collection_list_sts.php';

$collectionSts = new CollectStsClass($pdo);

$column = array(
    'cc.id',
    'ad.group_id',
    'cc.cus_id',
    'cus_name',
    'cc.mobile1',
    'pl.place',
    'occupations',
    'status',
    'gc.grace_period',
    'cc.id'
);

$query = "SELECT 
    ad.id,
    cc.id,
    ad.id as auction_id,
    ad.group_id,
    cc.cus_id,
    CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name,
    cc.mobile1, 
    pl.place,
    (SELECT GROUP_CONCAT(sc.occupation SEPARATOR ', ') 
     FROM source sc 
     WHERE sc.cus_id = cc.cus_id) AS occupations,
     gcm.id as cus_mapping_id,
    gc.chit_value,
    gc.grace_period,
    ad.date,              
    ad.chit_amount,
    ad.auction_month
FROM 
    auction_details ad
LEFT JOIN 
    group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
LEFT JOIN 
    customer_creation cc ON gcm.cus_id = cc.id 
LEFT JOIN 
    place pl ON cc.place = pl.id
LEFT JOIN
    group_creation gc ON ad.group_id = gc.grp_id
INNER JOIN (
    SELECT 
        cc.id AS cus_id,
        MAX(gc.chit_value) AS max_chit_value
    FROM 
        auction_details ad
    LEFT JOIN 
        group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
    LEFT JOIN 
        customer_creation cc ON gcm.cus_id = cc.id
    LEFT JOIN 
        group_creation gc ON ad.group_id = gc.grp_id
    WHERE 
        ad.status IN (2, 3)
    GROUP BY 
        cc.id
) AS subquery ON cc.id = subquery.cus_id AND gc.chit_value = subquery.max_chit_value
   JOIN users us ON FIND_IN_SET(gc.branch, us.branch)
WHERE 
    ad.status IN (2, 3)";

if (isset($_POST['search']) && $_POST['search'] != "") {
    $search = $_POST['search'];
    $query .= " AND ( cc.cus_id LIKE '%" . $search . "%'
                      OR CONCAT(cc.first_name, ' ', cc.last_name) LIKE '%" . $search . "%'
                      OR cc.mobile1 LIKE '%" . $search . "%'
                      OR pl.place LIKE '%" . $search . "%'
                      OR (SELECT GROUP_CONCAT(sc.occupation SEPARATOR ', ') 
                          FROM source sc 
                          WHERE sc.cus_id = cc.cus_id
                      ) LIKE '%" . $search . "%')";
}

$query .= " GROUP BY 
    cc.cus_id, ad.group_id
ORDER BY 
    max_chit_value DESC, ad.group_id, cc.cus_id";

$query1 = '';
if (isset($_POST['length']) && $_POST['length'] != -1) {
    $query1 = ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']);
}

$statement = $pdo->prepare($query);
$statement->execute();
$number_filter_row = $statement->rowCount();

$statement = $pdo->prepare($query . $query1);
$statement->execute();
$result = $statement->fetchAll();

$sno = isset($_POST['start']) ? $_POST['start'] + 1 : 1;
$data = [];
foreach ($result as $row) {
    $sub_array = array();
    $sub_array[] = $sno++;
    $sub_array[] = isset($row['group_id']) ? $row['group_id'] : '';
    $sub_array[] = isset($row['cus_id']) ? $row['cus_id'] : '';
    $sub_array[] = isset($row['cus_name']) ? $row['cus_name'] : '';
    $sub_array[] = isset($row['mobile1']) ? $row['mobile1'] : '';
    $sub_array[] = isset($row['place']) ? $row['place'] : '';
    $sub_array[] = isset($row['occupations']) ? $row['occupations'] : '';

    // Fetch status using the correct method call
    $status = $collectionSts->updateCollectStatus( $row['cus_id'], $row['auction_month']);
    $sub_array[] = $status;

    $grace_period = isset($row['grace_period']) ? $row['grace_period'] : 0; 
    $date = isset($row['date']) ? $row['date'] : ''; 
    
    $due_date = date('Y-m-d', strtotime($date));
    $grace_start_date = $due_date; 
    $grace_end_date = date('Y-m-d', strtotime($due_date . ' + ' . $grace_period . ' days'));

    $payment_query = "SELECT collection_date FROM collection 
                      WHERE  cus_id = :cus_id 
                      AND group_id = :group_id
                      AND auction_month = :auction_month";
    $payment_stmt = $pdo->prepare($payment_query);
    $payment_stmt->execute([
         ':group_id'=>$row['group_id'],
        ':cus_id' => $row['cus_id'],
        ':auction_month' => $row['auction_month']
    ]);

    $payment_row = $payment_stmt->fetch(PDO::FETCH_ASSOC);
    $collection_date = isset($payment_row['collection_date']) ? $payment_row['collection_date'] : null;
   // $collection_status = isset($payment_row['coll_status']) ? $payment_row['coll_status'] : null;

    if ($status === "Paid") {
        $status_color = 'green'; // Payment is made
    } elseif ($collection_date) {
        $collection_date = date('Y-m-d', strtotime($collection_date));
        if ($collection_date < $due_date) {
            $status_color = 'orange'; // Payment made before due date but status is payable
        } elseif ($collection_date > $grace_end_date) {
            $status_color = 'red'; // Missed payment after grace period
        } else {
            $status_color = 'orange'; // Payment made within grace period
        }
    } else {
        $current_date = date('Y-m-d');
        if ($current_date > $grace_end_date) {
            $status_color = 'red'; // Missed payment after grace period
        } elseif ($current_date >= $due_date && $current_date <= $grace_end_date) {
            $status_color = 'orange'; // Payment is due or within grace period
        } else {
            $status_color = 'red'; // Default to red if no payment status
        }
    }
    $sub_array[] = "<span style='display: inline-block; width: 20px; height: 20px; border-radius: 4px; background-color: $status_color;'></span>";

    $action = "<button class='btn btn-primary collectionListBtn' value='" . $row['id'] . "'>&nbsp;View</button>";
    $sub_array[] = $action;
    $data[] = $sub_array;
}

function count_all_data($pdo)
{
    $query = "SELECT COUNT(*) FROM auction_details";
    $statement = $pdo->prepare($query);
    $statement->execute();
    return $statement->fetchColumn();
}

$output = array(
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => count_all_data($pdo),
    'recordsFiltered' => $number_filter_row,
    'data' => $data
);

echo json_encode($output);
?>
