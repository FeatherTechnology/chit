<?php
require '../../ajaxconfig.php';
@session_start();
$user_id = $_SESSION['user_id'];
include 'collectionStatus.php';

$collectionSts = new CollectionStsClass($pdo);

$column = array(
    'ad.id',
    'cc.cus_id',
    'cus_name',
    'cc.mobile1',
    'pl.place',
    'occupations',
    'status',
    'gc.grace_period',
    'ad.id'
);

$query = "SELECT 
            ad.id,
            ad.group_id,
            cc.cus_id,
            CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name,
            cc.mobile1, 
            pl.place,
            (SELECT GROUP_CONCAT(sc.occupation SEPARATOR ', ')
                FROM source sc 
                WHERE sc.cus_id = cc.cus_id
            ) AS occupations,
            gc.grace_period,
            ad.date,              -- Add date column
            ad.chit_amount
        FROM 
            auction_details ad
         LEFT JOIN 
         group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
       LEFT JOIN 
            customer_creation cc ON gcm.cus_id= cc.id 
       LEFT JOIN 
            place pl ON cc.place = pl.id
        LEFT JOIN
             group_creation gc ON ad.group_id = gc.grp_id      
        WHERE 
            ad.status BETWEEN 2 AND 3";


if (isset($_POST['search']) && $_POST['search'] != "") {
    $search = $_POST['search'];
    $query .= " AND ( cc.cus_id LIKE '%" . $search . "%'
                      OR CONCAT(cc.first_name, ' ', cc.last_name) LIKE '%" . $search . "%'
                      OR cc.mobile1 LIKE '%" . $search . "%'
                      OR pl.place LIKE '%" . $search . "%'
                      OR (SELECT GROUP_CONCAT(sc.occupation SEPARATOR ', ') 
                          FROM source sc 
                          WHERE sc.cus_id = cc.cus_id
                      ) LIKE '%" . $search . "%'
                      OR cl.coll_status LIKE '%" . $search . "%')";
}

if (isset($_POST['order'])) {
    $column_index = $_POST['order']['0']['column'];
    $column_direction = $_POST['order']['0']['dir'];
    $query .= " ORDER BY " . $column[$column_index] . ' ' . $column_direction;
} else {
    $query .= " ORDER BY ad.id DESC";
}

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
    $sub_array[] = isset($row['cus_id']) ? $row['cus_id'] : '';
    $sub_array[] = isset($row['cus_name']) ? $row['cus_name'] : '';
    $sub_array[] = isset($row['mobile1']) ? $row['mobile1'] : '';
    $sub_array[] = isset($row['place']) ? $row['place'] : '';
    $sub_array[] = isset($row['occupations']) ? $row['occupations'] : '';
    
    // Fetch status using the updated method
    $chit_amount = isset($row['chit_amount']) ? $row['chit_amount'] : 0; // Default to 0 if not set
    $auction_month = isset($row['auction_month']) ? $row['auction_month'] :0;
    $status = $collectionSts->updateCollectionStatus($row['id'], $row['group_id'], $row['cus_id'], $auction_month, $chit_amount);
    $sub_array[] = $status;

    $sub_array[] = isset($row['grace_period']) ? $row['grace_period'] : '';
    $due_date = date('Y-m-d', strtotime($date));
    $grace_end_date = date('Y-m-d', strtotime($due_date . ' + ' . $grace_period . ' days'));
    $grace_start_date = $due_date; // Grace period starts from the due date

    // Fetch payment date from the collection table
    $payment_query = "SELECT collection_date FROM collection 
                      WHERE auction_id = :auction_id AND group_id = :group_id AND cus_id = :cus_id 
                      AND auction_month = :auction_month";
    $payment_stmt = $pdo->prepare($payment_query);
    $payment_stmt->execute([
        ':auction_id' => $row['id'],
        ':group_id' => $row['group_id'],
        ':cus_id' => $row['cus_id'],
        ':auction_month' => $date
    ]);

    $payment_row = $payment_stmt->fetch(PDO::FETCH_ASSOC);
    $collection_date = isset($payment_row['collection_date']) ? $payment_row['collection_date'] : null;

    if ($collection_date) {
        $collection_date = date('Y-m-d', strtotime($collection_date));
        if ($collection_date < $due_date) {
            $status_color = 'green'; // Paid before grace period
        } elseif ($collection_date > $grace_end_date) {
            $status_color = 'red'; // Paid after grace period
        } else {
            $status_color = 'orange'; // Paid during grace period
        }
    } else {
        $current_date = date('Y-m-d');
        if ($current_date > $grace_end_date) {
            $status_color = 'red'; // No payment and grace period is over
        } elseif ($current_date >= $due_date && $current_date <= $grace_end_date) {
            $status_color = 'orange'; // Grace period is active
        } else {
            $status_color = 'red'; // Grace period is not active
        }
    }

    // Place status color in the grace period column
    $sub_array[] = "<span style='display: inline-block; width: 10px; height: 10px; background-color: $status_color;'></span>";

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
