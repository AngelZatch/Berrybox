<div class="panel panel-default">
	<div class="panel-heading">
		<p class="chat-room">
			<span class="room-dj">AngelZatch</span>'s room
		</p>
		<p class="room-playing">Currently playing : <span class="room-track"></span></p>
		<?php if(basename($_SERVER['PHP_SELF']) != "room.php"){ ?>
			<div class="input-group">
				<input type="text" placeholder="Sumbit a link" class="form-control">
				<span class="input-group-btn"><button class="btn btn-default">Send</button></span>
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
