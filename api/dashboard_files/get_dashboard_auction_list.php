<?php
// Today Auction List and next Auction date in the future.
require '../../ajaxconfig.php';

if (isset($_POST['params']) && $_POST['params'] != '' && $_POST['params'] != '0') {
    $branch_id = "AND gc.branch = '" . $_POST['params'] . "'";
} else {
    $branch_id = '';
}

$currentMonth = date('m');
$currentYear = date('Y');
$auction_status = [1 => 'In Auction', 2 => 'Finished', 3 => 'Finished'];

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

// Check if search term is set and handle search query
$searchQuery = "";

if (isset($_POST['search']) && $_POST['search'] != "") {
    $search = $_POST['search'];
    $searchQuery = " AND (gc.grp_id LIKE '%" . $search . "%'
                         OR gc.grp_name LIKE '%" . $search . "%'
                         OR gc.chit_value LIKE '%" . $search . "%'
                         OR gc.total_months LIKE '%" . $search . "%'
                         OR gc.date LIKE '%" . $search . "%'
                         OR ad.auction_month LIKE '%" . $search . "%'
                         OR bc.branch_name LIKE '%" . $search . "%'
                         OR ad.status LIKE '%" . $search . "%')";
}

// Main query
$query = "WITH RankedDates AS (
    SELECT 
        gc.id,
        gc.grp_id,
        gc.grp_name,
        gc.chit_value,
        gc.total_months,
        gc.date AS creation_date,
        ad.date AS auction_date,
        ad.auction_month,
        bc.branch_name,
        ad.status,
        ROW_NUMBER() OVER (PARTITION BY gc.grp_id ORDER BY ad.date ASC) AS rn
    FROM 
        group_creation gc
    LEFT JOIN 
        auction_details ad ON gc.grp_id = ad.group_id
    JOIN 
        branch_creation bc ON gc.branch = bc.id
    JOIN 
        users us ON FIND_IN_SET(gc.branch, us.branch)
    WHERE 
        gc.status BETWEEN 2 AND 3 
        AND ad.status = 1 
        AND YEAR(ad.date) = '$currentYear'
        AND MONTH(ad.date) = '$currentMonth'
        $searchQuery $branch_id
),
UpcomingAuctions AS (
    SELECT 
        rd.id,
        rd.grp_id,
        rd.grp_name,
        rd.chit_value,
        rd.total_months,
        rd.creation_date,
        rd.auction_date,
        rd.auction_month,
        rd.branch_name,
        rd.status,
        ROW_NUMBER() OVER (PARTITION BY rd.grp_id ORDER BY rd.auction_date ASC) AS rn_within_month
    FROM 
        RankedDates rd
    WHERE 
        rd.auction_date >= CURDATE()
),
FilteredAuctions AS (
    SELECT 
        ua.id,
        ua.grp_id,
        ua.grp_name,
        ua.chit_value,
        ua.total_months,
        ua.creation_date,
        ua.auction_date,
        ua.auction_month,
        ua.branch_name,
        ua.status,
        ROW_NUMBER() OVER (ORDER BY ua.auction_date ASC) AS final_rn
    FROM 
        UpcomingAuctions ua
    WHERE 
        ua.auction_date = CURDATE()
        OR ua.auction_date = (
            SELECT MIN(a.auction_date)
            FROM UpcomingAuctions a
            WHERE a.auction_date > CURDATE()
        )
)
SELECT DISTINCT
    fa.id,
    fa.grp_id,
    fa.grp_name,
    fa.chit_value,
    fa.total_months,
    fa.creation_date,
    fa.auction_date,
    fa.auction_month,
    fa.branch_name,
    fa.status
FROM 
    FilteredAuctions fa
ORDER BY 
    fa.grp_id, fa.auction_date ASC;";

$query1 = '';
// if (isset($_POST['length']) && $_POST['length'] != -1) {
//     $query1 = ' LIMIT ' . intval($_POST['start']) . ', ' . intval($_POST['length']);
// }

$stmt = $pdo->prepare($query);
$stmt->execute();
$number_filter_row = $stmt->rowCount();

$stmt = $pdo->prepare($query . $query1);
$stmt->execute();
$result = $stmt->fetchAll();

$sno = isset($_POST['start']) ? $_POST['start'] + 1 : 1;
$data = [];
foreach ($result as $row) {
    $sub_array = array();
    $sub_array[] = $sno++;
    $sub_array[] = isset($row['grp_id']) ? $row['grp_id'] : '';
    $sub_array[] = isset($row['grp_name']) ? $row['grp_name'] : '';
    $sub_array[] = isset($row['chit_value']) ? moneyFormatIndia($row['chit_value']) : ''; // Apply formatting here
    $sub_array[] = isset($row['total_months']) ? $row['total_months'] : '';
    $sub_array[] = isset($row['creation_date']) ? $row['creation_date'] : '';
    $sub_array[] = isset($row['auction_month']) ? $row['auction_month'] : '';
    $sub_array[] = isset($row['branch_name']) ? $row['branch_name'] : '';
    $sub_array[] = isset($row['status']) ? $auction_status[$row['status']] : '';
    $action = "<a href='auction' class='btn btn-primary open-auction-list'  data-grpid='" . $row['grp_id']. "' data-grpname='".$row['grp_name']."' data-chitval='".$row['chit_value']."'>&nbsp;View</button>";

    $sub_array[] = $action;
    $data[] = $sub_array;
}
$stmt->closeCursor();

function count_all_data($pdo)
{
    // Adjusted the count query to remove the date condition and only filter by gc.status
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

function moneyFormatIndia($num) {
    $explrestunits = "";
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); // extracts the last three digits
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; 
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            // creates each of the 2 unit pairs, adds a comma
            if ($i == 0) {
                $explrestunits .= (int)$expunit[$i] . ","; // if first value , convert into integer
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }
    return $thecash;
}
?>
