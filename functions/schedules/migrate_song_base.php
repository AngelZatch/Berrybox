<?php
include "../db_connect.php";
$db = PDOFactory::getConnection();

$tokens = array("1FE9IC3FV0FISQ3", "5F9FVNP3SYK9XGD", "5T1Z8L2B6EJ4QX8", "8BRD9ATZCBRSDKD", '9FC00R2JLPY5D8O', "55LEQWBIXRWSXLS", "BQ92DPN8D356KB5", "DTPE0X0VSANKOY8", "FEJBFEJSRSTG24N", "HAMSOHVI0EEWZRP", "HHKMO1TM3Z90B4Q", "HNF20X4FFH6XNK1", "KRTVXFPPV7C153T", "MMKMW3R9U48SOSW", "MU3231U6HMAJJ6Y", "OFNQ9ZLBSN28GSV", "QR4G8OCOG0LW93T", "TTOAHAFH43DYRSK", "V849W0FWSSGSAH1", "VNSHP2XAIS3GV6E", "ZSIS86PQQFGBQA9");

for($i = 0; $i < sizeof($tokens), $i++){
	$querySongs = $db->query("SELECT history_link, video_name FROM roomHistory_$tokens[$i]");

	while($song = $querySongs->fetch(PDO::FETCH_ASSOC)){
		$edit = $db->query("UPDATE song_base SET video_name = '$song[video_name]' WHERE link = '$song[history_link]'");
	}

}
?>
