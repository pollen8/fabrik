
playerVersion = "3.4";
// 60 seconds. If we take longer than 60 seconds to switch pages, assume the user 
// has started a new session on the site for music purposes.
resumeTimeout = 60 * 1000;
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
	__com_mochibot__("46030122", this, 10301);
	fillColor();
	fillDefaults();
	fillOther();
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
//repeat_playlist = true;
//playlist_size = 3;
//player_title = "customizeable title test"
//song_url = "http://downloads.betterpropaganda.com/music/Imperial_Teen-Ivanka_128.mp3";
//song_title = "Imperial Teen - Ivanka";
//autoload=true
//playlist_url = "testplaylist02.xspf"
//info_button_text = "Buy Album"
//playlist_url = "http://hideout.com.br/shows/radio-test.xspf";
//playlist_url = "http://cchits.ning.com/recent/xspf/?xn_auth=no";
//radio_mode = true;

//TBB
local_data = null;
already_resumed = false;

function fillColor() {
	if (colour) {
		color = colour;
	}
	if (color) {
		color = color.toUpperCase();
		if (!alpha) {
			alpha = 10;
		}
		timeColor = new Color(time_mc.time_txt);
		volumeColor = new Color(volume_mc);
		trackColor = new Color(track_display_mc);
		buttonsColor = new Color(buttons_mc);
		playColor = new Color(play_mc);
		bgColor = new Color(bg_mc);
		hexChars = "0123456789ABCDEF";
		red = "0x"+color.charAt(0)+color.charAt(1);
		grn = "0x"+color.charAt(2)+color.charAt(3);
		blu = "0x"+color.charAt(4)+color.charAt(5);
		var myColorTransform:Object = {ra:100, rb:red, ga:100, gb:grn, ba:100, bb:blu, aa:alpha, ab:50};
		timeColor.setTransform(myColorTransform);
		volumeColor.setTransform(myColorTransform);
		trackColor.setTransform(myColorTransform);
		bgColor.setTransform(myColorTransform);
		buttonsColor.setTransform(myColorTransform);
		playColor.setTransform(myColorTransform);
	}
}
//constants         
DEFAULT_WELCOME_MSG = "Jukebox Player";
function fillDefaults() {
	DEFAULT_PLAYLIST_URL = "http://geekkid.net/jukebox/xplaylist.xml";
	LOADING_PLAYLIST_MSG = "Loading...";
	DEFAULT_LOADED_PLAYLIST_MSG = "Loaded - click to start";
	DEFAULT_INFOBUTTON_TXT = "Artist Info";
	DEFAULT_MAIN_URL = "http://geekkid.net";
	//default playlist if none is passed through query string
	if (!playlist_url) {
		if (!song_url) {
			playlist_url = DEFAULT_PLAYLIST_URL;
		} else {
			single_music_playlist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><playlist version=\"1\" xmlns = \"http://xspf.org/ns/0/\"><trackList>";
			single_music_playlist += "<track><location>"+song_url+"</location>";
			if (song_title) {
				single_music_playlist += "<annotation>"+song_title+"</annotation>";
			}
			single_music_playlist += "</track></trackList></playlist>";
		}
	}
	if (!load_message) {
		load_message = DEFAULT_LOADED_PLAYLIST_MSG;
	}
	if (useId3 != 1 || useId3 != "true" || useId3 != true) {
		useId3 = 0;
	} else {
		useId3 = 1;
	}
	if (shuffle == 1 || shuffle == "true" || shuffle == true) {
		shuffle = 1;
		shuffle_mc.gotoAndStop(2);
	} else {
		shuffle = 0;
		shuffle_mc.gotoAndStop(1);
	}
	//TBB
	if (autoresume && (!already_resumed)) {
		trace("autoresume set");
		local_data = SharedObject.getLocal("xspf_player_data");
		local_data_good = true;
		if (!local_data.data.is_set) {
			trace("local_data.data.is_set not set, not autoresuming");
			local_data_good = false;
		} 
		// One-minute limit for autoresume. Prevents autoresuming from occurring
		// when you come back to the site as part of an entirely separate
		// visit, which doesn't feel natural. Autoresume is meant as a way
		// to bridge clicks on links within the site.
		when = new Date();
		now = when.getTime();
		if (now - local_data.data.stopped_at > resumeTimeout) {
			trace("resume data is old, assume new session");
			local_data_good = false;				
		} else {
			if (!playlist_url) {
				if (song_url != local_data.data.song_url) {
					trace("single song url does not match, not autoresuming");
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
			volume_mc.volume_bar_mc._xscale = volume_level;
			trace("autoresuming from " + start_track + "," + start_time + " volume level: " + volume_level);
			autoresumed = true;
			_root.autoplay = "true";
			_root.autoload = "true";
			songClick = 1;
		}
		already_resumed = true;
	} else {
		trace("autoresume not set");
	}
	if (!start_track) {
		start_track = 1;
	} else {
		// TBB: somehow this became a 0
		songClick = 1;
	}
	if (!volume_level) {
		volume_level = 100;
	}
	if (timedisplay<1 or timedisplay>4) {
		timedisplay = 1;
	}
	if (timedisplay == 1) {
		timeClick = 1;
		timedisplay = 2;
	}
	//info button              
	if (!info_button_text) {
		info_button_text = DEFAULT_INFOBUTTON_TXT;
	}
	track_index = start_track-1;
	playlist_xml = new XML();
	playlist_xml.ignoreWhite = true;
	playlist_xml.onLoad = playlistLoaded;
}
info_mc._visible = false;
shuffle_mc._visible = false;
//variables initialization        
playlist_array = [];
playlist_mc.track_count = 0;
pause_position = 0;
time_mc._visible = false;
mysound = new Sound(this);
playlist_listener = new Object();
playlist_list.addEventListener("change", playlist_listener);
//play_btn.onPress = playTrack;
//functions
//xml parser
function playlistLoaded(success) {
	if (success) {
		var root_node = this.firstChild;
		for (var node = root_node.firstChild; node != null; node=node.nextSibling) {
			if (node.nodeName == "title") {
				playlist_title = node.firstChild.nodeValue;
			}
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
						if (track_child.nodeName == "title") {
							track_obj.title = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "creator") {
							track_obj.creator = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "annotation") {
							track_obj.annotation = track_child.firstChild.nodeValue;
						}
						if (track_child.nodeName == "info" and !infourl) {
							track_obj.info = track_child.firstChild.nodeValue;
						} else if (infourl) {
							track_obj.info = infourl;
						}
						if (track_child.nodeName == "meta") {
							track_obj.type = track_child.firstChild.nodeValue;
						}
					}
					track_obj.label = (tracks_array.length+1)+". ";
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
						track_obj.label = " - ";
					}
					tracks_array.push(track_obj);
				}
			}
		}
		playlist_array = tracks_array;
		if (!playlist_size) {
			playlist_size = playlist_array.length;
		}
		if (autoplay == 1 || autoplay == "true" || autoplay == true) {
			trace("autoplay calling loadTrack");
			loadTrack();
			if (autoresume && local_data_good && local_data.data.stopped) {
				trace("autoresume has stopped flag");
				stopTrack();
			}
			if (autoresume && local_data_good && local_data.data.paused) {
				trace("autoresume has paused flag");
				pause_position = local_data.data.pause_position;
				mysound.stop();
				play_mc.gotoAndStop(1);
			}
		} else {
			start_btn_mc.start_btn.onPress = loadTrack;
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
playlist_listener.change = function(eventObject) {
	annotation_txt.text = playlist_list.selectedItem.annotation;
	location_txt.text = playlist_list.selectedItem.location;
};

function loadTrack() {
	trace("LoadTrack");
	// TBB: fix this to work in all situations and never recurse
	first_track = track_index;
	while (playlist_array[track_index].type == "video") {
		track_index ++;
		track_index %= playlist_size;
		if (track_index == first_track) {
			// No non-video tracks, get outta dodge
			return;
		}
	}
	mysound.stop();
	mysound.start();
	mysound.stop();
	if (timedisplay) {
		clearInterval(timeInterval);
		time_mc.time_txt.text = "0:00";
	}
	if (shuffle == 1 && songClick != 1) {
		track_index = random(playlist_size);
	}
	songClick = 0;
	if (playlist_array[track_index].location.indexOf(".xspf") != -1 or playlist_array[track_index].location.indexOf(".xml") != -1) {
		playlist_url = playlist_array[track_index].location;
		for (i=0; i<playlist_mc.track_count; ++i) {
			removeMovieClip(playlist_mc.tracks_mc["track_"+i+"_mc"]);
		}
		playlist_mc.track_count = 0;
		playlist_size = 0;
		track_index = 0;
		autoload = true;
		autoplay = true;
		trace("Loading a playlist track");
		loadPlaylist();
		return (0);
	}
	start_btn_mc.start_btn._visible = false;
	if (useId3 != 1 && playlist_array[track_index].label != " - ") {
		track_display_mc.display_txt.text = playlist_array[track_index].label;
	} else {
		track_display_mc.display_txt.text = mysound.id3.artist+" - "+mysound.id3.songtitle;
	}
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	// TBB: when you start loading a streaming sound it starts playing, period. You can't
	// have playback start paused. Solution: kill the volume until after the seek.
	if (start_time > 0) {
		mysound.setVolume(0);
	}
	mysound.loadSound(playlist_array[track_index].location, true);
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	play_mc.gotoAndStop(2);
	//info button
	if (playlist_array[track_index].info != undefined) {
		info_mc._visible = true;
		info_mc.info_btn.onPress = function() {
			getURL(playlist_array[track_index].info, "_blank");
		};
	} else {
		info_mc._visible = false;
	}
	if (timedisplay) {
		timeInterval = setInterval(timeDisplay, 200);
	}
	shuffle_mc._visible = true;
	resizeUI();
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
		// HACK doesnt need to set the volume at every enterframe
		// TBB: we don't do this while waiting for the initial seek of a resume operation
		// because it would play sound from the wrong part of the song
		if (!start_time) {
			mysound.setVolume(this.volume_level);
		}
		var load_percent = (mysound.getBytesLoaded()/mysound.getBytesTotal())*100;
		track_display_mc.loader_mc.load_bar_mc._xscale = load_percent;
		var time_percent = (mysound.position/mysound.duration)*100;
		track_display_mc.loader_mc.time_bar_mc.time_count._xscale = time_percent;
	};
	// TBB
	if (start_time > 0) {
		jumpToTime(start_time);
		start_time = 0;
	}
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
info_mc.info_btn.onRollOver = function() {
	holderText = track_display_mc.display_txt.text;
	track_display_mc.display_txt.text = "get info";
};
info_mc.info_btn.onRollOut = function() {
	track_display_mc.display_txt.text = holderText;
};
shuffle_mc.onPress = function() {
	if (shuffle == 1) {
		shuffle = 0;
		shuffle_mc.gotoAndStop(1);
	} else {
		shuffle = 1;
		shuffle_mc.gotoAndStop(2);
	}
};
shuffle_mc.onRollOver = function() {
	holderText = track_display_mc.display_txt.text;
	track_display_mc.display_txt.text = "shuffle";
	shuffle_mc.gotoAndStop(3);
};
shuffle_mc.onRollOut = function() {
	track_display_mc.display_txt.text = holderText;
	(shuffle == 1) ? shuffle_mc.gotoAndStop(2) : shuffle_mc.gotoAndStop(1);
};
buttons_mc.stop_btn.onRelease = stopTrack;
play_mc.play_btn.onRelease = playTrack;
buttons_mc.next_btn.onRelease = nextTrack;
buttons_mc.prev_btn.onRelease = prevTrack;
if (no_continue != 1 || no_continue != "true" || no_continue != true) {
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
	trace("jumptotime called for " + when);
	if (play_mc._currentframe == 2) {
		if (mysound.duration == 0) {
			trace("delaying jump");
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
		trace("Going for jump");
		mysound.start(when);
		mysound.setVolume(this.volume_level);
	}
	track_display_mc.loader_mc.time_bar_mc.time_count._xscale = 
 		((when / mysound.duration) * 1000) * 100; 
}

function timeChange() {
	if (play_mc._currentframe == 2) {
		var percent = (this._parent._xmouse/this._width);
		if (percent>100) {
			percent = 100;
		}
		if (percent<0) {
			percent = 0;
		}
		timePlace = (mysound.duration*percent)/1000;
		trace("timeChange");
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
	};
}

function resumeCheck() {
	// TBB: there is no reliable "atexit" - like feature in Flash,
	// so we have to make a note of our position often if autoresume
	// is going to work. This is cheap because Flash doesn't actually
	// write the shared ojbect to disk relentlessly - it does that
	// on exit.
	if (autoresume) {
		if (!seenResume) {
			seenResume = true;
		} 
		local_data.data.playlist_url = playlist_url;
		local_data.data.song_url = song_url;
		local_data.data.stop_track = track_index + 1;
		local_data.data.stop_time = mysound.position / 1000;
		local_data.data.volume_level = volume_level;
		when = new Date();
		local_data.data.stopped_at = when.getTime();
		local_data.data.is_set = true;
		if (!local_data.data.is_set) {
			trace("saving autoresume data");
		}
	}
}

function timeDisplay() {
	if (timedisplay == 2) {
		//countup
		time = mysound.position/1000;
	} else if (timedisplay == 3) {
		//countdown
		time = (mysound.duration-mysound.position)/1000;
	} else if (timedisplay == 4) {
		//total time
		time = mysound.duration/1000;
	} else {
		min = "0";
		sec = "00";
	}
	min = Math.floor(time/60);
	sec = Math.floor(time%60);
	sec = (sec<10) ? "0"+sec : sec;
	time_mc.time_txt.text = min+":"+sec;
}
function stopTrack() {
	// TBB 
	if (autoresume) {
		clearInterval(resumeInterval);
	}
	trace("stopTrack");
	if (autoresume) {
		local_data.data.stopped = true;
	}

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
		seekTrack(_root.pause_position);
		play_mc.gotoAndStop(2);
		if (timedisplay) {
			timeInterval = setInterval(timeDisplay, 200);
		}
	} else if (play_mc._currentframe == 2) {
		_root.pause_position = mysound.position;
		mysound.stop();
		play_mc.gotoAndStop(1);
		// TBB
		if (autoresume) {
			local_data.data.paused = true;
			local_data.data.pause_position = pause_position;
		}
	}
}
function seekTrack(p_offset:Number) {
	mysound.stop();
	mysound.start(int((p_offset)/1000), 1);
}

// TBB: cleaned this up, fixed bugs
function nextTrack() {
	trace("nextTrack");
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
		trace("calling stopTrack");
		stopTrack();
		return;
	}
	loadTrack();
}

