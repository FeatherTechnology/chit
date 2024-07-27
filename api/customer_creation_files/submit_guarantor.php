<?php
require '../../ajaxconfig.php';
@session_start();

if (!empty($_FILES['gu_pic']['name'])) {
    $paths= "../../uploads/customer_creation/gu_pic/";
    $gpicture = $_FILES['gu_pic']['name'];
    $pic_temp = $_FILES['gu_pic']['tmp_name'];
    $fileExtension = pathinfo($gpicture, PATHINFO_EXTENSION);
    $gpicture = uniqid() . '.' . $fileExtension;
    while (file_exists($paths . $gpicture)) {
        $gpicture = uniqid() . '.' . $fileExtension;
    }
    move_uploaded_file($pic_temp, $paths . $gpicture);
} else {
    $gpicture = $_POST['gur_pic'];
}

$cus_id = $_POST['cus_id'];
$gua_relationship = $_POST['gua_relationship'];
$guarantor_name = $_POST['guarantor_name'];
$existing_cus = $_POST['existing_cus'];
$details = $_POST['details'];
$user_id = $_SESSION['user_id'];
$guarantor_id = $_POST['guarantor_id'];

if ($guarantor_id != '') {
    $qry = $pdo->prepare("UPDATE `guarantor_info` SET `cus_id` = :cus_id, `gua_relationship` = :gua_relationship, `guarantor_name` = :guarantor_name, `existing_cus` = :existing_cus, `details` = :details, `gu_pic` = :gpicture, `update_login_id` = :user_id, `updated_on` = now() WHERE `id` = :guarantor_id");
    $qry->execute(['cus_id' => $cus_id, 'gua_relationship' => $gua_relationship, 'guarantor_name' => $guarantor_name, 'existing_cus' => $existing_cus, 'details' => $details, 'gpicture' => $gpicture, 'user_id' => $user_id, 'guarantor_id' => $guarantor_id]);
    $result = $qry->rowCount() > 0 ? 'Success' : 'Failed';
} else {
    $qry = $pdo->prepare("INSERT INTO `guarantor_info`(`cus_id`, `gua_relationship`, `guarantor_name`, `existing_cus`, `details`, `gu_pic`, `insert_login_id`, `created_on`) VALUES (:cus_id, :gua_relationship, :guarantor_name, :existing_cus, :details, :gpicture, :user_id, now())");
    $qry->execute(['cus_id' => $cus_id, 'gua_relationship' => $gua_relationship, 'guarantor_name' => $guarantor_name, 'existing_cus' => $existing_cus, 'details' => $details, 'gpicture' => $gpicture, 'user_id' => $user_id]);
    $result = $qry->rowCount() > 0 ? 'Success' : 'Failed';
}

echo json_encode($result);
$pdo = null;
?>

