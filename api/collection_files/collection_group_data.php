<?php
require '../../ajaxconfig.php';
@session_start();

$id = $_POST['id']; // Ensure that you have sanitized and validated this input
$currentMonth = date('m');
$currentYear = date('Y');
include 'collectionStatus.php';

$collectionSts = new CollectionStsClass($pdo);

// Fetch auction details with customer mapping
$query1 = "SELECT
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
    AND MONTH(ad.date) ='$currentMonth'"; // Removed extra closing parenthesis
$statement = $pdo->query($query1);

$result = [];

if ($statement->rowCount() > 0) {
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $sub_array = [];

        // Grace Period Calculation
        $chit_amount = $row['chit_amount'] ?? 0;
        $auction_month = $row['auction_month'] ?? 0;
        $status = $collectionSts->updateCollectionStatus($row['cus_mapping_id'], $row['auction_id'], $row['grp_id'], $row['cus_id'], $row['auction_month'], $chit_amount);
        $sub_array['status'] = $status;

        $grace_period = $row['grace_period'] ?? 0;
        $date = $row['due_date'] ?? '';

        $due_date = date('Y-m-d', strtotime($date));
        $grace_start_date = $due_date;
        $grace_end_date = date('Y-m-d', strtotime($due_date . ' + ' . $grace_period . ' days'));

        
        $current_date = date('Y-m-d');

        if ($status === "Paid") {
            $status_color = 'green'; // Payment is made
        } elseif ($grace_end_date >= $current_date) {
            $status_color = 'orange'; // Payment is due but not yet
        } elseif ($grace_end_date < $current_date) {
            $status_color = 'red'; // Missed payment after grace period
        }

        $sub_array['id'] = $row['auction_id'];
        $sub_array['cus_mapping_id'] = $row['cus_mapping_id'];
        $sub_array['customer_id'] = $row['customer_id'];
        $sub_array['grp_id'] = $row['grp_id'];
        $sub_array['grp_name'] = $row['grp_name'];
        $sub_array['chit_value'] = moneyFormatIndia($row['chit_value']);
        $sub_array['chit_amount'] = $row['chit_amount'];
        $sub_array['grace_period'] = "<span style='display: inline-block; width: 20px; height: 20px; border-radius: 4px; background-color: $status_color;'></span>";
        $sub_array['charts'] = "<div class='dropdown'>
                                    <button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button>
                                    <div class='dropdown-content'>
                                        <a href='#' class='add_due' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}_{$row['auction_month']}'>Due Chart</a>
                                        <a href='#' class='commitment_chart' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment Chart</a>
                                    </div>
                                </div>";
        $sub_array['action'] = "<div class='dropdown'>
                                    <button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button>
                                    <div class='dropdown-content'>
                                        <a href='#' class='add_pay' data-value='{$row['grp_id']}_{$row['cus_id']}_{$row['auction_id']}_{$row['cus_mapping_id']}_{$row['customer_id']}'> Pay</a>
                                        <a href='#' class='add_commitment' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment</a>
                                    </div>
                                </div>";

        $result[] = $sub_array;
    }
}

echo json_encode($result);

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
