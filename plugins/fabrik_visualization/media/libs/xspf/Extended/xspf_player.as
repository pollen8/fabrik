// TBB: 60 seconds. If we take longer than 60 seconds to switch pages, assume the user 
// has started a new session on the site and autoresume is inappropriate.
resumeTimeout = 60 * 1000;

playerVersion = "4.8";
stop();
update._visible = false;
/*
Copyright (c) 2005, Fabricio Zuardi
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the author nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
Revisions by Lacy Morrow
"Resume play when following link" feature added by Thomas Boutell
*/
//DO NOT REMOVE THE FOLLOWING CODE//
function playerLoad() {
	__com_mochibot__("e530e336", this, 10301);
	fillColor();
	fillDefaults();
	fillOther();
	firstImage();
	resizeUI();
}
function __com_mochibot__(swfid, mc, lv) {
	var x, g, s, fv, sb, u, res, mb, mbc;
	mb = '__mochibot__';
	mbc = "mochibot.com";
	g = _global ? _global : _level0._root;
	if (g[mb+swfid]) {
		return g[mb+swfid];
	}
	s = System.security;
	x = mc._root['getSWFVersion'];
	fv = x ? mc.getSWFVersion() : (_global ? 6 : 5);
	if (!s) {
		s = {};
	}
	sb = s['sandboxType'];
	if (sb == "localWithFile") {
		return null;
	}
	x = s['allowDomain'];
	if (x) {
		s.allowDomain(mbc);
	}
	x = s['allowInsecureDomain'];
	if (x) {
		s.allowInsecureDomain(mbc);
	}
	u = "http://"+mbc+"/my/core.swf?mv=5&fv="+fv+"&v="+escape(getVersion())+"&swfid="+escape(swfid)+"&l="+lv+"&f="+mc+(sb ? "&sb="+sb : "");
	lv = (fv>6) ? mc.getNextHighestDepth() : g[mb+"level"] ? g[mb+"level"]+1 : lv;
	g[mb+"level"] = lv;
	if (fv == 5) {
		res = "_level"+lv;
		if (!eval(res)) {
			loadMovieNum(u, lv);
		}
	} else {
		res = mc.createEmptyMovieClip(mb+swfid, lv);
		res.loadMovie(u);
	}
	return res;
}
//
function fillColor() {
	if (colour) {
		color = colour;
	}
	if (color.length == 6) {
		color = color.toUpperCase();
		if (!alpha) {
			alpha = 10;
		}
		timeColor = new Color(time_mc.time_txt);
		settingsColor = new Color(settings_mc);
		buttonsColor = new Color(buttons_mc);
		playColor = new Color(play_mc);
		scrollbarColor = new Color(scrollbar_mc);
		playlistColor = new Color(playlist_mc);
		volumeColor = new Color(volume_mc);
		coverloadColor = new Color(cover_mc.load_bar_mc);
		txtColor = new Color(imageloadMC);
		coverbordColor = new Color(cover_mc.border_mc);
		trackColor = new Color(track_display_mc);
		bgColor = new Color(bg_mc);
		hexChars = "0123456789ABCDEF";
		red = "0x"+color.charAt(0)+color.charAt(1);
		grn = "0x"+color.charAt(2)+color.charAt(3);
		blu = "0x"+color.charAt(4)+color.charAt(5);
		var myColorTransform:Object = {ra:100, rb:red, ga:100, gb:grn, ba:100, bb:blu, aa:alpha, ab:50};
		timeColor.setTransform(myColorTransform);
		settingsColor.setTransform(myColorTransform);
		scrollbarColor.setTransform(myColorTransform);
		playlistColor.setTransform(myColorTransform);
		txtColor.setTransform(myColorTransform);
		coverloadColor.setTransform(myColorTransform);
		coverbordColor.setTransform(myColorTransform);
		volumeColor.setTransform(myColorTransform);
		trackColor.setTransform(myColorTransform);
		bgColor.setTransform(myColorTransform);
		buttonsColor.setTransform(myColorTransform);
		playColor.setTransform(myColorTransform);
	}
}
//autoresume=true
//autoplay=true                                                        
//alphabetize=true
//repeat_playlist = true;
//playlist_size = 3;
//player_title = "customizeable title test"
//song_url = "http://downloads.betterpropaganda.com/music/Imperial_Teen-Ivanka_128.mp3";
//playlist_url = "http://webjay.org/by/hideout/moviequotes.xspf";
//playlist_url = "http://hideout.com.br/shows/radio-test.xspf";
//song_title = "Imperial Teen - Ivanka";
//info_url = "http://geekkid.net"
//playlist_url= "http://hideout.com.br/tests/hideout2325.xspf"
//constants http://webjay.org/by/hideout/allshows.xspf http://geekkid.net/jukebox/xplaylist.xml
//playlists has priority over songs, if a playlist_url parameter is found the song_url is ignored
//default playlist if none is passed through query string
DEFAULT_WELCOME_MSG = "Jukebox Player";

//TBB
local_data = null;

