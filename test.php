<?php
include "functions/db_connect.php";
session_start();
$db = PDOFactory::getConnection();
$now = date('Y-m-d H:i:s');
$limitDate = date('Y-m-d H:i:s', time() - 30 * 60);
$load = $db->query("SELECT *
						FROM roomChat_FYRNDGIXQ08B4VW s
						LEFT JOIN user u ON s.message_author = u.user_token
						LEFT JOIN user_preferences up ON s.message_author=up.up_user_id
					WHERE message_time <= '$now' AND message_time > '$limitDate'
					ORDER BY message_time ASC");
?>
<pre>
<?php
while($messages = $load->fetch(PDO::FETCH_ASSOC)){
	print_r($messages);
}
?>
</pre>
