
function getUrlParameter(sParam)
{
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
    
	for (var i = 0; i < sURLVariables.length; i++) 
	{
		var sParameterName = sURLVariables[i].split('=');
        
		if (sParameterName[0] == sParam) 
		{
			return sParameterName[1];
		}
	}
}

function getUrlWithoutHash(url){
	if(url=='' || url===undefined){
		url = window.location.href;
	}
	url = url.replace(/#.*?$/, "");
	return url;
}

function getUrlWithoutHashAndArgs(url){
	if(url=='' || url===undefined){
		url = window.location.href;
	}
	url = url.replace(/#.*?$/, "");
	url = url.replace(/\?.*?$/, "");
	return url;
}

function getThisUrlToReload(specialurl) {
	var url = window.location.href;
    
	if(specialurl && specialurl !== undefined && specialurl != ''){
		url = specialurl;
	} else {
		url = url.replace(/\?message=.+&/, "?");
		url = url.replace(/\?message=.+$/, "?");
		url = url.replace(/&message=.+&/, "&");
		url = url.replace(/&message=.+$/, "&");
	}
	return getUrlWithoutHash(url);
}
