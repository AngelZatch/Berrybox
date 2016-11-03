/** Everything regarding the chat **/
// When the document is ready
$(function () {
}).on('focus', '.chatbox', function () {
	var box_token = getBoxToken();
	// Getting list of users for autocompletion
	getUserList(box_token);
	$(this).keypress(function (event) {
		if (event.which === 13) {
			sendMessage(box_token, 1, null, 'chatbox', '');
		}
	});
}).on('click', '.whisper-action', function(){
	var user = $("#user-card-name").text();
	$(".chatbox").val("/w "+user+" ");
	$(".chatbox").focus();
}).on('click', '#user-card-close', function(){
	$(".user-card").remove();
})

function getUserList(box_token) {
	$.get("functions/get_user_list.php", {
		box_token: box_token
	}).done(function (data) {
		var user_list = JSON.parse(data), autocomplete_list = [], i = 0;
		for (i; i < user_list.length; i++) {
			autocomplete_list.push(user_list[i].pseudo);
		}
		$(".chatbox").textcomplete([{
			match: /(^|\b)(\w{2,})$/,
			search: function (term, callback) {
				callback($.map(autocomplete_list, function (item) {
					return item.indexOf(term) === 0 ? item : null;
				}));
			},
			replace: function (item) {
				return item;
			}
		}]);
	});
}

function fetchEmotes() {
	return $.post("functions/fetch_emotes.php");
}

function sendMessage(box_token, scope, type, message, destination) {
	if (message == 'chatbox' && scope == 1) {
		var fullString = $(".chatbox").val();
		var actionToken = $(".chatbox").val().substr(0, 1);
		if (actionToken == '!') { // Detection de macros
			var action = $(".chatbox").val().substr(1).split(" ");
			switch(action[0]){
				case 'w':
					scope = 6;
					destination = action[1];
					message = "";
					for (var i = 2; i < action.length; i++) {
						message += action[i];
						if (i != action.length - 1) {
							message += " ";
						}
					}
					$(".chatbox").val('');
					$.post("functions/post_chat.php", {
						message: message,
						token: box_token,
						scope: scope,
						destination: destination,
						solveDestination: destination
					});
					break;

				case 'skip':
				case 'next':
					getNext(true, box_token);
					break;

				case 'shuffle':
					shufflePlaylist(box_token);
					break;

				default:
					$("#body-chat").append("<p class='system-message system-alert'>"+language_tokens.invalid_macro+"</p>");
					break;
			}
			$(".chatbox").val('');
		} else {
			var message = $(".chatbox").val();
			$(".chatbox").val('');
			$.post("functions/post_chat.php", {
				message: message,
				token: box_token,
				scope: scope,
				destination: destination
			});
		}
	} else {
		$.post("functions/post_chat.php", {
			message: message,
			token: box_token,
			scope: scope,
			type: type,
			destination: destination
		});
	}
}

