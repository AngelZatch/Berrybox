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
			<a href="home.php?lang=<?php echo $_GET["lang"];?>" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <?php echo $lang["back"];?></a>
			<div class="room-info">
				<p class="room-title"><?php echo $roomDetails["room_name"];?></p>
				<p class="room-creator"><span class="glyphicon glyphicon-user" title="<?php echo $lang["room_admin"];?>"></span> <?php echo $roomDetails["user_pseudo"];?> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="frame-play">
					<iframe src="" frameborder="0" width="100%" height="67%"></iframe>
				</div>
			</div>
			<div class="add-link">
				<?php if(isset($_SESSION["token"])){ ?>
				<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
				<button class="btn btn-primary btn-block play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
				<?php } else { ?>
				<p id="submit-required"><?php echo $lang["no_submit"];?></p>
				<?php } ?>
			</div>
		</div>
		<div class="col-lg-4" id="room-chat">
			<div class="panel panel-default panel-chat">
				<div class="panel-heading">
					<p class="chat-room">
						<span class="room-dj"><?php echo $roomDetails["user_pseudo"];?></span>
					</p>
					<div class="chat-options row">
						<div class="col-lg-6">
							<span class="glyphicon glyphicon-user" title="<?php echo $lang["watch_count"];?>"></span><span id="watch-count"></span>
						</div>
						<div class="col-lg-6">
							<div data-toggle="popover-x" data-target="#popover-chat-settings" data-placement="bottom" style="cursor:pointer;"><span class="glyphicon glyphicon-cog" title="<?php echo $lang["chat_settings"];?>"></span> <?php echo $lang["chat_settings"];?></div>
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
					<div class="input-group">
						<input type="text" class="form-control chatbox" placeholder="<?php echo $lang["chat_placeholder"];?>">
						<span class="input-group-btn btn-chat">
							<button class="btn btn-primary"><?php echo $lang["post_chat"];?></button>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
	</body>
</html>
<script>
	$(document).ready(function(){
		var roomToken = "<?php echo $roomToken;?>";
		var userToken = "<?php echo $_SESSION["token"];?>";

		// Join the room
		joinRoom(roomToken, userToken);

		// Load the chat
		setInterval(loadChat, 3000, roomToken);

		// Load the history of all submitted songs in this room
		//loadHistory(roomToken);

		// Loading the last submitted video
		loadCurrentPlay(roomToken);
		window.checkVideo = setInterval(loadCurrentPlay, 5000, roomToken);

		// Get the number of people in the room
		getWatchCount(roomToken);
		setInterval(getWatchCount, 15000, roomToken);
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
				sendMessage("<?php echo $roomToken;?>");
			}
		})
	}).on('click', '.btn-chat', function(){
		sendMessage("<?php echo $roomToken;?>");
	}).on('click', '.color-cube', function(){
		var color = $(this).attr('id').substr(6,6);
		var userToken = "<?php echo $_SESSION["token"];?>";
		$.post("functions/change_color.php", {userToken : userToken, color : color}).done(function(){
			$(".close").click();
		})
	})/*
	function loadHistory(roomToken){
		$.post("functions/fetch_history.php", {roomToken : roomToken}).done(function(data){
			var historySongs = JSON.parse(data);
			var listOfSongs = "<ul class='list-group'>";
			for(var i = 0; i < historySongs.length; i++){
				listOfSongs += "<li class='list-group-item'>";
				listOfSongs += historySongs[i].link;
				listOfSongs += "</li>";
			}
			listOfSongs += "</ul>";
			$(".room-history").empty();
			$(".room-history").append(listOfSongs);
		})
	}*/
	function joinRoom(roomToken, userToken){
		$.post("functions/join_room.php", {roomToken : roomToken, userToken : userToken});
	}
	function leaveRoom(roomToken, userToken){
		$.post("functions/leave_room.php", {roomToken : roomToken, userToken : userToken});
	}
	function loadCurrentPlay(roomToken){
		$.post("functions/load_current.php", {roomToken : roomToken}).done(function(data){
			if(data != null){
				var url = "https://www.youtube.com/embed/"+data+"?autoplay=1";
				console.log("Checking video with "+url);
				if(url != sessionStorage.getItem("currently-playing")){
					playVideo(url);
					$.post("functions/fetch_video_info.php", {id : data}).done(function(data){
						$(".currently-name").empty();
						$(".currently-name").html(data);
					})
					console.log("Different token. Playing new video : "+data);
				} else {
					console.log("Same token.");
				}
			}
		})
	}
	function submitLink(){
		// Get room token
		var roomToken = "<?php echo $roomToken;?>";

		// Get URL
		var src = $(".url-box").val();
		if(src != ''){
			clearInterval(window.checkVideo);
			var res = src.replace("watch?v=", "embed/");
			res += "?autoplay=1";

			// Load video into iframe
			var id = playVideo(res);

			// get ID of video
			var id = res.substr(30, 11);
			$.post("functions/fetch_video_info.php", {id : id}).done(function(data){
				$(".currently-name").empty();
				$(".currently-name").html(data);
			})

			// Empty URL box
			$(".url-box").val('');

			// Post URL into room history
			$.post("functions/post_history.php", {url : id, roomToken : roomToken}).done(function(data){
				window.checkVideo = setInterval(loadCurrentPlay, 5000, roomToken);
			})
		}
	}
	function playVideo(res){
		sessionStorage.setItem("currently-playing", res);
		console.log("Setting storage : "+res);
		$("#frame-play iframe").attr("src", res);
	}
	function sendMessage(data){
		var message = $(".chatbox").val();
		var token = data;
		$(".chatbox").val('');
		$.post("functions/post_chat.php", {message : message, token : token}).done(function(data){
			loadChat(token);
		})
	}
	function loadChat(data){
		var token = data;
		$.post("functions/load_chat.php", {token : token}).done(function(data){
			var messageList = JSON.parse(data);
			$(".panel-body").empty();
			for(var i = 0; i < messageList.length; i++){
				var message = "<p>";
				message += "<span class='message-time'>"+messageList[i].timestamp+"</span> ";
				if(messageList[i].status == 2){
					message += "<span class='glyphicon glyphicon-star' title='<?php echo $lang["room_admin"];?>'></span>";
				} else if(messageList[i].status == 3) {
					message += "<span class='glyphicon glyphicon-empty-star' title='<?php echo $lang["room_mod"];?>'></span>";
				}
				message += "<span class='message-author' style='color:"+messageList[i].authorColor+";'>";
				message += messageList[i].author;
				message += "</span>";
				message += " : "+messageList[i].content+"<br/>";
				message += "</p>";
				$(".body-chat").append(message);
				$(".body-chat").scrollTop($(".body-chat")[0].scrollHeight);
			}
		})
	}
	function getWatchCount(token){
		$.post("functions/get_watch_count.php", {token : token}).done(function(data){
			$("#watch-count").empty();
			$("#watch-count").append(" "+data);
		})
	}

</script>
