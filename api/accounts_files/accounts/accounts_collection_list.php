<?php
require "../../../ajaxconfig.php";

$collection_list_arr = array();
$cash_type = $_POST['cash_type'];
$bank_id = $_POST['bank_id'];

if ($cash_type == '1') {
    $cndtn = "coll_mode = '1' ";
} elseif ($cash_type == '2') {
    $cndtn = "coll_mode != '1' AND bank_id = '$bank_id' ";
}

//collection_mode = 1 - cash; 2 to 5 - bank;

$qry = $pdo->query("WITH first_query AS (
    SELECT 
        u.id AS userid, 
        u.name, 
        bc.branch_name, 
        COUNT(DISTINCT c.cus_id) AS no_of_customers, 
        SUM(c.collection_amount) AS total_amount, 
        '1' AS TYPE 
    FROM `collection` c 
    LEFT JOIN group_creation gc ON c.group_id = gc.grp_id 
    LEFT JOIN branch_creation bc ON gc.branch = bc.id 
    JOIN users u ON FIND_IN_SET(gc.branch, u.branch) 
    WHERE 
        c.collection_date > COALESCE(
            (SELECT created_on FROM accounts_collect_entry WHERE user_id = u.id ORDER BY id DESC LIMIT 1), 
            '1970-01-01 00:00:00'
        ) 
        AND c.collection_date <= NOW() 
        AND c.insert_login_id = u.id 
    GROUP BY u.id, bc.branch_name
),
second_query AS (
    SELECT 
        us.id AS userid, 
        us.name, 
        bc.branch_name, 
        SUM(ac.no_of_customers) AS no_of_customers, 
        SUM(ac.collection_amnt) AS total_amount, 
        '2' AS TYPE 
    FROM `accounts_collect_entry` ac 
    JOIN users us ON ac.user_id = us.id 
    LEFT JOIN branch_creation bc ON ac.branch = bc.id 
    WHERE 
        DATE(ac.created_on) = CURDATE() 
        AND ac.user_id NOT IN (SELECT userid FROM first_query) 
    GROUP BY us.id, bc.branch_name
)
SELECT 
    userid, 
    name, 
    branch_name, 
    SUM(no_of_customers) AS no_of_customers, 
    SUM(total_amount) AS total_amount, 
    MIN(TYPE) AS TYPE 
FROM (
    SELECT * FROM first_query 
    UNION ALL 
    SELECT * FROM second_query
) AS combined_query
GROUP BY userid, name, branch_name
ORDER BY userid ASC");

if ($qry->rowCount() > 0) {
    while ($data = $qry->fetch(PDO::FETCH_ASSOC)) {
        $disabled = ($data['TYPE'] == 2) ? 'disabled' : ''; // 1 - enabled; 2 - disabled
        $data['total_amount'] = moneyFormatIndia($data['total_amount']);
        $data['action'] = "<a href='#' class='collect-money' value='" . $data['userid'] . "'><button class='btn btn-primary' " . $disabled . ">Collect</button></a> ";
        $collection_list_arr[] = $data;
    }
}

echo json_encode($collection_list_arr);

//Format number in Indian Format
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
