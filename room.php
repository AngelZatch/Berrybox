<?php
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
				<p class="room-creator"><span class="glyphicon glyphicon-user"></span> <?php echo $roomDetails["user_pseudo"];?> | <span class="glyphicon glyphicon-play"></span> <span class="currently-name"></span></p>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="frame-play">
					<iframe src="" frameborder="0" width="100%" height="67%"></iframe>
				</div>
			</div>
			<div class="add-link">
				<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
				<button class="btn btn-primary btn-block play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
			</div>
		</div>
		<div class="col-lg-4" id="room-chat">
			<div class="panel panel-default panel-chat">
				<div class="panel-heading">
					<p class="chat-room">
						<span class="room-dj"><?php echo $roomDetails["user_pseudo"];?></span>
					</p>
					<?php if(basename($_SERVER['PHP_SELF']) != "room.php"){ ?>
					<div class="input-group">
						<input type="text" placeholder="Sumbit a link" class="form-control">
						<span class="input-group-btn"><button class="btn btn-default" value="<?php echo $roomToken;?>">Send</button></span>
					</div>
					<?php } ?>
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
		setInterval(loadChat, 3000, roomToken);
		//loadHistory(roomToken);
		loadCurrentPlay(roomToken);
		setInterval(loadCurrentPlay, 5000, roomToken);
	}).on('focus', '.chatbox', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				sendMessage("<?php echo $roomToken;?>");
			}
		})
	}).on('click', '.btn-chat', function(){
		sendMessage("<?php echo $roomToken;?>");
	}).on('click','.play-url', function(){
		// Get room token
		var roomToken = "<?php echo $roomToken;?>";

		// Get URL
		var src = $(".url-box").val();
		if(src != ''){
			var res = src.replace("watch?v=", "embed/");
			res += "?autoplay=1";

			// Load video into iframe
			playVideo(res);

			// Empty URL box
			$(".url-box").val('');

			// Post URL into room history
			$.post("functions/post_history.php", {url : id, roomToken : roomToken}).done(function(data){
				//loadHistory(roomToken);
			})
		}
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
	function loadCurrentPlay(roomToken){
		$.post("functions/load_current.php", {roomToken : roomToken}).done(function(data){
			var url = "https://youtube.com/embed/"+data+"?autoplay=1";
			if(url != sessionStorage.getItem("currently-playing")){
				playVideo(url);
			}
		})
	}
	function playVideo(res){
		$("#frame-play iframe").attr("src", res);
		// get ID of video
		var id = res.substr(30, 11);
		$.post("functions/fetch_video_info.php", {id : id}).done(function(data){
			$(".currently-name").empty();
			$(".currently-name").html(data);
		})
		sessionStorage.setItem("currently-playing", res);
	}
</script>
