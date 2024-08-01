<?php
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $auction_details = $_POST['auction_details'];

    try {
        // Prepare the SQL statement
        $sql = "INSERT INTO auction_details (date, auction_month, low_value, high_value, group_id) 
                VALUES (:date, :auction_month, :low_value, :high_value, :group_id)";
        $stmt = $pdo->prepare($sql);

        foreach ($auction_details as $auction) {
            // Ensure the date is correctly formatted
            $date = $auction['date'] ?? '';
            $auction_month = $auction['auction_month'] ?? '';
            $formatted_date = $date . '-' . $auction_month; // Concatenate date and month

            $stmt->execute([
                ':date' => $formatted_date,
                ':auction_month' => $auction_month,
                ':low_value' => $auction['low_value'] ?? 0,
                ':high_value' => $auction['high_value'] ?? 0,
                ':group_id' => $group_id
            ]);
        }

        $result = 1; // Success
    } catch (Exception $e) {
        $result = 2; // Failure
        $error_message = $e->getMessage();
    }

    echo json_encode([
        'result' => $result,
        'error_message' => $error_message ?? null
    ]);
}
?>
