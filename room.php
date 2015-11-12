<?php
session_start();
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_GET["id"];
$roomDetails = $db->query("SELECT *
							FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);

if(isset($_SESSION["token"])){
	$userDetails = $db->query("SELECT * FROM user u
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	$ppAdresss = "profile-pictures/".$userDetails["user_pp"];
} else {
	include "functions/tools.php";
	if(isset($_POST["login"])){
		session_start();

		$username = $_POST["login_name"];
		$password = $_POST["login_pwd"];

		$checkCredentials = $db->prepare("SELECT * FROM user WHERE user_pseudo=? AND user_pwd=?");
		$checkCredentials->bindParam(1, $username);
		$checkCredentials->bindParam(2, $password);
		$checkCredentials->execute();

		if($checkCredentials->rowCount() == 1){
			$credentials = $checkCredentials->fetch(PDO::FETCH_ASSOC);
			$_SESSION["username"] = $credentials["user_pseudo"];
			$_SESSION["power"] = $credentials["user_power"];
			$_SESSION["token"] = $credentials["user_token"];
			$_SESSION["lang"] = $credentials["user_lang"];
			header("Location: $_SERVER[REQUEST_URI]");
		}
	}
	if(isset($_POST["signup"])){
		$db = PDOFactory::getConnection();

		$betaKey = $_POST["beta"];
		$matchKey = $db->query("SELECT * FROM beta_keys WHERE key_value = '$betaKey' AND key_user IS NULL");
		if($matchKey->rowCount() == 1){
			$token = generateUserToken();
			$color = "000000";
			$access = "1";
			$pseudo = $_POST["login_name"];
			$power = "1";;

			try{
				$newUser = $db->prepare("INSERT INTO user(user_token, user_pseudo, user_pwd, beta_access) VALUES(:token, :pseudo, :pwd, :access)");
				$newUser->bindParam(':pseudo', $_POST["login_name"]);
				$newUser->bindParam(':pwd', $_POST["login_pwd"]);
				$newUser->bindParam(':token', $token);
				$newUser->bindParam(':access', $access);
				$newUser->execute();

				$newPref = $db->prepare("INSERT INTO user_preferences(up_user_id, up_color)
								VALUES(:token, :color)");
				$newPref->bindParam(':token', $token);
				$newPref->bindParam(':color', $color);
				$newPref->execute();

				$useKey = $db->query("UPDATE beta_keys SET key_user='$token' WHERE key_value='$betaKey'");
				header('Location: home.php?lang='.$_GET["lang"]);
				session_start();
				$_SESSION["username"] = $pseudo;
				$_SESSION["power"] = $power;
				$_SESSION["token"] = $token;
				$_SESSION["lang"] = "en";
				header("Location: $_SERVER[REQUEST_URI]");
			} catch(PDOException $e){
				echo $e->getMessage();
			}
		}
	}
}
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
		<link rel="stylesheet" href="assets/css/ekko-lightbox.min.css">
	</head>
	<body>
		<div class="col-lg-8 col-md-8" id="room-player">
			<div class="room-info">
				<div class="room-picture">
					<img src="profile-pictures/<?php echo $roomDetails["user_pp"];?>" class="profile-picture" title="<?php echo $roomDetails["user_pseudo"]." (".$lang["room_admin"].")";?>" alt="">
				</div>
				<p class="room-title"><?php echo $roomDetails["room_name"];?></p>
				<p class="room-creator"> <?php echo $roomDetails["user_pseudo"];?> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
				<div class="room-admin">
					<?php
					if(isset($_SESSION["token"])){
						if($_SESSION["token"] != $roomDetails["room_creator"]){?>
					<button class="btn btn-default btn-admin sync-on" id="btn-synchro"><span class="glyphicon glyphicon-refresh"></span> <?php echo $lang["sync-on"];?></button>
					<?php } else { ?>
					<button class="btn btn-danger btn-admin" onClick="closeRoom('<?php echo $roomToken;?>')"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo $lang["close_room"];?></button>
					<button class="btn btn-default btn-admin" onCLick="getNext(true)"><span class="glyphicon glyphicon-step-forward"></span> <?php echo $lang["skip"];?></button>
					<!--<div class="btn-group" id="dropdown-room-type">
<button class="btn btn-default btn-admin dropdown-toggle" id="room-type" data-toggle="dropdown">
<?php switch($roomDetails["room_protection"] == 1){
							case 1:?>
<span class="glyphicon glyphicon-volume-up"></span> <?php echo $lang["level_public"];?>
<?php break;
							case 2: ?>
<span class="glyphicon glyphicon-eye-open"></span> <?php echo $lang["level_protected"];?>
<?php break;
							case 3: ?>
<span class="glyphicon glyphicon-headphones"></span> <?php echo $lang["level_private"];?>
<?php break;
						} ?>
<span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-room">
<li><a class="dropdown-link"><?php echo $lang["level_public"];?></a></li>
<li><a class="dropdown-link"><?php echo $lang["level_locked"];?></a></li>
<li><a class="dropdown-link"><?php echo $lang["level_private"];?></a></li>
</ul>
</div>-->
					<?php }
					}?>
				</div>
			</div>
			<div id="currently-playing">
				<div class="modal-body" id="player"></div>
			</div>
			<div class="row under-video">
				<div class="add-link col-lg-12">
					<?php if(isset($_SESSION["token"])){ ?>
					<div class="input-group">
						<input type="text" placeholder="<?php echo $lang["youtube_message"];?>" class="form-control url-box">
						<span class="input-group-btn">
							<button class="btn btn-primary btn-block play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
						</span>
					</div>
					<?php } ?>
				</div>
				<div class="col-lg-6 mood-selectors">
					<p class="mood-question">Do you like this song?</p>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-like button-glyph" onClick="voteMood('like')">
							<span class="glyphicon glyphicon-thumbs-up"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-cry button-glyph" onClick="voteMood('cry')">
							<span class="glyphicon glyphicon-tint"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-love button-glyph" onClick="voteMood('love')">
							<span class="glyphicon glyphicon-heart"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-intense button-glyph" onClick="voteMood('intense')">
							<span class="glyphicon glyphicon-eye-open"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-sleep button-glyph" onClick="voteMood('sleep')">
							<span class="glyphicon glyphicon-bed"></span>
						</p>
					</div>
					<div class="col-lg-2">
						<p class="emotion-glyph emotion-energy button-glyph" onClick="voteMood('energy')">
							<span class="glyphicon glyphicon-flash"></span>
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-4" id="room-chat">
			<div class="panel panel-default panel-room panel-chat">
				<div class="panel-heading">
					<div class="chat-options row">
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-menu-list button-glyph">
							<span class="glyphicon glyphicon-dashboard" title="<?php echo $lang["menu"];?>"></span>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-song-list button-glyph">
							<span class="glyphicon glyphicon-list" title="<?php echo $lang["playlist"];?>"></span>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-user-list button-glyph">
							<span class="glyphicon glyphicon-user" title="<?php echo $lang["watch_count"];?>"></span><span id="watch-count"></span>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 toggle-options-list button-glyph">
							<span class="glyphicon glyphicon-cog" title="<?php echo $lang["chat_settings"];?>"></span>
						</div>
					</div>
				</div>
				<div class="panel-body" id="body-chat"></div>
				<div class="panel-footer">
					<?php if(isset($_SESSION["token"])){ ?>
					<input type="text" class="form-control chatbox" placeholder="<?php echo $lang["chat_placeholder"];?>">
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-md-3 full-panel" id="song-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-list"></span> <?php echo $lang["playlist"];?></div>
				<div class="panel-body" id="body-song-list"></div>
			</div>
		</div>
		<div class="col-lg-2 col-md-2 full-panel" id="user-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-user"></span><span id="watch-count"></span> <?php echo $lang["watch_count"];?></div>
				<div class="panel-body" id="body-user-list"></div>
			</div>
		</div>
		<div class="col-lg-2 col-md-2 full-panel" id="options-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["chat_settings"];?></div>
				<div class="panel-body" id="body-options-list">
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
		<?php if(isset($_SESSION["token"])){ ?>
		<div class="col-lg-2 col-md-3 full-panel" id="menu-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-dashboard" title=""></span> <?php echo $lang["menu"];?></div>
				<div class="panel-body" style="height: 93vh;">
					<div class="connected-user">
						<div class="menu-pp">
							<img src="<?php echo $ppAdresss;?>" alt="" style="width:inherit">
						</div>
						<p id="user-name"><?php echo $userDetails["user_pseudo"];?></p>
					</div>
					<div class="menu-options row">
						<ul class="nav nav-pills nav-stacked">
							<li><a href="profile.php?id=<?php echo $_SESSION["token"];?>&lang=<?php echo $_SESSION["lang"];?>"><span class="glyphicon glyphicon-user col-lg-2"></span> <?php echo $lang["my_profile"];?></a></li>
							<li><a href="home.php?lang=<?php echo $_SESSION["lang"];?>"><span class="glyphicon glyphicon-log-out col-lg-2"></span> <?php echo $lang["leave"];?></a></li>
							<li>
								<?php if($_SESSION["token"] == $roomDetails["room_creator"]){ ?>
								<p style="font-size:12px; padding:5px; text-align:center;"><?php echo $lang["warning_sync_admin"];?></p>
								<?php } ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php } else { ?>
		<a href="not_logged.php?lang=<?php echo $_GET["lang"];?>" id="no-credentials"></a>
		<?php } ?>
		<?php include "scripts.php";?>
		<script src="assets/js/ekko-lightbox.min.js"></script>
	</body>
</html>
<script>
	<?php if(isset($_SESSION["token"])){ ?>
	/* YOUTUBE PLAYER */
	var tag = document.createElement('script');
	tag.src = "https://www.youtube.com/iframe_api";
	var firstScriptTag = document.getElementsByTagName('script')[0];
	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	var player;
	function onYouTubeIframeAPIReady() {
		player = new YT.Player('player', {
			height: '75%',
			width: '60%',
			videoId: '',
			events: {
				'onReady': onPlayerReady,
				'onStateChange': onPlayerStateChange
			}
		});
	}

	var done = false;
	$(document).ready(function(){
		var userToken = "<?php echo $_SESSION["token"];?>";
		var roomToken = "<?php echo $roomToken;?>";
		window.roomState = "<?php echo $roomDetails["room_active"];?>";
		// Join the room
		function joinRoom(roomToken, userToken){
			return $.post("functions/join_room.php", {roomToken : roomToken, userToken : userToken});
		}
		joinRoom(roomToken, userToken).done(function(result){
			// Load the chat (every 2s)
			$("#body-chat").append("<p class='system-message'><?php echo $lang["welcome"];?></p>");
			setInterval(loadChat, 2000, roomToken, result);
			window.userPower = result;
			// Load the history of all submitted songs in this room (once, it will be refreshed if the user toggles the panel)
			loadSongHistory(roomToken, result);
			// Load all the active users in the room (once, it will be refreshed if the user toggles the panel)
			loadUsers(roomToken);
			// Set global chatHover & sync variables
			window.chatHovered = false;
			window.sync = true;
			// Check if creator is in the room
			if(userToken != "<?php echo $roomDetails["room_creator"];?>"){
				$.post("functions/check_creator.php", {roomToken : roomToken}).done(function(presence){
					if(presence == '0'){
						$("#body-chat").append("<p class='system-message system-alert'><?php echo $lang["no_admin"];?></p>");
					}
				})
			}
			// Watch the state of the user and of the room (refresh every 10s)
			setTimeout(userState, 10000, roomToken, userToken);
			setTimeout(roomState, 10000, roomToken);
		})

		// Get the number of people in the room (refresh every 30s)
		getWatchCount(roomToken);
		setInterval(getWatchCount, 30000, roomToken);
		$(window).on('beforeunload', function(event){
			var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
			$.post("functions/leave_room.php", {roomToken : "<?php echo $roomToken;?>", userToken : userToken});
		})
	}).on('click','.play-url', function(){
		submitLink();
	}).on('focus', '.url-box', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				submitLink();
			}
		})
	}).on('focus', '.chatbox', function(){
		$.post("functions/get_user_list.php", {roomToken : "<?php echo $roomToken;?>"}).done(function(data){
			var userList = JSON.parse(data);
			var autocompleteList = [];
			for(var i = 0; i < userList.length; i++){
				autocompleteList.push(userList[i].pseudo);
			}
			$(".chatbox").textcomplete([{
				match: /(^|\b)(\w{2,})$/,
				search: function(term, callback){
					callback($.map(autocompleteList, function(item){
						return item.indexOf(term) === 0 ? item : null;
					}));
				},
				replace: function(item){
					return item;
				}
			}]);
		});
		$(this).keypress(function(event){
			if(event.which == 13){
				sendMessage("<?php echo $roomToken;?>", 1, null, 'chatbox', '');
			}
		})
	}).on('click', '.btn-chat', function(){
		sendMessage("<?php echo $roomToken;?>", 1, null, 'chatbox', '');
	}).on('click', '.toggle-song-list, .toggle-menu-list, .toggle-user-list, .toggle-options-list', function(){
		var classToken = $(this).attr("class").split(' ')[4].substr(7);
		var position;
		if($("#"+classToken).css("display") == "none"){
			$("#"+classToken).toggle();
			position = "32%";
			switch(classToken){
				case "song-list":
					loadSongHistory("<?php echo $roomToken;?>", window.userPower);
					setTimeout((function(){
						$("#user-list").hide();
						$("#menu-list").hide();
						$("#options-list").hide();
					}), 200);
					$("#user-list").animate({
						right : "0px"
					}, 200);
					$("#menu-list").animate({
						right : "0px"
					}, 200);
					$("#options-list").animate({
						right : "0px"
					}, 200);
					break;

				case "user-list":
					loadUsers("<?php echo $roomToken;?>");
					setTimeout((function(){
						$("#song-list").hide();
						$("#menu-list").hide();
						$("#options-list").hide();
					}), 200);
					$("#song-list").animate({
						right : "0px"
					}, 200);
					$("#menu-list").animate({
						right : "0px"
					}, 200);
					$("#options-list").animate({
						right : "0px"
					}, 200);
					break;

				case "menu-list":
					setTimeout((function(){
						$("#song-list").hide();
						$("#user-list").hide();
						$("#options-list").hide();
					}), 200);
					$("#song-list").animate({
						right : "0px"
					}, 200);
					$("#user-list").animate({
						right : "0px"
					}, 200);
					$("#options-list").animate({
						right : "0px"
					}, 200);
					break;

				case "options-list":
					setTimeout((function(){
						$("#song-list").hide();
						$("#user-list").hide();
						$("#menu-list").hide();
					}), 200);
					$("#song-list").animate({
						right : "0px"
					}, 200);
					$("#user-list").animate({
						right : "0px"
					}, 200);
					$("#menu-list").animate({
						right : "0px"
					}, 200);
					break;
			}
		} else {
			$(this).t = setTimeout((function(){
				$("#"+classToken).hide();
			}), 200);
			position = "0px";
		}
		$("#"+classToken).animate({
			right : position
		}, 200);
	}).on('click', '.color-cube', function(){
		var color = $(this).attr('id').substr(6,6);
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		$.post("functions/change_color.php", {userToken : userToken, color : color}).done(function(){
			$(".close").click();
		})
	}).on('click', '#btn-synchro', function(){
		var $b = $(this);
		if($b.hasClass("sync-on")){
			$b.removeClass("sync-on");
			$b.empty();
			$b.addClass("sync-off");
			$b.html("<span class='glyphicon glyphicon-repeat'></span> " +"<?php echo $lang["sync-off"];?>");
			window.sync = false;
			$b.blur();
		} else {
			$b.addClass("sync-on");
			$b.empty();
			$b.removeClass("sync-off");
			$b.html("<span class='glyphicon glyphicon-refresh'></span> " +"<?php echo $lang["sync-on"];?>");
			window.sync = true;
			synchronize("<?php echo $roomToken;?>", userPower);
			$b.blur();
		}
	}).on('mouseenter', '.emotion-like', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["like"];?>");
			$(".mood-question").addClass("emotion-like");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseenter', '.emotion-cry', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["cry"];?>");
			$(".mood-question").addClass("emotion-cry");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseenter', '.emotion-love', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["love"];?>");
			$(".mood-question").addClass("emotion-love");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseenter', '.emotion-intense', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["intense"];?>");
			$(".mood-question").addClass("emotion-intense");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseenter', '.emotion-sleep', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["sleep"];?>");
			$(".mood-question").addClass("emotion-sleep");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseenter', '.emotion-energy', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["energy"];?>");
			$(".mood-question").addClass("emotion-energy");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseleave', '.emotion-like', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["mood-question"];?>");
			$(".mood-question").removeClass("emotion-like");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseleave', '.emotion-cry', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["mood-question"];?>");
			$(".mood-question").removeClass("emotion-cry");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseleave', '.emotion-love', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["mood-question"];?>");
			$(".mood-question").removeClass("emotion-love");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseleave', '.emotion-intense', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["mood-question"];?>");
			$(".mood-question").removeClass("emotion-intense");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseleave', '.emotion-sleep', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["mood-question"];?>");
			$(".mood-question").removeClass("emotion-sleep");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseleave', '.emotion-energy', function(){
		$(".mood-question").fadeOut('500', function(){
			$(".mood-question").empty();
			$(".mood-question").html("<?php echo $lang["mood-question"];?>");
			$(".mood-question").removeClass("emotion-energy");
			$(".mood-question").fadeIn('500');
		});
	}).on('mouseenter', '#body-chat', function(){
		window.chatHovered = true;
	}).on('mouseleave', '#body-chat', function(){
		window.chatHovered = false;
	})
	function onPlayerReady(event){
		sessionStorage.setItem("currently-playing", "");
		synchronize("<?php echo $roomToken;?>", userPower);
	}
	function onPlayerStateChange(event) {
		if(window.sync == true){
			if (event.data == YT.PlayerState.ENDED) {
				getNext(false);
			}
		}
		if(event.data == YT.PlayerState.PLAYING){
			var moodTimer = player.getDuration() * 1000;
			setTimeout(showMoodSelectors, moodTimer * 0.3);
			setTimeout(hideMoodSelectors, moodTimer - 10000);
		}
	}
	function getNext(skip){
		if(skip == true){
			var message = "{skip}";
			sendMessage("<?php echo $roomToken;?>", 4, 3, message);
		}
		if(userPower == 2){
			$.post("functions/get_next.php", {roomToken : "<?php echo $roomToken;?>", userPower : window.userPower, lastPlayed : sessionStorage.getItem("currently-playing")}).done(function(data){
				if(data != ""){
					var songInfo = JSON.parse(data);
					if(songInfo.link != null){
						playSong(songInfo.link, songInfo.title, 0);
					}
				} else {
					synchronize("<?php echo $roomToken;?>", userPower);
				}
			});
		} else {
			synchronize("<?php echo $roomToken;?>", userPower);
			$("#body-chat").append("<p class='system-message'><span class='glyphicon glyphicon-refresh'></span> <?php echo $lang["synchronizing"];?></p>");
		}
	}
	function userState(roomToken, userToken){
		$.post("functions/get_user_state.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
			if(data == 1){
				setTimeout(userState, 10000, roomToken, userToken);
			} else {
				$("#body-chat").append("<p class='system-message system-alert'><?php echo $lang["room_closing"];?></p>");
				setTimeout(function(){
					window.location.replace("home.php?lang=<?php echo $_GET["lang"];?>");
				}, 3000);
			}
		})
	}
	function roomState(roomToken){
		$.post("functions/get_room_state.php", {roomToken : roomToken}).done(function(data){
			window.roomState = data;
			if(data == 0){
				$(".under-video").hide('1000');
			}
			setTimeout(userState, 2000, roomToken);
		})
	}
	function synchronize(roomToken, userPower){
		/* This function synchronizes the current video for everyone */
		$.post("functions/load_current.php", {roomToken : roomToken, userPower : userPower}).done(function(data){
			var songInfo = JSON.parse(data);
			if(songInfo.link != null){
				if(songInfo.link != sessionStorage.getItem("currently-playing")){
					playSong(songInfo.link, songInfo.title, songInfo.timestart);
				} else {
					window.videoPending = setTimeout(synchronize, 3000, "<?php echo $roomToken;?>", userPower);
				}
			} else {
				window.videoPending = setTimeout(synchronize, 3000, "<?php echo $roomToken;?>", userPower);
			}
		})
	}
	function playSong(id, title, timestart){
		if(timestart != 0){
			//console.log("timestamp : "+timestart);
			var sTime = moment.utc(timestart).add(4, 's');
			//console.log("start of video fetched from DB : "+sTime);
			var sLocalTime = moment(sTime).local();
			//console.log("formatted : "+sLocalTime);
			var timeDelta = Math.round(moment().diff(sLocalTime)/1000);
			//console.log("TIME DELTA : "+timeDelta);
			player.loadVideoById(id, timeDelta);
		} else {
			player.loadVideoById(id);
		}
		sessionStorage.setItem("currently-playing", id);
		$(".currently-name").empty();
		$(".currently-name").html(title);
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		if(userToken == "<?php echo $roomDetails["room_creator"];?>" && (timestart == 0 || timeDelta <= 3)){
			var message = "{now_playing}"+title;
			sendMessage("<?php echo $roomToken;?>", 4, 2, message);
			$.post("functions/register_song.php", {id : id});
		}
	}
	function loadSongHistory(roomToken, userPower){
		if($("#song-list").css("display") != "none"){
			// Gets the whole history of the room
			$.post("functions/get_history.php", {roomToken : roomToken}).done(function(data){
				var songList = JSON.parse(data);
				$("#body-song-list").empty();
				var previousSongState = -1;
				for(var i = 0; i < songList.length; i++){
					var message = "";
					if(previousSongState != songList[i].videoStatus){
						switch(songList[i].videoStatus){
							case '0':
								var messageRank = "<p class='list-rank'><?php echo $lang["sl_upcoming"];?></p>";
								$("#body-song-list").append(messageRank);
								break;
							case '1':
								message += "<p class='list-rank'><?php echo $lang["now_playing"];?></p>";
								break;
							case '2':
								message += "<p class='list-rank'><?php echo $lang["sl_played"];?></p>";
								break;
						}
					}
					if(songList[i].videoStatus == 2){
						message += "<div class='row song-played'>";
						message += "<div class='col-lg-12'>";
					} else if(songList[i].videoStatus == 1){
						message += "<div class='row song-playing'>";
						message += "<div class='col-lg-12'>";
					} else if(songList[i].videoStatus == 3){
						message += "<div class='row song-ignored'>";
						message += "<div class='col-lg-9'>";
					} else {
						var message = "<div class='row song-upcoming'>";
						message += "<div class='col-lg-9'>";
					}
					message += "<p class='song-list-line'>";
					message += "<a href='http://www.youtube.com/watch?v="+songList[i].videoLink+"' target='_blank' title="+songList[i].videoName+">";
					message += songList[i].videoName;
					message +=  "</a>";
					message += "</p>";
					message += "</div>";
					if(userPower == 2 || userPower == 3){
						if(songList[i].videoStatus == 0){
							message += "<div class='col-lg-1'>";
							message += "<span class='glyphicon glyphicon-ban-circle button-glyph' onClick=ignoreSong("+songList[i].entry+")></span>";
							message += "</div>";
							/*message += "<div class='col-lg-1'>";
						message += "<span class='glyphicon glyphicon-arrow-up'></span>";
						message += "</div>";
						message += "<div class='col-lg-1'>";
						message += "<span class='glyphicon glyphicon-arrow-down'></span>";
						message += "</div>";*/
						} else if(songList[i].videoStatus == 3){
							message += "<div class='col-lg-1'>";
							message += "<span class='glyphicon glyphicon-leaf button-glyph' onClick=reinstateSong("+songList[i].entry+")></span>";
							message += "</div>";
						}
					}
					message += "</div>";
					previousSongState = songList[i].videoStatus;
					$("#body-song-list").append(message);
				}
			})
			setTimeout(loadSongHistory, 8000, roomToken, userPower);
		}
	}
	function ignoreSong(id){
		$.post("functions/ignore_song.php", {roomToken : "<?php echo $roomToken;?>", id : id}).done(function(data){
			var message = "{song_ignored}"+data;
			sendMessage("<?php echo $roomToken;?>", 4, 5, message);
		})
	}
	function reinstateSong(id){
		$.post("functions/reinstate_song.php", {roomToken : "<?php echo $roomToken;?>", id : id}).done(function(data){
			var message = "{song_reinstated}"+data;
			sendMessage("<?php echo $roomToken;?>", 4, 6, message);
		})
	}
	function showMoodSelectors(){
		$(".add-link").animate({
			'width': '50%'
		}, 300);
		$(".mood-selectors").show('900');
	}
	function voteMood(mood){
		$.post("functions/select_mood.php", {mood : mood, id : sessionStorage.getItem("currently-playing")}).done(function(){
			hideMoodSelectors();
		})
	}
	function hideMoodSelectors(){
		$(".mood-selectors").hide('900');
		$(".add-link").delay('900').animate({
			'width': '100%'
		}, 400);
	}
	function loadUsers(roomToken){
		if($("#user-list").css("display") != "none"){
			$.post("functions/get_user_list.php", {roomToken : roomToken}).done(function(data){
				var userList = JSON.parse(data);
				$("#body-user-list").empty();
				var previousRank = -1;
				for(var i = 0; i < userList.length; i++){
					var message = "";
					if(previousRank != userList[i].power){
						switch(userList[i].power){
							case '1':
								message += "<p class='list-rank'><?php echo $lang["ul_users"];?></p>";
								break;
							case '2':
								message += "<p class='list-rank'><?php echo $lang["ul_admin"];?></p>";
								break;
							case '3':
								message += "<p class='list-rank'><?php echo $lang["ul_mods"];?></p>";
								break;
						}
					}
					message += "<p>";
					message += userList[i].pseudo;
					message += "</p>";
					previousRank = userList[i].power;
					$("#body-user-list").append(message);
				}
			})
			setTimeout(loadSongHistory, 8000, roomToken);
		}
	}
	function submitLink(){
		if(window.roomState == 1){
			// Get room token
			var roomToken = "<?php echo $roomToken;?>";

			// Get URL
			var src = $(".url-box").val();
			if(src != ''){
				// get ID of video
				var id = src.substr(32, 11);

				// Post URL into room history
				$.post("functions/post_history.php", {url : id, roomToken : roomToken}).done(function(code){
					switch(code){
						case '1': // success code
							$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["song_submit_success"];?></p>");
							break;

						case '2': //db error code
							$("#body-chat").append("<p class='system-message system-warning'></span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["db_error"];?></p>");
							break;

						case '3': // Invalid link code
							$("#body-chat").append("<p class='system-message system-alert'><span class='glyphicon glyphicon-exclamation-sign'></span> <?php echo $lang["invalid_link"];?></p>");
							break;
					}
				});

				// Empty URL box
				$(".url-box").val('');
			}
		}
	}
	function sendMessage(roomToken, scope, type, message, destination){
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
			$.post("functions/post_chat.php", {message : message, token : roomToken, scope : scope, type: type, destination : destination});
		}
	}
	function loadChat(roomToken, userPower){
		var lang = "<?php echo $_GET["lang"];?>";
		if(!window.lastID){
			window.lastID = 0;
		}
		$.post("functions/load_chat.php", {token : roomToken, lang : lang, lastMessageID : window.lastID}).done(function(data){
			var messageList = JSON.parse(data);
			for(var i = 0; i < messageList.length; i++){
				var mTime = moment.utc(messageList[i].timestamp);
				var messageTime = moment(mTime).local().format("HH:mm");
				if(messageList[i].scope == 6){
					// Whispers
					if(messageList[i].destinationToken == "<?php echo $_SESSION["token"];?>"){
						var message = "<p class='whisper'>";
						message += "<span class='message-time'>"+messageTime+"</span> ";
						message += "<a href='user.php?id="+messageList[i].authorToken+"&lang=<?php echo $_GET["lang"];?>' style='text-decoration:none;'><span class='message-author' style='color:"+messageList[i].authorColor+";'>";
						message += messageList[i].author;
						message += "</span></a>";
						message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
						message += messageList[i].content;
						message += "</p>";
					} else if(messageList[i].authorToken == "<?php echo $_SESSION["token"];?>"){
						var message = "<p class='whisper'>";
						message += "<span class='message-time'>"+messageTime+"</span> ";
						message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
						message += "<a href='user.php?id="+messageList[i].destinationToken+"&lang=<?php echo $_GET["lang"];?>' style='text-decoration:none;'><span class='message-author' style='color:"+messageList[i].destinationColor+";'>";
						message += messageList[i].destination;
						message += "</span></a> : ";
						message += messageList[i].content;
						message += "</p>";
					} else {
						var message = ""; // Clear message if whisper has nowhere to go
					}
				} else if(messageList[i].scope == 5){
					// System messages viewable by only one user
					if(messageList[i].destinationToken == "<?php echo $_SESSION["token"];?>"){
						var message = "<p class='system-message system-alert'>";
						message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
						message += messageList[i].content;
						message += "</p>";
					} else {
						var message = ""; // Clear message
					}
				} else if(messageList[i].scope == 4){
					// System messages viewable by everyone
					var message = "<p class='system-message";
					switch(messageList[i].subType){
						case '1':
							message += "'><span class='glyphicon glyphicon-info-sign'></span> ";
							break;
						case '2':
							message += " sm-type-play'><span class='glyphicon glyphicon-play'></span> ";
							break;
						case '3':
							message += " sm-type-skip'><span class='glyphicon glyphicon-step-forward'></span> ";
							if(userPower != 2){
								synchronize("<?php echo $roomToken;?>", userPower);
							}
							break;
						case '4':
							message += " sm-type-close'><span class='glyphicon glyphicon-remove-circle'></span> ";
							break;
						case '5':
							message += " sm-type-ignore'><span class='glyphicon glyphicon-info-sign'></span> ";
							break;
						case '6':
							message += " sm-type-reinstate'><span class='glyphicon glyphicon-info-sign'></span> ";
							break;
					}
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
					var message = "<p class='standard-message'>";
					message += "<span class='message-time'>"+messageTime+"</span> ";
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
								message += "<span class='glyphicon glyphicon-star-empty moderation-option-enabled' title='<?php echo $lang["action_demote"];?>' onClick=demoteUser('"+messageList[i].authorToken+"')></span> ";
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
					message += "<a href='user.php?id="+messageList[i].authorToken+"&lang=<?php echo $_GET["lang"];?>' style='text-decoration:none;'><span class='message-author' style='color:"+messageList[i].authorColor+";'>";
					message += messageList[i].author;
					message += "</span></a>";
					message += " : "+messageList[i].content+"<br/>";
					message += "</p>";
				}
				window.lastID = messageList[i].id;
				$("#body-chat").append(message);
			}
		})
		if(!window.chatHovered){
			$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
		}
	}
	function timeoutUser(userToken){
		var roomToken = "<?php echo $roomToken;?>";
		$.post("functions/time_out.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
			var adminMessage = "<?php echo $lang["timeout_message_admin_first_part"];?>"+data+"<?php echo $lang["timeout_message_admin_second_part"];?>";
			sendMessage("<?php echo $roomToken;?>", 3, null, adminMessage);
			sendMessage("<?php echo $roomToken;?>", 5, null, "<?php echo $lang["timeout_message_user"];?>", userToken);
		})
	}
	function banUser(userToken){

	}
	function promoteUser(userToken){
		$.post("functions/promote_user.php", {roomToken : "<?php echo $roomToken;?>", adminToken : "<?php echo $_SESSION["token"];?>", userToken : userToken}).done(function(data){
			var message = "{user_promoted}"+data;
			// System message to everyone to alert the new mod
			sendMessage("<?php echo $roomToken;?>", 4, 1, message);
			// System message to the new mod only
			sendMessage("<?php echo $roomToken;?>", 5, null, "{you_promoted}", userToken);
		})
	}
	function demoteUser(userToken){
		$.post("functions/demote_user.php", {roomToken : "<?php echo $roomToken;?>", adminToken : "<?php echo $_SESSION["token"];?>", userToken : userToken}).done(function(data){
			var message = "{user_demoted}"+data;
			// System message to everyone to alert of the demote
			sendMessage("<?php echo $roomToken;?>", 4, 1, message);
			// System message to the affected user only
			sendMessage("<?php echo $roomToken;?>", 5, null, "{you_demoted}", userToken);
		});
	}
	function getWatchCount(roomToken){
		$.post("functions/get_watch_count.php", {token : roomToken}).done(function(data){
			$("#watch-count").empty();
			$("#watch-count").append(" "+data);
		})
	}
	function closeRoom(roomToken){
		sendMessage(roomToken, 4, 4, "{close_room_5}");
		$.post("functions/close_room.php", {roomToken : roomToken});
	}
	<?php } else { ?>
	$("#no-credentials").ekkoLightbox({
		onNavigate: false
	});
	$("#no-credentials").click();
	<?php } ?>
</script>
