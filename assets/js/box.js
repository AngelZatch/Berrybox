/** General script regarding all that's happening in the box **/
/* YOUTUBE PLAYER */
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var player;
var refresh_playlist_timer;
function onYouTubeIframeAPIReady() {
	player = new YT.Player('player', {
		height: '85%',
		width: '60%',
		videoId: '',
		events: {
			'onReady': onPlayerReady,
			'onStateChange': onPlayerStateChange
		}
	});
	// Since it's the last element to appear, we resize elements once it's done.
	resizeElements();
}

function getBoxToken(){
	return /([a-z0-9]+$)/i.exec(document.location.href)[0];
}

function onPlayerReady(event){
	var box_token = getBoxToken();
	sessionStorage.setItem("currently-playing", "");
	synchronize(box_token);
}

function onPlayerStateChange(event) {
	var box_token = getBoxToken();
	if(window.sync == true && window.autoplay != false){
		if (event.data == YT.PlayerState.ENDED) {
			getNext(false, box_token);
		}
	}
	if(event.data == YT.PlayerState.PLAYING){
		var moodTimer = player.getDuration() * 1000;
		if(user_token != -1)
			fetchMood(user_token, sessionStorage.getItem("currently-playing"));
	}
}

function addEntry(box_token, video_id, source){
	// Post URL into room history
	$.post("functions/post_history.php", {url : video_id, box_token : box_token, source : source}).done(function(code){
		console.log(code);
		if(source != "playlist"){
			switch(code){
				case 'ok': // success code
					$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> "+language_tokens.song_submit_success+"</p>");
					break;

				case 'error': // Invalid link code
					$("#body-chat").append("<p class='system-message system-alert'><span class='glyphicon glyphicon-exclamation-sign'></span> "+language_tokens.invalid_link+"</p>");
					break;

				case 'info':
					$("#body-chat").append("<p class='system-message system-alert'><span class='glyphicon glyphicon-exclamation-sign'></span> "+language_tokens.need_info+"</p>");
					break;

				default: // success code but the info are incomplete
					requestCompletion(code);
					break;
			}
		}
		$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
		return code;
	});
}

function getActiveWatchers(box_token){
	if($("#user-list").css("display") != "none"){
		$.get("functions/get_user_list.php", {box_token : box_token}).done(function(data){
			var userList = JSON.parse(data);
			$("#body-user-list").empty();
			var previousRank = -1;
			for(var i = 0; i < userList.length; i++){
				var message = "";
				if(previousRank != userList[i].power){
					switch(userList[i].power){
						case '1':
							message += "<p class='list-rank'>"+language_tokens.ul_users+"</p>";
							break;
						case '2':
							message += "<p class='list-rank'>"+language_tokens.ul_admin+"</p>";
							break;
						case '3':
							message += "<p class='list-rank'>"+language_tokens.ul_mods+"</p>";
							break;
					}
				}
				message += "<div class='row'>";
				message += "<p class='col-xs-10'>";
				message += userList[i].pseudo;
				message += "</p>";
				if(userList[i].pseudo != user_name && user_power == 2)
					message += "<p class='col-xs-1' title='"+language_tokens.transfer_box+"'><span class='glyphicon glyphicon-transfer button-glyph transfer-box' data-user='"+userList[i].pseudo+"'></span></p>";
				message += "</div>";
				previousRank = userList[i].power;
				$("#body-user-list").append(message);
			}
		})
		setTimeout(getActiveWatchers, 8000, box_token);
	}
}

function getBoxDetails(box_token){
	return $.get("functions/get_box_details.php", {box_token : box_token});
}

function getNext(skip, box_token){
	if(skip == true){
		var message = "{skip}";
		sendMessage(box_token, 4, 3, message);
	}
	if(user_power == 2){
		$.post("functions/get_next.php", {box_token : box_token, user_power : window.user_power, lastPlayed : sessionStorage.getItem("currently-playing")}).done(function(data){
			if(data != ""){
				var songInfo = JSON.parse(data);
				if(songInfo.link != null){
					playSong(songInfo.index, songInfo.link, songInfo.title, 0);
				}
			} else {
				synchronize(box_token);
			}
		});
	} else {
		synchronize(box_token);
		$(".sync-message").append("<span class='glyphicon glyphicon-refresh' title='"+language_tokens.synchronizing+"'> <span class='hidden-xs hidden-sm hidden-md'>"+language_tokens.synchronizing+"</span></span>");
	}
}

// Joining the box
function joinBox(box_token, user_token){
	return $.post("functions/join_room.php", {box_token : box_token, user_token : user_token});
}

