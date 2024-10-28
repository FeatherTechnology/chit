<?php
require "../../../ajaxconfig.php";
date_default_timezone_set('Asia/Kolkata'); // Set timezone

// Get the current date in 'Y-m-d' format
$currentDate = date('Y-m-d');

// Modify the query to filter based on the created_on date
$query = "
    SELECT dr.amount, dr.quantity, (dr.amount * dr.quantity) AS total_value 
    FROM denomination_table dl 
    LEFT JOIN denom_refer_table dr ON dl.id = dr.denom_id 
    WHERE DATE(dl.created_on) <> :currentDate
    ORDER BY dl.id DESC
";

// Prepare and execute the query with the current date as a parameter
$stmt = $pdo->prepare($query);
$stmt->execute(['currentDate' => $currentDate]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the result as JSON
echo json_encode($result);
?>

