<?php
require '../../ajaxconfig.php';

$response = array();
include 'grp_col_status.php';

$collectionSts = new GroupStsClass($pdo);

if (isset($_POST['group_id'])) { 
    $group_id = $_POST['group_id'];
    $auction_month = $_POST['auction_month'];

    try {
        $qry = "SELECT
            ad.group_id,
            cc.cus_id,
            CONCAT(cc.first_name, ' ', cc.last_name) AS cus_name,
            cc.mobile1,
            pl.place,
            (
                SELECT GROUP_CONCAT(sc.occupation SEPARATOR ', ')
                FROM source sc
                WHERE sc.cus_id = cc.cus_id
            ) AS occupations,
            gcm.id AS cus_mapping_id,
               CASE 
        WHEN ad.cus_name = gcm.cus_id AND gcm.settle_status = 'Yes' 
        THEN 'Yes' 
        ELSE '' 
    END AS settle_status, 
            ad.auction_month
        FROM
            auction_details ad
        LEFT JOIN group_cus_mapping gcm ON ad.group_id = gcm.grp_creation_id
        LEFT JOIN customer_creation cc ON gcm.cus_id = cc.id
        LEFT JOIN place pl ON cc.place = pl.id
        LEFT JOIN group_creation gc ON ad.group_id = gc.grp_id
        JOIN users us ON FIND_IN_SET(gc.branch, us.branch)
        WHERE
            gc.grp_id = '$group_id' AND ad.auction_month='$auction_month' 
        GROUP BY gcm.id
        ORDER BY cc.cus_id;";

        // Execute the query
        $stmt = $pdo->query($qry);

        // Fetch the result
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Loop through each row and update the group status
            foreach ($data as &$row) {
                $status = $collectionSts->updateGroupStatus($row['cus_mapping_id'], $row['group_id'], $row['cus_id'], $row['auction_month']);
                $row['action'] = $status; // Add the status to the row
                $row['settle_status'] = $row['settle_status'] ?? ''; 
                // Determine color based on settle_status
                if ($status=== 'Paid') {
                    $row['action'] = '<span style="color: green;"><strong>' . $row['action'] . '</strong></span>';

                } elseif ($status === 'Unpaid') {
                    $row['action']  = '<span style="color: red;"><strong>' . $row['action'] . '</strong></span>';
                } else {
                    $row['action']  = ''; // Set to empty string if not Paid or Unpaid
                }
            }

            echo json_encode($data);
        } else {
            echo json_encode([]);
        }

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    // Close the PDO connection
    $pdo = null;
}
?>