function fillDefaults() {
	LOADING_PLAYLIST_MSG = "Loading...";
	DEFAULT_LOADED_PLAYLIST_MSG = "Loaded - click to start";
	DEFAULT_MAIN_URL = "http://geekkid.net";
	DEFAULT_PLAYLIST_URL = "http://geekkid.net/jukebox/xplaylist.xml";
	if (!playlist_url) {
		if (!song_url) {
			playlist_url = DEFAULT_PLAYLIST_URL;
		} else {
			single_music_playlist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><playlist version=\"1\" xmlns = \"http://xspf.org/ns/0/\"><trackList>";
			single_music_playlist += "<track><location>"+song_url+"</location><annotation>"+song_title+"</annotation></track>";
			single_music_playlist += "</trackList></playlist>";
		}
	}
	if (!mainurl) {
		mainurl = DEFAULT_MAIN_URL;
	}
	if (!load_message) {
		load_message = DEFAULT_LOADED_PLAYLIST_MSG;
	}
	if (repeat == 1 || repeat == "true" || repeat == true) {
		repeat = 1;
		settings_mc.repeat_btn.gotoAndStop(1);
	} else {
		repeat = 0;
		settings_mc.repeat_btn.gotoAndStop(2);
	}
	if (shuffle == 1 || shuffle == "true" || shuffle == true) {
		shuffle = 1;
		settings_mc.shuffle_btn.gotoAndStop(1);
	} else {
		shuffle = 0;
		settings_mc.shuffle_btn.gotoAndStop(2);
	
	}
	//TBB
	if (autoresume) {
		local_data = SharedObject.getLocal("xspf_player_data");
		trace(local_data);
		local_data_good = true;
		if (!local_data.data.is_set) {
			local_data_good = false;
		} 
		// One-minute limit for autoresume. Prevents autoresuming from occurring
		// when you come back to the site as part of an entirely separate
		// visit, which doesn't feel natural. Autoresume is meant as a way
		// to bridge clicks on links within the site.
		when = new Date();
		now = when.getTime();
		if (local_data_good) {
			trace("data good going into timeout check");
		}
		if (now - local_data.data.stopped_at > resumeTimeout) {
			local_data_good = false;				
			trace(now);
			trace(local_data.data.stopped_at);
			trace("old resume data age in msec: " + (now - local_data.data.stopped_at));
			trace("resume data is old, assume new session");
		} else {
			if (!playlist_url) {
				if (song_url != local_data.data.song_url) {
					local_data_good = false;
				}
			} else if (playlist_url != local_data.data.playlist_url) {
				local_data_good = false;
			}
		}
		if (local_data_good) {
			start_track = local_data.data.stop_track;
			start_time = local_data.data.stop_time;
			volume_level = local_data.data.volume_level;
			this.volume_bar_mc._xscale = volume_level;
			volume_mc.volume_bar_mc._xscale = volume_level;
			trace("autoresumed " + start_track + "," + start_time);
			autoresumed = true;
			_root.autoplay = "true";
			_root.autoload = "true";

			songClick = 1;
		}
	}
	if (!start_track) {
		start_track = 1;
	} else {
		songClick = 1;
	}
	if (!volume_level) {
		volume_level = 100;
	}
	if (!buffer) {
		buffer = 5;
	}
	if (timedisplay<1 or timedisplay>4) {
		timedisplay = 1;
	}
	if (timedisplay == 1) {
		timeClick = 1;
		timedisplay = 2;
	}
	track_index = start_track-1;
	playlist_xml = new XML();
	playlist_xml.ignoreWhite = true;
	playlist_xml.onLoad = playlistLoaded;
}
//variables initialization                                                                                                                                    
playlist_array = [];
playlist_mc.track_count = 0;
pause_position = 0;
mouseListener = new Object();
imageloadMC._visible = false;
settings_mc.info_btn._visible = false;
settings_mc.buy_btn._visible = false;
time_mc._visible = false;
var makeConn_nc:NetConnection = new NetConnection();
makeConn_nc.connect(null);
var net_ns:NetStream = new NetStream(makeConn_nc);
net_ns.setBufferTime(buffer);
net_ns.onMetaData = function(obj) {
	totalTime = obj.duration;
};
this.createEmptyMovieClip("vidsnd", 0);
vidsnd.attachAudio(net_ns);
vidaudio = new Sound(vidsnd);
video_mc.attachVideo(net_ns);
mysound = new Sound(this);
xmlData = new XML();
xmlData.ignoreWhite = true;
xmlData.onLoad = loadimage;
playlist_listener = new Object();
playlist_list.addEventListener("change", playlist_listener);
//play_btn.onPress = playTrack;
//functions
//xml parser
function playlistLoaded(success) {
	if (success) {
		type = "";
		var root_node = this.firstChild;
		for (var node = root_node.firstChild; node != null; node=node.nextSibling) {
			if (node.nodeName == "trackList") {
				//tracks
				var tracks_array = [];
				for (var track_node = node.firstChild; track_node != null; track_node=track_node.nextSibling) {
					var track_obj = new Object();
					//track attributes
					for (var track_child = track_node.firstChild; track_child != null; track_child=track_child.nextSibling) {
						if (track_child.nodeName == "location") {
							track_obj.location = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "image") {
							track_obj.image = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "creator") {
							track_obj.creator = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "title") {
							track_obj.title = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "annotation") {
							track_obj.annotation = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "meta") {
							track_obj.type = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "info" and !infourl) {
							track_obj.info = track_child.firstChild.nodeValue;
						} else if (infourl) {
							track_obj.info = infourl;
						}
						if (track_child.nodeName == "purchase") {
							track_obj.purchase = track_child.firstChild.nodeValue;
						}
					}
					if (!track_obj.image && main_image) {
						track_obj.image = main_image;
					}
					if (!track_obj.info && infourl) {
						track_obj.info;
					}
					track_obj.label = "";
					if (track_obj.title) {
						if (track_obj.creator) {
							track_obj.label += track_obj.creator+' - ';
						}
						track_obj.label += track_obj.title;
					} else if (track_obj.creator) {
						track_obj.label += track_obj.creator;
					} else if (track_obj.annotation) {
						track_obj.label += track_obj.annotation;
					} else {
						track_obj.label += " - ";
					}
					tracks_array.push(track_obj);
				}
			}
		}
		if (alphabetize == 1 || alphabetize == "true" || alphabetize == true) {
			tracks_array.sortOn(["label"], 17);
		}
		for (i=0; i<tracks_array.length; i++) {
			tracks_array[i].label = (i+1)+". "+tracks_array[i].label;
			addTrack(tracks_array[i].label);
		}
		playlist_array = tracks_array;
		if (!playlist_size) {
			playlist_size = playlist_array.length;
		}
		if (autoplay == true || autoplay == 1 || autoplay == "true") {
			mediaCheck();
			if (autoresume && local_data_good && local_data.data.stopped) {
				trace("autoresume has stopped flag");
				stopTrack();
			}
			if (autoresume && local_data_good && local_data.data.paused) {
				trace("autoresume has paused flag");
				pause_position = local_data.data.pause_position;
				if (play_type == 1) {
					mysound.stop();
				} else if (play_type == 2) {
					net_ns.seek(local_data.data.pause_position / 1000);
					net_ns.pause(true);
				}
				play_mc.gotoAndStop(1);
			}
		} else {
			track_display_mc.display_txt.text = load_message;
			if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
				track_display_mc.onEnterFrame = scrollTitle;
			} else {
				track_display_mc.onEnterFrame = null;
				track_display_mc.display_txt._x = 0;
			}
		}
	} else {
		annotation_txt.text = "Error opening "+playlist_url;
	}
}
function loadimage(loaded) {
	if (loaded) {
		XMLimageURL = this.firstChild.childNodes[1].childNodes[3].childNodes[2].firstChild.firstChild.nodeValue;
		if (XMLimageURL) {
			cover_mc.content_mc.createEmptyMovieClip("photo", track_index);
			cover_mc.content_mc["photo"].loadMovie(XMLimageURL);
			imageloadMC._visible = false;
		} else {
			imageloadMC.text = "NO IMAGE";
			imageloadMC._visible = true;
		}
	}
}
playlist_listener.change = function(eventObject) {
	annotation_txt.text = playlist_list.selectedItem.annotation;
	location_txt.text = playlist_list.selectedItem.location;
};
function mediaCheck() {
	if (timedisplay) {
		clearInterval(timeInterval);
		time_mc.time_txt.text = "0:00";
	}
	if (shuffle == 1 and repeat != 1 and songClick != 1) {
		track_index = random(playlist_mc.track_count);
	}
	if (playlist_array[track_index].type) {
		if (playlist_array[track_index].type == "audio") {
			loadTrack();
		} else if (playlist_array[track_index].type == "video") {
			loadVideo();
		} else if (playlist_array[track_index].type == "playlist") {
			mysound.stop();
			mysound.start();
			mysound.stop();
			net_ns.close();
			video_mc.clear();
			playlist_url = playlist_array[track_index].location;
			for (i=0; i<playlist_mc.track_count; ++i) {
				removeMovieClip(playlist_mc.tracks_mc["track_"+i+"_mc"]);
			}
			playlist_mc.track_count = 0;
			playlist_size = 0;
			track_index = 0;
			autoload = "true";
			autoplay = "true";
			loadPlaylist();
			return (0);
		} else if (playlist_array[track_index].type == "link") {
			_root.getURL(playlist_array[track_index].location, "_blank");
		} else {
			noType();
		}
	} else {
		noType();
	}
}
function noType() {
	if (playlist_array[track_index].location.indexOf(".mp3") != -1) {
		loadTrack();
	} else if (playlist_array[track_index].location.indexOf(".flv") != -1 or playlist_array[track_index].location.indexOf(".swf") != -1) {
		loadVideo();
	} else if (playlist_array[track_index].location.indexOf(".xspf") != -1 or playlist_array[track_index].location.indexOf(".xml") != -1) {
		mysound.stop();
		mysound.start();
		mysound.stop();
		net_ns.close();
		video_mc.clear();
		playlist_url = playlist_array[track_index].location;
		for (i=0; i<playlist_mc.track_count; ++i) {
			removeMovieClip(playlist_mc.tracks_mc["track_"+i+"_mc"]);
		}
		playlist_mc.track_count = 0;
		playlist_size = 0;
		track_index = 0;
		autoload = "true";
		autoplay = "true";
		loadPlaylist();
		return (0);
	} else if (gotoany == 1 || gotoany == "true" || gotoany == true) {
		_root.getURL(playlist_array[track_index].location, "_blank");
	} else {
		loadTrack();
	}
}
function loadVideo() {
	play_type = 2;
	playin = 1;
	net_ns.close();
	video_mc.clear();
	mysound.stop();
	mysound.start();
	mysound.stop();
	video_mc._alpha = 0;
	songClick = 0;
	playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(2);
	start_btn_mc.start_btn._visible = false;
	track_display_mc.display_txt.text = playlist_array[track_index].label;
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	cover_mc.content_mc["photo"].removeMovieClip();
	imageloadMC.text = "LOADING";
	imageloadMC._visible = true;
	play_mc.gotoAndStop(2);
	//image from playlist
	if (playlist_array[track_index].info != undefined) {
		settings_mc.info_btn._visible = true;
		settings_mc.info_btn.onPress = function() {
			getURL(playlist_array[track_index].info, "_blank");
		};
	} else {
		settings_mc.info_btn._visible = false;
	}
	if (playlist_array[track_index].purchase != undefined) {
		settings_mc.buy_btn._visible = true;
		settings_mc.buy_btn.onPress = function() {
			getURL(playlist_array[track_index].purchase, "_blank");
		};
	} else {
		settings_mc.buy_btn._visible = false;
	}
	//Connect to Video instance
	net_ns.play(playlist_array[track_index].location);
	if (timedisplay) {
		timeInterval = setInterval(timeDisplay, 200);
	}
	// TBB
	if (autoresume) {
		trace("Setting resume interval");
		clearInterval(resumeInterval);
		resumeInterval = setInterval(resumeCheck, 100);
	}
	if (statsurl) {
		sendstats();
	}
	_root.onEnterFrame = function() {
		video_mc._alpha += (100-video_mc._alpha)/3;
		ns_sound.setVolume(this.volume_level);
		track_display_mc.loader_mc.load_bar_mc._xscale = load_percent;
		if (playin) {
			var time_percent = (net_ns.time/totalTime)*100;
			track_display_mc.loader_mc.time_bar_mc.time_count._xscale = time_percent;
		}
		if (totalTime<net_ns.time) {
			stopTrack();
		}
		if (no_continue != 1 && no_continue != "true" && no_continue != true && net_ns.time == totalTime) {
			nextTrack();
		}
	};
	// TBB
	if (start_time > 0) {
		jumpToTime(start_time);
		start_time = 0;
	}
}
function loadTrack() {
	play_type = 1;
	net_ns.close();
	mysound.stop();
	mysound.start();
	mysound.stop();
	songClick = 0;
	playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(2);
	start_btn_mc.start_btn._visible = false;
	track_display_mc.display_txt.text = playlist_array[track_index].label;
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	cover_mc.content_mc["photo"].removeMovieClip();
	// TBB: when you start loading a streaming sound it starts playing, period. You can't
	// have playback start paused. Solution: kill the volume until after the seek.
	if (start_time > 0) {
		mysound.setVolume(0);
	}
	mysound.loadSound(playlist_array[track_index].location, true);
	imageloadMC.text = "LOADING";
	imageloadMC._visible = true;
	play_mc.gotoAndStop(2);
	//image from playlist
	if (playlist_array[track_index].image != undefined) {
		cover_mc.content_mc.createEmptyMovieClip("photo", track_index);
		cover_mc.content_mc["photo"].loadMovie(playlist_array[track_index].image);
	} else {
		xmlData.load("http://webservices.amazon.com/onca/xml?Service=AWSECommerceService&Operation=ItemSearch&AWSAccessKeyId=0FHVSFEH0JNK8XSM7382&SearchIndex=Music&Artist="+playlist_array[track_index].creator+"&ResponseGroup=Images");
	}
	if (playlist_array[track_index].info != undefined) {
		settings_mc.info_btn._visible = true;
		settings_mc.info_btn.onPress = cover_mc.content_mc.onPress=function () {
			getURL(playlist_array[track_index].info, "_blank");
		};
	} else {
		settings_mc.info_btn._visible = false;
	}
	if (playlist_array[track_index].purchase != undefined) {
		settings_mc.buy_btn._visible = true;
		settings_mc.buy_btn.onPress = function() {
			getURL(playlist_array[track_index].purchase, "_blank");
		};
	} else {
		settings_mc.buy_btn._visible = false;
	}
	if (timedisplay) {
		timeInterval = setInterval(timeDisplay, 200);
	}
	// TBB
	if (autoresume) {
		trace("Setting resume interval");
		clearInterval(resumeInterval);
		resumeInterval = setInterval(resumeCheck, 100);
	}
	if (statsurl) {
		sendStats();
	}
	_root.onEnterFrame = function() {
		video_mc._alpha -= (video_mc._alpha)/3;
		if (video_mc._alpha<1) {
			video_mc.clear();
		}
		// TBB: we don't do this while waiting for the initial seek of a resume operation
		// because it would play sound from the wrong part of the song
		if (!start_time) {
			mysound.setVolume(this.volume_level);
		}
		var load_percent = (mysound.getBytesLoaded()/mysound.getBytesTotal())*100;
		track_display_mc.loader_mc.load_bar_mc._xscale = load_percent;
		var time_percent = (mysound.position/mysound.duration)*100;
		track_display_mc.loader_mc.time_bar_mc.time_count._xscale = time_percent;
		var image_load_percent = (cover_mc.content_mc["photo"].getBytesLoaded()/cover_mc.content_mc["photo"].getBytesTotal())*100;
		cover_mc.load_bar_mc._yscale = image_load_percent;
		if ((cover_mc.content_mc["photo"].getBytesLoaded()>4) && (image_load_percent == 100)) {
			//image loaded
			//make image fit
			imageloadMC._visible = false;
			cover_mc.content_mc["photo"]._width = cover_mc.load_bar_mc._width;
			cover_mc.content_mc["photo"]._height = cover_mc.load_bar_mc._height;
		}
	};
	// TBB
	if (start_time > 0) {
		jumpToTime(start_time);
		start_time = 0;
	}
}
settings_mc.info_btn.onRollOver = function() {
	holderText = track_display_mc.display_txt.text;
	track_display_mc.display_txt.text = "get info";
};
settings_mc.buy_btn.onRollOver = function() {
	holderText = track_display_mc.display_txt.text;
	track_display_mc.display_txt.text = "purchase";
};
settings_mc.shuffle_btn.onRollOver = function() {
	holderText = track_display_mc.display_txt.text;
	track_display_mc.display_txt.text = "shuffle";
};
settings_mc.repeat_btn.onRollOver = function() {
	holderText = track_display_mc.display_txt.text;
	track_display_mc.display_txt.text = "repeat";
};
settings_mc.repeat_btn.onRollOut = settings_mc.shuffle_btn.onRollOut=settings_mc.info_btn.onRollOut=settings_mc.buy_btn.onRollOver=function () {
	track_display_mc.display_txt.text = holderText;
};
buttons_mc.stop_btn.onRelease = stopTrack;
play_mc.play_btn.onRelease = playTrack;
buttons_mc.next_btn.onRelease = nextTrack;
buttons_mc.prev_btn.onRelease = prevTrack;
settings_mc.shuffle_btn.onRelease = shuffleClick;
settings_mc.repeat_btn.onRelease = repeatClick;
if (no_continue != 1 && no_continue != true && no_continue != "true") {
	mysound.onSoundComplete = nextTrack;
}
track_display_mc.loader_mc.time_bar_mc.useHandCursor = false;
track_display_mc.loader_mc.time_bar_mc.onPress = timeChange;
track_display_mc.loader_mc.time_bar_mc.onRelease = track_display_mc.loader_mc.time_bar_mc.onReleaseOutside=function () {
	this._parent.onMouseMove = this._parent.onMouseUp=null;
};
volume_mc.volume_btn.useHandCursor = false;
volume_mc.volume_btn.onPress = volumeChange;
volume_mc.volume_btn.onRelease = volume_mc.volume_btn.onReleaseOutside=function () {
	this._parent.onMouseMove = null;
};

