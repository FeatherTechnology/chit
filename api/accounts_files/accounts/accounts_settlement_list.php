<?php
require "../../../ajaxconfig.php";

$loan_issue_list_arr = array();
$cash_type = $_POST['cash_type'];
$bank_id = $_POST['bank_id'];

// Conditions based on cash_type
$cndtn = '';

if ($cash_type == '1') {
    $cndtn = "si.payment_type = '1'"; // Assuming '1' means cash
} elseif ($cash_type == '2') {
    $cndtn = "si.payment_type != '1' AND si.bank_name = '$bank_id'"; // Assuming non-cash and bank name check
}

$qry = $pdo->query("
    WITH SettlementSummary AS (
        SELECT 
            si.insert_login_id,
            si.settle_date,
            COUNT(DISTINCT si.auction_id) AS no_of_customers,
            SUM(
                CASE 
                    WHEN si.payment_type = '1' THEN (
                        si.settle_cash + si.cheque_val + si.transaction_val
                    )
                    ELSE 
                        CASE 
                            WHEN si.settle_type = '1' THEN si.settle_cash
                            WHEN si.settle_type = '2' THEN si.cheque_val
                            WHEN si.settle_type = '3' THEN si.transaction_val
                        END
                END
            ) AS total_settlement_amount
        FROM settlement_info si
        WHERE DATE(si.settle_date) = CURDATE()
        AND $cndtn
        GROUP BY si.insert_login_id, si.settle_date
    )
    SELECT
        a.name AS user_name,
        GROUP_CONCAT(DISTINCT bc.branch_name ORDER BY bc.branch_name SEPARATOR ', ') AS branch_names,
        ss.no_of_customers,
        ss.settle_date,
        ss.total_settlement_amount
    FROM
        users a
    INNER JOIN SettlementSummary ss ON ss.insert_login_id = a.id
    LEFT JOIN branch_creation bc ON FIND_IN_SET(bc.id, a.branch)
    GROUP BY
        a.name, ss.settle_date;
");

if ($qry->rowCount() > 0) {
    while ($data = $qry->fetch(PDO::FETCH_ASSOC)) {
        $data['no_of_customers'] = ($data['no_of_customers']) ? $data['no_of_customers'] : 0;
        $data['total_settlement_amount'] = ($data['total_settlement_amount']) ? moneyFormatIndia($data['total_settlement_amount']) : 0;
        $loan_issue_list_arr[] = $data;
    }
}

echo json_encode($loan_issue_list_arr);

// Format number in Indian Format
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


?>
