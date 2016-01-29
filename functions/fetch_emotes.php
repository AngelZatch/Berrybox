<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$emotes = $db->query("SELECT * FROM emotes e ORDER by emote_text ASC");

$emoteList = array();
while($emote = $emotes->fetch(PDO::FETCH_ASSOC)){
	$e = array();
	$e["emoteText"] = $emote["emote_text"];
	array_push($emoteList, $e);
}
echo json_encode($emoteList);
?>
