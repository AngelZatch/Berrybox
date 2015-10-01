function createCORSRequest(method, url){
	var xhr = new XMLHttpRequest();
	if("withCredentials" in xhr){
		xhr.open(method, url, true);
	} else if(typeof XDomainRequest != "undefined"){
		xhr = new XDomainRequest();
		xhr.open(method, url);
	} else {
		xhr = null;
	}
	return xhr;
}

function makeCORSRequest(){
	var url = "http://www.youtube.com/watch?v=lMYBhsQ0krw";
	var xhr = createCORSRequest('GET', url);
	if(!xhr){
		throw new Error('CORS not supported');
		return;
	}
	xhr.onload = function(){
		var responseText = xhr.responseText;
		console.log(responseText);
	};
	xhr.onerror = function(){
		console.log('Error');
	}
	xhr.send();
}
