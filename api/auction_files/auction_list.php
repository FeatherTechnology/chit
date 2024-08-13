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

// Adjusted the query to remove the date condition and only filter by gc.status
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
            gc.status BETWEEN 2 AND 3";

if (isset($_POST['search']) && $_POST['search'] != "") {
    $search = $_POST['search'];
    $query .= " AND (gc.grp_id LIKE '%" . $search . "%'
                      OR gc.grp_name LIKE '%" . $search . "%'
                      OR gc.chit_value LIKE '%" . $search . "%'
                      OR gc.total_months LIKE '%" . $search . "%'
                      OR gc.date LIKE '%" . $search . "%'
                      OR ad.auction_month LIKE '%" . $search . "%'
                      OR bc.branch_name LIKE '%" . $search . "%'
                      OR ad.status LIKE '%" . $search . "%')";
}
$query .= " GROUP BY gc.grp_id ";
$query .= " ORDER BY 
    CASE 
        WHEN ad.status = 1 THEN 0
        WHEN ad.status = 2 THEN 1
        ELSE 2
    END, 
    " . $column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'];

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
    $sub_array[] = isset($row['chit_value']) ? moneyFormatIndia($row['chit_value']) : ''; // Apply formatting here
    $sub_array[] = isset($row['total_months']) ? $row['total_months'] : '';
    $sub_array[] = isset($row['date']) ? $row['date'] : '';
    $sub_array[] = isset($row['auction_month']) ? $row['auction_month'] : '';
    $sub_array[] = isset($row['branch_name']) ? $row['branch_name'] : '';
    $sub_array[] = isset($row['status']) ? $auction_status[$row['status']] : '';
    $unique = $row['grp_id'] . '_' . $row['grp_name'] . '_' . $row['chit_value'];
    $action = "<button class='btn btn-primary auctionListBtn'  data-value='" . $unique. "'>&nbsp;View</button>";
   
    $sub_array[] = $action;
    $data[] = $sub_array;
}

function count_all_data($pdo)
{
    // Adjusted the count query to remove the date condition and only filter by gc.status
    $query = "SELECT COUNT(*) 
              FROM group_creation";
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

function moneyFormatIndia($num) {
    $explrestunits = "" ;
    if(strlen($num)>3){
        $lastthree = substr($num, strlen($num)-3, strlen($num));
        $restunits = substr($num, 0, strlen($num)-3); // extracts the last three digits
        $restunits = (strlen($restunits)%2 == 1) ? "0".$restunits : $restunits; 
        $expunit = str_split($restunits, 2);
        for($i=0; $i < sizeof($expunit); $i++){
            // creates each of the 2 unit pairs, adds a comma
            if($i==0){
                $explrestunits .= (int)$expunit[$i].","; // if first value , convert into integer
            }else{
                $explrestunits .= $expunit[$i].",";
            }
        }
        $thecash = $explrestunits.$lastthree;
    } else {
        $thecash = $num;
    }
    return $thecash;
}
?>
