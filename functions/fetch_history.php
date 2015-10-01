<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];

$queryHistory = $db->query("SELECT * FROM roomHistory_$roomToken");
$totalHistory = array();
while($history = $queryHistory->fetch(PDO::FETCH_ASSOC)){
	$h = array();
	$h["link"] = $history["history_link"];
	array_push($totalHistory, $h);
}
echo json_encode($totalHistory);
?>
