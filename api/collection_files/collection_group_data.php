<?php
require '../../ajaxconfig.php';
@session_start();
$id = $_POST['params']['id'];
$currentMonth = date('m');
$currentYear = date('Y');
include 'collectionStatus.php';

$collectionSts = new CollectionStsClass($pdo);

$column = array(
    'cc.id',
    'gc.grp_id',
    'gc.grp_name',
    'gc.chit_value',
    'ad.chit_amount',
    'status',
    'gc.grace_period',
    'cc.id',
    'cc.id'
);

$query = "SELECT
    ad.id AS auction_id,
    cc.id AS customer_id,
    gc.grp_id,
    gc.grp_name,
    gc.chit_value,
    ad.chit_amount,
    ad.auction_month,
    ad.date AS due_date,
    gcm.id AS cus_mapping_id,
    cc.cus_id,
    gc.grace_period
FROM
    auction_details ad
LEFT JOIN group_creation gc ON
    ad.group_id = gc.grp_id
LEFT JOIN group_cus_mapping gcm ON
    ad.group_id = gcm.grp_creation_id
LEFT JOIN customer_creation cc ON
    gcm.cus_id = cc.id
WHERE
   gc.status=3
    AND cc.id = '$id'
    AND YEAR(ad.date) = '$currentYear'
    AND MONTH(ad.date) ='$currentMonth'";

if (isset($_POST['search']) && $_POST['search'] != "") {
    $search = $_POST['search'];
    $query .= " AND ( gc.grp_id LIKE '%" . $search . "%'
                      OR gc.grp_name LIKE '%" . $search . "%'
                      OR gc.chit_value LIKE '%" . $search . "%'
                      OR ad.chit_amount LIKE '%" . $search . "%'
                      OR status LIKE '%" . $search . "%')";
}

$query .= " ORDER BY gc.grp_id";

$statement = $pdo->prepare($query);
$statement->execute();
$number_filter_row = $statement->rowCount();

$result = $statement->fetchAll();

$sno = isset($_POST['start']) ? $_POST['start'] + 1 : 1;
$data = [];

foreach ($result as $row) {
    $sub_array = [];
    $sub_array[] = $sno++;
    $sub_array[] = $row['grp_id'];
    $sub_array[] = $row['grp_name'];
    $sub_array[] = moneyFormatIndia($row['chit_value']);
    $roundedAmount = round($row['chit_amount']);
    $sub_array[] = moneyFormatIndia($roundedAmount);
    $chit_amount = $row['chit_amount'] ?? 0;
    $status = $collectionSts->updateCollectionStatus($row['cus_mapping_id'], $row['auction_id'], $row['grp_id'], $row['cus_id'], $row['auction_month'], $chit_amount);
    $sub_array[] = $status;

        // Grace Period Calculation
        $auction_month = $row['auction_month'] ?? 0;
        $grace_period = $row['grace_period'] ?? 0;
        $date = $row['due_date'] ?? ''; 
        $due_date = date('Y-m-d', strtotime($date));
        $grace_end_date = date('Y-m-d', strtotime($due_date . ' + ' . $grace_period . ' days'));
        
        $current_date = date('Y-m-d');
        if ($status === "Paid") {
            $status_color = 'green'; // Payment is made
        } elseif ($grace_end_date >= $current_date) {
            $status_color = 'orange'; // Payment is due but not yet
        } elseif ($grace_end_date < $current_date) {
            $status_color = 'red'; // Missed payment after grace period
        }
        
    $sub_array[] = "<span style='display: inline-block; width: 20px; height: 20px; border-radius: 4px; background-color: $status_color;'></span>";
    
    $sub_array[] = "<div class='dropdown'>
                        <button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button>
                        <div class='dropdown-content'>
                            <a href='#' class='add_due' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}_{$row['auction_month']}'>Due Chart</a>
                            <a href='#' class='commitment_chart' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment Chart</a>
                        </div>
                    </div>";
    $sub_array[] = "<div class='dropdown'>
                        <button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button>
                        <div class='dropdown-content'>
                            <a href='#' class='add_pay' data-value='{$row['grp_id']}_{$row['cus_id']}_{$row['auction_id']}_{$row['cus_mapping_id']}_{$row['customer_id']}'> Pay</a>
                            <a href='#' class='add_commitment' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment</a>
                        </div>
                    </div>";
    
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

echo json_encode($output); // Added missing semicolon

// Money format function
function moneyFormatIndia($num1)
{
    if ($num1 < 0) {
        $num = str_replace("-", "", $num1);
    } else {
        $num = $num1;
    }
    $explrestunits = "";
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3);
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits;
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            if ($i == 0) {
                $explrestunits .= (int)$expunit[$i] . ",";
            } else {
                $explrestunits .= $expunit[$i] . ",";
            }
        }
        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }

    if ($num1 < 0 && $num1 != '') {
        $thecash = "-" . $thecash;
    }

    return $thecash;
}
