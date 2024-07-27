<?php
require '../../ajaxconfig.php';
@session_start();
$user_id = $_SESSION['user_id'];

$column = array(
    'grp_id',
    'grp_name',
    'chit_value',
    'total_months',
    'date',
    'commission',
    'bc.branch_name'
);
$query = "SELECT gc.id, `grp_id`,`grp_name`,`chit_value`,`total_months`,`date`,`commission`, bc.branch_name FROM `group_creation` gc JOIN branch_creation bc ON gc.branch = bc.id WHERE 1 ";

if (isset($_POST['search'])) {
    if ($_POST['search'] != "") {
        $search = $_POST['search'];
        $query .= " AND (grp_id LIKE '" . $search . "%'
                    OR grp_name LIKE '%" . $search . "%'
                    OR chit_value LIKE '%" . $search . "%'
                    OR total_months LIKE '%" . $search . "%'
                    OR date LIKE '%" . $search . "%'
                    OR commission LIKE '%" . $search . "%'
                    OR bc.branch_name LIKE '%" . $search . "%')";
    }
}

if (isset($_POST['order'])) {
    $query .= " ORDER BY " . $column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'];
} else {
    $query .= ' ';
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
    $sub_array[] = isset($row['grp_id']) ? $row['grp_id'] : '';
    $sub_array[] = isset($row['grp_name']) ? $row['grp_name'] : '';
    $sub_array[] = isset($row['chit_value']) ? $row['chit_value'] : '';
    $sub_array[] = isset($row['total_months']) ? $row['total_months'] : '';
    $sub_array[] = isset($row['date']) ? $row['date'] : '';
    $sub_array[] = isset($row['commission']) ? $row['commission'] : '';
    $sub_array[] = isset($row['branch_name']) ? $row['branch_name'] : '';
    $sub_array[] = "<a href='#' class='edit-group-creation' value='" . $row['id'] . "' title='Edit details'><span class='icon-border_color'></span></a>";
    $data[] = $sub_array;
}
function count_all_data($pdo)
{
    $query = "SELECT COUNT(*) FROM group_creation";
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
