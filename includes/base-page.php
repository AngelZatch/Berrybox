<?php
include "../functions/db_connect.php";
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SHOW TABLES LIKE '%Room%'");
?>
<button class="btn btn-primary btn-block" id="create-room">Create a room</button>
<p>Active rooms</p>
<?php while($activeRooms = $queryActiveRooms->fetch(PDO::FETCH_ASSOC)){ ?>
<p><?php print_r($activeRooms);?></p>
<?php } ?>
