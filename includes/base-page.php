<?php
$db = PDOFactory::getConnection();
$queryActiveRooms = $db->query("SHOW TABLES LIKE '%Room%'");
?>
<button class="btn btn-primary btn-block" id="create-room">Create a room</button>
<p>Active rooms</p>
<div class="active-rooms row">

</div>
