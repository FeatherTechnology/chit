<?php
require '../../ajaxconfig.php';

$response = array();

if (isset($_POST['id'])) {
    $guarantor_id = $_POST['id'];

    $stmt = $pdo->prepare("SELECT guarantor_relationship FROM guarantor_info WHERE id = ?");
    $stmt->execute([$guarantor_id]);

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['guarantor_relationship'] = $row['guarantor_relationship'];
    } else {
        $response['guarantor_relationship'] = ''; // No relationship found
    }
} else {
    $response['guarantor_relationship'] = ''; // No guarantor ID provided
}

$pdo = null; // Close the connection

echo json_encode($response);
?>

