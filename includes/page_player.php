<?php
$roomToken = $_POST["roomToken"];
$roomDetails = $db->query("SELECT * FROM room r
								JOIN user u ON r.room_creator = u.user_token
								WHERE room_token=$roomToken")->fetch(PDO::FETCH_ASSOC);
?>
<p class="room-title"><span class="user-room"><?php echo $roomDetails["user_pseudo"];?></span>'s room</p>
<div id="currently-playing">
	Currently playing:
	<div class="modal-body" id="frame-play">
		<iframe src="" frameborder="0" width="100%" height="auto"></iframe>
	</div>
</div>
<div class="add-link">
	<input type="text" placeholder="Paste your YouTube link here" class="form-control url-box">
	<button class="btn btn-primary btn-block play-url" data-toggle="modal">Submit</button>
</div>
<div class="history col-lg-6">
	Recently played:
</div>
<div class="upcoming col-lg-6">
	Next:
</div>
