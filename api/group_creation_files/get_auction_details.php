<?php
require '../../ajaxconfig.php';
@session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_id = $_POST['group_id'];
    $grp_date = $_POST['grp_date'];
    $start_month = $_POST['start_month'];
    $total_months = intval($_POST['total_month']);
    $formatted_date = $grp_date . '-' . $start_month;
    $format_date = new DateTime($formatted_date);
    $formatted_date= $format_date->format('Y-m-d');

    try {
        // Prepare and execute SQL to get the first date and auction month from auction_details
        $subQuery = "SELECT COUNT(*) AS total FROM auction_details WHERE group_id = :group_id";
        $stmt = $pdo->prepare("SELECT date FROM auction_details WHERE group_id = :group_id ORDER BY auction_month ASC LIMIT 1");
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_ASSOC);
        if ($format_date == date && total == $total_months) 
        if ($stmt->rowCount() > 0){
       

                    // Fetch data from auction_details
                    $stmt = $pdo->prepare("SELECT id,auction_month,low_value,high_value FROM auction_details WHERE group_id = :group_id");
                    $stmt->execute([
                        ':group_id' => $group_id,
                    ]);
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo json_encode([
                        'result' => 1, // Data found
                        'data' => $data
                    ]);
                    exit();
                }
     
        

        // If conditions are not satisfied, calculate start and end month
        $startDate = new DateTime($start_month . "-01");
        $endDate = clone $startDate;
        $endDate->modify('+' . ($total_months - 1) . ' months');
        $endMonth = $endDate->format('Y-m');

        echo json_encode([
            'result' => 0, // Generate new rows
            'start_month' => $start_month,
            'end_month' => $endMonth
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'result' => 2, // Failure
            'error_message' => $e->getMessage()
        ]);
    }
}
?>