// TBB: part of autoresume logic

resume_time = 0;

function jumpToTimeAgain()
{
	clearInterval(jumpToTimeInterval);
	jumpToTime(resume_time);
}

function jumpToTime(when)
{
	if (play_mc._currentframe == 2) {
		if (play_type == 1) {
			if (mysound.duration == 0) {
				// We're not ready yet. Allow time for the headers of the
				// MP3 to be reopened
				resume_time = when;
				jumpToTimeInterval = setInterval(jumpToTimeAgain, 100);
				return;
			}
			// Often appears to be zero but the jump works fine
			// total = mysound.duration / 1000;
			// if (when > total) {
			//	when = total;
			// }
			mysound.start(when);
			mysound.setVolume(this.volume_level);
		} else if (play_type == 2) {
			total = totalTime;
			if (when > total) {
				when = total;
			}
			net_ns.seek(when);
		}
	} else {
	}
	track_display_mc.loader_mc.time_bar_mc.time_count._xscale = 
 		((when / mysound.duration) * 1000) * 100; 
}

function timeChange() {
	if (play_mc._currentframe == 2) {
		if (play_type == 1) {
			var percent = (this._parent._xmouse/this._width);
			// TBB: just noticing that it's called a percentage here but it's really a proportion (0.0-1.0). 
			if (percent>100) {
				percent = 100;
			}
			if (percent<0) {
				percent = 0;
			}
			timePlace = (mysound.duration*percent)/1000;
			mysound.start(timePlace);
			this._parent.onMouseMove = function() {
				var percent = (this._parent._xmouse/this._width);
				if (percent>100) {
					percent = 100;
				}
				if (percent<0) {
					percent = 0;
				}
				timePlace = (mysound.duration*percent)/1000;
				mysound.start(timePlace);
			};
		} else if (play_type == 2) {
			playin = 0;
			net_ns.pause(true);
			var percent = (this._parent._xmouse/this._width);
			// TBB: just noticing that it's called a percentage here but it's really a proportion (0.0-1.0). 
			if (percent>100) {
				percent = 100;
			}
			if (percent<0) {
				percent = 0;
			}
			track_display_mc.loader_mc.time_bar_mc.time_count._xscale = percent*100;
			timePlace = (totalTime*percent);
			this._parent.onMouseMove = function() {
				playin = 0;
				net_ns.pause(true);
				var percent = (this._parent._xmouse/this._width);
				if (percent>100) {
					percent = 100;
				}
				if (percent<0) {
					percent = 0;
				}
				track_display_mc.loader_mc.time_bar_mc.time_count._xscale = percent*100;
				timePlace = (totalTime*percent);
			};
			this._parent.onMouseUp = function() {
				net_ns.seek(timePlace);
				net_ns.pause(false);
				playin = 1;
			};
		}
	}
}
function volumeChange() {
	this._parent.onMouseMove = function() {
		var percent = (this._xmouse/this._width)*100;
		if (percent>100) {
			percent = 100;
		}
		if (percent<0) {
			percent = 0;
		}
		this.volume_bar_mc._xscale = percent;
		this._parent.volume_level = percent;
		mysound.setVolume(percent);
		vidaudio.setVolume(percent);
	};
}

