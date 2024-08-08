<?php
require '../../ajaxconfig.php';
@session_start();
$user_id = $_SESSION['user_id'];
$auction_status = [1 => 'In Auction', 2 => 'Finished'];

$column = array(
    'gc.id',
    'gc.grp_id',
    'gc.grp_name',
    'gc.chit_value',
    'gc.total_months',
    'gc.date',
    'ad.auction_month',
    'bc.branch_name',
    'ad.status',
    'gc.id'
);

// Base query with JOIN
$query = "SELECT 
            gc.id,
            gc.grp_id,
            gc.grp_name,
            gc.chit_value,
            gc.total_months,
            gc.date,
            ad.auction_month,
            bc.branch_name,
            ad.status
        FROM 
            group_creation gc
        LEFT JOIN 
            auction_details ad ON gc.grp_id = ad.group_id
        JOIN 
            branch_creation bc ON gc.branch = bc.id
        WHERE 
            ad.date = CURDATE() AND gc.status >= 2"; // Filter for current date only

// Add search condition if search term is provided
if (isset($_POST['search']) && $_POST['search'] != "") {
    $search = '%' . $_POST['search'] . '%';
    $query .= " AND (gc.grp_id LIKE :search
                    OR gc.grp_name LIKE :search
                    OR gc.chit_value LIKE :search
                    OR gc.total_months LIKE :search
                    OR gc.date LIKE :search
                    OR ad.auction_month LIKE :search
                    OR bc.branch_name LIKE :search
                    OR ad.status LIKE :search)";
}

// Add ordering condition
if (isset($_POST['order'])) {
    $query .= " ORDER BY " . $column[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'];
}

// Add pagination
$query1 = '';
if (isset($_POST['length']) && $_POST['length'] != -1) {
    $query1 = ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']);
}

// Prepare and execute the statement for counting filtered rows
$statement = $pdo->prepare($query);
if (isset($_POST['search']) && $_POST['search'] != "") {
    $statement->bindValue(':search', $search, PDO::PARAM_STR);
}
$statement->execute();
$number_filter_row = $statement->rowCount();

// Prepare and execute the statement for fetching paginated data
$statement = $pdo->prepare($query . $query1);
if (isset($_POST['search']) && $_POST['search'] != "") {
    $statement->bindValue(':search', $search, PDO::PARAM_STR);
}
$statement->execute();
$result = $statement->fetchAll();

// Prepare data for response
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
    $sub_array[] = isset($row['auction_month']) ? $row['auction_month'] : '';
    $sub_array[] = isset($row['branch_name']) ? $row['branch_name'] : '';
    $sub_array[] = isset($row['status']) ? $auction_status[$row['status']] : '';
    
    $action = "<button class='btn btn-primary auctionListBtn' value='" . $row['grp_id'] . "'>&nbsp;View</button>";
    $sub_array[] = $action;
    $data[] = $sub_array;
}

// Function to count all data
function count_all_data($pdo)
{
    $query = "SELECT COUNT(*) FROM group_creation";
    $statement = $pdo->prepare($query);
    $statement->execute();
    return $statement->fetchColumn();
}

// Prepare output for DataTables
$output = array(
    'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    'recordsTotal' => count_all_data($pdo),
    'recordsFiltered' => $number_filter_row,
    'data' => $data
);

echo json_encode($output);
?>
