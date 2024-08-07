<?php
require '../../ajaxconfig.php';

$response = array();

if (isset($_POST['group_id']) && isset($_POST['customer_name'])) {
    $group_id = $_POST['group_id'];
    $customer_name = $_POST['customer_name'];

    try {
        $sql = "SELECT 
                    cc.id AS cus_id 
                FROM 
                    group_cus_mapping gcm
                JOIN 
                    customer_creation cc ON gcm.cus_id = cc.id
                WHERE 
                    gcm.grp_creation_id = :group_id 
                    AND CONCAT(cc.first_name, ' ', cc.last_name) = :customer_name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $group_id, ':customer_name' => $customer_name]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response['status'] = 'success';
            $response['cus_id'] = $result['cus_id'];
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Customer not found.';
        }
    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Required parameters are missing.';
}

$pdo = null; // Close Connection
echo json_encode($response);
?>