seenResume = false;

function resumeCheck() {
	// TBB: there is no reliable "atexit" - like feature in Flash,
	// so we have to make a note of our position often if autoresume
	// is going to work. This is cheap because Flash doesn't actually
	// write the shared ojbect to disk relentlessly - it does that
	// on exit.
	if (autoresume) {
		local_data.data.playlist_url = playlist_url;
		local_data.data.song_url = song_url;
		local_data.data.stop_track = track_index + 1;
		var when;
		if (play_type == 1) {
			when = mysound.position / 1000;
		} else if (play_type == 2) {
			when = net_ns.time;
		}
		local_data.data.stop_time = when;
		local_data.data.volume_level = volume_level;
		when = new Date();
		local_data.data.stopped_at = when.getTime();
		local_data.data.is_set = true;
		if (!seenResume) {
			seenResume = true;
		}
	}
}

function timeDisplay() {
	if (timedisplay == 2) {
		//countup
		if (play_type == 1) {
			time = mysound.position/1000;
		}
		if (play_type == 2) {
			time = net_ns.time;
		}
	} else if (timedisplay == 3) {
		//countdown
		if (play_type == 1) {
			time = (mysound.duration-mysound.position)/1000;
		}
		if (play_type == 2) {
			time = (totalTime-net_ns.time);
		}
	} else if (timedisplay == 4) {
		//total time
		if (play_type == 1) {
			time = mysound.duration/1000;
		}
		if (play_type == 2) {
			time = totalTime;
		}
	} else {
		min = "0";
		sec = "00";
	}
	min = Math.floor(time/60);
	sec = Math.floor(time%60);
	sec = (sec<10) ? "0"+sec : sec;
	time_mc.time_txt.text = min+":"+sec;
}
function sendStats() {
	trace("sent...");
	playSong = playlist_array[track_index].sendinfo;
	annotation = playlist_array[track_index].annotation;
	loadVariables(statsurl, this, "POST");
	this.onData = function() {
		trace(output);
		trace("recieved!");
	};
}
function stopTrack() {
	// TBB
	if (autoresume) {
		clearInterval(resumeInterval);
		local_data.data.stopped = true;
	}

	net_ns.pause(true);
	net_ns.seek(0);
	mysound.stop();
	play_mc.gotoAndStop(1);
	mysound.stop();
	mysound.start();
	mysound.stop();
	_root.pause_position = 0;
	if (timedisplay) {
		clearInterval(timeInterval);
		time_mc.time_txt.text = "0:00";
	}
}
function playTrack() {
	if (play_mc._currentframe == 1) {
		//play

		// TBB
		if (autoresume) {
			local_data.data.paused = false;
			local_data.data.stopped = false;
		}
		
		if (play_type == 1) {
			mysound.stop();
			mysound.start(int((pause_position)/1000), 1);
		} else if (play_type == 2) {
			net_ns.pause(false);
		}
		play_mc.gotoAndStop(2);
		if (timedisplay) {
			timeInterval = setInterval(timeDisplay, 200);
		}
		if (autoresume) {
			alert("setting resumeCheck");
			resumeInterval = setInterval(resumeCheck, 100);
		}
	} else if (play_mc._currentframe == 2) {
		if (play_type == 1) {
			pause_position = mysound.position;
			mysound.stop();
			// TBB
			if (autoresume) {
				local_data.data.paused = true;
				local_data.data.pause_position = pause_position;
			}
		} else if (play_type == 2) {
			net_ns.pause(true);
			// TBB
			if (autoresume) {
				local_data.data.paused = true;
				// Be consistent, always store this in milliseconds
				local_data.data.pause_position = net_ns.time * 1000;
			}
		}
		play_mc.gotoAndStop(1);
	}
}

