<?php
require '../../ajaxconfig.php';
@session_start();

$response = array();

if (isset($_POST['group_id']) && isset($_POST['date']) && isset($_POST['cus_id']) && isset($_POST['value'])) {
    $group_id = $_POST['group_id'];
    $date = $_POST['date'];
    $cus_id = $_POST['cus_id'];
    $value = $_POST['value'];
    $user_id = $_SESSION['user_id'];

    try {
        // Convert date to yyyy-mm-dd format
        $formattedDate = date('Y-m-d', strtotime($date));

        // Fetch the auction_id from auction_details
        $sql = "SELECT id FROM auction_details WHERE group_id = :group_id AND date = :date";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $group_id,
            ':date' => $formattedDate
        ]);

        $auction = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($auction) {
            $auction_id = $auction['id'];

            // Prepare SQL statement to insert into auction_modal
            $sql = "INSERT INTO auction_modal (auction_id, group_id, date, cus_name, value, inserted_login_id) 
                    VALUES (:auction_id, :group_id, :date, :cus_id, :value, :insert_login_id)";
            $stmt = $pdo->prepare($sql);

            // Execute the SQL statement
            $stmt->execute([
                ':auction_id' => $auction_id,
                ':group_id' => $group_id,
                ':date' => $formattedDate,
                ':cus_id' => $cus_id,
                ':value' => $value,
                ':insert_login_id' => $user_id
            ]);

            if ($stmt->rowCount() > 0) {
                $response['status'] = 'success';
                $response['message'] = 'Row inserted successfully.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to insert row.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'No auction found for the provided group and date.';
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
