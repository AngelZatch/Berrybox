<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_POST["roomToken"];
$state = $_POST["state"];
try{
	if($state == '1'){
		$value = 0;
	} else {
		$value = 1;
	}
	$toggle = $db->query("UPDATE rooms SET room_submission_rights = $value WHERE room_token = '$roomToken'");
	echo $value;
} catch (PDOException $e){
	echo "2"; // Error code
}

?>
