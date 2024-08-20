<?php
require '../../ajaxconfig.php';
@session_start();

$id = $_POST['id']; // Assuming that you have sanitized the input in some way
include 'collectionStatus.php';

$collectionSts = new CollectionStsClass($pdo);

$query = "SELECT
    ad.id,
    cc.id as customer_id,
    gc.grp_id,
    gc.grp_name,
    gc.chit_value,
    ad.chit_amount,
    ad.auction_month,
    ad.date, -- Assuming this is the due date field
    gcm.id as cus_mapping_id,
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
    ad.status IN (2, 3)
    AND cc.id = :id
    AND MONTH(ad.date) = MONTH(CURDATE()) 
    AND YEAR(ad.date) = YEAR(CURDATE())";

$statement = $pdo->prepare($query);
$statement->execute([':id' => $id]);

$result = [];

if ($statement->rowCount() > 0) {
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $sub_array = [];

        // Grace Period Calculation
        $chit_amount = $row['chit_amount'] ?? 0;
        $auction_month = $row['auction_month'] ?? 0;
        $status = $collectionSts->updateCollectionStatus($row['cus_mapping_id'], $row['id'], $row['grp_id'], $row['cus_id'], $auction_month, $chit_amount);

        $grace_period = $row['grace_period'] ?? 0;
        $due_date = $row['date'] ? date('Y-m-d', strtotime($row['date'])) : ''; 

        $grace_start_date = $due_date; 
        $grace_end_date = date('Y-m-d', strtotime($due_date . ' + ' . $grace_period . ' days'));

        // Status Calculation
        $payment_query = "SELECT collection_date FROM collection 
                          WHERE cus_mapping_id =:cus_mapping_id AND auction_id = :id AND group_id = :grp_id AND cus_id = :cus_id 
                          AND auction_month = :auction_month";

        $payment_stmt = $pdo->prepare($payment_query);
        $payment_stmt->execute([
            ':cus_mapping_id' => $row['cus_mapping_id'],
            ':id' => $row['id'],
            ':grp_id' => $row['grp_id'],
            ':cus_id' => $row['cus_id'],
            ':auction_month' => $auction_month
        ]);

        $payment_row = $payment_stmt->fetch(PDO::FETCH_ASSOC);
        $collection_date = isset($payment_row['collection_date']) ? date('Y-m-d', strtotime($payment_row['collection_date'])) : null;

        $status_color = 'red'; // Default to red
        if ($collection_date) {
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
                $status_color = 'red'; // Due date and grace period exceeded without payment
            } elseif ($current_date >= $due_date && $current_date <= $grace_end_date) {
                $status_color = 'orange'; // Still within grace period
            }
        }

        $status_label = $collection_date ? 'Paid' : 'Payable';
        $sub_array['id'] = $row['id'];
        $sub_array['cus_mapping_id'] = $row['cus_mapping_id'];
        $sub_array['customer_id'] = $row['customer_id'];
        $sub_array['grp_id'] = $row['grp_id'];
        $sub_array['grp_name'] = $row['grp_name'];
        $sub_array['chit_value'] = moneyFormatIndia($row['chit_value']);
        $sub_array['chit_amount'] = ($row['chit_amount']);
        $sub_array['status'] = $status_label;
        $sub_array['grace_period'] = "<span style='display: inline-block; width: 20px; height: 20px;border-radius: 4px;  background-color: $status_color;'></span>";
        $sub_array['charts'] =   "<div class='dropdown'><button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button><div class='dropdown-content'><a href='#' class='add_due'data-value='{$row['grp_id']}_{$row['cus_id']}_{$row['id']}'>Due Chart</a>
      <a href='#' class='commitment_chart'data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment Chart</a></div></div>";
        $sub_array['action'] =   "<div class='dropdown'><button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button><div class='dropdown-content'><a href='#' class='add_pay'data-value='{$row['grp_id']}_{$row['cus_id']}_{$row['id']}_{$row['cus_mapping_id']}_{$row['customer_id']}'> Pay</a>
        <a href='#' class='add_commitment 'data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment</a></div></div>";

        $result[] = $sub_array;
    }
}

echo json_encode($result);

function moneyFormatIndia($num) {
    $explrestunits = "";
    if (strlen($num) > 3) {
        $lastthree = substr($num, strlen($num) - 3, strlen($num));
        $restunits = substr($num, 0, strlen($num) - 3); 
        $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits; 
        $expunit = str_split($restunits, 2);
        for ($i = 0; $i < sizeof($expunit); $i++) {
            $explrestunits .= ($i == 0) ? (int)$expunit[$i] . "," : $expunit[$i] . ",";
        }
        $thecash = $explrestunits . $lastthree;
    } else {
        $thecash = $num;
    }
    return $thecash;
}
?>
