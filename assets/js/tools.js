$(document).ready(function(){
	jQuery.expr[':'].regex = function(elem, index, match) {
		var matchParams = match[3].split(','),
			validLabels = /^(data|css):/,
			attr = {
				method: matchParams[0].match(validLabels) ?
				matchParams[0].split(':')[0] : 'attr',
				property: matchParams.shift().replace(validLabels,'')
			},
			regexFlags = 'ig',
			regex = new RegExp(matchParams.join('').replace(/^s+|s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	}
	menuPopover = false;
	// When user leaves the room, he has to be removed from the box
	if(/(\/follow)/.exec(top.location.pathname) !== null || /(\/create)/.exec(top.location.pathname) !== null){
		document.title = $("legend").text()+" | Berrybox";
	}
	$(window).on('beforeunload', function(event){
		sessionStorage.removeItem("currently-playing");
	}).resize(function(){
		resizeElements();
	});
}).on('click', function(e){ // Simulate closure of popover
	if(menuPopover == true){
		$(".popover-trigger").click();
		menuPopover = false;
	}
}).on('click', '.popover-trigger', function(e){ // Preventing previous event if user clicks on the trigger to actually show the popover
	e.stopPropagation();
	if(menuPopover == true){
		menuPopover = false;
	} else {
		menuPopover = true;
	}
}).on('activate', '.btn-switch', function(){
	// Custom event when a switch button is clicked. It will graphically enable it and disable the paired buttons
	var pair = $(this).data('twin');
	$(this).addClass('btn-disabled');
	$(this).removeClass('disabled');
	$("#"+pair).removeClass('btn-disabled');
	$("#"+pair).addClass('disabled');
})

function getUserLang(){
	return $.get("functions/get_user_lang.php");
}

function removeFeedback(elementId){
	$(elementId).removeClass("has-error");
	$(elementId).removeClass("has-success");
	$(elementId).removeClass("has-warning");
	$(elementId+">.form-control-feedback").remove();
	$(elementId+">.error-message").remove();
}
function applySuccessFeedback(elementId){
	$(elementId).addClass("has-success");
	$(elementId).append("<span class='glyphicon glyphicon-ok form-control-feedback' aria-hidden='true'></span>");
}
function applyErrorFeedback(elementId){
	$(elementId).addClass("has-error");
	$(elementId).append("<span class='glyphicon glyphicon-remove form-control-feedback' aria-hidden='true'></span>");
}
function applyWarningFeedback(elementId){
	$(elementId).addClass("has-warning");
	$(elementId).append("<span class='glyphicon glyphicon-warning-sign form-control-feedback' aria-hidden='true'></span>");
}

// Updates a whole row
function updateEntry(table, values, target_id){
	return $.post("functions/update_entry.php", {table : table, target_id : target_id, values : values});
}

function deleteEntry(table, target_id){
	return $.post("functions/delete_entry.php", {table : table, target_id : target_id});
}

function resizeElements(){
	console.log("resizing");
	// Keeping chat body to a non-overflowing height
	var chat_pos = $("#room-chat").position().top;
	var heading_chat_height = $("#heading-chat").outerHeight();
	var footer_chat_height = $("#footer-chat").outerHeight();
	var body_chat_height = window.innerHeight - (chat_pos + heading_chat_height + footer_chat_height);
	console.log(chat_pos, heading_chat_height, footer_chat_height);
	$("#body-chat").outerHeight(body_chat_height);
}

$.notify.addStyle('berrybox', {
	html: "<div><span data-notify-text/></div>",
	classes: {
		base: {
			"border-right": "5px solid",
			"box-shadow": "0px 2px 10px black",
			"background-color": "#101010",
			"color": "white",
			"font-size": "1.1em",
			"font-style": "italic",
			"font-weight": "600",
			"padding": "15px",
			"width": "300px"
		},
		success : {
			"border-color": "#87CE77"
		},
		warning: {
			"border-color": "#F0AD4E"
		},
		error: {
			"border-color": "#D9534F"
		},
		info: {
			"border-color": "#D08AC3"
		}
	}
})
