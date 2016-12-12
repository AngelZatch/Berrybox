/** Everything regarding the chat **/
// When the document is ready
$(document).ready(function () {

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
	$(".chatbox").val("!w "+user+" ");
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
					if(window.user_power == 2 || window.user_power == 3){
						$(".btn-skip").trigger('click');
					} else {
						$("#body-chat").append("<p class='system-message'><span class='glyphicon glyphicon-minus-sign'></span> "+language_tokens.no_power+"</p>");
					}
					break;

				case 'shuffle':
				case 'random':
					if(window.user_power == 2 || window.user_power == 3){
						$(".shuffle-playlist").trigger('click');
					} else {
						$("#body-chat").append("<p class='system-message'><span class='glyphicon glyphicon-minus-sign'></span> "+language_tokens.no_power+"</p>");
					}
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

function displayMessage(message){
	var formatted_message = "";
	var when = moment(moment.utc(message.timeStamp)).local().format("HH:mm");

	// Emotes
	var split_message = message.content.split(' ');
	for (var j = 0; j < emotes.length; j++){
		for (var k = 0; k < split_message.length; k++){
			if(split_message[k] == emotes[j]){
				var emote_message = split_message[k].replace(split_message[k], "<img src='assets/emotes/"+emotes[j]+".png' class='chat-emote' title='"+emotes[j]+"'>");
				split_message[k] = emote_message;
			}
		}
	}
	message.content = split_message.join(" ");

	// URL in message
	var url_regex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
	var m = url_regex.exec(message.content);
	if(m != null){
		var extracted_url = "<a href='"+m[0]+"' target='_blank'>"+m[0]+"</a>";
		message.content = message.content.replace(url_regex, extracted_url);
	}

	switch(message.scope){
		case "1":
			formatted_message += "<p class='standard-message'>";
			formatted_message += "<span class='message-time'>" + when + "</span> ";
			if(message.authorStatus == 2){ // If the author currently is administrator
				formatted_message += "<span class='chat-icon' title='"+language_tokens.room_admin+"'><img src='assets/berrybox-creator-logo.png'></span> ";
			} else if(message.authorStatus == 3){
				formatted_message += "<span class='chat-icon' title='"+language_tokens.room_mod+"'><img src='assets/berrybox-moderator-logo.png'></span> ";
			}
			if(message.authorGlobalPower == 1){
				formatted_message += "<span class='chat-icon' title='"+language_tokens.staff+"'><img src='assets/berrybox-staff-logo.png'></span> ";
			}
			/*if(message.featured_badge){
				formatted_message += "<span class='chat-icon' title=''><img src='assets/badges/"+message.featured_badge+".png'></span> ";
			}*/
			formatted_message += "<a style='text-decoration:none'><span class='message-author author-linkback' style='color:#" + message.authorColor + ";'>";
			formatted_message += message.author;
			formatted_message += "</span></a>";
			formatted_message += " : " + message.content + "<br/>";
			formatted_message += "</p>";

			break;

		case "2": // System messages viewable only by the creator
			if(user_power == 2){
				formatted_message = "<p class='system-message'>";
				formatted_message += "<span class='glyphicon glyphicon-exclamation-sign'></span>";
				formatted_message += message.content;
				formatted_message += "</p>";
			}
			break;

		case "3": // System messages for moderators
			if(user_power == 2 || user_power == 3){
				formatted_message = "<p class='system-message'>";
				formatted_message += "<span class='glyphicon glyphicon-exclamation-sign'></span>";
				formatted_message += message.content;
				formatted_message += "</p>";
			}
			break;

		case "4": // Global system messages
			formatted_message += "<p class='system-message";
			switch (message.subType) {
				case "1":
					formatted_message += "'><span class='glyphicon glyphicon-info-sign'></span> ";
					break;

				case "2":
					formatted_message += " sm-type-play'><span class='glyphicon glyphicon-play'></span>";
					break;

				case "3":
					formatted_message += " sm-type-skip'><span class='glyphicon glyphicon-step-forward'></span> ";
					break;

				case "4":
					formatted_message += " sm-type-close'><span class='glyphicon glyphicon-remove-circle'></span> ";
					break;

				case "5":
					formatted_message += " sm-type-ignore'><span class='glyphicon glyphicon-info-sign'></span> ";
					break;

				case "6":
					formatted_message += " sm-type-reinstate'><span class='glyphicon glyphicon-info-sign'></span> ";
					break;

				case "7":
					formatted_message += " sm-type-open'><span class='glyphicon glyphicon-play-circle'><span> ";
					break;
			}
			formatted_message += message.content;
			formatted_message += "</p>";
			break;

		case "5": // System messages to one specific user
			if(message.destinationToken == user_token){
				formatted_message += "<p class='system-message system-alert'>";
				formatted_message += "<span class='glyphicon glyphicon-exclamation-sign'></span> ";
				formatted_message += "<span class='message-content'> "+message.content+"</span>";
				formatted_message += "</p>";
			}
			break;

		case "6": // Whisper
			if (message.destinationToken == user_token || message.authorToken == user_token) {
				formatted_message += "<p class='whisper'>";
				// User badge
				formatted_message += "<span class='message-time'>" + when + "</span> ";
				/*if(message.featured_badge){
					formatted_message += "<span class='chat-icon' title=''><img src='assets/badges/"+message.featured_badge+".png'></span> ";
				}*/
				formatted_message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#" + message.authorColor + ";'>";
				formatted_message += message.author;
				formatted_message += "</span></a>";
				formatted_message += "<span class='glyphicon glyphicon-chevron-right'></span> ";
				formatted_message += "<a style='text-decoration:none;'><span class='message-author author-linkback' style='color:#" + message.destinationColor + ";'>";
				formatted_message += message.destination;
				formatted_message += "</span></a> : ";
				formatted_message += message.content;
				formatted_message += "</p>";
			}
			break;
	}
	$("#body-chat").append(formatted_message);
	if (!window.chatHovered) {
		$("#body-chat").scrollTop($("#body-chat")[0].scrollHeight);
	}
}
