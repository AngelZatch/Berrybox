function sendMessage(data){
	var message = $(".chatbox").val();
	var token = data;
	$.post("functions/post_chat.php", {message : message, token : token}).done(function(){
		$(".chatbox").val('');
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
