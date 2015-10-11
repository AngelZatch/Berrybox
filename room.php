<?php
session_start();
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_GET["id"];
$roomDetails = $db->query("SELECT *
							FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);

if(isset($_GET["lang"])){
	$lang = $_GET["lang"];
	$_SESSION["lang"] = $lang;

	include_once "languages/lang.".$lang.".php";
} else {
	header("Location:room.php?id=$roomToken&lang=en");
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $roomDetails["user_pseudo"];?>'s Strawberry room</title>
		<?php include "styles.php";?>
	</head>
	<body>
		<div class="col-lg-8" id="room-player">
			<div class="room-info">
				<p class="room-title"><a href="home.php?lang=<?php echo $_GET["lang"];?>" class="btn btn-default leave-room"><span class="glyphicon glyphicon-arrow-left"></span> <?php echo $lang["back"];?></a> <?php echo $roomDetails["room_name"];?></p>
				<p class="room-creator"><span class="glyphicon glyphicon-user" title="<?php echo $lang["room_admin"];?>"></span> <?php echo $roomDetails["user_pseudo"];?> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="player"></div>
			</div>
			<div class="add-link">
				<?php if(isset($_SESSION["token"])){ ?>
				<div class="input-group">
					<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
					<span class="input-group-btn">
						<button class="btn btn-primary btn-block play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
					</span>
				</div>
				<?php } else { ?>
				<p class="submit-required"><?php echo $lang["no_submit"];?></p>
				<?php } ?>
			</div>
		</div>
		<div class="col-lg-4" id="room-chat">
			<div class="panel panel-default panel-chat">
				<div class="panel-heading">
					<div class="chat-options row">
						<div class="col-lg-3 toggle-song-list">
							<span class="glyphicon glyphicon-list"></span> <?php echo $lang["playlist"];?>
						</div>
						<div class="col-lg-4">
							<span class="glyphicon glyphicon-user" title="<?php echo $lang["watch_count"];?>"></span><span id="watch-count"></span>
						</div>
						<div class="col-lg-5">
							<div data-toggle="popover-x" data-target="#popover-chat-settings" data-placement="bottom bottom-right" style="cursor:pointer;"><span class="glyphicon glyphicon-cog" title="<?php echo $lang["chat_settings"];?>"></span> <?php echo $lang["chat_settings"];?></div>
							<div class="popover popover-default popover-lg" id="popover-chat-settings">
								<div class="arrow"></div>
								<div class="popover-title"><span class="close" data-dismiss="popover-x">&times;</span><?php echo $lang["chat_settings"];?></div>
								<div class="popover-content">
									<p><?php echo $lang["color_pick"];?></p>
									<div class="color-cube" id="color-67fc97"></div>
									<div class="color-cube" id="color-4e96f2"></div>
									<div class="color-cube" id="color-db8bf7"></div>
									<div class="color-cube" id="color-e416a1"></div>
									<div class="color-cube" id="color-1bddcf"></div>
									<div class="color-cube" id="color-31a03f"></div>
									<div class="color-cube" id="color-fb4836"></div>
									<div class="color-cube" id="color-4b524d"></div>
									<div class="color-cube" id="color-6a3b88"></div>
									<div class="color-cube" id="color-a16ce8"></div>
									<div class="color-cube" id="color-dfe092"></div>
									<div class="color-cube" id="color-c9c00c"></div>
									<div class="color-cube" id="color-707e66"></div>
									<div class="color-cube" id="color-0954ee"></div>
									<div class="color-cube" id="color-ad6337"></div>
									<div class="color-cube" id="color-5f1107"></div>
									<div class="color-cube" id="color-c372d4"></div>
									<div class="color-cube" id="color-e17db6"></div>
									<div class="color-cube" id="color-ca2004"></div>
									<div class="color-cube" id="color-4df847"></div>
									<div class="color-cube" id="color-0c89a8"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-body body-chat"></div>
				<div class="panel-footer footer-chat">
					<?php if(isset($_SESSION["token"])){ ?>
					<div class="input-group">
						<input type="text" class="form-control chatbox" placeholder="<?php echo $lang["chat_placeholder"];?>">
						<span class="input-group-btn btn-chat">
							<button class="btn btn-primary"><?php echo $lang["post_chat"];?></button>
						</span>
					</div>
					<?php } else { ?>
					<p class="submit-required"><?php echo $lang["no_chat"];?></p>
					<?php } ?>

				</div>
			</div>
		</div>
		<div class="col-lg-3" id="song-list">
			<div class="panel panel-default panel-song-list">
				<div class="panel-heading"><?php echo $lang["playlist"];?></div>
				<div class="panel-body" id="body-song-list"></div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
<script>
	/* YOUTUBE PLAYER */
	var tag = document.createElement('script');
	tag.src = "https://www.youtube.com/iframe_api";
	var firstScriptTag = document.getElementsByTagName('script')[0];
	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	var player;
	function onYouTubeIframeAPIReady() {
		player = new YT.Player('player', {
			height: '507',
			width: '832',
			videoId: '',
			events: {
				'onReady': onPlayerReady,
				'onStateChange': onPlayerStateChange
			}
		});
	}

	var done = false;
	$(document).ready(function(){
		var roomToken = "<?php echo $roomToken;?>";
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		// Join the room
		function joinRoom(roomToken, userToken){
			return $.post("functions/join_room.php", {roomToken : roomToken, userToken : userToken});
		}
		joinRoom(roomToken, userToken).done(function(result){
			// Load the chat
			setInterval(loadChat, 2000, roomToken, result);
			// Load the history of all submitted songs in this room
			loadSongHistory(roomToken);
			setInterval(loadSongHistory, 10000, roomToken);
			//window.checkVideo = setInterval(synchronize, 5000, roomToken);
		})

		// Get the number of people in the room
		getWatchCount(roomToken);
		setInterval(getWatchCount, 30000, roomToken);
	}).on('click','.play-url', function(){
		submitLink();
	}).on('focus', '.url-box', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				submitLink();
			}
		})
	}).on('focus', '.chatbox', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				sendMessage("<?php echo $roomToken;?>", 1, 'chatbox', '');
			}
		})
	}).on('click', '.btn-chat', function(){
		sendMessage("<?php echo $roomToken;?>", 1, 'chatbox', '');
	}).on('click', '.color-cube', function(){
		var color = $(this).attr('id').substr(6,6);
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		$.post("functions/change_color.php", {userToken : userToken, color : color}).done(function(){
			$(".close").click();
		})
	}).on('click', '.toggle-song-list', function(){
		var position = ($("#song-list").css("right") == "0px")?"32%":"0px"
		$("#song-list").animate({
			'right': position
		}, 200);
	}).unload(function(){
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		$.post("functions/leave_room.php", {roomToken : "<?php echo $roomToken;?>", userToken : userToken});
	})
	function onPlayerReady(event){
		synchronize("<?php echo $roomToken;?>");
	}
	function onPlayerStateChange(event) {
		if (event.data == YT.PlayerState.ENDED) {
			$.post("functions/get_next.php", {roomToken : "<?php echo $roomToken;?>", lastPlayed : sessionStorage.getItem("currently-playing")}).done(function(data){
				var songInfo = JSON.parse(data);
				if(songInfo.link != null){
					playSong(songInfo.link, songInfo.title);
				} else {
					setTimeout(onPlayerStateChange, 2000, event);
				}
			});
		}
	}
	function synchronize(roomToken){
		/* This function synchronizes the current video for everyone */
		$.post("functions/load_current.php", {roomToken : roomToken}).done(function(data){
			var songInfo = JSON.parse(data);
			if(songInfo.link != null){
				playSong(songInfo.link, songInfo.title);
			}
		})
	}
	function playSong(id, title){
		player.loadVideoById(id);
		sessionStorage.setItem("currently-playing", id);
		$(".currently-name").empty();
		$(".currently-name").html(title);
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		if(userToken == "<?php echo $roomDetails["room_creator"];?>"){
			sendMessage("<?php echo $roomToken;?>", 4, title);
		}
	}
	function loadSongHistory(roomToken){
		// Gets the whole history of the room
		$.post("functions/get_history.php", {roomToken : roomToken}).done(function(data){
			var songList = JSON.parse(data);
			$("#body-song-list").empty();
			for(var i = 0; i < songList.length; i++){
				var message = "<p>";
				message = songList[i].videoName;
				message += "</p>";
				$("#body-song-list").append(message);
			}
		})
	}
	function submitLink(){
		// Get room token
		var roomToken = "<?php echo $roomToken;?>";

		// Get URL
		var src = $(".url-box").val();
		if(src != ''){
			// get ID of video
			var id = src.substr(32, 11);

			// Post URL into room history
			$.post("functions/post_history.php", {url : id, roomToken : roomToken});

			// Empty URL box
			$(".url-box").val('');
		}
	}
	function sendMessage(roomToken, scope, message, destination){
		if(message == 'chatbox' && scope == 1){
			var fullString = $(".chatbox").val();
			var actionToken = $(".chatbox").val().substr(0,1);
			if(actionToken == '/'){
				var action = $(".chatbox").val().substr(1).split(" ");
				if(action[0] == 'w'){
					scope = 6;
					destination = action[1];
					message = "";
					for(var i = 2; i < action.length; i++){
						message += action[i];
						if(i != action.length-1){
							message += " ";
						}
					}
					$(".chatbox").val('');
					$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, destination : destination, solveDestination : destination});
				}
			} else {
				var message = $(".chatbox").val();
				$(".chatbox").val('');
				$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, destination : destination});
			}
		} else {
			$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, destination : destination});
		}
	}
	function loadChat(data, userPower){
		var token = data;
		$.post("functions/load_chat.php", {token : token}).done(function(data){
			var messageList = JSON.parse(data);
			$(".body-chat").empty();
			for(var i = 0; i < messageList.length; i++){
				if(messageList[i].scope == 6){
					// Whispers
					if(messageList[i].destinationToken == "<?php echo $_SESSION["token"];?>"){
						var message = "<p class='whisper'>";
						message += "<span class='message-time'>"+messageList[i].timestamp+"</span> ";
						message += "<span class='message-author' style='color:"+messageList[i].authorColor+";'>";
						message += messageList[i].author;
						message += "</span>";
						message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
						message += messageList[i].content;
						message += "</p>";
					} else if(messageList[i].authorToken == "<?php echo $_SESSION["token"];?>"){
						var message = "<p class='whisper'>";
						message += "<span class='message-time'>"+messageList[i].timestamp+"</span> ";
						message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
						message += "<span class='message-author' style='color:"+messageList[i].destinationColor+";'>";
						message += messageList[i].destination;
						message += "</span> : ";
						message += messageList[i].content;
						message += "</p>";
					} else {
						var message = ""; // Clear message if whisper has nowhere to go
					}
				} else if(messageList[i].scope == 5){
					// System messages viewable by only one user
					if(messageList[i].destination == "<?php echo $_SESSION["token"];?>"){
						var message = "<p class='system-message system-alert'>";
						message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
						message += messageList[i].content;
						message += "</p>";
					} else {
						var message = ""; // Clear message
					}
				} else if(messageList[i].scope == 4){
					// System messages viewable by everyone
					var message = "<p class='system-message'>";
					message += "<span class='glyphicon glyphicon-info-sign'></span> ";
					message += messageList[i].content;
					message += "</p>";
				} else if(messageList[i].scope == 3){
					// System messages viewable by the moderators
					if(userPower == 2 || userPower == 3){
						var message = "<p class='system-message'>";
						message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
						message += messageList[i].content;
						message += "</p>";
					} else {
						var message = ""; // Clear message
					}
				} else if(messageList[i].scope == 2){
					// System messages viewable by the creator
					if(userPower == 2){
						var message = "<p class='system-message'>";
						message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
						message += messageList[i].content;
						message += "</p>";
					} else {
						var message = ""; // Clear message
					}
				} else if(messageList[i].scope == 1){
					// Chat for everyone
					var message = "<p>";
					message += "<span class='message-time'>"+messageList[i].timestamp+"</span> ";
					if(messageList[i].status == 2){
						// If author is creator
						message += "<span class='glyphicon glyphicon-star' title='<?php echo $lang["room_admin"];?>'></span> ";
					} else if(messageList[i].status == 3) {
						// If author is a moderator
						if((userPower == 2 || userPower == 3) && messageList[i].authorToken != "<?php echo $_SESSION["token"];?>"){
							// If current user is a mod or an admin, he can timeout the mod
							message += "<span class='glyphicon glyphicon-time moderation-option' title='<?php echo $lang["action_timeout"];?>' onClick=timeoutUser('"+messageList[i].authorToken+"')></span> ";
							if(userPower == 2){
								// Specific actions to the admin : ban & demote
								message += "<span class='glyphicon glyphicon-fire moderation-option' title='<?php echo $lang["action_ban"];?>' onClick=banUser('"+messageList[i].authorToken+"')></span> ";
								message += "<span class='glyphicon glyphicon-star-empty moderation-option-enabled' title='<?php echo $lang["room_mod"];?>'></span> ";
							}
						}
						else {
							// If current user has no power here
							message += "<span class='glyphicon glyphicon-star-empty' title='<?php echo $lang["room_mod"];?>'></span> ";
						}
					} else {
						// If author is a standard user
						if(userPower == 2 || userPower == 3){
							// Mod & admin actions
							message += "<span class='glyphicon glyphicon-time moderation-option' title='<?php echo $lang["action_timeout"];?>' onClick=timeoutUser('"+messageList[i].authorToken+"')></span> ";
							message += "<span class='glyphicon glyphicon-fire moderation-option' title='<?php echo $lang["action_ban"];?>' onClick=banUser('"+messageList[i].authorToken+"')></span> ";
							if(userPower == 2){
								//Admin action
								message += "<span class='glyphicon glyphicon-star-empty moderation-option' title='<?php echo $lang["action_promote"];?>' onClick=promoteUser('"+messageList[i].authorToken+"')></span> ";
							}
						}
					}
					message += "<span class='message-author' style='color:"+messageList[i].authorColor+";'>";
					message += messageList[i].author;
					message += "</span>";
					message += " : "+messageList[i].content+"<br/>";
					message += "</p>";
				}
				$(".body-chat").append(message);
			}
			$(".body-chat").scrollTop($(".body-chat")[0].scrollHeight);
		})
	}
	function timeoutUser(userToken){
		var roomToken = "<?php echo $roomToken;?>";
		$.post("functions/time_out.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
			var adminMessage = "<?php echo $lang["timeout_message_admin_first_part"];?>"+data+"<?php echo $lang["timeout_message_admin_second_part"];?>";
			sendMessage("<?php echo $roomToken;?>", 3, adminMessage);
			sendMessage("<?php echo $roomToken;?>", 5, "<?php echo $lang["timeout_message_user"];?>", userToken);
		})
	}
	function banUser(token){

	}
	function promoteUser(token){
		console.log(token);
	}
	function getWatchCount(token){
		$.post("functions/get_watch_count.php", {token : token}).done(function(data){
			$("#watch-count").empty();
			$("#watch-count").append(" "+data);
		})
	}

</script>