// TBB: cleaned this up, fixed bugs
function nextTrack() {
	playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(1);
	var at_end = false;
	if (shuffle != 1) {
		if (track_index<playlist_size-1) {
			track_index++;
		} else if (repeat_playlist) {
			trace("repeat_playlist was " + repeat_playlist);
			track_index = 0;
		} else {
			trace("at_end set");
			at_end = true;
		}
	}
	trace("at_end is " + at_end + " and track_index is " + track_index);
	last_track_index = track_index;
	playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(1);
	// TBB: repeating the last track forever when repeat is not set is a bug.
	if (at_end) {
		stopTrack();
		return;
	}
	mediaCheck();
}

function prevTrack() {
	playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(1);
	if (shuffle != 1 and repeat != 1) {
		if (track_index>0) {
			track_index--;
		}
	}
	last_track_index = track_index;
	playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(1);
	mediaCheck();
}
function scrollTitle() {
	track_display_mc.display_txt._x -= 5;
	if (track_display_mc.display_txt._x+track_display_mc.display_txt._width<0) {
		track_display_mc.display_txt._x = track_display_mc.mask_mc._width;
	}
}
function resizeUI() {
	bg_mc._width = Stage.width;
	track_display_mc.loader_mc._width = Stage.width-track_display_mc._x-3;
	if (timedisplay) {
		track_display_mc.mask_mc._width = track_display_mc.loader_mc._width-53;
	} else {
		track_display_mc.mask_mc._width = track_display_mc.loader_mc._width-26;
	}
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	volume_mc._x = Stage.width-22;
	start_btn_mc._xscale = Stage.width;
	//playlist area tinier than the album cover
	if (Stage.width<2.5*cover_mc._width) {
		//
		if (Stage.height>2.5*cover_mc._height) {
			//send album cover to bottom
			cover_mc._y = Stage.height-cover_mc._height-2-info_mc._height-2;
			info_mc._y = Stage.height-info_mc._height-2;
			var covervisible = 1;
		} else {
			var covervisible = 0;
			//hide album cover
			cover_mc._y = Stage.height;
		}
		//send playlist to left
		playlist_mc._x = cover_mc._x;
		scrollbar_mc.bg_mc._height = Stage.height-(19+(cover_mc._height+info_mc._height+4)*covervisible);
		playlist_mc.bg_mc._height = Stage.height-(19+(cover_mc._height+info_mc._height+4)*covervisible);
		playlist_mc.mask_mc._height = Stage.height-(23+(cover_mc._height+info_mc._height+4)*covervisible);
	} else {
		cover_mc._y = 17;
		info_mc._y = 153;
		playlist_mc._x = 138;
		scrollbar_mc.bg_mc._height = Stage.height-19;
		//-18 for bottom space
		playlist_mc.bg_mc._height = Stage.height-19;
		playlist_mc.mask_mc._height = Stage.height-23;
	}
	scrollbar_mc._x = Stage.width-12;
	playlist_mc.mask_mc._width = Stage.width-(playlist_mc._x+19);
	playlist_mc.bg_mc._width = Stage.width-(playlist_mc._x+14);
	settings_mc._y = Stage.height-42;
	time_mc._x = Stage.width-52;
	update._x = Stage.width-32;
	update._y = Stage.height-32;
}
function addTrack(track_label) {
	playlist_mc.tracks_mc.attachMovie("track_item", "track_"+playlist_mc.track_count+"_mc", playlist_mc.track_count);
	playlist_mc.tracks_mc["track_"+playlist_mc.track_count+"_mc"]._y += playlist_mc.track_count*14;
	playlist_mc.tracks_mc["track_"+playlist_mc.track_count+"_mc"].display_txt.autoSize = "left";
	playlist_mc.tracks_mc["track_"+playlist_mc.track_count+"_mc"].display_txt.text = track_label;
	playlist_mc.tracks_mc["track_"+playlist_mc.track_count+"_mc"].bg_mc.index = playlist_mc.track_count;
	playlist_mc.tracks_mc["track_"+playlist_mc.track_count+"_mc"].bg_mc.select_btn.onPress = function() {
		playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(1);
		last_track_index = track_index;
		playlist_mc.tracks_mc["track_"+track_index+"_mc"].bg_mc.gotoAndStop(1);
		track_index = this._parent.index;
		songClick = 1;
		mediaCheck();
	};
	playlist_mc.track_count++;
}
//scroll
mouseListener.onMouseWheel = function(wheel) {
	scrollbar_mc.v = (wheel/-3);
	scrollTracks();
};
scrollbar_mc.up_btn.onPress = function() {
	this._parent.v = -1;
	this._parent.onEnterFrame = scrollTracks;
};
scrollbar_mc.down_btn.onPress = function() {
	this._parent.v = 1;
	this._parent.onEnterFrame = scrollTracks;
};
scrollbar_mc.up_btn.onRelease = scrollbar_mc.down_btn.onRelease=function () {
	this._parent.onEnterFrame = null;
};
scrollbar_mc.handler_mc.drag_btn.onPress = function() {
	var scroll_top_limit = 19;
	var scroll_bottom_limit = scrollbar_mc.bg_mc._height-scrollbar_mc.handler_mc._height-2;
	this._parent.startDrag(false, this._parent._x, scroll_top_limit, this._parent._x, scroll_bottom_limit);
	this._parent.isdragging = true;
	this._parent.onEnterFrame = scrollTracks;
};
scrollbar_mc.handler_mc.drag_btn.onRelease = scrollbar_mc.handler_mc.drag_btn.onReleaseOutside=function () {
	stopDrag();
	this._parent.isdragging = false;
	this._parent.onEnterFrame = null;
};
function scrollTracks() {
	var scroll_top_limit = 19;
	var scroll_bottom_limit = scrollbar_mc.bg_mc._height-scrollbar_mc.handler_mc._height-2;
	var list_bottom_limit = 1;
	var list_top_limit = (1-Math.round(playlist_mc.tracks_mc._height))+Math.floor(playlist_mc.mask_mc._height/14)*14;
	if (playlist_mc.tracks_mc._height>playlist_mc.mask_mc._height) {
		if (scrollbar_mc.handler_mc.isdragging) {
			var percent = (scrollbar_mc.handler_mc._y-scroll_top_limit)/(scroll_bottom_limit-scroll_top_limit);
			playlist_mc.tracks_mc._y = (list_top_limit-list_bottom_limit)*percent+list_bottom_limit;
		} else {
			if (scrollbar_mc.v == -1) {
				if (playlist_mc.tracks_mc._y+14<list_bottom_limit) {
					playlist_mc.tracks_mc._y += 14;
				} else {
					playlist_mc.tracks_mc._y = list_bottom_limit;
				}
				var percent = (playlist_mc.tracks_mc._y-1)/(list_top_limit-1);
				scrollbar_mc.handler_mc._y = percent*(scroll_bottom_limit-scroll_top_limit)+scroll_top_limit;
			} else if (scrollbar_mc.v == 1) {
				if (playlist_mc.tracks_mc._y-14>list_top_limit) {
					playlist_mc.tracks_mc._y -= 14;
				} else {
					playlist_mc.tracks_mc._y = list_top_limit;
				}
				var percent = (playlist_mc.tracks_mc._y-1)/(list_top_limit-1);
				scrollbar_mc.handler_mc._y = percent*(scroll_bottom_limit-scroll_top_limit)+scroll_top_limit;
			}
		}
	}
}
function shuffleClick() {
	if (settings_mc.shuffle_btn._currentframe == 1) {
		shuffle = 0;
		settings_mc.shuffle_btn.gotoAndStop(2);
	} else if (settings_mc.shuffle_btn._currentframe == 2) {
		shuffle = 1;
		settings_mc.shuffle_btn.gotoAndStop(1);
	}
}
function repeatClick() {
	if (settings_mc.repeat_btn._currentframe == 1) {
		repeat = 0;
		settings_mc.repeat_btn.gotoAndStop(2);
	} else if (settings_mc.repeat_btn._currentframe == 2) {
		repeat = 1;
		settings_mc.repeat_btn.gotoAndStop(1);
	}
}
start_btn_mc.start_btn.onPress = function() {
	if (loaded == 1) {
		Autoplay();
	}
};
function Autoplay() {
	autoplay = 1;
	loadPlaylist();
}
function loadPlaylist() {
	if (_root.playlistLoaded != true) {
		track_display_mc.display_txt.text = LOADING_PLAYLIST_MSG;
		if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
			track_display_mc.onEnterFrame = scrollTitle;
		} else {
			track_display_mc.onEnterFrame = null;
			track_display_mc.display_txt._x = 0;
		}
		//playlist
		if (playlist_url) {
			playlist_xml.load(unescape(playlist_url));
		} else {
			//single track
			playlist_xml.parseXML(single_music_playlist);
			playlist_xml.onLoad(true);
		}
		_root.playlistLoaded = true;
	} else {
		mediaCheck();
	}
}
//first click - load playlist                                     
//main
Stage.scaleMode = "noScale";
Stage.align = "LT";
this.onResize = resizeUI;
Stage.addListener(this);
Mouse.addListener(mouseListener);
if (!player_title) {
	player_title = DEFAULT_WELCOME_MSG;
}
track_display_mc.display_txt.text = player_title;
track_display_mc.display_txt.autoSize = "left";
if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
	track_display_mc.onEnterFrame = scrollTitle;
} else {
	track_display_mc.onEnterFrame = null;
	track_display_mc.display_txt._x = 0;
}
function fillOther() {
	track_display_mc.display_txt.text = player_title;
	if (timedisplay) {
		time_mc._visible = true;
		time_mc.time_txt.text = "0:00";
		if (timeClick) {
			time_mc.useHandCursor = false;
			time_mc.onRelease = function() {
				if (timedisplay<4) {
					timedisplay++;
				} else {
					timedisplay = 2;
				}
			};
		}
	}
	if (_root.setup) {
		loadVariables("http://geekkid.net/jukebox/version.txt?"+Math.random(), this);
		update.onEnterFrame = function() {
			if (version) {
				if (version>_root.playerVersion) {
					this._visible = true;
					this.onPress = function() {
						getURL("http://geekkid.net/jukebox/");
					};
				}
				delete update.onEnterFrame;
			}
		};
	}
	//start to play automatically if parameter autoplay is present              
	if (_root.autoplay == "true" || _root.autoplay == 1 || _root.autoplay == true) {
		Autoplay();
	} else if (_root.autoload == "true" || _root.autoload == 1 || _root.autoload == true) {
		loadPlaylist();
	}
}
//load image 
function firstImage() {
	if (image && !(autoplay == 1 || autoplay == "true" || _root.autoplay == true)) {
		imageloadMC.text = "LOADING";
		imageloadMC._visible = true;
		//image from playlist
		cover_mc.content_mc.createEmptyMovieClip("photo", this.getNextHighestDepth());
		cover_mc.content_mc["photo"].loadMovie(image);
		cover_mc.onEnterFrame = function() {
			var image_load_percent = (cover_mc.content_mc["photo"].getBytesLoaded()/cover_mc.content_mc["photo"].getBytesTotal())*100;
			cover_mc.load_bar_mc._yscale = image_load_percent;
			if ((cover_mc.content_mc["photo"].getBytesLoaded()>4) && (image_load_percent == 100)) {
				//image loaded
				//make image fit
				imageloadMC._visible = false;
				cover_mc.content_mc["photo"]._width = cover_mc.load_bar_mc._width;
				cover_mc.content_mc["photo"]._height = cover_mc.load_bar_mc._height;
				delete cover_mc.onEnterFrame;
			}
		};
	}
}
//customized menu                                                                                                                                                    
var my_cm:ContextMenu = new ContextMenu();
my_cm.customItems.push(new ContextMenuItem("Play-", playTrack));
my_cm.customItems.push(new ContextMenuItem("Stop", stopTrack));
my_cm.customItems.push(new ContextMenuItem("Next", nextTrack));
my_cm.customItems.push(new ContextMenuItem("Previous", prevTrack));
my_cm.customItems.push(new ContextMenuItem("About...", function () {
	getURL(mainurl, "_blank");
}, true));
my_cm.hideBuiltInItems();
this.menu = my_cm;
resizeUI();
firstImage();
if (loadurl) {
	track_display_mc.display_txt.text = "Loading...";
	loadVariables(loadurl, _root);
	this.onEnterFrame = function() {
		if (loaded == 1 || getTimer()>3000) {
			loaded = 1;
			playerLoad();
			this.onEnterFrame = null;
		}
	};
} else {
	trace("noload");
	loaded = 1;
	playerLoad();
}
