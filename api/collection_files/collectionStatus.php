<?php
require '../../ajaxconfig.php';
@session_start();

class CollectionStsClass
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateCollectionStatus($auction_id, $group_id, $cus_id, $auction_month, $chit_amount)
{
    // Initialize status to 'Payable'
    $coll_status = 'Payable';

    // Query to check if a payment exists for the auction month
    $query = "SELECT collection_amount, collection_date FROM collection 
              WHERE auction_id = :auction_id AND group_id = :group_id AND cus_id = :cus_id 
              AND auction_month = :auction_month";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([
        ':auction_id' => $auction_id,
        ':group_id' => $group_id,
        ':cus_id' => $cus_id,
        ':auction_month' => $auction_month
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Payment exists
        $collection_amount = $row['collection_amount'];

        // Check if the full chit amount is paid
        if ($collection_amount >= $chit_amount) {
            $coll_status = 'Paid';
        } else {
            $coll_status = 'Payable'; // Balance is remaining
        }
    } else {
        // No payment found, check if due date has passed
        $due_date = date('Y-m-t', strtotime($auction_month . '-01')); // Last day of the auction month
        $current_date = date('Y-m-d');

        if ($current_date > $due_date) {
            $coll_status = 'Payable'; // If due date has passed and no payment is made
        } else {
            $coll_status = 'Payable'; // Due date has not passed, no payment made
        }
    }

    // Update the collection status in the database
    $update_query = "UPDATE collection 
                     SET coll_status = :coll_status 
                     WHERE auction_id = :auction_id AND group_id = :group_id AND cus_id = :cus_id 
                     AND auction_month = :auction_month";
    $update_stmt = $this->pdo->prepare($update_query);
    $update_stmt->execute([
        ':coll_status' => $coll_status,
        ':auction_id' => $auction_id,
        ':group_id' => $group_id,
        ':cus_id' => $cus_id,
        ':auction_month' => $auction_month
    ]);
    
    return $coll_status; // Return the status for use in your table
}
}

?>
