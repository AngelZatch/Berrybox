<?php
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_POST["roomToken"];
$roomDetails = $db->query("SELECT *
							FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);
?>
<div class="col-lg-8">
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
</div>
<div class="col-lg-4">
	<div class="panel panel-default">
		<div class="panel-heading">
			<p class="chat-room">
				<span class="room-dj"><?php echo $roomDetails["user_pseudo"];?></span>'s room
			</p>
			<p class="room-playing">Currently playing : <span class="room-track"></span></p>
			<?php if(basename($_SERVER['PHP_SELF']) != "room.php"){ ?>
			<div class="input-group">
				<input type="text" placeholder="Sumbit a link" class="form-control">
				<span class="input-group-btn"><button class="btn btn-default" value="<?php echo $roomToken;?>">Send</button></span>
			</div>
			<?php } ?>
		</div>
		<div class="panel-body body-chat"></div>
		<div class="panel-footer">
			<div class="input-group">
				<input type="text" class="form-control chatbox">
				<span class="input-group-btn btn-chat">
					<button class="btn btn-primary">Chat</button>
				</span>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).on('focus', '.chatbox', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				sendMessage("<?php echo $roomToken;?>");
			}
		})
	}).on('click', '.btn-chat', function(){
		sendMessage("<?php echo $roomToken;?>");
	});
</script>
