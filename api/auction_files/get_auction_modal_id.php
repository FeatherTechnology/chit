<?php
require '../../ajaxconfig.php';

$group_id = $_POST['group_id'];
$date = $_POST['date'];

// Fetch IDs and values from auction_modal
$date = date('Y-m-d', strtotime($date));
$query = "SELECT id, cus_name FROM auction_modal WHERE group_id = ? AND date = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$group_id, $date]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $data]);
?>
