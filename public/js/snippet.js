// Get All scripts tags in the file
var scripts = document.getElementsByTagName("script");
// The currently executing include file will always be the last one in the scripts array
var currentScriptTag = scripts[scripts.length-1];
var currentScriptSrcUrl = currentScriptTag.src;

// Extract Snippet Id from Script Src
var currentScriptSrcUrlParts = currentScriptSrcUrl.split('?');
var cucurrentScriptFileUrl = currentScriptSrcUrlParts[0];
var cucurrentScriptParams = currentScriptSrcUrlParts[1];
const urlParams = new URLSearchParams(cucurrentScriptParams);
var snippetId = urlParams.get('id');

// Extract Host URl 
const cucurrentScriptFileUrlParts = new URL(cucurrentScriptFileUrl);
var myHostUrl = cucurrentScriptFileUrlParts['protocol']+'//'+cucurrentScriptFileUrlParts['hostname'];
if ("port" in cucurrentScriptFileUrlParts) {
	myHostUrl = myHostUrl+':'+cucurrentScriptFileUrlParts['port'];
}

// Extract current Page URL path for rules matching..
var currentPageUrl = window.location.href;
const currentPageUrlParts = new URL(currentPageUrl);
var currentPageURLPathName = currentPageUrlParts['pathname'];

var xmlhttp = new XMLHttpRequest();   // new HttpRequest instance
var requestUrl = myHostUrl+'/get_alert_message?snippet_id='+snippetId+'&current_page_path_name='+currentPageURLPathName;

var xhttp = new XMLHttpRequest();
xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    	var response = JSON.parse(this.response);
    	if(response.alert_message) {
			alert(response.alert_message);
    	}
    }
};
xhttp.open("GET", requestUrl, true);
xhttp.send();
