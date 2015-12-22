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
})

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
