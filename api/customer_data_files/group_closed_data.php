<?php
require '../../ajaxconfig.php';
@session_start();

$id = $_POST['id']; // Ensure that you have sanitized and validated this input
include '../collection_files/collectionStatus.php';
$collectionSts = new CollectionStsClass($pdo);
$group_status = [4 => 'Closed'];
// Fetch auction details with customer mapping
$query = "SELECT
    ad.id AS auction_id,
    cc.id AS customer_id,
    gc.grp_id,
    gc.grp_name,
    gc.chit_value,
    gc.status as grp_status,
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
    ad.status IN (2, 3) AND gc.status=4
    AND cc.id = :id
    AND MONTH(ad.date) = MONTH(CURDATE()) 
    AND YEAR(ad.date) = YEAR(CURDATE())";
$statement = $pdo->prepare($query);
$statement->execute([':id' => $id]);

$result = [];

if ($statement->rowCount() > 0) {
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $sub_array = [];

        // Debugging: Check if all expected keys are present
        if (!isset($row['grp_id'])) {
            error_log("Missing 'grp_id' in row data: " . print_r($row, true));
            continue;
        }

        // Grace Period Calculation
        $chit_amount = $row['chit_amount'] ?? 0;
        $auction_month = $row['auction_month'] ?? 0;
        $status = $collectionSts->updateCollectionStatus($row['cus_mapping_id'], $row['auction_id'], $row['grp_id'], $row['cus_id'], $row['auction_month'], $row['chit_amount']);
        $sub_array['status'] = $status;

        $grace_period = $row['grace_period'] ?? 0;
        $date = $row['due_date'] ?? '';

        $due_date = date('Y-m-d', strtotime($date));
        $grace_start_date = $due_date;
        $grace_end_date = date('Y-m-d', strtotime($due_date . ' + ' . $grace_period . ' days'));

        $payment_query = "SELECT collection_date, coll_status FROM collection 
                          WHERE cus_mapping_id = :cus_mapping_id 
                          AND auction_id = :auction_id 
                          AND group_id = :group_id 
                          AND cus_id = :cus_id 
                          AND auction_month = :auction_month";
        $payment_stmt = $pdo->prepare($payment_query);
        $payment_stmt->execute([
            ':cus_mapping_id' => $row['cus_mapping_id'],
            ':auction_id' => $row['auction_id'],
            ':group_id' => $row['grp_id'], // Ensure group_id is correctly used here
            ':cus_id' => $row['cus_id'],
            ':auction_month' => $row['auction_month']
        ]);

        $payment_row = $payment_stmt->fetch(PDO::FETCH_ASSOC);
        $collection_date = $payment_row['collection_date'] ?? null;
        //  $collection_status = $payment_row['coll_status'] ?? null;

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
        // Fetch all customers' payment statuses for the group
        $group_payment_query = "SELECT coll_status FROM collection 
   WHERE group_id = :group_id 
   AND auction_month = :auction_month   ORDER BY created_on DESC
                     LIMIT 1";
        $group_payment_stmt = $pdo->prepare($group_payment_query);
        $group_payment_stmt->execute([
            ':group_id' => $row['grp_id'],
            ':auction_month' => $row['auction_month']
        ]);

        $group_payment_statuses = $group_payment_stmt->fetchAll(PDO::FETCH_COLUMN);

        // Check if all customers have paid
        $all_paid = true;
        foreach ($group_payment_statuses as $status) {
            if ($status !== 'Paid') {
                $all_paid = false;
                break;
            }
        }

        // Update the group's collection status based on payments
        if ($all_paid) {
            $sub_array['collection_status'] = 'Completed';
        } else {
            $sub_array['collection_status'] = 'InCollection';
        }
        $sub_array['id'] = $row['auction_id'];
        $sub_array['cus_mapping_id'] = $row['cus_mapping_id'];
        $sub_array['customer_id'] = $row['customer_id'];
        $sub_array['grp_id'] = $row['grp_id'];
        $sub_array['grp_name'] = $row['grp_name'];
        $sub_array['chit_value'] = moneyFormatIndia($row['chit_value']);
        $sub_array['grp_status'] = $group_status[$row['grp_status']];
        $sub_array['grace_period'] = "<span style='display: inline-block; width: 20px; height: 20px; border-radius: 4px; background-color: $status_color;'></span>";
        $sub_array['charts'] = "<div class='dropdown'>
                                    <button class='btn btn-outline-secondary'><i class='fa'>&#xf107;</i></button>
                                    <div class='dropdown-content'>
                                        <a href='#' class='add_due' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}_{$row['auction_month']}'>Due Chart</a>
                                        <a href='#' class='commitment_chart' data-value='{$row['grp_id']}_{$row['cus_mapping_id']}'>Commitment Chart</a>
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