function prevTrack() {
	last_track_index = track_index;
	if (track_index>0) {
		track_index--;
		loadTrack();
	}
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
		track_display_mc.mask_mc._width = track_display_mc.loader_mc._width-68;
	} else {
		track_display_mc.mask_mc._width = track_display_mc.loader_mc._width-46;
	}
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	if (info_mc._visible) {
		info_mc._x = Stage.width-info_mc._width-4;
	} else {
		info_mc._x = Stage.width-4;
	}
	shuffle_mc._x = info_mc._x-shuffle_mc._width+.5;
	volume_mc._x = shuffle_mc._x-volume_mc._width-1.5;
	time_mc._x = volume_mc._x-time_mc._width+5;
	update._x = time_mc._x-update._width;
	start_btn_mc._xscale = Stage.width;
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
function fillOther() {
	trace("fillOther");
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
	if (setup) {
		loadVariables("http://geekkid.net/jukebox/version.txt?"+Math.random(), "_root");
		update.onEnterFrame = function() {
			if (slimversion) {
				if (slimversion>_root.playerVersion) {
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
	if (autoplay == 1 || autoplay == "true" || autoplay == true) {
		start_btn_mc.start_btn.onPress();
		trace("autoplay present, calling loadPlaylist");
		// TBB: make sure first-click event didn't beat us to it
		if (!playlistLoadedOnce) {
			loadPlaylist();
		}
	} else if (autoload == 1 || autoload == "true" || autoload == true) {
		trace("autoload present, calling loadPlaylist");
		// TBB: make sure first-click event didn't beat us to it
		if (!playlistLoadedOnce) {
			loadPlaylist();
		}
	}
}
function loadPlaylist() {
	// TBB
	playlistLoadedOnce = true;
	trace("loadPlaylist beginning");
	
	track_display_mc.display_txt.text = LOADING_PLAYLIST_MSG;
	if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
		track_display_mc.onEnterFrame = scrollTitle;
	} else {
		track_display_mc.onEnterFrame = null;
		track_display_mc.display_txt._x = 0;
	}
	//playlist
	if (playlist_url) {
		trace("loading playlist");
		playlist_xml.load(playlist_url);
	} else {
		//single track
		playlist_xml.parseXML(single_music_playlist);
		trace("before explicit onLoad call");
		playlist_xml.onLoad(true);
	}
}
// first click - load playlist.
// TBB: only if we're not up and running already. Otherwise we'll
// restart autoresuming songs and/or do some stuttering.
start_btn_mc.start_btn.onPress = function() {
	if (!playlistLoadedOnce) {
		autoplay = true;
		trace("first click calling loadPlaylist");
		loadPlaylist();
	}
};
//main
Stage.scaleMode = "noScale";
Stage.align = "LT";
this.onResize = resizeUI;
Stage.addListener(this);
if (!player_title) {
	player_title = DEFAULT_WELCOME_MSG;
}
track_display_mc.display_txt.autoSize = "left";
track_display_mc.display_txt.text = player_title;
if (track_display_mc.display_txt._width>track_display_mc.mask_mc._width) {
	track_display_mc.onEnterFrame = scrollTitle;
} else {
	track_display_mc.onEnterFrame = null;
	track_display_mc.display_txt._x = 0;
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
