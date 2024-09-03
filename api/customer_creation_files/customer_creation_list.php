<?php
require '../../ajaxconfig.php';
@session_start();
$user_id = $_SESSION['user_id'];
$reference = [1=>'Promotion',2=>'Customer',3=>'Well Known person'];
$column = array(
    'cc.id',
    'cc.cus_id',
    'cp.cus_name',
    'cc.first_name',
    'cc.last_name',
    'cc.mobile1',
    'pl.place',
    'cc.chit_limit',
    'cc.reference_type ',
    'cc.id'
);
$query = "SELECT cc.id, cc.cus_id, cc.first_name, cc.last_name, cc.mobile1, pl.place,cc.chit_limit,cc.reference_type 
 FROM customer_creation cc 
 LEFT JOIN place pl ON cc.place = pl.id
WHERE 1";

if (isset($_POST['search'])) {
    if ($_POST['search'] != "") {
        $search = $_POST['search'];
        $query .= " AND (cc.cus_id LIKE '" . $search . "%'
                      OR cc.cus_name LIKE '%" . $search . "%'
                      OR cc.first_name LIKE '%" . $search . "%'
                      OR cc.last_name LIKE '%" . $search . "%'
                      OR cc.mobile1 LIKE '%" . $search . "%'
                      OR pl.place LIKE '%" . $search . "%'
                      OR cc.chit_limit LIKE '%" . $search . "%'
                      OR cc.reference_type LIKE '%" . $search . "%')";
    }
}

// if (isset($_POST['order'])) {
//     $query .= " ORDER BY " . $column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'];
// } else {
//     $query .= ' ';
// }
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
    $sub_array[] = isset($row['first_name']) ? $row['first_name'] : '';
    $sub_array[] = isset($row['last_name']) ? $row['last_name'] : '';
    $sub_array[] = isset($row['mobile1']) ? $row['mobile1'] : '';
    $sub_array[] = isset($row['place']) ? $row['place'] : '';
    $sub_array[] = isset($row['chit_limit']) ? $row['chit_limit'] : '';
    $sub_array[] = isset($row['reference_type']) && isset($reference[$row['reference_type']]) ? $reference[$row['reference_type']] : '';
    $action = "<span class='icon-border_color customerActionBtn' value='" . $row['id'] . "'></span>&nbsp;&nbsp;&nbsp;";
    $action .= "<span class='icon-delete customerDeleteBtn' value='" . $row['id'] . "'></span>";
    $sub_array[] = $action;
    $data[] = $sub_array;
}
function count_all_data($pdo)
{
    $query = "SELECT COUNT(*) FROM customer_creation";
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
