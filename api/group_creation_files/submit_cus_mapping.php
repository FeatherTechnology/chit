<?php 
require "../../ajaxconfig.php";
@session_start();
$user_id = $_SESSION['user_id'];

$group_id = $_POST['group_id'];
$group_name = $_POST['group_name'];

?>