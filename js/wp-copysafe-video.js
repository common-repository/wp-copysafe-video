//  WordPress Copysafe Video
//  Copyright (c) 1998-2022 ArtistScope. All Rights Reserved.
//  www.artistscope.com
//
// The Copysafe Video Player plugin is supported across all Windows since XP
//
// Debugging outputs the generated html into a textbox instead of rendering

// REDIRECTS

var m_szLocation = document.location.href.replace(/&/g,'%26');

// var wpcsv_plugin_url = "/plugins/content/copysafevideo/html/";

var m_szDownloadNo = wpcsv_plugin_url + "download_no.html";
var m_szDownloadBrowser = wpcsv_plugin_url + "download.html?ref=" + m_szLocation;
var m_szDownloadPlugin = wpcsv_plugin_url + "download.html?ref=" + m_szLocation;

// var szDocName = "/images/copysafevideo/VideoPlayerIntro_vid.class";

//===========================
//   DO NOT EDIT BELOW 
//===========================

var m_szAgent = navigator.userAgent.toLowerCase();
var m_szBrowserName = navigator.appName.toLowerCase();
var m_szPlatform = navigator.platform.toLowerCase();
var m_bNetscape = false;
var m_bMicrosoft = false;
var m_szPlugin = "";

// var m_bpASPS = true;
// var m_bpFx = true;
// var m_bpChrome = true;
// var m_bpDebugging = true;

// var m_bpWidth = "600";
// var m_bpHeight = "438";
// var m_bpAllowRemote = "1";

var m_bWin64 = ((m_szPlatform == "win64") || (m_szPlatform.indexOf("win64")!=-1) || (m_szAgent.indexOf("win64")!=-1));
var m_bWin32 = ((m_szPlatform == "win32") || (m_szPlatform.indexOf("win32")!=-1));
var m_bWindows = (m_szAgent.indexOf("windows nt")!=-1);

var m_bASPS = ((m_szAgent.indexOf("artisreader")!=-1) && (m_bpASPS));
var m_bFirefox = ((m_szAgent.indexOf("firefox") != -1) && (m_bpFx));
var m_bChrome = ((m_szAgent.indexOf("chrome") != -1) && !(window.chrome && chrome.webstore && chrome.webstore.install) && (m_bpChrome));

var m_bNetscape = ((m_bASPS) || (m_bChrome) || (m_bFirefox));
var checkPlugin = "";
navigator.plugins.refresh(false);
var szDescription = "CopySafe Video Player Plugin";

var m_bpwatermarked = m_bpwatermarked;

String.prototype.strstr = function(search) {
    var position = this.indexOf(search);
    if (position == -1) {
        /*return false;*/
    }
    return this.slice(position);
};

var x = navigator.userAgent;
var browsername = x.strstr('ArtisBrowser');
var broversion = browsername.split('/');

document.MM_returnValue=true;

if (typeof navigator.plugins[szDescription] == "object") {
	checkPlugin = true;
} 
if(m_szMode == "debug") {
  m_bpDebugging = true;
}

if(broversion[0] == 'ArtisBrowser'  ) { 
    
	if ((m_bWindows) && (m_bMicrosoft)  && broversion[1] >= m_min_Version ) {
		
		if (checkPlugin) {
			
			// hide JavaScript from non-JavaScript browsers
			if ((m_szMode == "licensed") || (m_szMode == "debug")) {
				insertCopysafeVideo();
			}
			else {
				document.writeln("<img src='" + wpcsv_plugin_url + "images/image_placeholder.jpg' border='0' alt='Demo mode'>");
			}
		} else {
			window.location = unescape(m_szDownloadBrowser);
			document.MM_returnValue = false;
		}
	}else if ((m_bWindows) && (m_bNetscape) && broversion[1] >= m_min_Version ) {
		if (checkPlugin) {
			
			// hide JavaScript from non-JavaScript browsers
			if ((m_szMode == "licensed") || (m_szMode == "debug")) {
				insertCopysafeVideo();
			}
			else {
				document.writeln("<img src='" + wpcsv_plugin_url + "images/image_placeholder.jpg' border='0' alt='Demo mode'>");
			}
		} else {
			window.location = unescape(m_szDownloadBrowser);
			document.MM_returnValue = false;
		}
	} else {
		window.location=unescape(m_szDownloadNo);
		document.MM_returnValue=false;
	}
} else {
	
	
	if ((m_bWindows) && (m_bMicrosoft) ) {
		if ((m_szMode == "licensed") || (m_szMode == "debug")) {
				insertCopysafeVideo();
		}
		else {
			
			document.writeln("<img src='" + wpcsv_plugin_url + "images/image_placeholder.jpg' border='0' alt='Demo mode'>");
		}
	
	}else if ((m_bWindows) && (m_bNetscape) ) {
		
			// hide JavaScript from non-JavaScript browsers
			if ((m_szMode == "licensed") || (m_szMode == "debug")) {
				insertCopysafeVideo();
			}
			else {
				
				document.writeln("<img src='" + wpcsv_plugin_url + "images/image_placeholder.jpg' border='0' alt='Demo mode'>");
			}
	} 
	else {
		
		window.location=unescape(m_szDownloadBrowser);
		document.MM_returnValue=false;
	}
}

/*
if ((m_bWindows) && (m_bNetscape) > 0) {
	if (m_bpDebugging) {
		insertCopysafeVideo();
	}
} else {
	window.location = unescape(m_szDownloadNo);
	document.MM_returnValue=false;
}*/
// The copysafe-insert functions

function insertCopysafeVideo() {
	if (m_bpDebugging == true) {
		document.writeln("<textarea rows='27' cols='80'>");
	}
	if (m_bpwatermarked == true ) {
		document.writeln("<embed id='npCopySafeVideo' type='application/x-asvp-video-plugin' src='" + m_szImageFolder + m_szClassName + "' width='" + m_bpWidth + "' height='" + m_bpHeight + "' watermark='"+watermarkstring+"' remote='" + m_bpAllowRemote + "'/>");
	} else {
		document.writeln("<embed id='npCopySafeVideo' type='application/x-asvp-video-plugin' src='" + m_szImageFolder + m_szClassName + "' width='" + m_bpWidth + "' height='" + m_bpHeight + "' remote='" + m_bpAllowRemote + "'/>");
	}
	if (m_bpDebugging == true) {
		document.writeln("</textarea />");
	}
}