function loadChat(box_token, user_power, user_token, emotes, user_lang) {
	if (!window.lastID)
		window.lastID = 0;

	$.get("functions/load_chat.php", {
		box_token: box_token,
		user_lang: user_lang,
		last_message_id: window.lastID
	}).done(function (data) {
		var messages = JSON.parse(data);
		for (var i = 0; i < messages.length; i++) {
			var message_time = moment(moment.utc(messages[i].timeStamp)).local().format("HH:mm");
			if (messages[i].id != window.lastID) {
				window.lastID = messages[i].id;
				// Emotes
				var split_message = messages[i].content.split(' ');
				for (var j = 0; j < emotes.length; j++) {
					for (var k = 0; k < split_message.length; k++) {
						if (split_message[k] == emotes[j]) {
							var emote_message = split_message[k].replace(split_message[k], "<img src='assets/emotes/" + emotes[j] + ".png' class='chat-emote' title='" + emotes[j] + "'>");
							split_message[k] = emote_message;
						}
					}
				}
				messages[i].content = split_message.join(" ");

				// URL in message
				var url_regex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
				var m = url_regex.exec(messages[i].content);
				if (m != null) {
					var extraced_url = "<a href='" + m[0] + "' target='_blank'>" + m[0] + "</a>";
					messages[i].content = messages[i].content.replace(url_regex, extraced_url);
				}

				// Display
				var message = "";
				switch (messages[i].scope) {
					case "6": // Message is a whisper
						if (messages[i].destinationToken == user_token || messages[i].authorToken == user_token) {
							message += "<p class='whisper'>";
							message += "<span class='message-time'>" + message_time + "</span>";
							message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#" + messages[i].authorColor + ";'>";
							message += messages[i].author;
							message += "</span></a>";
							message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
							message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#" + messages[i].destinationColor + ";'>";
							message += messages[i].destination;
							message += "</span></a> : ";
							message += messages[i].content;
							message += "</p>";
						} else {
							var message = ""; // Clear message if whisper has nowhere to go
						}
						break;

					case "5": // System messages only viewable by one specific user
						if (messages[i].destinationToken == user_token) {
							message += "<p class='system-message system-alert'>";
							message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
							message += "<span class='message-content>" + messages[i].content + "</span>";
							message += "</p>";
						} else {
							var message = "";
						}
						break;

					case "4": // Global system messages
						message += "<p class='system-message";
						switch (messages[i].subType) {
							case "1":
								message += "'><span class='glyphicon glyphicon-info-sign'></span> ";
								break;

							case "2":
								message += " sm-type-play'><span class='glyphicon glyphicon-play'></span> ";
								break;

							case "3":
								message += " sm-type-skip'><span class='glyphicon glyphicon-step-forward'></span> ";
								if (user_power != 2)
									synchronize(box_token, user_power);
								break;

							case "4":
								message += " sm-type-close'><span class='glyphicon glyphicon-remove-circle'></span> ";
								break;

							case "5":
								message += " sm-type-ignore'><span class='glyphicon glyphicon-info-sign'></span> ";
								break;

							case "6":
								message += " sm-type-reinstate'><span class='glyphicon glyphicon-info-sign'></span> ";
								break;

							case "7":
								message += " sm-type-open'><span class='glyphicon glyphicon-play-circle'><span> ";
								break;
						}
						message += messages[i].content;
						message += "</p>";
						break;

					case "3": // System messages viewable by the moderators
						if (user_power == 2 || user_power == 3) {
							message += "<p class='system-message'>";
							message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
							message += messages[i].content;
							message += "</p>";
						} else {
							var message = ""; // Clear message
						}
						break;

					case "2": // System message viewable by the creator only
						if (user_power == 2) {
							var message = "<p class='system-message'>";
							message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
							message += messages[i].content;
							message += "</p>";
						} else {
							var message = ""; // Clear message
						}
						break;

					case "1": // Basic chat message
						message += "<p class='standard-message'>";
						message += "<span class='message-time'>" + message_time + "</span> ";
						if (messages[i].status == 2) { // If author is the creator
							message += "<span class='chat-icon' title='"+language_tokens.room_admin+"'><img src='assets/berrybox-creator-logo.png'></span> ";
						} else if (messages[i].status == 3) { // If author is the moderator
							if ((user_power == 2 || user_power == 3) && messages[i].authorToken != user_token) {
								// Needs timeout buttons
								if (user_power == 2) {
									// Needs ban & demote buttons specific to the creator
									message += "<span class='chat-icon' title='"+language_tokens.room_mod+"'><img src='assets/berrybox-moderator-logo.png'></span> ";
								}
							} else {
								message += "<span class='chat-icon' title='"+language_tokens.room_mod+"'><img src='assets/berrybox-moderator-logo.png'></span>";
							}
						} else { // If author is a standard user
							if (user_power == 2 || user_power == 3) {
								// Mod actions
								if (user_power == 2) {
									// Admin actions
								}
							}
						}
						if (messages[i].authorPower == 1) {
							message += "<span class='chat-icon' title='"+language_tokens.staff+"'><img src='assets/berrybox-staff-logo.png'></span> ";
						}
						message += "<a style='text-decoration:none'><span class='message-author author-linkback' style='color:#" + messages[i].authorColor + ";'>";
						message += messages[i].author;
						message += "</span></a>";
						message += " : " + messages[i].content + "<br/>";
						message += "</p>";
						break;

					default:
						break;
				}
				$("#body-chat").append(message);
			} else {
				console.log("Double fetch. Denied");
			}
			if (!window.chatHovered) {
				$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
			}
		}
	})
	// once everything is done, we're restarting the whole thing in the next 1.5 seconds
	setTimeout(loadChat, 1500, box_token, user_power, user_token, emotes, lang);
}
