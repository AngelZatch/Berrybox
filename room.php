<?php
session_start();
include "functions/db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_GET["id"];
$roomDetails = $db->query("SELECT *
							FROM rooms r
							JOIN user u ON r.room_creator = u.user_token
							WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);
$creatorStats = $db->query("SELECT *
							FROM user_stats us
							WHERE user_token = '$roomDetails[room_creator]'")->fetch(PDO::FETCH_ASSOC);
$colorList = $db->query("SELECT * FROM name_colors WHERE color_status = 0");
$queryTypes = $db->query("SELECT * FROM room_types");

if(isset($_SESSION["token"])){
	$userDetails = $db->query("SELECT * FROM user u
							JOIN user_preferences up ON up.up_user_id = u.user_token
							WHERE user_token='$_SESSION[token]'")->fetch(PDO::FETCH_ASSOC);
	if($userDetails["up_theme"] == "1"){ // userDetails only for this page. On all the other rooms, it's userSettings
		$theme = "dark";
	} else {
		$theme = "light";
	}
	$ppAdresss = "profile-pictures/".$userDetails["user_pp"];
	$userLang = $userDetails["user_lang"];
	if($userLang == ""){
		$userLang = "en";
	}
	include_once "languages/lang.".$userLang.".php";
} else {
	include "functions/tools.php";
	if(isset($_POST["login"])){
		if(!isset($_SESSION)){
			session_start();
		}

		$username = $_POST["username"];
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
		$token = generateReference(6);
		$colorID = rand(1,20);
		$color = $db->query("SELECT color_value FROM name_colors WHERE number = $colorID")->fetch(PDO::FETCH_ASSOC);
		$pseudo = $_POST["username"];
		$power = "1";;

		try{
			$db->beginTransaction();
			$newUser = $db->prepare("INSERT INTO user(user_token, user_pseudo, user_pwd) VALUES(:token, :pseudo, :pwd)");
			$newUser->bindParam(':pseudo', $_POST["username"]);
			$newUser->bindParam(':pwd', $_POST["login_pwd"]);
			$newUser->bindParam(':token', $token);
			$newUser->execute();

			$newPref = $db->prepare("INSERT INTO user_preferences(up_user_id, up_color)
								VALUES(:token, :color)");
			$newPref->bindParam(':token', $token);
			$newPref->bindParam(':color', $color["color_value"]);
			$newPref->execute();

			$newStats = $db->prepare("INSERT INTO user_stats(user_token) VALUES(:token)");
			$newStats->bindParam(':token', $token);
			$newStats->execute();

			$db->commit();
			header('Location: home');
			session_start();
			$_SESSION["username"] = $pseudo;
			$_SESSION["power"] = $power;
			$_SESSION["token"] = $token;
			$_SESSION["lang"] = "en";
			header("Location: $_SERVER[REQUEST_URI]");
		} catch(PDOException $e){
			$db->rollBack();
			echo $e->getMessage();
		}
	}
}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $roomDetails["room_name"];?> | <?php echo $roomDetails["user_pseudo"];?> | Berrybox</title>
		<base href="../">
		<?php include "styles.php";
		if(isset($_SESSION["token"])){ ?>
		<link rel="stylesheet" href="assets/css/<?php echo $theme;?>-theme.css">
		<?php } else { ?>
		<link rel="stylesheet" href="assets/css/light-theme.css">
		<?php } ?>
		<link rel="stylesheet" href="assets/css/ekko-lightbox.min.css">
	</head>
	<body>
		<div class="col-lg-8 col-md-8" id="room-player">
			<div class="room-info">
				<div class="room-picture">
					<img src="profile-pictures/<?php echo $roomDetails["user_pp"];?>" class="profile-picture" title="<?php echo $roomDetails["user_pseudo"]." (".$lang["room_admin"].")";?>" alt="">
				</div>
				<p id="room-title"><?php echo $roomDetails["room_name"];?></p>
				<p> <a href="user/<?php echo $roomDetails["user_pseudo"];?>" target="_blank"><?php echo $roomDetails["user_pseudo"];?></a> | <span class="glyphicon glyphicon-play" title="<?php echo $lang["now_playing"];?>"></span> <span class="currently-name"></span></p>
				<div class="room-admin">
					<?php
					if(isset($_SESSION["token"])){
						if($_SESSION["token"] != $roomDetails["room_creator"]){?>
					<div class="room-buttons col-lg-2">
						<button class="btn btn-default btn-admin sync-on" id="btn-synchro"><span class="glyphicon glyphicon-refresh"></span> <?php echo $lang["sync-on"];?></button>
					</div>
					<?php } else { ?>
					<div class="room-buttons col-lg-2">
						<button class="btn btn-default btn-admin" onClick="getNext(true)"><span class="glyphicon glyphicon-step-forward"></span> <?php echo $lang["skip"];?></button>
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
					</div>
					<?php }
					}?>
					<div class="room-quick-messages col-lg-8"></div>
					<div class="creator-stats col-lg-2">
						<span class="glyphicon glyphicon-eye-open" title="<?php echo $lang["total_views"];?>"></span> <?php echo $creatorStats["stat_visitors"];?>
						<!--<span class="glyphicon glyphicon-heart"></span> <?php echo $creatorStats["stat_followers"];?>-->
					</div>
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
							<button class="btn btn-primary play-url" data-toggle="modal"><?php echo $lang["submit_link"];?></button>
						</span>
					</div>
					<?php } ?>
				</div>
				<div class="col-lg-6 mood-selectors">
					<p class="mood-question"><?php echo $lang["mood-question"];?></p>
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
			<div class="panel panel-default panel-room">
				<div class="panel-heading">
					<div class="chat-options row">
						<div class="col-lg-12 room-brand"><a href="home">Berrybox</a></div>
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
				<div class="panel-body panel-section">
					<input type="text" class="form-control" id="playlist-filter" placeholder="Filter the playlist">
				</div>
				<div class="panel-body full-panel-body" id="body-song-list">
				</div>
			</div>
		</div>
		<div class="col-lg-2 col-md-2 full-panel" id="user-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-user"></span><span id="watch-count"></span> <?php echo $lang["watch_count"];?></div>
				<div class="panel-body full-panel-body" id="body-user-list"></div>
			</div>
		</div>
		<div class="col-lg-3 col-md-3 full-panel" id="options-list">
			<div class="panel panel-default panel-room panel-list">
				<div class="panel-heading"><span class="glyphicon glyphicon-cog"></span> <?php echo $lang["chat_settings"];?></div>
				<div class="panel-body" id="body-options-list">
					<div id="colors" class="room-option">
						<p><?php echo $lang["color_pick"];?></p>
						<?php while($color = $colorList->fetch(PDO::FETCH_ASSOC)){
	$colorValue = $color["color_value"];
	if(strcasecmp($colorValue,$userDetails["up_color"]) == 0){?>
						<div class="color-cube cube-selected" id="color-<?php echo $colorValue;?>" style="background-color:#<?php echo $colorValue;?>"></div>
						<?php } else { ?>
						<div class="color-cube" id="color-<?php echo $colorValue;?>" style="background-color:#<?php echo $colorValue;?>"></div>
						<?php }
}?>
					</div>
					<div class="room-option">
						<div class="option-title"><?php echo $lang["user_theme"];?>
							<span style="float:right;">
								<input type="checkbox" class="user-option-toggle" name="toggle-theme" <?php echo($userDetails["up_theme"]=='0')?'checked':'unchecked';?>>
							</span>
						</div>
						<span class="tip"><?php echo $lang["theme_tip"];?></span>
					</div>
					<?php if($_SESSION["token"] == $roomDetails["room_creator"]){ ?>
					<div class="room-option">
						<div class="option-title"><?php echo $lang["play_type"];?>
							<span style="float: right;">
								<input type="checkbox" class="admin-option-toggle" name="toggle-autoplay" <?php echo($roomDetails["room_play_type"]=='1')?'unchecked':'checked';?>>
							</span>
						</div>
						<span class="tip"><?php echo $lang["play_type_tip"];?></span>
					</div>
					<div class="room-option">
						<div class="option-title"><?php echo $lang["submit_type"];?>
							<span style="float: right;">
								<input type="checkbox" class="admin-option-toggle" name="toggle-submit" <?php echo($roomDetails["room_submission_rights"]=='1')?'checked':'unchecked';?>>
							</span>
						</div>
						<span class="tip"><?php echo $lang["submit_type_tip"];?></span>
					</div>
					<div class="room-option">
						<span class="option-title"><?php echo $lang["room_params"];?></span><br>
						<span class="tip"><?php echo $lang["room_params_tip"];?></span>
						<form class="form-horizontal">
							<div class="form-group">
								<label for="speakLang" class="col-lg-4 control-label"><?php echo $lang["speak_lang"];?></label>
								<div class="col-lg-8">
									<select name="speakLang" id="" class="form-control">
										<option value="en" <?php if($roomDetails["room_lang"]=="en") echo "selected='selected'";?>>English</option>
										<option value="fr" <?php if($roomDetails["room_lang"]=="fr") echo "selected='selected'";?>>Français</option>
										<option value="jp" <?php if($roomDetails["room_lang"]=="jp") echo "selected='selected'";?>>日本語</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="roomType" class="col-sm-4 control-label"><?php echo $lang["room_type"];?></label>
								<div class="col-lg-8">
									<select name="roomType" id="" class="form-control">
										<?php while($type = $queryTypes->fetch(PDO::FETCH_ASSOC)) {
	if($type["id"] == $roomDetails["room_type"]){?>
										<option value="<?php echo $type["id"];?>" selected="selected"><?php echo $lang[$type["type"]];?></option>
										<?php } else { ?>
										<option value="<?php echo $type["id"];?>"><?php echo $lang[$type["type"]];?></option>
										<?php } } ?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="description" class="col-sm-4 control-label"><?php echo $lang["description_limit"];?></label>
								<div class="col-lg-8">
									<textarea name="description" id="description" cols="30" rows="5" class="form-control"><?php echo $roomDetails["room_description"];?></textarea>
								</div>
							</div>
						</form>
						<button class="btn btn-primary btn-block" id="save-room-button" onClick="saveRoomChanges('<?php echo $roomToken;?>')"><?php echo $lang["save_changes"];?></button>
					</div>
					<span class="option-title"><?php echo $lang["close_room"];?></span><br>
					<span class="tip"><?php echo $lang["close_room_tip"];?></span>
					<button class="btn btn-danger btn-admin btn-block" onClick="closeRoom('<?php echo $roomToken;?>')"><span class="glyphicon glyphicon-remove-circle"></span> <?php echo $lang["close_room"];?></button>
					<?php } ?>
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
							<li><a href="profile/settings"><span class="glyphicon glyphicon-user col-lg-2"></span> <?php echo $lang["my_profile"];?></a></li>
							<li><a href="home"><span class="glyphicon glyphicon-log-out col-lg-2"></span> <?php echo $lang["leave"];?></a></li>
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
		<a href="not_logged.php?lang=en" id="no-credentials"></a>
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
			// Get power of the user
			window.userPower = result;
			// Load the chat
			if(window.userPower == 2){
				$("#body-chat").append("<p class='system-message'><?php echo $lang["welcome_admin"];?></p>");
			} else {
				$("#body-chat").append("<p class='system-message'><?php echo $lang["welcome"];?></p>");
			}
			loadChat(roomToken, result);
			// Load the history of all submitted songs in this room (once, it will be refreshed if the user toggles the panel)
			loadSongHistory(roomToken, result);
			// Load all the active users in the room (once, it will be refreshed if the user toggles the panel)
			loadUsers(roomToken);
			// State of the room
			watchRoom(roomToken);
			// Set global chatHover & sync variables
			window.chatHovered = false;
			window.sync = true;
			// Check if creator
			if(userToken != "<?php echo $roomDetails["room_creator"];?>"){
				// If user is not the creator, check presence of the creator
				$.post("functions/check_creator.php", {roomToken : roomToken}).done(function(presence){
					if(presence == '0'){
						$("#body-chat").append("<p class='system-message system-alert'><?php echo $lang["no_admin"];?></p>");
					}
				})
			} else {
				// If user is the creator, then start autoplay
				if("<?php echo $roomDetails["room_play_type"];?>" == 1){
					window.autoplay = true;
				} else {
					window.autoplay = false;
				}
			}
			// Watch the state of the user and of the room (refresh every 10s)
			setTimeout(userState, 10000, roomToken, userToken);
			// Keep PHP session alive (every 30 mins)
			setInterval(function(){$.post("functions/refresh_session.php");},1800000);
		})
		// Get the number of people in the room (refresh every 30s)
		getWatchCount(roomToken);
		setInterval(getWatchCount, 30000, roomToken);
		$(window).on('beforeunload', function(event){
			var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
			sessionStorage.removeItem("currently-playing");
			$.post("functions/leave_room.php", {roomToken : "<?php echo $roomToken;?>", userToken : userToken});
		})
		$(":regex(name,toggle-autoplay)").bootstrapSwitch({
			size: 'small',
			onText: '<i class="glyphicon glyphicon-hourglass"></i> <?php echo $lang["manual_play"];?>',
			offText: '<i class="glyphicon glyphicon-play-circle"></i> <?php echo $lang["auto_play"];?>',
			onColor: 'info',
			offColor: 'default',
			onSwitchChange: function(){
				var state = (window.autoplay)?'1':'0';
				$.post("functions/toggle_autoplay.php", {roomToken : "<?php echo $roomToken;?>", state : state}).done(function(data){
					if(data == 0){
						sendMessage("<?php echo $roomToken;?>", 4, 1, "{auto-off}");
						window.autoplay = false;
					} else{
						sendMessage("<?php echo $roomToken;?>", 4, 1, "{auto-on}");
						window.autoplay = true;
					}
				})
			}
		});
		$(":regex(name,toggle-submit)").bootstrapSwitch({
			size: 'small',
			onText: '<i class="glyphicon glyphicon-ok-sign"></i> <?php echo $lang["submit_all"];?>',
			offText: '<i class="glyphicon glyphicon-ok-circle"></i> <?php echo $lang["submit_mod"];?>',
			onColor: 'success',
			offColor: 'warning',
			onSwitchChange: function(){
				var state = "<?php echo $roomDetails["room_submission_rights"];?>";
				/*console.log(state);*/
				$.post("functions/toggle_submission_rights.php", {roomToken : "<?php echo $roomToken;?>", state : state}).done(function(data){
					if(data == 0){
						window.submission = false;
						sendMessage("<?php echo $roomToken;?>", 4, 1, "{submission_mod}");
					} else {
						window.submission = true;
						sendMessage("<?php echo $roomToken;?>", 4, 1, "{submission_all}");
					}
				})
			}
		});
		$(":regex(name,toggle-theme)").bootstrapSwitch({
			size: 'small',
			onText: '<?php echo $lang["light"];?>',
			offText: '<?php echo $lang["dark"];?>',
			onColor: 'light',
			offColor: 'dark',
			onSwitchChange: function(){
				var state = "<?php echo $userDetails["up_theme"];?>";
				$.post("functions/toggle_theme.php", {userToken : "<?php echo $_SESSION["token"];?>", state : state}).done(function(data){
					location.reload();
				})
			}
		})
		// Dynamic filtering of the playlist
		$('#playlist-filter').on('keyup', function(){
			var $rows = $('.playlist-entry');
			var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
			$rows.show().filter(function(){
				var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
				return !~text.indexOf(val);
			}).hide();
		});
	}).on('click','.play-url', function(){
		submitLink();
	}).on('focus', '.url-box', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				submitLink();
			}
		})
	}).on('click', '.send-info', function(){
		fillInfo();
	}).on('click', '.cancel-info', function(){
		var id = $(this).attr("id").substr(12);
		$("#warning-"+id).remove();
	}).on('focus', '.info-box', function(){
		$(this).keypress(function(event){
			if(event.which == 13){
				fillInfo();
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
			position = "32.5%";
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
		var cube = $(this);
		var color = $(this).attr('id').substr(6,6);
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		$.post("functions/change_color.php", {userToken : userToken, color : color}).done(function(){
			$(".close").click();
			$(".color-cube").removeClass("cube-selected");
			cube.addClass("cube-selected");
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
	}).on('mouseenter', '#room-title', function(){
		if(userPower == 2){
			$(this).append(" <span class='glyphicon glyphicon-edit button-glyph' id='edit-title-button'></span>");
		}
	}).on('mouseleave', '#room-title', function(){
		$("#edit-title-button").remove();
	}).on('click', '#edit-title-button', function(){
		var title = $("#room-title").text();
		$("#room-title").replaceWith("<input type='text' class='form-control' style='width:61vw;margin-bottom:5px' id='edit-title-input' value='"+title+"'>");
	}).on('blur', '#edit-title-input', function(){
		var newTitle = $(this).val();
		$.post("functions/retitle_room.php", {title : newTitle, roomToken : "<?php echo $roomToken;?>"});
		$(this).replaceWith("<p id='room-title'>"+newTitle+"</p>");
	}).on('click', '.author-linkback', function(){
		var user = $(this).text();
		var currentLine = $(this).parents("p");
		$(".user-card").remove();
		$.post("functions/fetch_user_card.php", {user : user}).done(function(data){
			var details = JSON.parse(data);
			var userCard = "<div class='user-card'>";
			userCard += "<div class='user-card-info'>";
			userCard += "<div class='medium-pp'>";
			userCard += "<img src='"+details.user_pp+"' alt='' style='width:inherit'>";
			userCard += "</div>";
			userCard += "<p id='user-card-name'>"+details.user_pseudo+"<span class='glyphicon glyphicon-remove button-glyph' id='user-card-close'></span></p>";
			userCard += "<div class='row user-card-stats'>";
			userCard += "<div class='col-lg-3'><span class='glyphicon glyphicon-eye-open' title='<?php echo $lang["total_views"];?>'></span> "+details.visitors+"</div>";
			userCard += "<div class='col-lg-3'><span class='glyphicon glyphicon-plus' title='<?php echo $lang["rooms_created"];?>'></span> "+details.rooms+"</div>";
			userCard += "<div class='col-lg-3'><span class='glyphicon glyphicon-music' title='<?php echo $lang["songs_submitted"];?>'></span> "+details.songs+"</div>";
			userCard += "</div>"; // user-card-stats
			userCard += "</div>"; // user-card-info
			userCard += "<div class='user-card-actions'>";
			userCard += "<button class='btn btn-primary whisper-action'><?php echo $lang["whisper"];?></button>";
			userCard += "</div>"; // user-card-actions
			userCard += "</div>"; //user-card
			currentLine.after(userCard);
			$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
		})
	}).on('click', '#user-card-close', function(){
		$(".user-card").remove();
	}).on('click', '.whisper-action', function(){
		var user = $("#user-card-name").text();
		$(".chatbox").val("/w "+user+" ");
		$(".chatbox").focus();
	})
	function onPlayerReady(event){
		sessionStorage.setItem("currently-playing", "");
		synchronize("<?php echo $roomToken;?>", userPower);
	}
	function onPlayerStateChange(event) {
		/*console.log(window.autoplay);*/
		if(window.sync == true && window.autoplay != false){
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
						playSong(songInfo.index, songInfo.link, songInfo.title, 0);
					}
				} else {
					synchronize("<?php echo $roomToken;?>", userPower);
				}
			});
		} else {
			synchronize("<?php echo $roomToken;?>", userPower);
			$(".room-quick-messages").append("<div class='system-message' id='message-synchro'><span class='glyphicon glyphicon-refresh'></span> <?php echo $lang["synchronizing"];?></div>");
		}
	}
	function userState(roomToken, userToken){
		$.post("functions/get_user_state.php", {roomToken : roomToken, userToken : userToken}).done(function(data){
			if(data == 1){
				setTimeout(userState, 10000, roomToken, userToken);
			} else {
				$("#body-chat").append("<p class='system-message system-alert'><?php echo $lang["room_closing"];?></p>");
				setTimeout(function(){
					window.location.replace("home");
				}, 3000);
			}
		})
	}
	function watchRoom(roomToken){
		$.post("functions/get_room_state.php", {roomToken : roomToken}).done(function(data){
			var states = JSON.parse(data);
			window.roomState = states.room_active;

			// Name of the room
			if($("#room-title").text() != states.room_name){
				$("#room-title").text(states.room_name);
			}
			if(document.title != states.room_name){
				document.title = states.room_name+" | <?php echo $roomDetails["user_pseudo"];?> | Berrybox";
			}

			// Submission of videos
			if(states.room_submission_rights == '0'){
				if(window.userPower == 1){
					window.submission = false;
					$(".add-link").hide();
				} else {
					window.submission = true;
				}
			} else {
				window.submission = true;
				$(".add-link").show();
			}

			// State of the autoplay
			var autoplayWatch = states.room_play_type;

			// State active of the room
			if(window.roomState == 0){
				$(".under-video").hide('1000');
			}
			// Watch the state of the room every 10 seconds
			setTimeout(watchRoom, 10000, roomToken);
		})
	}
	function synchronize(roomToken, userPower){
		/* This function synchronizes the current video for everyone */
		$.post("functions/load_current.php", {roomToken : roomToken, userPower : userPower}).done(function(data){
			var songInfo = JSON.parse(data);
			if(songInfo.link != null){
				if(songInfo.index != sessionStorage.getItem("currently-playing")){
					playSong(songInfo.index, songInfo.link, songInfo.title, songInfo.timestart);
				} else {
					window.videoPending = setTimeout(synchronize, 3000, "<?php echo $roomToken;?>", userPower);
				}
			} else {
				window.videoPending = setTimeout(synchronize, 3000, "<?php echo $roomToken;?>", userPower);
			}
		})
	}
	function playSong(index, id, title, timestart){
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
		$("#message-synchro").remove();
		sessionStorage.setItem("currently-playing", index);
		$(".currently-name").empty();
		$(".currently-name").html(title);
		var userToken = "<?php echo isset($_SESSION["token"])?$_SESSION["token"]:null;?>";
		if(userToken == "<?php echo $roomDetails["room_creator"];?>" && (timestart == 0 || timeDelta <= 3)){
			var message = "{now_playing}"+title;
			sendMessage("<?php echo $roomToken;?>", 4, 2, message);
			$.post("functions/register_song.php", {index : index});
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
					var songName = songList[i].videoName.replace(/'/g, "\&#39");
					if(previousSongState != songList[i].videoStatus){
						switch(songList[i].videoStatus){
							case '0':
								if(previousSongState != 3){
									var messageRank = "<p class='list-rank'><?php echo $lang["sl_upcoming"];?></p>";
									$("#body-song-list").append(messageRank);
								}
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
						message += "<div class='row playlist-entry song-played'>";
						message += "<div class='col-lg-10'>";
					} else if(songList[i].videoStatus == 1){
						message += "<div class='row playlist-entry song-playing'>";
						message += "<div class='col-lg-12'>";
					} else if(songList[i].videoStatus == 3){
						message += "<div class='row playlist-entry song-ignored'>";
						message += "<div class='col-lg-10'>";
					} else {
						var message = "<div class='row playlist-entry song-upcoming'>";
						message += "<div class='col-lg-10'>";
					}
					message += "<p class='song-list-line'><a href='https://www.youtube.com/watch?v="+songList[i].videoLink+"' target='_blank' title='"+songName+"'>"+songList[i].videoName+"</a></p></div>";
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
							message += "<span class='glyphicon glyphicon-ok-circle button-glyph' onClick=reinstateSong("+songList[i].entry+")></span>";
							message += "</div>";
						}
					}
					if(songList[i].videoStatus == 2){
						message += "<div class='col-lg-1'><span class='glyphicon glyphicon-repeat button-glyph' onClick=requeueSong("+songList[i].entry+")></span></div>";
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
	function requeueSong(id){
		$.post("functions/requeue_song.php", {roomToken : "<?php echo $roomToken;?>", id : id, userToken : "<?php echo $_SESSION["token"];?>"}).done(function(data){
			if(data == "1"){
				$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["song_submit_success"];?></p>");
			} else {
				$("#body-chat").append("<p class='system-message system-warning'></span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["no_fetch"];?></p>");
			}
			$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
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
				var reg = new RegExp(/\?v=([a-z0-9\-\_]+)\&?/i); // works for all youtube links except youtu.be type
				var res = reg.exec(src);
				if(res == null){
					var alt = new RegExp(/\.be\/([a-z0-9\-\_]+)\&?/i); // works for youtu.be type links
					res = alt.exec(src);
				}
				var id = res[1];

				// Post URL into room history
				$.post("functions/post_history.php", {url : id, roomToken : roomToken}).done(function(code){
					console.log(code);
					switch(code){
						case '1': // success code
							$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["song_submit_success"];?></p>");
							break;

						case '3': // Invalid link code
							$("#body-chat").append("<p class='system-message system-alert'><span class='glyphicon glyphicon-exclamation-sign'></span> <?php echo $lang["invalid_link"];?></p>");
							break;

						default: // success code but the info are incomplete
							$("#body-chat").append("<div id='warning-"+code+"'><p class='system-message system-warning'><span class='glyphicon glyphicon-question-sign'></span> <?php echo $lang["no_fetch"];?><div class='input-group info-box-group'><input type='text' placeholder='<?php echo $lang["fill_placeholder"];?>' class='form-control info-box' id='info-"+code+"'><span class='input-group-btn'><button class='btn btn-primary send-info'><?php echo $lang["fill_missing"];?></button><button class='btn btn-danger cancel-info' id='cancel-info-"+code+"'>Cancel</button></div></div>");
							break;
					}
					$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
				});
				// Empty URL box
				$(".url-box").val('');
			}
		}
	}
	function fillInfo(){
		var name = $(".info-box").val();
		var id = $(".info-box").attr("id").substr(5);
		$.post("functions/fill_info.php", {index : id, name : name}).done(function(data){
			$("#warning-"+id).remove();
			$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> <?php echo $lang["info_fill_success"];?></p>");
		})
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
		var lang = "<?php echo $userLang;?>";
		if(!window.lastID){
			window.lastID = 0;
		}
		$.post("functions/load_chat.php", {token : roomToken, lang : lang, lastMessageID : window.lastID}).done(function(data){
			var messageList = JSON.parse(data);
			for(var i = 0; i < messageList.length; i++){
				var mTime = moment.utc(messageList[i].timestamp);
				var messageTime = moment(mTime).local().format("HH:mm");
				if(messageList[i].id != window.lastID){
					window.lastID = messageList[i].id;
					if(messageList[i].scope == 6){
						// Whispers
						if(messageList[i].destinationToken == "<?php echo $_SESSION["token"];?>"){
							var message = "<p class='whisper'>";
							message += "<span class='message-time'>"+messageTime+"</span> ";
							message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].authorColor+";'>";
							message += messageList[i].author;
							message += "</span></a>";
							message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
							message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].destinationColor+";'>";
							message += messageList[i].destination;
							message += "</span></a> : ";
							message += messageList[i].content;
							message += "</p>";
						} else if(messageList[i].authorToken == "<?php echo $_SESSION["token"];?>"){
							var message = "<p class='whisper'>";
							message += "<span class='message-time'>"+messageTime+"</span> ";
							message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].authorColor+";'>";
							message += messageList[i].author;
							message += "</span></a>";
							message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
							message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#"+messageList[i].destinationColor+";'>";
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
							message += "<span class='chat-icon' title='<?php echo $lang["room_admin"];?>'><img src='assets/berrybox-creator-logo.png'><span> ";
						} else if(messageList[i].status == 3) {
							// If author is a moderator
							if((userPower == 2 || userPower == 3) && messageList[i].authorToken != "<?php echo $_SESSION["token"];?>"){
								// If current user is a mod or an admin, he can timeout the mod
								/*message += "<span class='glyphicon glyphicon-time moderation-option' title='<?php echo $lang["action_timeout"];?>' onClick=timeoutUser('"+messageList[i].authorToken+"')></span> ";*/
								if(userPower == 2){
									// Specific actions to the admin : ban & demote
									/*message += "<span class='glyphicon glyphicon-fire moderation-option' title='<?php echo $lang["action_ban"];?>' onClick=banUser('"+messageList[i].authorToken+"')></span> ";*/
									message += "<span class='chat-icon' title='<?php echo $lang["room_mod"];?>'><img src='assets/berrybox-moderator-logo.png'><span> ";
									message += "<span class='glyphicon glyphicon-star-empty moderation-option-enabled' title='<?php echo $lang["action_demote"];?>' onClick=demoteUser('"+messageList[i].authorToken+"')></span> ";
								}
							}
							else {
								// If current user has no power here
								message += "<span class='chat-icon' title='<?php echo $lang["room_mod"];?>'><img src='assets/berrybox-moderator-logo.png'><span> ";
							}
						} else {
							// If author is a standard user
							if(userPower == 2 || userPower == 3){
								// Mod & admin actions
								/*message += "<span class='glyphicon glyphicon-time moderation-option' title='<?php echo $lang["action_timeout"];?>' onClick=timeoutUser('"+messageList[i].authorToken+"')></span> ";
								message += "<span class='glyphicon glyphicon-fire moderation-option' title='<?php echo $lang["action_ban"];?>' onClick=banUser('"+messageList[i].authorToken+"')></span> ";*/
								if(userPower == 2){
									//Admin action
									message += "<span class='glyphicon glyphicon-star-empty moderation-option' title='<?php echo $lang["action_promote"];?>' onClick=promoteUser('"+messageList[i].authorToken+"')></span> ";
								}
							}
						}
						message += "<a style='text-decoration:none'><span class='message-author author-linkback' style='color:#"+messageList[i].authorColor+";'>";
						message += messageList[i].author;
						message += "</span></a>";
						message += " : "+messageList[i].content+"<br/>";
						message += "</p>";
					}
					$("#body-chat").append(message);
				} else {
					console.log("Double fetch. Denied");
				}
				if(!window.chatHovered){
					$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
				}
			}
		})
		// Once the function has done everything, it fires a timeout to restart the whole process in 2 seconds
		setTimeout(loadChat, 2000, roomToken, userPower);
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
	function saveRoomChanges(roomToken){
		var language = $('[name=speakLang]').val();
		var description = $("#description").val();
		var type = $('[name=roomType]').val();
		$.post("functions/edit_room.php", {type : type, language : language, description : description, roomToken : roomToken}).done(function(){
			$("#save-room-button").blur();
			$("#save-room-button").text("<?php echo $lang["save_changes_feedback"];?>");
			$("#save-room-button").switchClass("btn-primary", "btn-success feedback", 200, "easeOutBack");
			setTimeout(function(){
				$("#save-room-button").switchClass("btn-success feedback", "btn-primary", 1000, "easeInQuad")
				$("#save-room-button").text("<?php echo $lang["save_changes"];?>");
			}, 1500);
		});
	}
	function closeRoom(roomToken){
		sendMessage(roomToken, 4, 4, "{close_room_5}");
		$.post("functions/close_room.php", {roomToken : roomToken, userToken : "<?php echo $_SESSION["token"];?>"});
	}
	<?php } else { ?>
	$("#no-credentials").ekkoLightbox({
		onNavigate: false
	});
	$("#no-credentials").click();
	<?php } ?>
</script>