// Loading playlist
function loadPlaylist(box_token, forced){
	if(forced){
		clearTimeout(refresh_playlist_timer);
	}
	/*console.log("fetching"+moment());*/
	// Load the playlist
	if($("#song-list").css("display") != "none"){
		// Gets the whole history of the room
		if(!$("#playlist-filter").is(":focus")){
			$.post("functions/get_history.php", {box_token : box_token}).done(function(data){
				var songList = JSON.parse(data);
				var uVideos = 0, pVideos = 0;
				$("#body-song-list").empty();
				var previousSongState = -1;
				for(var i = 0; i < songList.length; i++){
					var message = "";
					var songName = songList[i].videoName.replace(/'/g, "\&#39");
					if(previousSongState != songList[i].videoStatus){
						switch(songList[i].videoStatus){
							case '0':
							case '3':
								if(i == 0){
									var messageRank = "<p class='list-rank' id='list-upcoming'>"+language_tokens.sl_upcoming+"</p>";
									$("#body-song-list").append(messageRank);
								}
								break;

							case '1':
								message += "<p class='list-rank'>"+language_tokens.now_playing+"</p>";
								break;

							case '2':
								message += "<p class='list-rank' id='list-played'>"+language_tokens.sl_played+"</p>";
								break;
						}
					}
					var nameLength;
					if(songList[i].videoStatus == 2){
						message += "<div class='row playlist-entry song-played'>";
						nameLength = 10;
						pVideos++;
					} else if(songList[i].videoStatus == 1){
						message += "<div class='row playlist-entry song-playing'>";
						nameLength = 11;
					} else if(songList[i].videoStatus == 3){
						message += "<div class='row playlist-entry song-ignored'>";
						nameLength = 10;
					} else {
						var message = "<div class='row playlist-entry song-upcoming'>";
						uVideos++;
						nameLength = 10;
					}
					if(window.user_power != 2 && window.user_power != 3){
						nameLength++;
					}
					// Playlist ordering
					if(window.user_power == 2 || window.user_power == 3){
						if(songList[i].videoStatus == "0" || songList[i].videoStatus == "3"){
							nameLength -= 2;

							// Up button
							message += "<div class='col-xs-1'>";
							if(i != 0){
								message += "<span class='glyphicon glyphicon-arrow-up button-glyph swap-order' id='up-"+songList[i].entry+"' data-order='"+songList[i].order+"' title='"+language_tokens.song_up+"'></span>";
							}
							message += "</div>";

							// Down button
							message += "<div class='col-xs-1'>";
							if(songList[i+1].videoStatus == 0 || songList[i+1].videoStatus == 3){
								message += "<span class='glyphicon glyphicon-arrow-down button-glyph swap-order' id='down-"+songList[i].entry+"' data-order='"+songList[i].order+"' title='"+language_tokens.song_down+"'></span>";
							}
							message += "</div>";
						}
					}
					message += "<div class='col-xs-"+nameLength+"'>";
					message += "<p class='song-list-line'><a href='https://www.youtube.com/watch?v="+songList[i].videoLink+"' target='_blank' title='"+songName+"'>"+songList[i].videoName+"</a></p></div>";
					/*console.log(songList[i].pending);*/
					if(window.room_state == 1){
						if(window.user_power == 2 || window.user_power == 3){
							message += "<div class='col-xs-1'>";
							if(songList[i].pending == 1){
								message += "<span class='glyphicon glyphicon-pencil button-glyph' onClick=requestCompletion("+songList[i].index+")></span>";
							}
							message += "</div>";
							message += "<div class='col-xs-1'>";
							if(songList[i].videoStatus == 0){
								message += "<span class='glyphicon glyphicon-ban-circle button-glyph ignore-video' id='ignore-"+songList[i].entry+"' data-target='"+songList[i].entry+"'></span>";
							} else if(songList[i].videoStatus == 3){
								message += "<span class='glyphicon glyphicon-ok-circle button-glyph reinstate-video' id='reinstate-"+songList[i].entry+"' data-target='"+songList[i].entry+"'></span>";
							}
						}
						if(songList[i].videoStatus == 2 && user_token != -1 && ((window.room_submission_rights == 2 && user_power != 1) || (window.room_submission_rights == 1))){
							message += "<span class='glyphicon glyphicon-repeat button-glyph quick-requeue' id='quick-requeue-"+songList[i].entry+"' data-target='"+songList[i].entry+"'></span>";
						}
						message += "</div>";
					}
					message += "</div>";
					previousSongState = songList[i].videoStatus;
					$("#body-song-list").append(message);
				}
				$("#list-upcoming").append(" ("+uVideos+")");
				$("#list-played").append(" ("+pVideos+")");
			})
		}
		// Load the likes of the user
		if(user_token != -1){
			if(!$("#likes-filter").is(":focus")){
				$.get("functions/get_likes.php", {user_token : user_token}).done(function(data){
					var likes = JSON.parse(data);
					$("#body-song-likes").empty();
					for(var i = 0; i < likes.length; i++){
						var message = "";
						var video_name = likes[i].video_name.replace(/'/g, "\&#39");
						message += "<div class='row likes-entry'>";
						message += "<div class='col-xs-11'>";
						message += "<p class='song-list-line'>";
						message += "<span class='glyphicon glyphicon-"+likes[i].key_icon+" emotion-"+likes[i].key_mood+"'></span> ";
						message += "<a href='"+likes[i].video_link+"' target='_blank' title='"+video_name+"'>"+likes[i].video_name+"</a></p></div>";
						if(window.room_state == 1){
							message += "<span class='glyphicon glyphicon-circle-arrow-right button-glyph quick-submit' id='quick-submit-"+likes[i].video_id+"' data-target='"+likes[i].video_id+"'></span>";
						}
						message += "</div>";
						$("#body-song-likes").append(message);
					}
				})
			}
		}
		refresh_playlist_timer = setTimeout(loadPlaylist, 8000, box_token, false);
	}
}

// Playing video
function playSong(index, id, title, timestart){
	var box_token = getBoxToken();
	/*console.log(timestart);*/
	if(timestart != 0){
		/*console.log("timestamp : "+timestart);*/
		var sTime = moment.utc(timestart).add(7, 's');
		/*console.log("start of video fetched from DB : "+sTime);*/
		var sLocalTime = moment(sTime).local();
		/*console.log("formatted : "+sLocalTime);*/
		var timeDelta = Math.round(moment().diff(sLocalTime)/1000);
		/*console.log("TIME DELTA : "+timeDelta);*/
		if(timeDelta < 0){
			timeDelta = 0;
		}
		player.loadVideoById(id, timeDelta);
	} else {
		player.loadVideoById(id, 0);
	}
	$(".sync-message").empty();
	sessionStorage.setItem("currently-playing", index);
	document.title = title+" | Berrybox";
	$(".currently-name").empty();
	$(".currently-name").html(title);
	if(user_token == box_details.room_creator && (timestart == 0 || timeDelta <= 3)){
		var message = "{now_playing}"+title;
		sendMessage(box_token, 4, 2, message);
		$.post("functions/register_song.php", {index : index});
	}
	displayMoodVotes(index);
}

// Requeueing song
function requeueSong(box_token, video_id){
	$.post("functions/requeue_song.php", {box_token : box_token, video_id : video_id, user_token : user_token}).done(function(data){
		console.log(data);
		if(data == "1"){
			$("#body-chat").append("<p class='system-message system-success'><span class='glyphicon glyphicon-ok-sign'></span> "+language_tokens.song_submit_success+"</p>");
		} else {
			// To change
			$("#body-chat").append("<p class='system-message system-warning'></span class='glyphicon glyphicon-question-sign'></span> "+language_tokens.no_fetch+"</p>");
		}
		$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
		loadPlaylist(box_token, true);
	})
}

function shufflePlaylist(box_token){
	$.get("functions/playlist_shuffle.php", {box_token : box_token}).done(function(data){
		console.log(data);
		loadPlaylist(box_token, true);
		$("#body-chat").append("<p class='system-message'><span class='glyphicon glyphicon-question-sign'></span> "+language_tokens.playlist_shuffled+"</p>");
	})
}

function submitLink(){
	$(".submit-warning").empty();
	var box_token = getBoxToken();
	// Get URL
	var src = $(".url-box").val();
	if(src != ''){
		// get playlist if it exists
		var playreg = new RegExp(/list=([a-z0-9\-\_]+)\&?/i);
		var playres = playreg.exec(src);
		if(playres != null){
			pID = playres[1];
			//console.log("Playlist detected : "+pID);
			$(".under-video").append("<div class='modal-body' id='sec-player' style='display:none;'></div>");
			var secondaryPlayer;
			secondaryPlayer = new YT.Player('sec-player', {
				height: '5',
				width: '5',
				videoId: '',
				events: {
					'onReady': onSecPlayerReady,
					'onStateChange': onSecPlayerStateChange
				}
			});
		} else {
			// if there's no playlist, get ID of video
			var reg = new RegExp(/\?v=([a-z0-9\-\_]+)\&?/i); // works for all youtube links except youtu.be type
			var res = reg.exec(src);
			if(res == null){
				var alt = new RegExp(/\.be\/([a-z0-9\-\_]+)\&?/i); // works for youtu.be type links
				res = alt.exec(src);
			}
			var id = res[1];
			// We call the function to add the id to the database
			addEntry(box_token, id, "solo");
			// Empty URL box
			$(".url-box").val('');
		}
	}
}

// Synchronize between users
function synchronize(box_token){
	/* This function synchronizes the current video for everyone */
	$.post("functions/load_current.php", {box_token : box_token, user_power : window.user_power}).done(function(data){
		/*console.log(data);*/
		var songInfo = JSON.parse(data);
		if(songInfo.link != null){
			if(songInfo.index != sessionStorage.getItem("currently-playing")){
				playSong(songInfo.index, songInfo.link, songInfo.title, songInfo.timestart);
			} else {
				window.videoPending = setTimeout(synchronize, 3000, box_token);
			}
		} else {
			window.videoPending = setTimeout(synchronize, 3000, box_token);
		}
	})
}

// Watch the state of the user and update it to 1.
function userState(box_token, user_token){
	$.get("functions/get_user_state.php", {box_token : box_token, user_token : user_token}).done(function(data){
		var values = JSON.parse(data);
		/*console.log("your power is "+values.room_user_state);*/
		window.user_power = values.room_user_state;
		if(values.room_user_state == 4) {
			setTimeout(function(){
				window.location.replace("home");
			}, 3000);
		}
	})
}

// Ignores or reinstates a video in the queue
function toggleVideoInQueue(box_token, video_id, play_flag){
	$.post("functions/toggle_video_in_queue.php", {box_token : box_token, video_id : video_id, play_flag : play_flag}).done(function(data){
		if(play_flag == 0){
			var message = "{song_reinstated}"+data;
			sendMessage(box_token, 4, 6, message, null);
		}
		if(play_flag == 3){
			var message = "{song_ignored}"+data;
			sendMessage(box_token, 4, 5, message, null);
		}
		loadPlaylist(box_token, true);
	})
}

function transferBox(box_token, user_target){
	$.post("functions/transfer_box.php", {box_token : box_token, user_target : user_target}).done(function(){
		watchBoxState(box_token);
		var message = user_target+" "+language_tokens.box_transfered;
		sendMessage(box_token, 4, 1, message);
	});
}

function watchBoxState(box_token){
	/* This function will watch everything regarding the state of the box
	Title, creator, number of watchers, state (opened or closed)
	*/
	if(window.box_watch_time)
		clearTimeout(window.box_watch_time);
	$.get("functions/get_box_status.php", {box_token : box_token}).done(function(data){
		var box_variables = JSON.parse(data);
		userState(box_token, window.user_token);

		// Name of the room
		$("#room-title").text(box_variables.room_name);

		// Cretor stats
		$(".creator-views").text(box_variables.stat_visitors);
		$(".creator-followers").text(box_variables.stat_followers);

		// Creator of the room
		if(box_variables.user_pseudo != $("#box-creator-link").text()){
			if(box_variables.user_pseudo == window.user_name){
				console.log("you have been made admin");
				$(".btn-toggle-follow").remove();
				// Admin options in options menu
				var options = "<div class='admin-options'>";
				options += "<div role='separator' class='divider options-divider'></div>";
				options += "<p class='options-title'>"+language_tokens.creator_options+"</p>";
				// Play type buttons
				options += "<div class='room-option'>";
				options += "<p class='option-title'>"+language_tokens.play_type+"</p>";
				options += "<span class='tip'>"+language_tokens.play_type_tip+"</span>";
				options += "<div class='row'>";
				options += "<div class='col-lg-6'>";
				options += "<span class='btn btn-primary btn-block btn-switch toggle-box-state' id='select-automatic' data-field='room_play_type' data-value='1' data-twin='select-manual'>";
				options += "<span class='glyphicon glyphicon-play-circle'></span> ";
				options += language_tokens.auto_play;
				options += "</span>";
				options += "</div>";
				options += "<div class='col-lg-6'>";
				options += "<span class='btn btn-primary btn-block btn-switch toggle-box-state' id='select-manual' data-field='room_play_type' data-value='2' data-twin='select-automatic'>";
				options += "<span class='glyphicon glyphicon-hourglass'></span> ";
				options += language_tokens.manual_play;
				options += "</span>";
				options += "</div>";
				options += "</div>";
				options += "</div>";

				// Submission rights
				options += "<div class='room-option'>";
				options += "<p class='option-title'>"+language_tokens.submit_type+"</p>";
				options += "<span class='tip'>"+language_tokens.submit_type_tip+"</span>";
				options += "<div class='row'>";
				options += "<div class='col-lg-6'>";
				options += "<span class='btn btn-primary btn-block btn-switch toggle-box-state' id='select-everyone' data-field='room_submission_rights' data-value='1' data-twin='select-mods-only'>";
				options += "<span class='glyphicon glyphicon-ok-sign'></span> ";
				options += language_tokens.submit_all;
				options += "</span>";
				options += "</div>";
				options += "<div class='col-lg-6'>";
				options += "<span class='btn btn-primary btn-block btn-switch toggle-box-state' id='select-mods-only' data-field='room_submission_rights' data-value='2' data-twin='select-everyone'>";
				options += "<span class='glyphicon glyphicon-ok-circle'></span> ";
				options += language_tokens.submit_mod;
				options += "</span>";
				options += "</div>";
				options += "</div>";
				options += "</div>";

				// Protection
				options += "<div class='room-option'>";
				options += "<p class='option-title'>"+language_tokens.room_protection+"</p>";
				options += "<span class='tip'>"+language_tokens.protection_tip+"</span>";
				options += "<div class='row'>";
				options += "<div class='col-lg-6'>";
				options += "<span class='btn btn-primary btn-block btn-switch toggle-box-state' id='select-public' data-field='room_protection' data-value='1' data-twin='select-private'>";
				options += "<span class='glyphicon glyphicon-volume-up'></span> ";
				options += language_tokens.level_public;
				options += "</span>";
				options += "</div>";
				options += "<div class='col-lg-6'>";
				options += "<span class='btn btn-primary btn-block btn-switch toggle-box-state' id='select-private' data-field='room_protection' data-value='2' data-twin='select-public'>";
				options += "<span class='glyphicon glyphicon-headphones'></span> ";
				options += language_tokens.level_private;
				options += "</span>";
				options += "</div>";
				options += "</div>";
				options += "</div>";

				// Box parameters
				options += "<div class='room-option'>";
				options += "<p class='option-title'>"+language_tokens.room_params+"</p>";
				options += "<span class='tip'>"+language_tokens.room_params_tip+"</span>";
				options += "<form class='form-horizontal' id='form-box-details'>";
				options += "<div class='form-group'>"; // Box name
				options += "<label for='room_name' class='col-lg-4 control-label'>"+language_tokens.room_name+"</label>";
				options += "<div class='col-lg-8'><input type='text' name='room_name' class='form-control' value='"+box_variables.room_name+"'></div>";
				options += "</div>";
				options += "<div class='form-group'>"; // Box language
				options += "<label for='room_lang' class='col-lg-4 control-label'>"+language_tokens.speak_lang+"</label>";
				options += "<div class='col-lg-8'>";
				options += "<select name='room_lang' id='' class='form-control'>";
				options += "<option value='en'>"+language_tokens.lang_en+"</option>";
				options += "<option value='fr'>"+language_tokens.lang_fr+"</option>";
				options += "<option value='jp'>"+language_tokens.lang_jp+"</option>";
				options += "</select>";
				options += "</div>";
				options += "</div>";
				options += "<div class='form-group'>"; // Box type
				options += "<label form='room_type' class='col-lg-4 control-label'>"+language_tokens.room_type+"</label>";
				options += "<div class='col-lg-8'>";
				options += "<select name='room_type' class='form-control' id='type-select'>";
				$.get("functions/fetch_box_types.php", {box_token : box_token}).done(function(data){
					var types = JSON.parse(data);
					for(var i = 0; i < types.length; i++){
						$("#type-select").append(
							$("<option></option>").text(language_tokens[types[i].type]).val(types[i].id)
						);
					}
				})
				options += "</select>";
				options += "</div>";
				options += "</div>";
				options += "<div class='form-group'>"; // Box description
				options += "<label form='room_description' class='col-lg-4 control-label'>"+language_tokens.description_limit+"</label>";
				options += "<div class='col-lg-8'>";
				options += "<textarea name='room_description' cols='30' rows='5' class='form-control'>"+box_variables.room_description+"</textarea>";
				options += "</div>";
				options += "</div>";
				options += "</form>";
				options += "<button class='btn btn-primary btn-block' id='save-room-button'>"+language_tokens.save_changes+"</button>";

				options += "</div>";
				$(".admin-options").empty();
				$("#body-options-list").append(options);

				// Playlist buttons
				options = "<button class='btn btn-default btn-admin btn-skip col-xs-6'><span class='glyphicon glyphicon-step-forward resize-lg'></span> <span class='hidden-xs hidden-sm hidden-md'>"+language_tokens.skip+"</span></button>";
				options += "<button class='btn btn-default btn-admin shuffle-playlist col-xs-6'><span class='glyphicon glyphicon-question-sign resize-lg'></span> <span class='hidden-xs hidden-sm hidden-md'>"+language_tokens.shuffle+"</span></button>";
				$(".playlist-actions").append(options);
			} else {
				// If the user is no longer the creator
				console.log("you have lost admin");
				$(".admin-options").empty();
				$(".playlist-actions").empty();
				// Add the follow button
				var follow_button_classes = "", follow_button_id = "", follow_button_text = "";
				if(box_variables.following_creator == 1){
					follow_button_classes = 'btn-unfollow btn-active';
					follow_button_id = 'unfollow';
					follow_button_text = language_tokens.following+" "+box_variables.user_pseudo;
				} else {
					follow_button_classes = 'btn-follow';
					follow_button_id = 'follow';
					follow_button_text = language_tokens.follow+" "+box_variables.user_pseudo;
				}
				var follow_button = "<button class='btn btn-primary btn-toggle-follow "+follow_button_classes+"' id='box-title-"+follow_button_id+"' value='"+box_variables.user_pseudo+"'>";
				follow_button += "<span class='glyphicon glyphicon-heart'></span>";
				follow_button += "<span class='hidden-xs hidden-sm'> ";
				follow_button += follow_button_text;
				follow_button += "</span>";
				follow_button += "</button>";
				if($(".btn-toggle-follow").length == 0)
					$(".creator-stats").prepend(follow_button);

				// Change the box settings menu to only the sync button
				if($(".user-options").length == 0){
					var options = "<div class='user-options'>";
					options += "<span class='option-title'>"+language_tokens.sync+"</span><br>";
					options += "<span class='tip'>"+language_tokens.sync_tip+"</span>";
					options += "<button class='btn btn-default btn-admin btn-block sync-on' id='btn-synchro'><span class='glyphicon glyphicon-refresh'></span> "+language_tokens.sync_on+"</button>";
					options += "</div>";
					$("#body-options-list").empty();
					$("#body-options-list").append(options);
				}
				// Do something for the sync button ?
			}
			$("#box-creator-link").attr("href", "user/"+box_variables.user_pseudo);
			$("#box-creator-link").text(box_variables.user_pseudo);
			$("#box-creator-picture").attr("src", "profile-pictures/"+box_variables.user_pp);
			$("#box-creator-picture").attr("title", box_variables.user_pseudo+" ("+language_tokens.room_admin+")");
		}

		// Submission of videos
		if(box_variables.room_submission_rights == 2){ // Moderators only
			if(window.user_power == 1){
				$(".add-link").hide();
				$(".room-quick-messages").addClass("col-sm-offset-4");
			}
			if(window.user_power == 2){
				if(window.submission)
					sendMessage(box_token, 4, 1, "{submission_mod}");
			}
			window.submission = false;
			$(".submission-message").html("<span class='glyphicon glyphicon-play-circle' title='"+language_tokens.submit_mod+"'></span> <span class='hidden-xs hidden-sm hidden-md'>"+language_tokens.submit_mod+"</span></div>");
			$("#select-mods-only").trigger('activate');
		} else { // Everyone
			if(!window.submission && user_power == 2)
				sendMessage(box_token, 4, 1, "{submission_all}");
			window.submission = true;
			$(".add-link").show();
			$(".room-quick-messages").removeClass("col-sm-offset-4");
			$(".submission-message").empty();
			$("#select-everyone").trigger('activate');
		}
		window.room_submission_rights = box_variables.room_submission_rights;

		if(box_variables.room_protection == 2){
			if(window.protection == 1 && user_power == 2)
				sendMessage(box_token, 4, 1, "{protect_private}");
			window.protection = 2;
			$(".protection-message").html("<span class='glyphicon glyphicon-headphones' title='"+language_tokens.level_private+"'></span> <span class='hidden-xs hidden-sm hidden-md'>"+language_tokens.level_private+"</span></div>");
			$("#select-private").trigger('activate');
		} else {
			if(window.protection == 2 && user_power == 2)
				sendMessage(box_token, 4, 1, "{protect_public}");
			window.protection = 1;
			$(".protection-message").empty();
			$("#select-public").trigger('activate');
		}

		// State of the autoplay
		if(box_variables.room_play_type == 1){
			if(!window.autoplay && user_power == 2)
				sendMessage(box_token, 4, 1, "{auto-on}");
			window.autoplay = true;
			$(".play-message").empty();
			$("#select-automatic").trigger('activate');
		} else {
			if(window.autoplay && user_power == 2)
				sendMessage(box_token, 4, 1, "{auto-off}");
			window.autoplay = false;
			$(".play-message").html("<span class='glyphicon glyphicon-hourglass' title='"+language_tokens.manual_play+"'></span> <span class='hidden-xs hidden-sm hidden-md'>"+language_tokens.manual_play+"</span></div>");
			$("#select-manual").trigger('activate');
		}

		// State active of the room
		if(window.room_state != 0 && box_variables.room_active == 0){
			$("#body-chat").append("<p class='system-message'><span class='glyphicon glyphicon-info-sign'></span> "+language_tokens.room_closing+"</p>");
		}
		window.room_state = box_variables.room_active;


		// Number of watchers
		$("#watch-count").empty();
		$("#watch-count").append(" "+box_variables.present_watchers);
		// Watch the state of the room every 10 seconds
		box_watch_time = setTimeout(watchBoxState, 10000, box_token);
	})
}

$(document).ready(function(){
	var box_token = getBoxToken();
	$.when(getBoxDetails(box_token)).done(function(data){
		window.box_details = JSON.parse(data);
		window.roomState = box_details.room_active;
		// Join the room
		$.when(joinBox(box_token, user_token), getUserLang()).done(function(result, lang){
			// Get power of the user
			window.user_power = result[0];
			window.language_tokens = JSON.parse(lang[0]);
			window.lang = language_tokens.user_lang;

			// State of the box
			watchBoxState(box_token);

			// Welcome message
			if(user_power == 2){
				$("#body-chat").append("<p class='system-message'>"+language_tokens.welcome_admin+"</p>");
			} else {
				$("#body-chat").append("<p class='system-message'>"+language_tokens.welcome+"</p>");
			}
			// Check if creator
			if(user_token != box_details.room_creator){
				// If user is not the creator, check presence of the creator
				$.post("functions/check_creator.php", {box_token : box_token}).done(function(presence){
					if(presence == 0){
						$("#body-chat").append("<p class='system-message system-alert'>"+language_tokens.no_admin+"</p>");
					}
				})
			} else {
				// If user is the creator, then start autoplay
				if(box_details.room_play_type == 1){
					window.autoplay = true;
				} else {
					window.autoplay = false;
				}
			}
			// Keep PHP session alive (every 30 mins)
			setInterval(function(){$.post("functions/refresh_session.php");},1800000);
			fetchEmotes().done(function(data){
				var emoteList = JSON.parse(data);
				var emotes = [];
				for(var i = 0; i < emoteList.length; i++){
					emotes.push(emoteList[i].emoteText);
				}
				loadChat(box_token, user_token, emotes, window.lang);
			})
			// Set global chatHover & sync variables
			window.chatHovered = false;
			window.sync = true;

			// Once box is joined, we setup the YouTube player
			/*function onYouTubeIframeAPIReady() {
				player = new YT.Player('player', {
					height: '75%',
					width: '60%',
					videoId: '',
					events: {
						'onReady': onPlayerReady,
						'onStateChange': onPlayerStateChange
					}
				});
			}*/
		})
	});
	// Dynamic filtering of the playlist
	$('#playlist-filter').on('keyup', function(){
		var $rows = $('.playlist-entry');
		var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		$rows.show().filter(function(){
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();
	});
	$('#likes-filter').on('keyup', function(){
		var $rows = $('.likes-entry');
		var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
		$rows.show().filter(function(){
			var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
			return !~text.indexOf(val);
		}).hide();
	});
}).on('mouseenter', '.btn-unfollow', function(){
	var id = $(this).attr("id");
	var user = $(this).val();
	if($(this).hasClass("btn-card")){
		var text = "<span class='glyphicon glyphicon-minus'></span> <span class='hidden-xs hidden-sm'></span>";
	} else {
		var text = "<span class='glyphicon glyphicon-minus'></span> <span class='hidden-xs hidden-sm'>"+language_tokens.unfollow+" "+user+"</span>";
	}
	$("#"+id).html(text);
	$("#"+id).removeClass("btn-active");
	$("#"+id).addClass("btn-danger");
}).on('mouseleave', '.btn-unfollow', function(){
	var id = $(this).attr("id");
	var user = $(this).val();
	if($(this).hasClass("btn-card")){
		var text = "<span class='glyphicon glyphicon-heart'></span> <span class='hidden-xs hidden-sm'></span>";
	} else {
		var text = "<span class='glyphicon glyphicon-heart'></span> <span class='hidden-xs hidden-sm'>"+language_tokens.following+" "+user+"</span>";
	}
	$("#"+id).html(text);
	$("#"+id).removeClass("btn-danger");
	$("#"+id).addClass("btn-active");
}).on('click', '.btn-unfollow', function(){
	var followed_token = $(this).attr("value");
	var id = $(this).attr("id");
	$.post("functions/unfollow_user.php", {userFollowing : user_token, userFollowed : followed_token}).done(function(data){
		$("#"+id).removeClass("btn-active");
		if($("#"+id).hasClass("btn-card")){
			var text = "<span class='glyphicon glyphicon-heart'></span> <span class='hidden-xs hidden-sm'></span>";
		} else {
			var text = "<span class='glyphicon glyphicon-heart'></span> <span class='hidden-xs hidden-sm'>"+language_tokens.follow+" "+followed_token+"</span>";
		}
		$("#"+id).html(text);
		$("#"+id).removeClass("btn-danger");
		$("#"+id).removeClass("btn-unfollow");
		$("#"+id).addClass("btn-follow");
		$("#"+id).attr("id", id.substr(0, id.length - 6)+"follow");
		if(followed_token == box_details.creator_pseudo){
			$("#box-title-unfollow").removeClass("btn-active");
			$("#box-title-unfollow").html(text);
			$("#box-title-unfollow").removeClass("btn-danger");
			$("#box-title-unfollow").removeClass("btn-unfollow");
			$("#box-title-unfollow").addClass("btn-follow");
			$("#box-title-unfollow").attr("id", "box-title-follow");
		}
	})
}).on('click', '.btn-follow', function(){
	var followed_token = $(this).attr("value");
	var id = $(this).attr("id");
	$.post("functions/follow_user.php", {userFollowing : user_token, userFollowed : followed_token}).done(function(data){
		$("#"+id).addClass("btn-active");
		if($("#"+id).hasClass("btn-card")){
			var text = "<span class='glyphicon glyphicon-heart'></span> <span class='hidden-xs hidden-sm'></span>";
		} else {
			var text = "<span class='glyphicon glyphicon-heart'></span> <span class='hidden-xs hidden-sm'>"+language_tokens.following+" "+followed_token+"</span>";
		}
		$("#"+id).html(text);
		$("#"+id).removeClass("btn-follow");
		$("#"+id).addClass("btn-unfollow");
		$("#"+id).attr("id", id.substr(0, id.length - 8)+"unfollow");
		if(followed_token == box_details.creator_pseudo){
			$("#box-title-follow").addClass("btn-active");
			$("#box-title-follow").html(text);
			$("#box-title-follow").removeClass("btn-follow");
			$("#box-title-follow").addClass("btn-unfollow");
			$("#box-title-follow").attr("id", "box-title-unfollow");
		}
	})
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
}).on('click', '.btn-chat', function(){
	var box_token = getBoxToken();
	sendMessage(box_token, 1, null, 'chatbox', '');
}).on('click', '.color-cube', function(){
	var cube = $(this);
	var color = $(this).attr('id').substr(6,6);
	$.post("functions/change_color.php", {user_token : user_token, color : color}).done(function(){
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
		$b.html("<span class='glyphicon glyphicon-repeat'></span> "+language_tokens.sync_off);
		window.sync = false;
		$b.blur();
	} else {
		var box_token = getBoxToken();
		$b.addClass("sync-on");
		$b.empty();
		$b.removeClass("sync-off");
		$b.html("<span class='glyphicon glyphicon-refresh'></span> "+language_tokens.sync_on);
		window.sync = true;
		synchronize(box_token);
		$b.blur();
	}
}).on("click", ".emotion-container", function(){
	if($(this).hasClass("selected")){
		var mood_id = 0;
	} else {
		var mood_id = document.getElementById($(this).attr("id")).dataset.mood;
	}
	voteMood(mood_id, user_token, sessionStorage.getItem("currently-playing"));
}).on('click', '.ignore-video', function(){
	var target = document.getElementById($(this).attr("id")).dataset.target;
	var box_token = getBoxToken();
	toggleVideoInQueue(box_token, target, 3);
}).on('click', '.reinstate-video', function(){
	var target = document.getElementById($(this).attr("id")).dataset.target;
	var box_token = getBoxToken();
	toggleVideoInQueue(box_token, target, 0);
}).on('click', '.toggle-song-list, .toggle-menu-list, .toggle-user-list, .toggle-options-list', function(){
	var box_token = getBoxToken();
	var classToken = $(this).attr("class").split(' ')[4].substr(7);
	var position, top = "0px";
	if($("#"+classToken).css("display") == "none"){
		$("#"+classToken).toggle();
		if(window.innerWidth > 1024){
			position = "24.2%";
		}
		else if(window.innerWidth < 768){
			position = "0%";
			top = $("#heading-chat").offset().top + 10;
		} else{
			position = "31.9%";
		}

		switch(classToken){
			case "song-list":
				loadPlaylist(box_token, false);
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
				if(jQuery(window).width() > 992){
					$("#currently-playing").animate({
						width: "65%"
					}, 200);
				}
				break;

			case "user-list":
				getActiveWatchers(box_token);
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
				if(jQuery(window).width() > 992){
					$("#currently-playing").animate({
						width: "77%"
					}, 200);
				}
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
				if(jQuery(window).width() > 992){
					$("#currently-playing").animate({
						width: "77%"
					}, 200);
				}
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
				if(jQuery(window).width() > 992){
					$("#currently-playing").animate({
						width: "65%"
					}, 200);
				}
				break;
		}
	} else {
		$(this).t = setTimeout((function(){
			$("#"+classToken).hide();
		}), 200);
		position = "0px";
		if(window.innerWidth < 768){
			top = window.innerHeight + 1;
		}
		if(jQuery(window).width() > 992){
			$("#currently-playing").animate({
				width: "100%",
				top: top
			}, 200);
		}
	}
	$("#"+classToken).animate({
		right : position,
		top: top
	}, 200);
}).on('mouseenter', '#body-chat', function(){
	window.chatHovered = true;
}).on('mouseleave', '#body-chat', function(){
	window.chatHovered = false;
}).on('click', '.quick-submit', function(){
	var box_token = getBoxToken();
	var target = document.getElementById($(this).attr("id")).dataset.target;
	addEntry(box_token, target, "solo");
}).on('click', '.quick-requeue', function(){
	var box_token = getBoxToken();
	var target = document.getElementById($(this).attr("id")).dataset.target;
	requeueSong(box_token, target);
}).on('click', '#save-room-button', function(){
	var box_token = getBoxToken();
	$.when(updateEntry("rooms", $("#form-box-details").serialize(), box_token)).done(function(data){
		console.log(data);
		$("#save-room-button").blur();
		$("#save-room-button").text(language_tokens.save_changes_feedback);
		$("#save-room-button").switchClass("btn-primary", "btn-success feedback", 200, "easeOutBack");
		setTimeout(function(){
			$("#save-room-button").switchClass("btn-success feedback", "btn-primary", 1000, "easeInQuad")
			$("#save-room-button").text(language_tokens.save_changes);
		}, 1500);
	});
}).on('keyup', '.url-box', function(){
	var src = $(".url-box").val();
	if(src != ''){
		$(".play-url").addClass("disabled");
		$(".play-url").attr("disabled", "disabled");
		$(".play-url").text(language_tokens.searching);
		var compare;
		if(compare){
			clearTimeout(compare);
		}
		compare = setTimeout(function(){
			// try to find playlist ID
			var playreg = new RegExp(/list=([a-z0-9\-\_]+)\&?/i);
			var playres = playreg.exec(src);
			if(playres != null){
				$(".url-box").blur();
				$(".submit-warning").html("<span class='glyphicon glyphicon-ok'></span>"+language_tokens.submit_playlist_link);
				$(".submit-warning").addClass("system-success");
				$(".submit-warning").removeClass("system-warning");
				$(".play-url").removeClass("disabled");
				$(".play-url").removeAttr("disabled");
				$(".play-url").text(language_tokens.submit_link);
			} else {
				// if there's no playlist, try to find video ID
				var reg = new RegExp(/\?v=([a-z0-9\-\_]+)\&?/i);
				var res = reg.exec(src);
				if(res == null || res[1].length != 11){
					var alt = new RegExp(/\.be\/([a-z0-9\-\_]+)\&?/i);
					var res = alt.exec(src);
					if(res != null && res[1].length != 11){
						$(".url-box").blur();
						$(".submit-warning").html("<span class='glyphicon glyphicon-ok'></span>"+language_tokens.submit_video_link);
						$(".submit-warning").addClass("system-success");
						$(".submit-warning").removeClass("system-warning");
						$(".play-url").removeClass("disabled");
						$(".play-url").removeAttr("disabled");
						$(".play-url").text(language_tokens.submit_link);
					} else {
						$(".submit-warning").html("<span class='glyphicon glyphicon-alert'></span> "+language_tokens.submit_no_link);
						$(".submit-warning").removeClass("system-success");
						$(".submit-warning").addClass("system-warning");
					}
				} else {
					$(".url-box").blur();
					$(".submit-warning").html("<span class='glyphicon glyphicon-ok'></span>"+language_tokens.submit_video_link);
					$(".submit-warning").addClass("system-success");
					$(".submit-warning").removeClass("system-warning");
					$(".play-url").removeClass("disabled");
					$(".play-url").removeAttr("disabled");
					$(".play-url").text(language_tokens.submit_link);
				}
			}
		}, 2000);
	} else {
		$(".submit-warning").empty();
	}
}).on('click', '.btn-skip', function(){
	var box_token = getBoxToken();
	getNext(true, box_token);
}).on('click', '.shuffle-playlist', function(){
	var box_token = getBoxToken();
	shufflePlaylist(box_token);
}).on('click', '.toggle-box-state', function(){
	var clicked = $(this);
	var field_name = document.getElementById(clicked.attr("id")).dataset.field;
	var field_value = document.getElementById(clicked.attr("id")).dataset.value;
	var value = {};
	value[field_name] = field_value;
	var box_token = getBoxToken();
	$.when(updateEntry("rooms", $.param(value), box_token)).done(function(){
		clicked.trigger('activate');
	})
}).on('click', '.swap-order', function(){
	var pressed = document.getElementById($(this).attr("id"));
	var m = /(\w*)-(\d*)/.exec(pressed.id);
	var action = m[1], entry_id = m[2], box_token = getBoxToken();
	var current_order = pressed.dataset.order;
	console.log(entry_id, current_order, action, box_token);
	$.post("functions/swap_playlist_order.php", {history_id : entry_id, current_order : current_order, action : action, box_token : box_token}).done(function(data){
		loadPlaylist(box_token, true)
	});
}).on('click', '.transfer-box', function(){
	var box_token = getBoxToken();
	var user_target = $(this).data('user');
	transferBox(box_token, user_target);
})
